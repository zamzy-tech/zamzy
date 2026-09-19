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

$dbFile = __DIR__ . '/../data/zamzy.db';
if (file_exists($dbFile)) {
    try {
        $pdo = new PDO('sqlite:' . $dbFile);
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE UPPER(code) = UPPER(:code) AND is_active = 1 LIMIT 1");
        $stmt->execute([':code' => $code]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($c) {
            $type = strtoupper($c['discount_type']);
            $val = (int)$c['discount_value'];
            if ($type === 'PERCENTAGE') {
                $discountAmount = (int)round($totalAmount * ($val / 100));
                $display = "{$val}% OFF";
            } else {
                $discountAmount = min($totalAmount, $val);
                $display = "₹" . ($val / 100) . " OFF";
            }
            echo json_encode([
                'valid' => true,
                'code' => $c['code'],
                'discountType' => $type,
                'discountValue' => $val,
                'discountAmount' => $discountAmount,
                'discountDisplay' => $display
            ]);
            exit;
        }
    } catch (Exception $e) {}
}

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
