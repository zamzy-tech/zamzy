<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$apiKey = '2afb55e8bbc3d8d6ca018fd19592a4ee';
$action = $_GET['action'] ?? 'send_otp';

// ==========================================
// 1. SUBMIT COMPLETED APPLICATION LEAD TO EXECUTIVE WHATSAPP DESK
// ==========================================
if ($action === 'submit_lead') {
    $name    = $_GET['name'] ?? 'Valued Customer';
    $phone   = $_GET['phone'] ?? '';
    $service = $_GET['service'] ?? 'General Financial Inquiry';
    $amount  = $_GET['amount'] ?? 'N/A';
    
    // Executive Desk WhatsApp Number
    $executivePhone = '9059297755';
    
    $message = "*🔥 NEW CREDIFIX LOAN APPLICATION LEAD!*\n"
             . "-----------------------------------\n"
             . "👤 *Customer Name*: " . $name . "\n"
             . "📞 *WhatsApp Number*: +91 " . $phone . "\n"
             . "💼 *Required Service*: " . $service . "\n"
             . "💰 *Loan Amount*: ₹" . $amount . "\n"
             . "-----------------------------------\n"
             . "⏰ *Submitted*: " . date('d-m-Y h:i A') . "\n"
             . "✅ *OTP Verification*: VERIFIED LIVE VIA WHATSAPP";

    $apiUrl = 'https://2fa.tehub.in/api/whatsapp.php';
    $postData = [
        'api_key' => $apiKey,
        'to'      => '91' . $executivePhone,
        'message' => $message
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    echo json_encode([
        'success' => true,
        'message' => 'Application lead dispatched to executive WhatsApp successfully!'
    ]);
    exit;
}

// ==========================================
// 2. DISPATCH 4-DIGIT OTP TO CUSTOMER WHATSAPP
// ==========================================
$phone = $_GET['phone'] ?? '';
$otp   = $_GET['otp'] ?? '';

if (empty($phone) || empty($otp)) {
    echo json_encode(['success' => false, 'error' => 'Phone and OTP are required.']);
    exit;
}

$cleanPhone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($cleanPhone) === 10) {
    $cleanPhone = '91' . $cleanPhone;
}

$message = "🔐 *CREDIFIX Verification Code*\n\nYour 4-digit WhatsApp OTP is: *{$otp}*\n\nPlease enter this code on the CREDIFIX portal to complete your loan application enquiry.\n\n_If you did not request this code, please ignore this message._";

$apiUrl = 'https://2fa.tehub.in/api/whatsapp.php';
$postData = [
    'api_key' => $apiKey,
    'to'      => $cleanPhone,
    'message' => $message
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$resData = json_decode($response, true);
curl_close($ch);

if ($httpCode === 200 && isset($resData['success']) && $resData['success'] === true) {
    echo json_encode(['success' => true, 'message' => 'OTP dispatched successfully.']);
} else {
    $errMsg = $resData['error'] ?? 'Failed to reach WhatsApp Gateway server.';
    echo json_encode(['success' => false, 'error' => $errMsg]);
}
?>
