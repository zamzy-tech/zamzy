<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$phone = trim($input['phone'] ?? '');
$otp = trim($input['otp'] ?? '');

if (empty($phone) || empty($otp)) {
    http_response_code(400);
    echo json_encode(['error' => 'Phone number and OTP code are required.']);
    exit;
}

$cleanPhone = preg_replace('/\D/', '', $phone);
$storeFile = __DIR__ . '/../../data/otp_store.json';

if (!file_exists($storeFile)) {
    http_response_code(400);
    echo json_encode(['error' => 'No OTP requested for this phone number or OTP has expired.']);
    exit;
}

$store = json_decode(file_get_contents($storeFile), true) ?? [];

if (!isset($store[$cleanPhone])) {
    http_response_code(400);
    echo json_encode(['error' => 'No OTP requested for this phone number or OTP has expired.']);
    exit;
}

$entry = $store[$cleanPhone];

if (time() > $entry['expiresAt']) {
    unset($store[$cleanPhone]);
    file_put_contents($storeFile, json_encode($store));
    http_response_code(400);
    echo json_encode(['error' => 'OTP has expired. Please request a new code.']);
    exit;
}

if (trim($entry['otp']) !== $otp) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid OTP code. Please check your WhatsApp and try again.']);
    exit;
}

// Mark verified
$store[$cleanPhone]['verified'] = true;
file_put_contents($storeFile, json_encode($store));

echo json_encode([
    'success' => true,
    'verified' => true,
    'message' => 'WhatsApp number verified successfully!'
]);
