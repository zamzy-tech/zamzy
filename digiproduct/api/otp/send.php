<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
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

// Generate 6-digit OTP
$otp = (string)rand(100000, 999999);
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

function directDispatchWhatsApp($toPhone, $message) {
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
        curl_close($ch);
        $data = json_decode($response, true);
        return ($httpCode >= 200 && $httpCode < 300 && !empty($data['success']));
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

function directDispatchSmtp($toEmail, $subject, $htmlBody, $toName = 'Customer') {
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
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        return @mail($toEmail, $subject, $htmlBody, $headers);
    }
    
    stream_set_timeout($socket, 10);
    
    $read = function($expectedCode = null) use ($socket) {
        $resp = '';
        while ($line = fgets($socket, 1024)) {
            $resp .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        if ($expectedCode !== null && substr($resp, 0, 3) !== (string)$expectedCode) return false;
        return $resp;
    };
    
    $send = function($cmd) use ($socket) {
        fwrite($socket, $cmd . "\r\n");
    };
    
    if (!$read(220)) { fclose($socket); return false; }
    
    $send("EHLO " . ($_SERVER['SERVER_NAME'] ?? 'zamzy.in'));
    $read(250);
    
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
    
    $msg = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
    $msg .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>\r\n";
    $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
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

$message = "🔐 *ZAMZY Verification Code*\n\nYour 6-digit verification OTP for ZAMZY Digital Products is:\n\n*{$otp}*\n\nValid for 10 minutes. Do not share this code with anyone.";

// 1. Dispatch via ZAMZY WhatsApp Gateway
$waSent = false;
if (!empty($formattedPhone)) {
    $waSent = directDispatchWhatsApp($formattedPhone, $message);
}

// 2. Dispatch via ZAMZY Email SMTP Gateway
$emailSent = false;
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $subject = "🔐 Your ZAMZY Verification Code: {$otp}";
    $htmlBody = <<<HTML
    <div style="font-family:sans-serif; background:#0f172a; color:#f8fafc; padding:30px; border-radius:10px; max-width:500px; margin:auto;">
        <h2 style="color:#818cf8; margin-top:0;">ZAMZY Digital Products Verification</h2>
        <p>Hi {$name},</p>
        <p>Your one-time verification code for accessing your downloads / checkout is:</p>
        <div style="font-size:32px; font-weight:bold; letter-spacing:6px; color:#34d399; margin:20px 0; padding:15px; background:#1e293b; text-align:center; border-radius:8px; border:1px solid #334155;">
            {$otp}
        </div>
        <p style="font-size:12px; color:#94a3b8;">This code is valid for 10 minutes. If you did not request this, please ignore this email.</p>
    </div>
HTML;
    $emailSent = directDispatchSmtp($email, $subject, $htmlBody, $name);
}

$destinations = [];
if ($waSent) $destinations[] = 'WhatsApp (+91 ' . $last10 . ')';
if ($emailSent) $destinations[] = 'Email (' . $email . ')';

$msgText = count($destinations) > 0 
    ? "Verification code sent to " . implode(' and ', $destinations) . "."
    : "Verification code dispatched. Please enter the 6-digit OTP.";

echo json_encode([
    'success' => true,
    'message' => $msgText,
    'waSent' => $waSent,
    'emailSent' => $emailSent
]);



