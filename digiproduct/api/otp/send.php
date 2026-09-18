<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$phone = trim($input['phone'] ?? '');

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

// Format Indian phone number with 91 prefix if 10 digits
$formattedPhone = (strlen($cleanPhone) === 10) ? '91' . $cleanPhone : $cleanPhone;

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

$last10 = substr($cleanPhone, -10);
$formattedPhone = '91' . $last10;

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

// Load WhatsApp Credentials from SQLite database if available
$apiKey = getenv('WHATSAPP_API_KEY') ?: '';
$phoneNumberId = getenv('WHATSAPP_PHONE_NUMBER_ID') ?: '';

$dbFile = $dataDir . '/zamzy.db';
if (file_exists($dbFile)) {
    try {
        $db = new PDO('sqlite:' . $dbFile);
        $stmt = $db->prepare("SELECT key, value FROM site_settings WHERE key IN ('whatsapp_api_key', 'whatsapp_phone_number_id')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        if (!empty($settings['whatsapp_api_key'])) $apiKey = trim($settings['whatsapp_api_key']);
        if (!empty($settings['whatsapp_phone_number_id'])) $phoneNumberId = trim($settings['whatsapp_phone_number_id']);
    } catch (Exception $e) {}
}

$message = "🔒 ZAMZY Verification Code\n\nHi, your 6-digit WhatsApp verification OTP for ZAMZY Digital Products checkout is:\n\n*{$otp}*\n\nDo not share this code with anyone. Valid for 10 minutes.";
$waMeLink = "https://wa.me/{$formattedPhone}?text=" . urlencode($message);

$sentViaApi = false;
if (!empty($apiKey) && !empty($phoneNumberId)) {
    $ch = curl_init("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages");
    $payload = json_encode([
        'messaging_product' => 'whatsapp',
        'to' => $formattedPhone,
        'type' => 'text',
        'text' => ['body' => $message]
    ]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $sentViaApi = true;
    }
}

echo json_encode([
    'success' => true,
    'message' => "OTP sent to +{$formattedPhone} via WhatsApp.",
    'sentViaApi' => $sentViaApi,
    'debugLink' => $waMeLink,
    'otp' => (!empty($apiKey) && !empty($phoneNumberId)) ? null : $otp // For testing if credentials not set yet
]);
