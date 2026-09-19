<?php
/**
 * ZAMZY Native SMTP Mailer & Automated Deliverables Engine
 * Connects directly to authenticated SSL/TLS SMTP socket without external dependencies.
 */

require_once __DIR__ . '/db.php';

/**
 * Send an email using SMTP direct socket connection with fallback.
 * 
 * @param string $toEmail Recipient email address
 * @param string $subject Email subject line
 * @param string $htmlBody HTML content
 * @param string $toName Optional recipient name
 * @param string &$debugLog Output variable to capture SMTP protocol log
 * @return array ['success' => bool, 'message' => string, 'log' => string]
 */
function sendSmtpEmail($toEmail, $subject, $htmlBody, $toName = '', &$debugLog = null) {
    $debugLog = "";
    $log = function($msg) use (&$debugLog) {
        $debugLog .= date('[H:i:s] ') . $msg . "\n";
    };

    $toEmail = trim($toEmail);
    if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        $log("Error: Invalid recipient email address: '{$toEmail}'");
        return ['success' => false, 'message' => 'Invalid recipient email address', 'log' => $debugLog];
    }

    // Retrieve settings
    $smtpHost = getSetting('smtp_host', 'mail.zamzy.in');
    $smtpPort = intval(getSetting('smtp_port', '465'));
    $smtpSecure = strtolower(trim(getSetting('smtp_secure', 'ssl'))); // ssl, tls, none
    $smtpUser = getSetting('smtp_username', 'no-reply@zamzy.in');
    $smtpPass = getSetting('smtp_password', 'shacartc_zamzy');
    $fromEmail = getSetting('smtp_from_email', 'no-reply@zamzy.in');
    $fromName = getSetting('smtp_from_name', 'ZAMZY Learning');

    $log("Initiating SMTP connection to {$smtpHost}:{$smtpPort} (Secure: {$smtpSecure})...");

    $remoteHost = $smtpHost;
    if ($smtpPort == 465 || $smtpSecure === 'ssl') {
        $remoteHost = 'ssl://' . $smtpHost;
    }

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $errno = 0;
    $errstr = '';
    $timeout = 12; // seconds

    $socket = @stream_socket_client($remoteHost . ':' . $smtpPort, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

    if (!$socket) {
        $log("Socket connection failed: {$errstr} ({$errno}). Attempting fallback via PHP mail()...");
        return fallbackMailSend($toEmail, $toName, $fromEmail, $fromName, $subject, $htmlBody, $debugLog);
    }

    stream_set_timeout($socket, $timeout);

    $readResponse = function($expectedCode = null) use ($socket, $log) {
        $response = "";
        while ($line = fgets($socket, 1024)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $log("SERVER: " . trim($response));
        if ($expectedCode !== null) {
            $code = substr($response, 0, 3);
            if ($code !== (string)$expectedCode) {
                return false;
            }
        }
        return $response;
    };

    $sendCommand = function($cmd, $hide = false) use ($socket, $log) {
        if ($hide) {
            $log("CLIENT: [CREDENTIAL HIDDEN]");
        } else {
            $log("CLIENT: " . trim($cmd));
        }
        fwrite($socket, $cmd . "\r\n");
    };

    // 1. Initial Greeting (220)
    $banner = $readResponse(220);
    if ($banner === false) {
        fclose($socket);
        $log("Banner error. Attempting fallback mail().");
        return fallbackMailSend($toEmail, $toName, $fromEmail, $fromName, $subject, $htmlBody, $debugLog);
    }

    // 2. EHLO
    $sendCommand("EHLO zamzy.in");
    $ehloRes = $readResponse();

    // 3. STARTTLS if port 587 and not ssl
    if ($smtpPort == 587 || $smtpSecure === 'tls') {
        $sendCommand("STARTTLS");
        $tlsRes = $readResponse(220);
        if ($tlsRes !== false) {
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $sendCommand("EHLO zamzy.in");
            $readResponse();
        }
    }

    // 4. AUTH LOGIN
    $sendCommand("AUTH LOGIN");
    $authRes = $readResponse(334);
    if ($authRes === false) {
        fclose($socket);
        $log("AUTH LOGIN rejected. Attempting fallback mail().");
        return fallbackMailSend($toEmail, $toName, $fromEmail, $fromName, $subject, $htmlBody, $debugLog);
    }

    // Send Username (Base64)
    $sendCommand(base64_encode($smtpUser), true);
    $userRes = $readResponse(334);
    if ($userRes === false) {
        fclose($socket);
        $log("Username rejected by mail server.");
        return ['success' => false, 'message' => 'SMTP Username rejected by mail server', 'log' => $debugLog];
    }

    // Send Password (Base64)
    $sendCommand(base64_encode($smtpPass), true);
    $passRes = $readResponse(235);
    if ($passRes === false) {
        fclose($socket);
        $log("Authentication failed: invalid SMTP credentials.");
        return ['success' => false, 'message' => 'SMTP Authentication failed: Invalid username/password', 'log' => $debugLog];
    }

    $log("Authentication successful as {$smtpUser}!");

    // 5. MAIL FROM
    $sendCommand("MAIL FROM: <" . $fromEmail . ">");
    if ($readResponse(250) === false) {
        fclose($socket);
        return fallbackMailSend($toEmail, $toName, $fromEmail, $fromName, $subject, $htmlBody, $debugLog);
    }

    // 6. RCPT TO
    $sendCommand("RCPT TO: <" . $toEmail . ">");
    if ($readResponse(250) === false) {
        fclose($socket);
        return ['success' => false, 'message' => "Recipient <{$toEmail}> rejected by mail server", 'log' => $debugLog];
    }

    // 7. DATA
    $sendCommand("DATA");
    if ($readResponse(354) === false) {
        fclose($socket);
        return fallbackMailSend($toEmail, $toName, $fromEmail, $fromName, $subject, $htmlBody, $debugLog);
    }

    // Build MIME Headers
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
    $encodedToName = !empty($toName) ? '=?UTF-8?B?' . base64_encode($toName) . '?= ' : '';

    $messageId = '<' . time() . '.' . bin2hex(random_bytes(8)) . '@' . parse_url('http://' . $smtpHost, PHP_URL_HOST) . '>';

    $headers = [];
    $headers[] = "Date: " . date('r');
    $headers[] = "From: {$encodedFromName} <{$fromEmail}>";
    $headers[] = "To: {$encodedToName}<{$toEmail}>";
    $headers[] = "Subject: {$encodedSubject}";
    $headers[] = "Message-ID: {$messageId}";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "Content-Transfer-Encoding: base64";
    $headers[] = "X-Mailer: ZAMZY Automated Engine v2.0";

    $payload = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.";

    $sendCommand($payload);
    $dataRes = $readResponse(250);

    // QUIT
    $sendCommand("QUIT");
    @fclose($socket);

    if ($dataRes !== false) {
        $log("SUCCESS: Email successfully dispatched to {$toEmail} via authenticated SMTP!");
        return ['success' => true, 'message' => "Email sent successfully to {$toEmail}", 'log' => $debugLog];
    }

    return fallbackMailSend($toEmail, $toName, $fromEmail, $fromName, $subject, $htmlBody, $debugLog);
}

/**
 * Fallback to PHP native mail() if socket is firewalled
 */
function fallbackMailSend($toEmail, $toName, $fromEmail, $fromName, $subject, $htmlBody, &$debugLog) {
    $debugLog .= date('[H:i:s] ') . "Triggering PHP mail() fallback...\n";
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $toFormatted = !empty($toName) ? "{$toName} <{$toEmail}>" : $toEmail;
    $sent = @mail($toFormatted, $encodedSubject, $htmlBody, $headers);

    if ($sent) {
        $debugLog .= date('[H:i:s] ') . "Fallback mail() succeeded.\n";
        return ['success' => true, 'message' => "Email sent via server mail daemon fallback", 'log' => $debugLog];
    }

    $debugLog .= date('[H:i:s] ') . "Fallback mail() also failed.\n";
    return ['success' => false, 'message' => "Failed to deliver email through both SMTP socket and mail()", 'log' => $debugLog];
}

/**
 * Generates the responsive, cyber-styled webinar access HTML email
 */
function buildWebinarDeliveryEmailHtml($student) {
    $name = htmlspecialchars($student['full_name'] ?? 'Student');
    $regCode = htmlspecialchars($student['reg_code'] ?? 'ZAM-FSW-' . strtoupper(substr(uniqid(), -6)));
    $utr = htmlspecialchars($student['utr_reference'] ?? 'VERIFIED_GATEWAY');
    $amount = number_format(floatval($student['amount'] ?? 96), 2);

    $webinarTitle = htmlspecialchars(getSetting('webinar_title', 'Full Stack Web Development Live Webinar'));
    $schedule = htmlspecialchars(getSetting('webinar_schedule', 'Live Batch: Weekends 6:00 PM - 8:30 PM IST'));
    $meetingLink = trim(getSetting('webinar_meeting_link', 'https://meet.google.com/qmv-xyza-web'));
    $whatsappLink = trim(getSetting('webinar_whatsapp_link', 'https://chat.whatsapp.com/sample-zamzy-fullstack'));
    $resourcesRaw = getSetting('webinar_resources', "• Complete Full Stack Architecture Blueprint & Curriculum (PDF)\n• GitHub Repositories & Starter Kits\n• Interview Cheatsheets & Free Tooling Access");
    $notes = nl2br(htmlspecialchars(getSetting('webinar_email_notes', 'Please join 5 minutes prior to the scheduled start time. Ensure you have Google Meet / Chrome installed and your laptop ready with VS Code.')));

    $txId = $student['transaction_id'] ?? '';
    $invoicePdfUrl = (!empty($txId) && (strpos($txId, 'fg_') === 0 || strpos($txId, 'FG') === 0))
        ? "https://famgateway.in/transaction-details.php?id=" . urlencode($txId) . "&download=pdf"
        : "";

    // Format resources with line breaks and links
    $resourcesLines = explode("\n", str_replace("\r", "", $resourcesRaw));
    $resourcesHtml = "";
    foreach ($resourcesLines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Auto linkify URLs inside resources
        $formattedLine = preg_replace(
            '~(https?://[^\s<>]+)~i',
            '<a href="$1" target="_blank" style="color:#00ffcc; text-decoration:underline; font-weight:600;">$1</a>',
            htmlspecialchars($line)
        );
        $resourcesHtml .= "<li style=\"margin-bottom:8px; color:#e2e8f0; font-size:14px; line-height:1.6;\">{$formattedLine}</li>";
    }

    $invoiceBlock = "";
    if (!empty($invoicePdfUrl)) {
        $invoiceBlock = <<<INVOICE_HTML
                    <!-- Official Tax Receipt & Invoice Download Card -->
                    <div style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.3); border-radius:8px; padding:18px 20px; text-align:center; margin-bottom:28px;">
                        <div style="color:#38bdf8; font-size:13px; font-weight:700; font-family:'Courier New', monospace; letter-spacing:1px; margin-bottom:6px; text-transform:uppercase;">
                            📄 Official GST / Tax Invoice &amp; Payment Receipt
                        </div>
                        <p style="color:#94a3b8; font-size:12px; margin:0 0 14px 0;">
                            Your payment is government-compliant and MSME verified (UDYAM-BR-28-0050000). Click below to download your signed PDF receipt.
                        </p>
                        <a href="{$invoicePdfUrl}" target="_blank" style="display:inline-block; background:#0284c7; color:#ffffff; text-decoration:none; font-weight:700; font-size:13px; letter-spacing:0.5px; padding:10px 22px; border-radius:6px; box-shadow:0 2px 10px rgba(2,132,199,0.35);">
                            📥 Download Official PDF Invoice
                        </a>
                    </div>
INVOICE_HTML;
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$webinarTitle} - Seat Confirmed &amp; Invoice</title>
</head>
<body style="margin:0; padding:0; background-color:#06060c; font-family:'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#e2e8f0; -webkit-font-smoothing:antialiased;">
    <div style="background-color:#06060c; padding:30px 15px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px; background:#0f0f1c; border-radius:12px; border:1px solid #1e293b; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.8);">
            
            <!-- Header Glow Banner with Brand Logo -->
            <tr>
                <td style="padding:32px 30px 24px 30px; background:linear-gradient(135deg, #0d1117 0%, #111927 100%); border-bottom:1px solid rgba(6,182,212,0.25); text-align:center;">
                    <div style="margin-bottom:12px;">
                        <img src="https://zamzy.in/images/logo.png" alt="ZAMZY" style="height:48px; max-width:240px; object-fit:contain;" />
                    </div>
                    <div style="display:inline-block; background:rgba(0,255,204,0.15); border:1px solid #00ffcc; color:#00ffcc; font-size:11px; font-weight:700; font-family:'Courier New', monospace; letter-spacing:2px; text-transform:uppercase; padding:5px 14px; border-radius:30px;">
                        ✓ SEAT UNLOCKED &amp; CONFIRMED
                    </div>
                    <h1 style="color:#ffffff; font-size:22px; font-weight:700; margin:16px 0 6px 0; line-height:1.3;">
                        {$webinarTitle}
                    </h1>
                    <p style="color:#94a3b8; font-size:13px; margin:0; font-family:'Courier New', monospace;">
                        {$schedule}
                    </p>
                </td>
            </tr>

            <!-- Body Content -->
            <tr>
                <td style="padding:30px;">
                    <p style="font-size:16px; color:#f8fafc; margin-top:0; margin-bottom:18px; line-height:1.6;">
                        Hi <strong style="color:#38bdf8;">{$name}</strong>,
                    </p>
                    <p style="font-size:14px; color:#cbd5e1; margin-bottom:24px; line-height:1.6;">
                        Congratulations! Your payment of <strong style="color:#00ffcc;">₹{$amount}</strong> has been successfully verified. Your seat has been reserved for the live workshop. Below are your live meeting credentials, exclusive community invite, and learning materials.
                    </p>

                    <!-- Student Registration Details Card -->
                    <table width="100%" cellpadding="0" cellspacing="0" style="background:#16192b; border:1px solid rgba(255,255,255,0.08); border-radius:8px; margin-bottom:24px;">
                        <tr>
                            <td style="padding:16px 20px;">
                                <table width="100%" cellpadding="4" cellspacing="0" style="font-size:13px; font-family:'Courier New', monospace;">
                                    <tr>
                                        <td style="color:#94a3b8; width:40%;">Registration ID:</td>
                                        <td style="color:#38bdf8; font-weight:700;">{$regCode}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#94a3b8;">Payment Status:</td>
                                        <td style="color:#00ffcc; font-weight:700;">CONFIRMED / VERIFIED</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#94a3b8;">UTR / Txn Ref:</td>
                                        <td style="color:#f8fafc;">{$utr}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#94a3b8;">Amount Paid:</td>
                                        <td style="color:#f8fafc;">₹{$amount} INR</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    {$invoiceBlock}

                    <!-- Live Meeting CTA Button -->
                    <?php if (!empty(\$meetingLink)): ?>
                    <div style="text-align:center; margin-bottom:24px;">
                        <a href="{$meetingLink}" target="_blank" style="display:block; background:linear-gradient(90deg, #06b6d4 0%, #3b82f6 100%); color:#ffffff; text-decoration:none; font-weight:700; font-size:15px; letter-spacing:0.5px; padding:15px 24px; border-radius:8px; box-shadow:0 4px 20px rgba(6,182,212,0.4); text-transform:uppercase;">
                            🎥 Click Here To Join Live Meeting Room
                        </a>
                        <div style="font-size:11px; color:#64748b; margin-top:8px; font-family:'Courier New', monospace; word-break:break-all;">
                            Direct Link: <a href="{$meetingLink}" style="color:#38bdf8;">{$meetingLink}</a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- WhatsApp Community Link -->
                    <?php if (!empty(\$whatsappLink)): ?>
                    <div style="text-align:center; margin-bottom:28px;">
                        <a href="{$whatsappLink}" target="_blank" style="display:block; background:#25D366; color:#06230f; text-decoration:none; font-weight:800; font-size:14px; letter-spacing:0.5px; padding:13px 20px; border-radius:8px; box-shadow:0 4px 15px rgba(37,211,102,0.25); text-transform:uppercase;">
                            💬 Join Exclusive WhatsApp Community
                        </a>
                        <div style="font-size:11px; color:#64748b; margin-top:6px; font-family:'Courier New', monospace; word-break:break-all;">
                            Access announcements &amp; code solutions: <a href="{$whatsappLink}" style="color:#25D366;">{$whatsappLink}</a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Course Resources & PDFs -->
                    <div style="background:#131626; border-left:4px solid #8b5cf6; padding:18px 20px; border-radius:0 8px 8px 0; margin-bottom:24px;">
                        <div style="font-size:13px; font-weight:700; text-transform:uppercase; color:#c4b5fd; letter-spacing:1px; margin-bottom:10px; font-family:'Courier New', monospace;">
                            📚 Course Materials, Starter Kits &amp; Documents
                        </div>
                        <ul style="margin:0; padding-left:18px;">
                            {$resourcesHtml}
                        </ul>
                    </div>

                    <!-- Important Instructions -->
                    <div style="background:rgba(255,255,255,0.03); border:1px dashed rgba(255,255,255,0.12); padding:16px 20px; border-radius:8px; margin-bottom:24px;">
                        <div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#94a3b8; letter-spacing:1px; margin-bottom:8px; font-family:'Courier New', monospace;">
                            ⚡ Important Prep Guidelines
                        </div>
                        <div style="font-size:13px; color:#94a3b8; line-height:1.6;">
                            {$notes}
                        </div>
                    </div>

                    <p style="font-size:13px; color:#64748b; line-height:1.6; margin-bottom:0;">
                        Need help or have questions? Simply reply directly to this email or reach us at <a href="mailto:no-reply@zamzy.in" style="color:#38bdf8;">no-reply@zamzy.in</a>.
                    </p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="padding:22px 30px; background:#0a0a14; border-top:1px solid rgba(255,255,255,0.06); text-align:center;">
                    <div style="font-size:12px; color:#475569; font-family:'Courier New', monospace;">
                        &copy; <?= date('Y') ?> ZAMZY Technologies. All rights reserved.<br>
                        Autonomous Education &amp; Cloud Engineering Infrastructure
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;
}

