<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataDir = __DIR__ . '/../data';
$dbFile = $dataDir . '/zamzy.db';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

$action = $_GET['action'] ?? 'vault';

function verifyCustomerToken($token) {
    if (empty($token) || strpos($token, '.') === false) return null;
    $parts = explode('.', $token);
    if (count($parts) !== 2) return null;
    $payloadB64 = $parts[0];
    $sig = $parts[1];
    $tokenSecret = getenv('APP_SECRET') ?: 'zamzy_vault_sec_' . md5(__DIR__);
    $expected = hash_hmac('sha256', $payloadB64, $tokenSecret);
    if (!hash_equals($expected, $sig)) return null;

    $json = base64_decode(strtr($payloadB64, '-_', '+/'));
    $data = json_decode($json, true);
    if (!$data || !isset($data['exp']) || time() > $data['exp']) return null;
    return $data;
}

// ─── ACTION: DOWNLOAD PDF ACCESS PASS ────────────────────────────
if ($action === 'download_pdf') {
    $orderNumber = trim($_GET['order'] ?? '');
    $token = trim($_GET['token'] ?? '');
    $authData = verifyCustomerToken($token);

    if (empty($orderNumber)) {
        die('Error: Order number is required.');
    }

    $stmt = $pdo->prepare("
        SELECT o.*, 
               COALESCE(NULLIF(o.customer_name, ''), c.name, 'Valued Customer') as customer_name,
               COALESCE(NULLIF(o.customer_email, ''), c.email, '') as customer_email,
               COALESCE(NULLIF(o.customer_phone, ''), c.phone, '') as customer_phone,
               COALESCE(NULLIF(o.product_name, ''), 'ZAMZY Digital Product') as product_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.order_number = ? AND UPPER(o.payment_status) IN ('PAID', 'FULFILLED', 'VERIFIED')
    ");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die('Error: Verified order not found.');
    }

    // IDOR Check if token passed
    if ($authData) {
        $tokenEmail = strtolower($authData['email'] ?? '');
        $tokenPhone = $authData['phone'] ?? '';
        $orderEmail = strtolower($order['customer_email'] ?? '');
        $orderPhone = preg_replace('/\D/', '', $order['customer_phone'] ?? '');

        if (!empty($tokenEmail) && !empty($orderEmail) && $tokenEmail !== $orderEmail) {
            http_response_code(403);
            die('Access Denied: You do not have entitlement for this order pass.');
        }
    }

    // Get order items / drive link
    $itemStmt = $pdo->prepare("
        SELECT p.name, p.resource_reference, p.slug 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $itemStmt->execute([$order['id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    $resourceLink = !empty($items[0]['resource_reference']) ? $items[0]['resource_reference'] : 'https://zamzy.in/digiproduct';
    $productTitle = htmlspecialchars($order['product_name']);
    $custName = htmlspecialchars($order['customer_name']);
    $custEmail = htmlspecialchars($order['customer_email']);
    $orderRef = htmlspecialchars($order['order_number']);
    $paidAmount = number_format(floatval($order['total']) / 100, 2);
    $payDate = htmlspecialchars($order['created_at'] ?? date('Y-m-d H:i'));

    // Output clean printable PDF / HTML pass
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Access Pass — {$orderRef}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #07090e; color: #f1f5f9; margin: 0; padding: 40px 20px; }
        .pass-card { max-width: 650px; margin: 0 auto; background: #0f1422; border: 2px solid #6366f1; border-radius: 16px; padding: 36px; box-shadow: 0 10px 40px rgba(0,0,0,0.8); }
        .pass-header { border-bottom: 1px solid #334155; padding-bottom: 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: 800; letter-spacing: 2px; color: #fff; }
        .badge { background: rgba(16,185,129,0.2); border: 1px solid #10b981; color: #34d399; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
        .title { font-size: 22px; font-weight: 700; color: #ffffff; margin-top: 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .details-table td { padding: 10px 0; border-bottom: 1px solid #1e293b; }
        .details-table td:first-child { color: #94a3b8; width: 40%; }
        .details-table td:last-child { color: #f8fafc; font-weight: 600; }
        .btn-access { display: block; text-align: center; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 16px; padding: 16px; border-radius: 10px; margin: 28px 0 12px; box-shadow: 0 4px 20px rgba(99,102,241,0.4); }
        .btn-print { display: block; text-align: center; background: #1e293b; color: #cbd5e1; border: 1px solid #475569; font-weight: 600; font-size: 13px; padding: 10px; border-radius: 8px; cursor: pointer; margin-bottom: 20px; }
        .support { font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #1e293b; padding-top: 16px; }
        @media print {
            body { background: #fff; color: #000; padding: 0; }
            .pass-card { border: 1px solid #000; box-shadow: none; background: #fff; color: #000; }
            .title { color: #000; }
            .details-table td { color: #000 !important; border-bottom: 1px solid #ddd; }
            .btn-access { background: #000; color: #fff; box-shadow: none; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="pass-card">
        <div class="pass-header">
            <div class="logo">ZAMZY DIGITAL</div>
            <div class="badge">✓ OFFICIAL ACCESS PASS</div>
        </div>
        <h1 class="title">{$productTitle}</h1>
        <table class="details-table">
            <tr><td>Order Reference</td><td>{$orderRef}</td></tr>
            <tr><td>Customer Name</td><td>{$custName}</td></tr>
            <tr><td>Registered Email</td><td>{$custEmail}</td></tr>
            <tr><td>Amount Paid</td><td>₹{$paidAmount} INR</td></tr>
            <tr><td>Issue Date</td><td>{$payDate}</td></tr>
        </table>
        <a href="{$resourceLink}" target="_blank" class="btn-access">🚀 CLICK HERE TO OPEN DIGITAL RESOURCE ROOM →</a>
        <button type="button" class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF Pass</button>
        <p style="font-size:13px; color:#cbd5e1; line-height:1.6; text-align:center;">
            Direct Drive URL: <a href="{$resourceLink}" style="color:#818cf8;">{$resourceLink}</a>
        </p>
        <div class="support">
            Need help? Contact ZAMZY Engineering Helpdesk: <strong>work@zamzy.in</strong> | +91 7287060553
        </div>
    </div>
</body>
</html>
HTML;
    exit;
}

// ─── ACTION: GET CUSTOMER PRODUCTS VAULT ────────────────────────
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?? $_POST;

$token = trim($input['token'] ?? '');
$authData = verifyCustomerToken($token);

$identifier = trim($input['identifier'] ?? $input['email'] ?? $input['phone'] ?? '');
$email = strtolower(trim($input['email'] ?? ($authData['email'] ?? '')));
$phone = trim($input['phone'] ?? ($authData['phone'] ?? ''));

if (empty($identifier) && empty($email) && empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please provide email or phone number.']);
    exit;
}

if (empty($email) && !empty($identifier) && str_contains($identifier, '@')) {
    $email = strtolower($identifier);
}
if (empty($phone) && !empty($identifier) && !str_contains($identifier, '@')) {
    $phone = $identifier;
}

$cleanPhone = preg_replace('/\D/', '', $phone);
$last10 = (strlen($cleanPhone) >= 10) ? substr($cleanPhone, -10) : $cleanPhone;

// Query all verified customer orders matching email OR phone
$orders = [];
try {
    $stmt = $pdo->prepare("
        SELECT o.id, o.order_number, o.total, o.payment_status, o.created_at,
               COALESCE(NULLIF(o.customer_name, ''), c.name, 'Customer') as customer_name,
               COALESCE(NULLIF(o.customer_email, ''), c.email, '') as customer_email,
               COALESCE(NULLIF(o.customer_phone, ''), c.phone, '') as customer_phone,
               COALESCE(NULLIF(o.product_name, ''), 'Digital Product Package') as product_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE (
            (:email != '' AND (LOWER(o.customer_email) = :email OR LOWER(c.email) = :email))
            OR
            (:phone != '' AND (o.customer_phone LIKE :phoneWild OR c.phone LIKE :phoneWild))
        )
        AND UPPER(o.payment_status) IN ('PAID', 'FULFILLED', 'VERIFIED')
        ORDER BY o.id DESC
    ");
    $stmt->execute([
        ':email' => $email,
        ':phone' => $last10,
        ':phoneWild' => '%' . $last10 . '%'
    ]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to query purchases: ' . $e->getMessage()]);
    exit;
}

if (empty($orders)) {
    echo json_encode([
        'success' => true,
        'customerName' => 'Customer',
        'count' => 0,
        'products' => [],
        'message' => 'No active purchases found for this email/mobile number.'
    ]);
    exit;
}

// Build list of purchased products
$purchasedProducts = [];
$customerName = $orders[0]['customer_name'] ?? 'Customer';

foreach ($orders as $order) {
    $itemStmt = $pdo->prepare("
        SELECT oi.id, oi.price, oi.is_addon, p.id as product_id, p.name, p.slug, p.resource_reference, p.thumbnail_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $itemStmt->execute([$order['id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        $purchasedProducts[] = [
            'orderNumber' => $order['order_number'],
            'productName' => $order['product_name'],
            'totalPaid' => number_format(floatval($order['total']) / 100, 2),
            'purchaseDate' => $order['created_at'] ?? date('Y-m-d'),
            'accessUrl' => 'https://zamzy.in/digiproduct',
            'pdfUrl' => '/digiproduct/api/customer.php?action=download_pdf&order=' . urlencode($order['order_number']),
            'isAddon' => false
        ];
    } else {
        foreach ($items as $item) {
            $purchasedProducts[] = [
                'orderNumber' => $order['order_number'],
                'productId' => $item['product_id'],
                'productName' => $item['name'],
                'totalPaid' => number_format(floatval($item['price']) / 100, 2),
                'purchaseDate' => $order['created_at'] ?? date('Y-m-d'),
                'accessUrl' => !empty($item['resource_reference']) ? $item['resource_reference'] : 'https://zamzy.in/digiproduct',
                'pdfUrl' => '/digiproduct/api/customer.php?action=download_pdf&order=' . urlencode($order['order_number']),
                'isAddon' => (bool)$item['is_addon']
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'customerName' => $customerName,
    'count' => count($purchasedProducts),
    'products' => $purchasedProducts
]);
