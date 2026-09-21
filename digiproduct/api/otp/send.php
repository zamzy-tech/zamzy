<?php
// Fast Output Buffering for instant response
if (!ob_get_level()) {
    ob_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit(0);
}

// Ignore client abort so background email/whatsapp dispatch completes cleanly
ignore_user_abort(true);
set_time_limit(30);

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$identifier = trim($input['identifier'] ?? $input['phone'] ?? $input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$name = trim($input['name'] ?? $input['customerName'] ?? 'Customer');

if (empty($identifier) && empty($phone) && empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please provide a valid Mobile Number or Email Address.']);
    exit;
}

if (empty($phone) && !empty($identifier) && !str_contains($identifier, '@')) {
    $phone = $identifier;
}
if (empty($email) && !empty($identifier) && str_contains($identifier, '@')) {
    $email = $identifier;
}

$cleanPhone = preg_replace('/\D/', '', $phone);
$last10 = (strlen($cleanPhone) >= 10) ? substr($cleanPhone, -10) : '';
$formattedPhone = $last10 ? ('91' . $last10) : '';

// Initialize JSON store
$dataDir = __DIR__ . '/../../data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0777, true);
}
$storeFile = $dataDir . '/otp_store.json';
$store = file_exists($storeFile) ? json_decode(file_get_contents($storeFile), true) : [];
if (!is_array($store)) $store = [];
$now = time();

// Purge expired
foreach ($store as $p => $info) {
    if (isset($info['expiresAt']) && $info['expiresAt'] < $now) {
        unset($store[$p]);
    }
}

// Security: Rate limiting & Resend Cooldown
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$ipKey = 'ip_' . md5($ip);
$identKey = 'ident_' . md5(strtolower($cleanPhone ?: $email ?: $identifier));

// Check cooldown (15s for snappy UX)
if (isset($store[$identKey]) && ($now - ($store[$identKey]['last_sent'] ?? 0) < 15)) {
    $waitSec = 15 - ($now - $store[$identKey]['last_sent']);
    http_response_code(429);
    echo json_encode(['error' => "Please wait {$waitSec} seconds before requesting another code."]);
    exit;
}

// Check IP rate limit (max 15 requests per 10 mins)
$ipHistory = $store[$ipKey]['history'] ?? [];
$ipHistory = array_filter($ipHistory, fn($t) => ($now - $t) < 600);
if (count($ipHistory) >= 15) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many OTP requests. Please wait 10 minutes before trying again.']);
    exit;
}
$ipHistory[] = $now;
$store[$ipKey] = ['history' => $ipHistory, 'expiresAt' => $now + 600];

// Generate 4-digit OTP
$otp = (string)rand(1000, 9999);
$expiresAt = $now + 600; // 10 minutes

$entry = [
    'otp' => $otp,
    'expiresAt' => $expiresAt,
    'verified' => false,
    'attempts' => 0,
    'last_sent' => $now,
    'email' => $email,
    'phone' => $last10
];

if (!empty($cleanPhone)) $store[$cleanPhone] = $entry;
if (!empty($last10)) $store[$last10] = $entry;
if (!empty($formattedPhone)) $store[$formattedPhone] = $entry;
if (!empty($email)) $store[strtolower($email)] = $entry;
if (!empty($identifier)) $store[strtolower(trim($identifier))] = $entry;
$store[$identKey] = ['last_sent' => $now, 'expiresAt' => $now + 600];

file_put_contents($storeFile, json_encode($store));

// Formulate instant user response text
$hasPhone = !empty($last10);
$hasEmail = !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);

if ($hasPhone && $hasEmail) {
    $msgText = "Verification code dispatched to your WhatsApp (+91 {$last10}) and Email ({$email}). Code: {$otp}";
} elseif ($hasPhone) {
    $msgText = "Verification code dispatched to your WhatsApp (+91 {$last10}). Code: {$otp}";
} elseif ($hasEmail) {
    $msgText = "Verification code dispatched to your Email ({$email}). Code: {$otp}";
} else {
    $msgText = "Verification code: {$otp}. Enter code to proceed.";
}

$responsePayload = json_encode([
    'success' => true,
    'message' => $msgText,
    'otp' => $otp,
    'phone' => $last10,
    'email' => $email
]);

// Send instant HTTP response to browser so UI transitions in < 30ms
header('Content-Length: ' . strlen($responsePayload));
header('Connection: close');
echo $responsePayload;

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    while (ob_get_level() > 0) {
        @ob_end_flush();
    }
    @flush();
    if (function_exists('session_write_close')) {
        @session_write_close();
    }
}

// ─── BACKGROUND DISPATCH HELPERS (HIGH PERFORMANCE) ───────────────

function directDispatchWhatsAppFast($toPhone, $message, &$errOut = null) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);
    if (strlen($cleanPhone) === 10) {
        $cleanPhone = '91' . $cleanPhone;
    }
    $endpoint = 'https://zamzy.in/api/whatsapp.php';
    $apiKey = '3c5b81fc69022511c682a14156e1c1fd';
    
    $payload = json_encode([
        'to' => $cleanPhone,
        'message' => $message,
        'type' => 'general'
    ]);
    
    if (function_exists('curl_init')) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_TCP_NODELAY => 1,
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && !empty($data['success'])) {
            return true;
        }
        $errOut = $data['error'] ?? $curlErr ?? ("HTTP " . $httpCode);
        return false;
    }
    return false;
}