/**
 * Triggers the automated delivery email for a webinar registration.
 * 
 * @param int|array $registration Registration ID or Database associative array row
 * @return array Result of the email dispatch
 */
function sendWebinarDeliveryEmail($registration) {
    $pdo = getDbConnection();
    $student = null;

    if (is_numeric($registration)) {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM `zamzy_webinar_registrations` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => $registration]);
            $student = $stmt->fetch();
        }
    } else if (is_array($registration)) {
        $student = $registration;
    }

    if (!$student || empty($student['email'])) {
        return ['success' => false, 'message' => 'No valid registration or email found'];
    }

    $toEmail = $student['email'];
    $toName = $student['full_name'] ?? '';
    $subject = "🎟️ Seat Confirmed: " . getSetting('webinar_title', 'Full Stack Web Development Live Webinar') . " — Access Links & Materials";

    $htmlBody = buildWebinarDeliveryEmailHtml($student);

    $debugLog = "";
    $result = sendSmtpEmail($toEmail, $subject, $htmlBody, $toName, $debugLog);

    if ($result['success'] && !empty($student['id']) && $pdo) {
        try {
            $upd = $pdo->prepare("UPDATE `zamzy_webinar_registrations` SET `email_sent` = 1 WHERE `id` = :id");
            $upd->execute([':id' => $student['id']]);
        } catch (Exception $e) {}
    }

    return $result;
}

