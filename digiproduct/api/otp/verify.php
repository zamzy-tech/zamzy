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
$otp = trim($input['otp'] ?? '');

if (empty($identifier) && empty($phone) && empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Mobile number or email is required.']);
    exit;
}
if (empty($otp)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter the 6-digit OTP code.']);
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

$storeFile = __DIR__ . '/../../data/otp_store.json';

if (!file_exists($storeFile)) {
    http_response_code(400);
    echo json_encode(['error' => 'No OTP requested or session has expired.']);
    exit;
}

$store = json_decode(file_get_contents($storeFile), true) ?? [];

$entryKey = null;
if (!empty($identifier) && isset($store[strtolower($identifier)])) {
    $entryKey = strtolower($identifier);
} elseif (!empty($email) && isset($store[strtolower($email)])) {
    $entryKey = strtolower($email);
} elseif (!empty($cleanPhone) && isset($store[$cleanPhone])) {
    $entryKey = $cleanPhone;
} elseif (!empty($last10) && isset($store[$last10])) {
    $entryKey = $last10;
} elseif (!empty($formattedPhone) && isset($store[$formattedPhone])) {
    $entryKey = $formattedPhone;
}

if (!$entryKey) {
    http_response_code(400);
    echo json_encode(['error' => 'No active OTP found. Please request a new verification code.']);
    exit;
}

$entry = $store[$entryKey];

if (time() > $entry['expiresAt']) {
    unset($store[$entryKey]);
    file_put_contents($storeFile, json_encode($store));
    http_response_code(400);
    echo json_encode(['error' => 'OTP has expired. Please request a new code.']);
    exit;
}

if (trim($entry['otp']) !== $otp) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid OTP code. Please check your WhatsApp / Email and try again.']);
    exit;
}

// Mark verified
$entry['verified'] = true;
$store[$entryKey] = $entry;
file_put_contents($storeFile, json_encode($store));

// Generate a signed session token
$token = bin2hex(random_bytes(16));

echo json_encode([
    'success' => true,
    'message' => 'Verification successful!',
    'verified' => true,
    'token' => $token,
    'email' => $entry['email'] ?? $email,
    'phone' => $entry['phone'] ?? $last10
]);

