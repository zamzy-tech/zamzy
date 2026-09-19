<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$productId = (int)($input['productId'] ?? 0);
$customerName = trim($input['customerName'] ?? '');
$customerEmail = trim($input['customerEmail'] ?? '');
$customerPhone = trim($input['customerPhone'] ?? '');
$addonProductId = !empty($input['addonProductId']) ? (int)$input['addonProductId'] : null;
$couponCode = trim($input['couponCode'] ?? '');

if (empty($customerName) || empty($customerEmail) || empty($customerPhone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Full Name, Email Address, and Phone Number are required.']);
    exit;
}

// Default prices in paise
$catalog = [
    1 => 24900, // USA Business Prospects ₹249
    2 => 19900, // India Business Leads ₹199
    3 => 34900, // Bundle ₹349
    4 => 4900   // Meta Ads Mastery ₹49
];

$mainPrice = $catalog[$productId] ?? 24900;
$addonPrice = ($addonProductId && isset($catalog[$addonProductId])) ? $catalog[$addonProductId] : 0;
$totalAmount = $mainPrice + $addonPrice;

// Apply discount coupon
if (!empty($couponCode) && strtoupper($couponCode) === 'ZAMZY10') {
    $discount = (int)round($totalAmount * 0.10);
    $totalAmount = max(100, $totalAmount - $discount);
}

// Generate unique Order Number
$orderNumber = 'ORD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

// Load Razorpay Credentials from main DB, SQLite DB or env
$razorpayKeyId = getenv('PAYMENT_API_KEY') ?: '';
$razorpayKeySecret = getenv('PAYMENT_SECRET') ?: '';

$mainDb = __DIR__ . '/../../db.php';
if (file_exists($mainDb)) {
    require_once $mainDb;
    if (function_exists('getSetting')) {
        $razorpayKeyId = getSetting('razorpay_key_id', getSetting('payment_api_key', $razorpayKeyId));
        $razorpayKeySecret = getSetting('razorpay_key_secret', getSetting('payment_secret', $razorpayKeySecret));
    }
}

if (empty($razorpayKeyId)) {
    $dbFile = __DIR__ . '/../data/zamzy.db';
    if (file_exists($dbFile)) {
        try {
            $db = new PDO('sqlite:' . $dbFile);
            $stmt = $db->prepare("SELECT key, value FROM site_settings WHERE key IN ('razorpay_key_id', 'razorpay_key_secret')");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($settings['razorpay_key_id'])) $razorpayKeyId = trim($settings['razorpay_key_id']);
            if (!empty($settings['razorpay_key_secret'])) $razorpayKeySecret = trim($settings['razorpay_key_secret']);
        } catch (Exception $e) {}
    }
}

$razorpayOrderId = null;

// Create live Razorpay order via cURL if valid credentials are set
if (!empty($razorpayKeyId) && !empty($razorpayKeySecret) && strpos($razorpayKeyId, 'rzp_') === 0) {
    $ch = curl_init("https://api.razorpay.com/v1/orders");
    $rzpPayload = json_encode([
        'amount' => $totalAmount,
        'currency' => 'INR',
        'receipt' => $orderNumber,
        'notes' => [
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone
        ]
    ]);
    curl_setopt($ch, CURLOPT_USERPWD, $razorpayKeyId . ':' . $razorpayKeySecret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rzpPayload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $rzpRes = curl_exec($ch);
    $rzpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($rzpStatus >= 200 && $rzpStatus < 300) {
        $rzpData = json_decode($rzpRes, true);
        if (!empty($rzpData['id'])) {
            $razorpayOrderId = $rzpData['id'];
        }
    }
}

echo json_encode([
    'success' => true,
    'orderNumber' => $orderNumber,
    'amount' => $totalAmount,
    'currency' => 'INR',
    'paymentMode' => 'razorpay',
    'razorpayOrderId' => $razorpayOrderId,
    'razorpayKeyId' => !empty($razorpayKeyId) ? $razorpayKeyId : 'rzp_test_default'
]);