/**
 * Triggers the automated WhatsApp notification dispatch for a confirmed webinar registration.
 * 
 * @param int|array $registration Registration ID or Database associative array row
 * @return array Result of the WhatsApp API dispatch
 */
function sendWebinarDeliveryWhatsApp($registration) {
    $pdo = getDbConnection();
    $student = null;

    if (is_numeric($registration)) {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM `zamzy_webinar_registrations` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => $registration]);
            $student = $stmt->fetch();
        }
    } else if (is_array($registration)) {
        $student = $registration;
    }

    if (!$student || empty($student['phone'])) {
        return ['success' => false, 'message' => 'No valid student record or phone number found'];
    }

    $enabled = getSetting('whatsapp_api_enabled', '1');
    if ($enabled === '0' || $enabled === 0 || $enabled === false) {
        return ['success' => false, 'message' => 'WhatsApp automated notification is disabled in admin settings'];
    }

    $apiUrl = getSetting('whatsapp_api_endpoint', 'https://zamzy.in/api/whatsapp.php');
    $apiKey = getSetting('whatsapp_api_key', '3c5b81fc69022511c682a14156e1c1fd');

    $studentName = $student['full_name'] ?? 'Student';
    $regCode = $student['reg_code'] ?? 'ZMW-REG';
    $amount = $student['amount'] ?? getSetting('webinar_price', '96');
    $utr = $student['utr_reference'] ?? $student['transaction_id'] ?? 'CONFIRMED';
    $schedule = getSetting('webinar_schedule', 'Live Batch: Weekends 6:00 PM - 8:30 PM IST');
    $webinarTitle = getSetting('webinar_title', 'Full Stack Web Development Live Webinar');
    $meetingLink = getSetting('webinar_meeting_link', '');
    $whatsappLink = getSetting('webinar_whatsapp_link', '');
    $resources = getSetting('webinar_resources', '');
    $prepNotes = getSetting('webinar_email_notes', '');

    $customTemplate = getSetting('whatsapp_msg_template', '');
    $txId = $student['transaction_id'] ?? '';
    $invoicePdfUrl = (!empty($txId) && (strpos($txId, 'fg_') === 0 || strpos($txId, 'FG') === 0))
        ? "https://famgateway.in/transaction-details.php?id=" . urlencode($txId) . "&download=pdf"
        : "";

    if (empty($customTemplate)) {
        $msg = "🎉 *Registration Confirmed — ZAMZY Live Webinar!*\n\n"
             . "Dear *" . $studentName . "*,\n"
             . "Congratulations! Your seat for the *" . $webinarTitle . "* has been confirmed.\n\n"
             . "📌 *Registration Code:* " . $regCode . "\n"
             . "💰 *Amount Paid:* ₹" . $amount . " (Ref: " . $utr . ")\n"
             . "📅 *Schedule:* " . $schedule . "\n\n";

        if (!empty($whatsappLink)) {
            $msg .= "🔗 *Official WhatsApp Community Group:*\n" . $whatsappLink . "\n\n";
        }
        if (!empty($meetingLink)) {
            $msg .= "🎥 *Live Session Room Link:*\n" . $meetingLink . "\n\n";
        }
        if (!empty($invoicePdfUrl)) {
            $msg .= "📄 *Official PDF Tax Invoice / Receipt:*\n" . $invoicePdfUrl . "\n\n";
        }
        if (!empty($resources)) {
            $msg .= "📚 *Course Materials & Starter Kits:*\n" . $resources . "\n\n";
        }
        if (!empty($prepNotes)) {
            $msg .= "⚡ *Prep Guidelines:* " . $prepNotes . "\n\n";
        }
        $msg .= "_Please join the WhatsApp group immediately to receive timely session alerts and access instructions._\n\n"
              . "Warm Regards,\n*ZAMZY Academy*";
    } else {
        $replacements = [
            '{name}' => $studentName,
            '{reg_code}' => $regCode,
            '{amount}' => $amount,
            '{utr}' => $utr,
            '{schedule}' => $schedule,
            '{webinar_title}' => $webinarTitle,
            '{meeting_link}' => $meetingLink,
            '{whatsapp_link}' => $whatsappLink,
            '{invoice_url}' => $invoicePdfUrl,
            '{resources}' => $resources,
            '{notes}' => $prepNotes
        ];
        $msg = str_replace(array_keys($replacements), array_values($replacements), $customTemplate);
    }

    // Clean phone number (Ensure country code, e.g. 919876543210)
    $cleanPhone = preg_replace('/[^0-9]/', '', $student['phone']);
    if (strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }

    $payload = json_encode([
        'to' => $cleanPhone,
        'message' => $msg,
        'type' => 'general'
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $isSuccess = ($httpCode >= 200 && $httpCode < 300);
    $responseData = json_decode($response, true);

    if ($isSuccess && isset($responseData['success']) && $responseData['success'] === true) {
        if (!empty($student['id']) && $pdo) {
            try {
                $upd = $pdo->prepare("UPDATE `zamzy_webinar_registrations` SET `whatsapp_sent` = 1 WHERE `id` = :id");
                $upd->execute([':id' => $student['id']]);
            } catch (Exception $e) {}
        }
        return ['success' => true, 'message' => 'WhatsApp message dispatched successfully', 'response' => $responseData];
    } else {
        $errMsg = $responseData['error'] ?? $responseData['message'] ?? $curlErr ?? ("HTTP " . $httpCode);
        return ['success' => false, 'message' => $errMsg, 'raw' => $response];
    }
}

/**
 * Direct dispatch of custom WhatsApp message via REST API
 */
function sendWhatsAppMessageDirect($toPhone, $message, $endpoint = null, $apiKey = null) {
    if (empty($endpoint)) {
        $endpoint = getSetting('whatsapp_api_endpoint', 'https://zamzy.in/api/whatsapp.php');
    }
    if (empty($apiKey)) {
        $apiKey = getSetting('whatsapp_api_key', '3c5b81fc69022511c682a14156e1c1fd');
    }

    $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);
    if (strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }

    $payload = json_encode([
        'to' => $cleanPhone,
        'message' => $message,
        'type' => 'general'
    ]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['success']) && $responseData['success'] === true) {
        return ['success' => true, 'message' => 'WhatsApp message dispatched successfully', 'response' => $responseData];
    }

    $errMsg = $responseData['error'] ?? $responseData['message'] ?? $curlErr ?? ("HTTP " . $httpCode);
    return ['success' => false, 'error' => $errMsg, 'raw' => $response];
}

