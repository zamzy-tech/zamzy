<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = $_POST;
}

$code = strtoupper(trim($input['couponCode'] ?? ($input['code'] ?? '')));
$totalAmount = (int)($input['totalAmount'] ?? 0);

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['valid' => false, 'error' => 'Coupon code is required.']);
    exit;
}

$c = null;
$dbFile = __DIR__ . '/../data/zamzy.db';

if (file_exists($dbFile)) {
    try {
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE UPPER(coupon_code) = :code AND active = 1 LIMIT 1");
        $stmt->execute([':code' => $code]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$c) {
            $stmt2 = $pdo->prepare("SELECT * FROM coupons WHERE UPPER(code) = :code AND (is_active = 1 OR active = 1) LIMIT 1");
            $stmt2->execute([':code' => $code]);
            $c = $stmt2->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log('[COUPON_API_ERR] ' . $e->getMessage());
    }
}

// Fallbacks if not found in database
if (!$c) {
    $fallbacks = [
        'EX100'    => ['type' => 'PERCENTAGE', 'val' => 100],
        'ZAMZY100' => ['type' => 'PERCENTAGE', 'val' => 100],
        'ZAMZY10'  => ['type' => 'PERCENTAGE', 'val' => 10],
        'LAUNCH50' => ['type' => 'PERCENTAGE', 'val' => 50],
        'SAVE50'   => ['type' => 'PERCENTAGE', 'val' => 50],
    ];
    if (isset($fallbacks[$code])) {
        $c = [
            'coupon_code' => $code,
            'discount_type' => $fallbacks[$code]['type'],
            'discount_value' => $fallbacks[$code]['val']
        ];
    }
}

if ($c) {
    $couponCodeName = $c['coupon_code'] ?? ($c['code'] ?? $code);
    $type = strtoupper($c['discount_type'] ?? 'FLAT');
    $val = (int)($c['discount_value'] ?? 0);

    if (strpos($type, 'PERCENT') !== false) {
        $discountAmount = (int)round($totalAmount * ($val / 100));
        $display = "{$val}% OFF";
    } else {
        $flatVal = ($val <= 1000 && $totalAmount > 1000) ? $val * 100 : $val;
        $discountAmount = min($totalAmount > 0 ? $totalAmount : $flatVal, $flatVal);
        $display = "₹" . (int)round($flatVal / 100) . " OFF";
    }

    echo json_encode([
        'valid' => true,
        'code' => $couponCodeName,
        'discountType' => $type,
        'discountValue' => $val,
        'discountAmount' => $discountAmount,
        'discountDisplay' => $display
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['valid' => false, 'error' => 'Invalid or expired coupon code.']);
