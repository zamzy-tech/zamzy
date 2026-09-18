<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$code = strtoupper(trim($input['couponCode'] ?? ''));
$totalAmount = (int)($input['totalAmount'] ?? 0);

if ($code === 'ZAMZY10') {
    $discountAmount = (int)round($totalAmount * 0.10);
    echo json_encode([
        'valid' => true,
        'code' => 'ZAMZY10',
        'discountType' => 'PERCENTAGE',
        'discountValue' => 10,
        'discountAmount' => $discountAmount,
        'discountDisplay' => '10% OFF'
    ]);
} else {
    http_response_code(400);
    echo json_encode(['valid' => false, 'error' => 'Invalid or expired coupon code.']);
}