/**
 * Helper to construct direct payment gateway URL for reminders
 * Prioritizes dynamic Razorpay Payment Links (via API or Setting) & FamGateway links
 */
function getStudentPaymentLink($student) {
    // 1. Check if Razorpay API keys are configured and create a live Razorpay Payment Link dynamically
    $keyId = trim(getSetting('razorpay_key_id', ''));
    $keySecret = trim(getSetting('razorpay_key_secret', ''));

    if (!empty($keyId) && !empty($keySecret) && !empty($student['reg_code'])) {
        $amountInPaise = intval(round(floatval($student['amount'] ?? 149) * 100));
        if ($amountInPaise <= 0) $amountInPaise = 14900;

        $baseUrl = rtrim(getSetting('site_url', 'https://zamzy.in'), '/');
        $callbackUrl = $baseUrl . '/fullstack-webinar.php?pay_reg=' . urlencode($student['reg_code']);

        $payload = [
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'accept_partial' => false,
            'reference_id' => $student['reg_code'],
            'description' => getSetting('webinar_title', 'Full Stack Web Development Live Webinar'),
            'customer' => [
                'name' => !empty($student['full_name']) ? $student['full_name'] : 'Student',
                'email' => !empty($student['email']) ? $student['email'] : 'student@zamzy.in',
                'contact' => !empty($student['phone']) ? preg_replace('/[^0-9]/', '', $student['phone']) : '919876543210'
            ],
            'notify' => [
                'sms' => false,
                'email' => false
            ],
            'reminder_enable' => false,
            'notes' => [
                'reg_code' => $student['reg_code']
            ],
            'callback_url' => $callbackUrl,
            'callback_method' => 'get'
        ];

        $ch = curl_init('https://api.razorpay.com/v1/payment_links');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_USERPWD => $keyId . ':' . $keySecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        if ($res) {
            $data = json_decode($res, true);
            if (!empty($data['short_url'])) {
                return $data['short_url'];
            }
        }
    }

    // 2. Configured custom Razorpay payment page URL from system settings
    $customLink = trim(getSetting('razorpay_payment_link', getSetting('webinar_payment_link', '')));
    if (!empty($customLink)) {
        $sep = (strpos($customLink, '?') !== false) ? '&' : '?';
        return $customLink . $sep . 'reg_code=' . urlencode($student['reg_code'] ?? '') . '&amount=' . urlencode($student['amount'] ?? '149');
    }

    // 3. Fallback: Instant Checkout landing page URL with auto Razorpay modal trigger
    $baseUrl = rtrim(getSetting('site_url', 'https://zamzy.in'), '/');
    return $baseUrl . '/fullstack-webinar.php?pay_reg=' . urlencode($student['reg_code'] ?? '');
}

