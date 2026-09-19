<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

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

$dbFile = __DIR__ . '/../data/zamzy.db';
if (file_exists($dbFile)) {
    try {
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE UPPER(coupon_code) = UPPER(:code) AND active = 1 LIMIT 1");
        $stmt->execute([':code' => $code]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$c) {
            try {
                $stmt2 = $pdo->prepare("SELECT * FROM coupons WHERE UPPER(code) = UPPER(:code) AND is_active = 1 LIMIT 1");
                $stmt2->execute([':code' => $code]);
                $c = $stmt2->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $ex) {}
        }

        if ($c) {
            $couponCodeName = $c['coupon_code'] ?? ($c['code'] ?? $code);
            $type = strtoupper($c['discount_type'] ?? 'FLAT');
            $val = (int)($c['discount_value'] ?? 0);

            if (strpos($type, 'PERCENT') !== false) {
                $discountAmount = (int)round($totalAmount * ($val / 100));
                $display = "{$val}% OFF";
            } else {
                $discountAmount = min($totalAmount > 0 ? $totalAmount : $val, $val);
                $display = "₹" . (int)round($val / 100) . " OFF";
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
    } catch (Exception $e) {
        error_log('[COUPON_API_ERR] ' . $e->getMessage());
    }
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

