<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$name = trim($input['name'] ?? $input['customerName'] ?? '');

if (empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'WhatsApp phone number is required.']);
    exit;
}

// Clean phone number (strip non-digits)
$cleanPhone = preg_replace('/\D/', '', $phone);
if (strlen($cleanPhone) < 10) {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter a valid 10-digit mobile number.']);
    exit;
}

$last10 = substr($cleanPhone, -10);
$formattedPhone = '91' . $last10;

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

// Save OTP under all phone variants (full digits, last 10 digits, and formatted 91-prefixed)
$entry = [
    'otp' => $otp,
    'expiresAt' => $expiresAt,
    'verified' => false
];
$store[$cleanPhone] = $entry;
$store[$last10] = $entry;
$store[$formattedPhone] = $entry;
file_put_contents($storeFile, json_encode($store));

// Include ZAMZY Mailer and WhatsApp gateway infrastructure
$mailerPath = __DIR__ . '/../../../mailer.php';
if (file_exists($mailerPath)) {
    require_once $mailerPath;
}

$message = "🔐 *ZAMZY Verification Code*\n\nYour 6-digit verification OTP for ZAMZY Digital Products checkout is:\n\n*{$otp}*\n\nValid for 10 minutes. Do not share this code with anyone.";

// 1. Dispatch via ZAMZY WhatsApp Gateway
$waSent = false;
if (function_exists('sendWhatsAppMessageDirect')) {
    $waRes = sendWhatsAppMessageDirect($last10, $message);
    $waSent = !empty($waRes['success']);
}

// 2. Dispatch via ZAMZY Email SMTP Gateway
$emailSent = false;
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && function_exists('sendOtpEmail')) {
    $emailRes = sendOtpEmail($email, $otp, 'digiproduct_checkout', $name);
    $emailSent = !empty($emailRes['success']);
}

$destinations = [];
if ($waSent) $destinations[] = 'WhatsApp';
if ($emailSent) $destinations[] = 'Email (' . $email . ')';

$msgText = count($destinations) > 0 
    ? "OTP sent to " . implode(' & ', $destinations) . "."
    : "OTP generated: {$otp}. Please check your phone / email.";

echo json_encode([
    'success' => true,
    'message' => $msgText,
    'waSent' => $waSent,
    'emailSent' => $emailSent,
    'otp' => ($waSent || $emailSent) ? null : $otp // For testing fallback if gateways not configured
]);

