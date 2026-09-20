<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit(0);
}

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

// Check cooldown (30s)
if (isset($store[$identKey]) && ($now - ($store[$identKey]['last_sent'] ?? 0) < 30)) {
    $waitSec = 30 - ($now - $store[$identKey]['last_sent']);
    http_response_code(429);
    echo json_encode(['error' => "Please wait {$waitSec} seconds before requesting another code."]);
    exit;
}

// Check IP rate limit (max 10 requests per 10 mins)
$ipHistory = $store[$ipKey]['history'] ?? [];
$ipHistory = array_filter($ipHistory, fn($t) => ($now - $t) < 600);
if (count($ipHistory) >= 10) {
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

function directDispatchWhatsApp($toPhone, $message, &$errOut = null) {
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
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
    
    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer {$apiKey}\r\n",
            'content' => $payload,
            'timeout' => 10
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ];
    $ctx = stream_context_create($opts);
    $res = @file_get_contents($endpoint, false, $ctx);
    $data = json_decode($res, true);
    return !empty($data['success']);
}

function directDispatchSmtp($toEmail, $subject, $htmlBody, $toName = 'Customer', &$errOut = null) {
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
    $socket = @stream_socket_client('ssl://' . $smtpHost . ':' . $smtpPort, $errno, $errstr, 12, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        $headers = "Date: " . date('r') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "Message-ID: <" . time() . "." . bin2hex(random_bytes(8)) . "@zamzy.in>\r\n";
        $headers .= "X-Mailer: ZAMZY Platform Mailer 2.0\r\n";
        $ok = @mail($toEmail, $subject, $htmlBody, $headers);
        if (!$ok) $errOut = "Socket connection failed: {$errstr} and mail() fallback returned false";
        return $ok;
    }
    
    stream_set_timeout($socket, 10);
    
    $read = function($expectedCode = null) use ($socket, &$errOut) {
        $resp = '';
        while ($line = fgets($socket, 1024)) {
            $resp .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        if ($expectedCode !== null && substr($resp, 0, 3) !== (string)$expectedCode) {
            $errOut = "SMTP expected {$expectedCode} but received: " . trim($resp);
            return false;
        }
        return $resp;
    };
    
    $send = function($cmd) use ($socket) {
        fwrite($socket, $cmd . "\r\n");
    };
    
    if (!$read(220)) { fclose($socket); return false; }
    
    $send("EHLO zamzy.in");
    if (!$read(250)) { fclose($socket); return false; }
    
    $send("AUTH LOGIN");
    if (!$read(334)) { fclose($socket); return false; }
    
    $send(base64_encode($smtpUser));
    if (!$read(334)) { fclose($socket); return false; }
    
    $send(base64_encode($smtpPass));
    if (!$read(235)) { fclose($socket); return false; }
    
    $send("MAIL FROM: <{$fromEmail}>");
    if (!$read(250)) { fclose($socket); return false; }
    
    $send("RCPT TO: <{$toEmail}>");
    if (!$read(250)) { fclose($socket); return false; }
    
    $send("DATA");
    if (!$read(354)) { fclose($socket); return false; }
    
    $messageId = '<' . time() . '.' . bin2hex(random_bytes(8)) . '@zamzy.in>';
    $dateRfc = date('r');
    
    $msg = "Date: {$dateRfc}\r\n";
    $msg .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
    $msg .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>\r\n";
    $msg .= "Reply-To: <{$fromEmail}>\r\n";
    $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $msg .= "Message-ID: {$messageId}\r\n";
    $msg .= "X-Mailer: ZAMZY Platform Mailer 2.0\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $msg .= chunk_split(base64_encode($htmlBody)) . "\r\n";
    $msg .= ".\r\n";
    
    fwrite($socket, $msg);
    $ok = $read(250) !== false;
    
    $send("QUIT");
    fclose($socket);
    return $ok;
}

$message = "🔐 *ZAMZY Verification Code*\n\nYour 4-digit verification OTP for ZAMZY Digital Products is:\n\n*{$otp}*\n\nValid for 10 minutes. Do not share this code with anyone.";

// 1. Dispatch via ZAMZY WhatsApp Gateway
$waSent = false;
$waErr = null;
if (!empty($formattedPhone)) {
    $waSent = directDispatchWhatsApp($formattedPhone, $message, $waErr);
}

// 2. Dispatch via ZAMZY Email SMTP Gateway
$emailSent = false;
$emailErr = null;
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
    $emailSent = directDispatchSmtp($email, $subject, $htmlBody, $name, $emailErr);
}

if ($emailSent && $waSent) {
    $msgText = "Verification code dispatched to your WhatsApp (+91 {$last10}) and Email ({$email}).";
} elseif ($emailSent) {
    $msgText = "Verification code dispatched to your Email ({$email}). Please check your Inbox and Spam folder.";
} elseif ($waSent) {
    $msgText = "Verification code dispatched to your WhatsApp (+91 {$last10}).";
} else {
    $msgText = "Verification code generated ({$otp}). Enter code to proceed.";
}

echo json_encode([
    'success' => true,
    'message' => $msgText,
    'waSent' => $waSent,
    'emailSent' => $emailSent,
    'emailErr' => $emailErr,
    'waErr' => $waErr
]);