/**
 * Builds the cyber-styled HTML payment reminder email with direct checkout link
 */
function buildWebinarReminderEmailHtml($student) {
    $name = htmlspecialchars($student['full_name'] ?? 'Student');
    $regCode = htmlspecialchars($student['reg_code'] ?? '');
    $amount = number_format(floatval($student['amount'] ?? 96), 2);
    $webinarTitle = htmlspecialchars(getSetting('webinar_title', 'Full Stack Web Development Live Webinar'));
    $schedule = htmlspecialchars(getSetting('webinar_schedule', 'Live Batch: Weekends 6:00 PM - 8:30 PM IST'));
    
    $paymentUrl = getStudentPaymentLink($student);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⌛ Complete Your Webinar Registration - ZAMZY</title>
</head>
<body style="margin:0; padding:0; background-color:#06060c; font-family:'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#e2e8f0; -webkit-font-smoothing:antialiased;">
    <div style="background-color:#06060c; padding:30px 15px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px; background:#0f0f1c; border-radius:12px; border:1px solid #1e293b; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.8);">
            
            <!-- Header Glow Banner -->
            <tr>
                <td style="padding:32px 30px 24px 30px; background:linear-gradient(135deg, #0d1117 0%, #1a1630 100%); border-bottom:1px solid rgba(255,190,11,0.25); text-align:center;">
                    <div style="margin-bottom:12px;">
                        <img src="https://zamzy.in/images/logo.png" alt="ZAMZY" style="height:48px; max-width:240px; object-fit:contain;" />
                    </div>
                    <div style="display:inline-block; background:rgba(255,190,11,0.15); border:1px solid #ffbe0b; color:#ffbe0b; font-size:11px; font-weight:700; font-family:'Courier New', monospace; letter-spacing:2px; text-transform:uppercase; padding:5px 14px; border-radius:30px;">
                        ⏳ ACTION REQUIRED: PAYMENT PENDING
                    </div>
                    <h1 style="color:#ffffff; font-size:22px; font-weight:700; margin:16px 0 6px 0; line-height:1.3;">
                        {$webinarTitle}
                    </h1>
                    <p style="color:#94a3b8; font-size:13px; margin:0; font-family:'Courier New', monospace;">
                        {$schedule}
                    </p>
                </td>
            </tr>

            <!-- Body Content -->
            <tr>
                <td style="padding:30px;">
                    <p style="font-size:16px; color:#f8fafc; margin-top:0; margin-bottom:18px; line-height:1.6;">
                        Hi <strong style="color:#ffbe0b;">{$name}</strong>,
                    </p>
                    <p style="font-size:14px; color:#cbd5e1; margin-bottom:24px; line-height:1.6;">
                        We noticed your registration for the <strong>{$webinarTitle}</strong> is still <strong>PENDING</strong>. Seats are filling fast, and your slot is currently reserved for a limited time.
                    </p>

                    <!-- Student Registration Details Card -->
                    <table width="100%" cellpadding="0" cellspacing="0" style="background:#16192b; border:1px solid rgba(255,190,11,0.2); border-radius:8px; margin-bottom:24px;">
                        <tr>
                            <td style="padding:16px 20px;">
                                <table width="100%" cellpadding="4" cellspacing="0" style="font-size:13px; font-family:'Courier New', monospace;">
                                    <tr>
                                        <td style="color:#94a3b8; width:40%;">Registration ID:</td>
                                        <td style="color:#38bdf8; font-weight:700;">{$regCode}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#94a3b8;">Status:</td>
                                        <td style="color:#ffbe0b; font-weight:700;">⏳ PENDING PAYMENT</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#94a3b8;">Payable Fee:</td>
                                        <td style="color:#00ffcc; font-weight:700;">₹{$amount} INR</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!-- Payment Link CTA Button -->
                    <div style="text-align:center; margin-bottom:28px;">
                        <a href="{$paymentUrl}" target="_blank" style="display:block; background:linear-gradient(90deg, #9d4edd 0%, #00ffcc 100%); color:#06060c; text-decoration:none; font-weight:800; font-size:16px; letter-spacing:0.5px; padding:16px 24px; border-radius:8px; box-shadow:0 4px 20px rgba(157,78,221,0.4); text-transform:uppercase;">
                            💳 Click Here To Complete Payment (₹{$amount})
                        </a>
                        <div style="font-size:11px; color:#64748b; margin-top:8px; font-family:'Courier New', monospace; word-break:break-all;">
                            Direct Payment Link: <a href="{$paymentUrl}" style="color:#38bdf8;">{$paymentUrl}</a>
                        </div>
                    </div>

                    <!-- Benefits Card -->
                    <div style="background:#131626; border-left:4px solid #00ffcc; padding:18px 20px; border-radius:0 8px 8px 0; margin-bottom:24px;">
                        <div style="font-size:13px; font-weight:700; text-transform:uppercase; color:#00ffcc; letter-spacing:1px; margin-bottom:10px; font-family:'Courier New', monospace;">
                            🎁 What You Unlock Immediately Upon Payment:
                        </div>
                        <ul style="margin:0; padding-left:18px; color:#e2e8f0; font-size:13px; line-height:1.7;">
                            <li>Verified Google Meet Room Link &amp; Live Workshop Access</li>
                            <li>Official Certificate of Participation (ZAMZY Academy Verified)</li>
                            <li>Full Stack Architecture Blueprint &amp; Source Code PDFs</li>
                            <li>VIP WhatsApp Discussion &amp; Doubt Clearing Community</li>
                        </ul>
                    </div>

                    <p style="font-size:13px; color:#64748b; line-height:1.6; margin-bottom:0;">
                        Having trouble paying? Reply directly to this email or chat with our coordinator on WhatsApp at <a href="tel:+916369517740" style="color:#38bdf8;">+91 6369517740</a>.
                    </p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="padding:22px 30px; background:#0a0a14; border-top:1px solid rgba(255,255,255,0.06); text-align:center;">
                    <div style="font-size:12px; color:#475569; font-family:'Courier New', monospace;">
                        &copy; <?= date('Y') ?> ZAMZY Technologies. All rights reserved.
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;
}

