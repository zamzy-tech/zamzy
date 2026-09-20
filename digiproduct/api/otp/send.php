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

// Generate 6-digit OTP
$otp = (string)rand(100000, 999999);
$expiresAt = time() + 600; // 10 minutes

// Save OTP to JSON store
$dataDir = __DIR__ . '/../../data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0777, true);
}
$storeFile = $dataDir . '/otp_store.json';
$store = file_exists($storeFile) ? json_decode(file_get_contents($storeFile), true) : [];

// Purge expired
$now = time();
foreach ($store as $p => $info) {
    if (isset($info['expiresAt']) && $info['expiresAt'] < $now) {
        unset($store[$p]);
    }
}

$entry = [
    'otp' => $otp,
    'expiresAt' => $expiresAt,
    'verified' => false,
    'email' => $email,
    'phone' => $last10
];

if (!empty($cleanPhone)) $store[$cleanPhone] = $entry;
if (!empty($last10)) $store[$last10] = $entry;
if (!empty($formattedPhone)) $store[$formattedPhone] = $entry;
if (!empty($email)) $store[strtolower($email)] = $entry;
if (!empty($identifier)) $store[strtolower(trim($identifier))] = $entry;

file_put_contents($storeFile, json_encode($store));

// Include ZAMZY Mailer and WhatsApp gateway infrastructure
$mailerPath = __DIR__ . '/../../../mailer.php';
if (file_exists($mailerPath)) {
    require_once $mailerPath;
}

$message = "🔐 *ZAMZY Verification Code*\n\nYour 6-digit verification OTP for ZAMZY Digital Products is:\n\n*{$otp}*\n\nValid for 10 minutes. Do not share this code with anyone.";

// 1. Dispatch via ZAMZY WhatsApp Gateway
$waSent = false;
if (!empty($formattedPhone) && function_exists('sendWhatsAppMessageDirect')) {
    $waRes = sendWhatsAppMessageDirect($formattedPhone, $message);
    $waSent = !empty($waRes['success']);
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
    if (function_exists('sendSmtpEmail')) {
        $emailRes = sendSmtpEmail($email, $subject, $htmlBody, $name);
        $emailSent = !empty($emailRes['success']);
    }
}

$destinations = [];
if ($waSent) $destinations[] = 'WhatsApp (+91 ' . $last10 . ')';
if ($emailSent) $destinations[] = 'Email (' . $email . ')';

$msgText = count($destinations) > 0 
    ? "Verification code sent to " . implode(' and ', $destinations) . "."
    : "Verification code generated: {$otp}. Please enter the 6-digit OTP.";

echo json_encode([
    'success' => true,
    'message' => $msgText,
    'waSent' => $waSent,
    'emailSent' => $emailSent,
    'otp' => ($waSent || $emailSent) ? null : $otp
]);