function directDispatchSmtpFast($toEmail, $subject, $htmlBody, $toName = 'Customer', &$errOut = null) {
    $smtpHost = 'mail.zamzy.in';
    $smtpPort = 465;
    $smtpUser = 'no-reply@zamzy.in';
    $smtpPass = 'shacartc_zamzy';
    $fromEmail = 'no-reply@zamzy.in';
    $fromName = 'ZAMZY Digital Products';
    
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);
    
    $errno = 0; $errstr = '';
    $socket = @stream_socket_client('ssl://' . $smtpHost . ':' . $smtpPort, $errno, $errstr, 2.5, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        // Fallback to PHP mail()
        $headers = "Date: " . date('r') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "Message-ID: <" . time() . "." . bin2hex(random_bytes(8)) . "@zamzy.in>\r\n";
        $headers .= "X-Mailer: ZAMZY Fast Mailer 2.0\r\n";
        return @mail($toEmail, $subject, $htmlBody, $headers);
    }
    
    stream_set_timeout($socket, 2.5);
    
    $readMultiline = function() use ($socket) {
        $res = '';
        while ($l = fgets($socket, 1024)) {
            $res .= $l;
            if (isset($l[3]) && $l[3] === ' ') break;
        }
        return $res;
    };
    
    // 1. Read greeting banner
    $readMultiline();
    
    // 2. EHLO
    fwrite($socket, "EHLO zamzy.in\r\n");
    $readMultiline();
    
    // 3. Fast AUTH PLAIN (single step handshake)
    $plainAuth = base64_encode("\0" . $smtpUser . "\0" . $smtpPass);
    fwrite($socket, "AUTH PLAIN " . $plainAuth . "\r\n");
    $authResp = $readMultiline();
    
    if (substr($authResp, 0, 3) !== '235') {
        // Fallback to AUTH LOGIN if PLAIN failed
        fwrite($socket, "AUTH LOGIN\r\n");
        fgets($socket, 1024);
        fwrite($socket, base64_encode($smtpUser) . "\r\n");
        fgets($socket, 1024);
        fwrite($socket, base64_encode($smtpPass) . "\r\n");
        $readMultiline();
    }
    
    // 4. PIPELINE: MAIL FROM + RCPT TO + DATA
    $batch = "MAIL FROM:<{$fromEmail}>\r\nRCPT TO:<{$toEmail}>\r\nDATA\r\n";
    fwrite($socket, $batch);
    $readMultiline(); // MAIL FROM
    $readMultiline(); // RCPT TO
    $readMultiline(); // DATA
    
    $messageId = '<' . time() . '.' . bin2hex(random_bytes(8)) . '@zamzy.in>';
    $dateRfc = date('r');
    
    $msg = "Date: {$dateRfc}\r\n";
    $msg .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
    $msg .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>\r\n";
    $msg .= "Reply-To: <{$fromEmail}>\r\n";
    $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $msg .= "Message-ID: {$messageId}\r\n";
    $msg .= "X-Mailer: ZAMZY Fast Mailer 2.0\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $msg .= chunk_split(base64_encode($htmlBody)) . "\r\n";
    $msg .= ".\r\nQUIT\r\n";
    
    fwrite($socket, $msg);
    $readMultiline(); // 250 OK
    $readMultiline(); // 221 Bye
    
    fclose($socket);
    return true;
}

// ─── EXECUTE BACKGROUND DISPATCH ───────────────────────────────────

$message = "🔐 *ZAMZY Verification Code*\n\nYour 4-digit verification OTP for ZAMZY Digital Products is:\n\n*{$otp}*\n\nValid for 10 minutes. Do not share this code with anyone.";

// 1. Dispatch WhatsApp
if (!empty($formattedPhone)) {
    directDispatchWhatsAppFast($formattedPhone, $message);
}

// 2. Dispatch Email
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $subject = "🔐 Your ZAMZY Verification Code: {$otp}";
    $htmlBody = <<<HTML
    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background:#0B0B0F; color:#f8fafc; padding:32px 24px; border-radius:12px; max-width:520px; margin:20px auto; border:1px solid rgba(139,92,246,0.3); box-shadow:0 10px 30px rgba(0,0,0,0.8);">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:11px; font-weight:700; color:#06B6D4; letter-spacing:2px; text-transform:uppercase;">ZAMZY DIGITAL PRODUCTS</div>
            <h2 style="color:#FFFFFF; margin:8px 0 0; font-size:22px; font-weight:800;">Verification Code</h2>
        </div>
        <p style="font-size:14px; color:#e2e8f0; line-height:1.6; margin-bottom:16px;">Hello <strong>{$name}</strong>,</p>
        <p style="font-size:14px; color:#cbd5e1; line-height:1.6;">Use the verification code below to verify your purchase and unlock immediate access:</p>
        <div style="font-size:36px; font-weight:800; letter-spacing:8px; color:#8B5CF6; margin:24px 0; padding:18px; background:#111116; text-align:center; border-radius:10px; border:1px solid #7C3AED; font-family:monospace;">
            {$otp}
        </div>
        <p style="font-size:12px; color:#94a3b8; line-height:1.5;">This one-time code is valid for <strong>10 minutes</strong>. If you did not request this verification, you can safely disregard this email.</p>
        <hr style="border:none; border-top:1px solid rgba(255,255,255,0.08); margin:24px 0 16px;">
        <div style="font-size:11px; color:#64748b; text-align:center;">
            &copy; ZAMZY Technologies &bull; Secure Digital Delivery Gateway
        </div>
    </div>
HTML;
    directDispatchSmtpFast($email, $subject, $htmlBody, $name);
}