/**
 * Triggers a payment reminder (Email + WhatsApp) for a pending registration.
 * 
 * @param int|array $registration Registration ID or Database associative array row
 * @param string $triggerReason '10min', 'daily', or 'manual'
 * @return array ['success' => bool, 'email' => array, 'whatsapp' => array]
 */
function sendWebinarPaymentReminder($registration, $triggerReason = 'automated') {
    $pdo = getDbConnection();
    $student = null;

    if (is_numeric($registration)) {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM `zamzy_webinar_registrations` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => $registration]);
            $student = $stmt->fetch();
        }
    } else if (is_array($registration)) {
        $student = $registration;
    }

    if (!$student || empty($student['id']) || strtolower($student['payment_status'] ?? '') !== 'pending') {
        return ['success' => false, 'message' => 'Registration is not pending or not found'];
    }

    $paymentUrl = getStudentPaymentLink($student);

    $name = $student['full_name'] ?? 'Student';
    $regCode = $student['reg_code'] ?? '';
    $amount = number_format(floatval($student['amount'] ?? 96), 2);
    $webinarTitle = getSetting('webinar_title', 'Full Stack Web Development Live Webinar');

    // 1. Dispatch Payment Reminder Email
    $emailResult = ['success' => false];
    if (!empty($student['email'])) {
        $subject = "⌛ Payment Pending: Complete your registration for {$webinarTitle} (Reg Code: {$regCode})";
        $htmlBody = buildWebinarReminderEmailHtml($student);
        $debugLog = "";
        $emailResult = sendSmtpEmail($student['email'], $subject, $htmlBody, $name, $debugLog);
    }

    // 2. Dispatch Payment Reminder WhatsApp Message
    $waResult = ['success' => false];
    if (!empty($student['phone'])) {
        $waMsg = "⏳ *Payment Pending — ZAMZY Full Stack Webinar*\n\n"
               . "Dear *" . $name . "*, 👋\n\n"
               . "Your registration (*Code: " . $regCode . "*) for the *" . $webinarTitle . "* (₹" . $amount . ") is currently *PENDING*.\n\n"
               . "🚀 Complete your payment now to reserve your seat and unlock live session links & study materials:\n\n"
               . "👉 *Direct Payment Link:*\n" . $paymentUrl . "\n\n"
               . "💡 _If you face any payment issues or need assistance, simply reply to this WhatsApp message._\n\n"
               . "Warm Regards,\n*ZAMZY Academy*";

        $waResult = sendWhatsAppMessageDirect($student['phone'], $waMsg);
    }

    $isDispatched = ($emailResult['success'] || $waResult['success']);

    // 3. Update Database Tracking
    if ($isDispatched && $pdo) {
        try {
            $is10minFlag = ($triggerReason === '10min' || empty($student['reminded_10min'])) ? 1 : intval($student['reminded_10min'] ?? 0);
            $lastDate = $student['last_reminder_date'] ?? null;
            $todayStr = date('Y-m-d');

            $newCountToday = ($lastDate === $todayStr) ? (intval($student['reminders_count_today'] ?? 0) + 1) : 1;
            $newTotal = intval($student['reminders_total'] ?? 0) + 1;

            $upd = $pdo->prepare("UPDATE `zamzy_webinar_registrations` 
                                   SET `reminded_10min` = :r10,
                                       `reminders_count_today` = :rcnt,
                                       `last_reminder_date` = :rdate,
                                       `last_reminder_at` = NOW(),
                                       `reminders_total` = :rtot
                                   WHERE `id` = :id");
            $upd->execute([
                ':r10' => $is10minFlag,
                ':rcnt' => $newCountToday,
                ':rdate' => $todayStr,
                ':rtot' => $newTotal,
                ':id' => $student['id']
            ]);
        } catch (Exception $e) {}
    }

    return [
        'success' => $isDispatched,
        'email' => $emailResult,
        'whatsapp' => $waResult,
        'message' => $isDispatched ? 'Payment reminder dispatched successfully' : 'Could not send reminder via email or WhatsApp'
    ];
}

/**
 * Automates background payment reminder checks for pending registrations.
 * - Sends 10-minute post-registration follow-up link
 * - Sends twice-daily reminders thereafter
 * 
 * @param PDO $pdo Database connection handle
 * @return array Audit summary of reminders sent
 */
function runWebinarPaymentRemindersCheck($pdo = null) {
    if (!$pdo) {
        $pdo = getDbConnection();
    }
    if (!$pdo) {
        return ['success' => false, 'message' => 'No database connection'];
    }

    $dispatched10min = 0;
    $dispatchedDaily = 0;
    $errors = [];

    // A. Process 10-minute post-registration reminders
    try {
        $sql10 = "SELECT * FROM `zamzy_webinar_registrations` 
                  WHERE `payment_status` = 'pending' 
                    AND `reminded_10min` = 0 
                    AND `created_at` <= (NOW() - INTERVAL 10 MINUTE)
                  ORDER BY `id` ASC LIMIT 20";
        $stmt10 = $pdo->query($sql10);
        $pending10 = $stmt10->fetchAll();

        foreach ($pending10 as $row) {
            $res = sendWebinarPaymentReminder($row, '10min');
            if ($res['success']) {
                $dispatched10min++;
            }
        }
    } catch (Exception $e) {
        $errors[] = "10min check error: " . $e->getMessage();
    }

    // B. Process twice-daily payment reminders
    try {
        $sqlDaily = "SELECT * FROM `zamzy_webinar_registrations` 
                     WHERE `payment_status` = 'pending' 
                       AND `created_at` <= (NOW() - INTERVAL 10 MINUTE)
                       AND (
                           `last_reminder_date` IS NULL 
                           OR `last_reminder_date` < CURDATE()
                           OR (`last_reminder_date` = CURDATE() AND `reminders_count_today` < 2)
                       )
                       AND (
                           `last_reminder_at` IS NULL 
                           OR `last_reminder_at` <= (NOW() - INTERVAL 4 HOUR)
                       )
                     ORDER BY `id` ASC LIMIT 30";
        $stmtDaily = $pdo->query($sqlDaily);
        $pendingDaily = $stmtDaily->fetchAll();

        foreach ($pendingDaily as $row) {
            $res = sendWebinarPaymentReminder($row, 'daily');
            if ($res['success']) {
                $dispatchedDaily++;
            }
        }
    } catch (Exception $e) {
        $errors[] = "Daily check error: " . $e->getMessage();
    }

    // Store last execution timestamp in settings
    try {
        setSetting('last_reminder_cron_run', date('Y-m-d H:i:s'));
    } catch (Exception $e) {}

    return [
        'success' => true,
        'dispatched_10min' => $dispatched10min,
        'dispatched_daily' => $dispatchedDaily,
        'total_dispatched' => ($dispatched10min + $dispatchedDaily),
        'errors' => $errors,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

/**
 * Dispatch verification OTP code via SMTP Email
 * Sends the exact same OTP code as WhatsApp to the user's email address.
 */
function sendOtpEmail($toEmail, $otpCode, $context = 'verification', $recipientName = '') {
    $toEmail = trim($toEmail);
    if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid recipient email address for OTP dispatch'];
    }

    $subject = "🔐 Verification Code: {$otpCode} - ZAMZY Platform";
    $displayName = !empty($recipientName) ? htmlspecialchars($recipientName) : 'User';

    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>ZAMZY Verification Code</title>
    </head>
    <body style="margin:0; padding:0; background-color:#090d16; font-family:\'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color:#e2e8f0;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px; margin:24px auto; background:#0f172a; border-radius:14px; border:1px solid #1e293b; overflow:hidden; box-shadow:0 12px 30px rgba(0,0,0,0.5);">
            <tr>
                <td style="background:linear-gradient(135deg,#7c3aed,#2563eb); padding:24px 30px; text-align:center;">
                    <h1 style="margin:0; font-size:24px; color:#ffffff; font-weight:800; letter-spacing:1px;">ZAMZY PLATFORM</h1>
                    <p style="margin:4px 0 0 0; color:#e0e7ff; font-size:13px; font-weight:500;">Security & Identity Verification</p>
                </td>
            </tr>
            <tr>
                <td style="padding:32px 30px; text-align:center;">
                    <h2 style="margin:0 0 12px 0; color:#ffffff; font-size:18px; font-weight:700;">Hello ' . $displayName . ',</h2>
                    <p style="margin:0 0 24px 0; color:#94a3b8; font-size:14px; line-height:1.6;">
                        Your verification code for <strong>' . htmlspecialchars($context) . '</strong> is below. Please enter this code on the form to verify your identity.
                    </p>
                    <div style="background:#090d16; border:2px dashed #3b82f6; border-radius:12px; padding:20px 28px; display:inline-block; margin-bottom:24px;">
                        <span style="font-family:\'Courier New\', Courier, monospace; font-size:36px; font-weight:800; color:#38bdf8; letter-spacing:8px;">' . htmlspecialchars($otpCode) . '</span>
                    </div>
                    <p style="margin:0 0 10px 0; color:#f59e0b; font-size:13px; font-weight:600;">
                        ⏱️ This code is valid for 10 minutes only.
                    </p>
                    <p style="margin:0; color:#64748b; font-size:12px;">
                        If you did not request this verification code, please disregard this email.
                    </p>
                </td>
            </tr>
            <tr>
                <td style="background:#090d16; padding:16px 30px; text-align:center; border-top:1px solid #1e293b; color:#64748b; font-size:11px;">
                    © ' . date('Y') . ' ZAMZY Platform. All rights reserved.
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return sendSmtpEmail($toEmail, $subject, $htmlBody, $displayName);
}


