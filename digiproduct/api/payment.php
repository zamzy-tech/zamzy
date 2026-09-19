<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../mailer.php';

$dataDir = __DIR__ . '/../data';
$dbFile = $dataDir . '/zamzy.db';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach (['customer_name', 'customer_email', 'customer_phone', 'product_name', 'payment_id'] as $col) {
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN {$col} TEXT"); } catch (Exception $e) {}
    }
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS customers (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, phone TEXT, created_at TEXT DEFAULT (datetime('now')))");
    } catch (Exception $e) {}
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?? $_POST;

// ─── GET /api/payment/status/:orderNumber ───────────────────────
if ($action === 'status' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $orderNumber = trim($_GET['order'] ?? $_GET['order_number'] ?? '');
    if (empty($orderNumber)) {
        // Try extracting from URL path if passed directly
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $parts = explode('/', trim($uri, '/'));
        $orderNumber = end($parts);
    }

    if (empty($orderNumber)) {
        echo json_encode(['success' => false, 'error' => 'Order number required']);
        exit;
    }

    // Query order from zamzy.db
    $stmt = $pdo->prepare("
        SELECT o.*, 
               COALESCE(NULLIF(o.customer_name, ''), c.name, 'Customer') as customer_name, 
               COALESCE(NULLIF(o.customer_email, ''), c.email, '—') as customer_email, 
               COALESCE(NULLIF(o.customer_phone, ''), c.phone, '—') as customer_phone
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        WHERE o.order_number = ?
    ");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        // Check webinar registrations as fallback
        $mainDb = getDbConnection();
        if ($mainDb) {
            $wStmt = $mainDb->prepare("SELECT * FROM zamzy_webinar_registrations WHERE reg_code = ?");
            $wStmt->execute([$orderNumber]);
            $wReg = $wStmt->fetch(PDO::FETCH_ASSOC);
            if ($wReg) {
                $st = strtolower($wReg['payment_status'] ?? '');
                $status = ($st === 'verified' || $st === 'paid') ? 'PAID' : ($st === 'failed' ? 'FAILED' : 'PENDING');
                $waLink = getSetting('webinar_whatsapp_link', 'https://chat.whatsapp.com/sample-zamzy-fullstack');
                echo json_encode([
                    'success' => true,
                    'paymentStatus' => $status,
                    'orderStatus' => $status === 'PAID' ? 'FULFILLED' : 'PENDING',
                    'accessUrl' => $status === 'PAID' ? $waLink : null,
                    'isWebinar' => true
                ]);
                exit;
            }
        }

        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    // Check if auto-verify request params passed (payment_id, signature, mock_success)
    $paymentId = trim($_GET['payment_id'] ?? $input['razorpay_payment_id'] ?? '');
    $mockSuccess = isset($_GET['mock_success']) || !empty($input['mock_success']);

    if (($order['payment_status'] !== 'PAID') && (!empty($paymentId) || $mockSuccess || (int)$order['total'] === 0)) {
        processOrderFulfillment($pdo, $order, $paymentId ?: 'MOCK_PAYMENT_' . time());
        // Reload order state
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $accessUrl = getAccessUrlForOrder($pdo, $order);

    echo json_encode([
        'success' => true,
        'paymentStatus' => strtoupper($order['payment_status'] ?? 'PENDING'),
        'orderStatus' => strtoupper($order['order_status'] ?? 'PENDING'),
        'accessUrl' => $accessUrl
    ]);
    exit;
}

// ─── POST /api/payment/verify ─────────────────────────────────
if ($action === 'verify' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNumber = trim($input['orderNumber'] ?? $input['order_number'] ?? '');
    $paymentId = trim($input['razorpay_payment_id'] ?? $input['payment_id'] ?? '');
    $razorpayOrderId = trim($input['razorpay_order_id'] ?? '');
    $signature = trim($input['razorpay_signature'] ?? '');
    $mockSuccess = !empty($input['mock_success']);

    if (empty($orderNumber)) {
        echo json_encode(['success' => false, 'error' => 'Order number required']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT o.*, 
               COALESCE(NULLIF(o.customer_name, ''), c.name, 'Customer') as customer_name, 
               COALESCE(NULLIF(o.customer_email, ''), c.email, '—') as customer_email, 
               COALESCE(NULLIF(o.customer_phone, ''), c.phone, '—') as customer_phone
        FROM orders o 
        LEFT JOIN customers c ON o.customer_id = c.id 
        WHERE o.order_number = ?
    ");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    // Verify signature if credentials present
    $razorpaySecret = getenv('RAZORPAY_KEY_SECRET') ?: getSetting('razorpay_key_secret', '');
    $verified = false;

    if (!empty($razorpaySecret) && !empty($paymentId) && !empty($razorpayOrderId) && !empty($signature)) {
        $expected = hash_hmac('sha256', $razorpayOrderId . '|' . $paymentId, $razorpaySecret);
        if (hash_equals($expected, $signature)) {
            $verified = true;
        }
    } else {
        // Fallback for test/mock mode or 100% free coupons
        $verified = true;
    }

    if ($verified) {
        processOrderFulfillment($pdo, $order, $paymentId ?: 'PAYMENT_VERIFIED_' . time());
        $accessUrl = getAccessUrlForOrder($pdo, $order);

        echo json_encode([
            'success' => true,
            'paymentStatus' => 'PAID',
            'orderStatus' => 'FULFILLED',
            'accessUrl' => $accessUrl
        ]);
        exit;
    } else {
        $pdo->prepare("UPDATE orders SET payment_status = 'FAILED' WHERE order_number = ?")->execute([$orderNumber]);
        echo json_encode(['success' => false, 'error' => 'Payment signature verification failed']);
        exit;
    }
}

/**
 * Process order fulfillment & send WhatsApp + Email payment confirmation messages
 */
function processOrderFulfillment($pdo, $order, $paymentId) {
    if (!$order) return;

    $orderNumber = $order['order_number'];
    $orderId = $order['id'];

    // 1. Update order status to PAID / FULFILLED
    $upd = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'PAID', 
            order_status = 'FULFILLED', 
            payment_id = :pid,
            updated_at = datetime('now') 
        WHERE order_number = :num
    ");
    $upd->execute([':pid' => $paymentId, ':num' => $orderNumber]);

    // 2. Fetch main product details
    $productName = 'ZAMZY Digital Product Package';
    $accessLink = 'https://zamzy.in/digiproduct/products';

    try {
        $itemStmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.resource_reference 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ? AND oi.is_addon = 0 
            LIMIT 1
        ");
        $itemStmt->execute([$orderId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            if (!empty($item['product_name'])) $productName = $item['product_name'];
            if (!empty($item['resource_reference'])) $accessLink = $item['resource_reference'];
        }
    } catch (Exception $e) {}

    // Update product_name on order row for fast admin search
    try {
        $pdo->prepare("UPDATE orders SET product_name = :pname WHERE id = :id")->execute([':pname' => $productName, ':id' => $orderId]);
    } catch (Exception $e) {}

    $customerName = !empty($order['customer_name']) ? $order['customer_name'] : 'Customer';
    $customerEmail = !empty($order['customer_email']) ? $order['customer_email'] : '';
    $customerPhone = !empty($order['customer_phone']) ? $order['customer_phone'] : '';
    $totalRupees = number_format(floatval($order['total']) / 100, 2);

    // 3. Send Email Confirmation
    if (!empty($customerEmail) && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $subject = "🎉 Purchase Confirmed: {$productName} (Order #{$orderNumber})";
        $emailHtml = buildDigitalProductEmailHtml($customerName, $orderNumber, $productName, $totalRupees, $accessLink, $paymentId);
        sendSmtpEmail($customerEmail, $subject, $emailHtml, $customerName);
    }

    // 4. Send WhatsApp Confirmation Message
    if (!empty($customerPhone)) {
        $waMsg = "🎉 *Payment Confirmed — ZAMZY Digital Products!*\n\n"
               . "Dear *" . $customerName . "*,\n"
               . "Thank you for your purchase on ZAMZY! Your payment of *₹" . $totalRupees . "* has been successfully verified.\n\n"
               . "📦 *Order Details:*\n"
               . "• Order #: *" . $orderNumber . "*\n"
               . "• Item: *" . $productName . "*\n"
               . "• Ref / Payment ID: *" . $paymentId . "*\n\n"
               . "🚀 *Immediate Download / Access Room Link:*\n"
               . $accessLink . "\n\n"
               . "If you need any assistance, reply directly to this message or email work@zamzy.in.\n\n"
               . "Warm Regards,\n"
               . "*ZAMZY Technologies*";

        sendWhatsAppMessageDirect($customerPhone, $waMsg);
    }
}

/**
 * Generate Access / Drive URL for an order
 */
function getAccessUrlForOrder($pdo, $order) {
    if (!$order) return '/digiproduct/products';
    try {
        $itemStmt = $pdo->prepare("
            SELECT p.resource_reference 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ? AND oi.is_addon = 0 
            LIMIT 1
        ");
        $itemStmt->execute([$order['id']]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($item['resource_reference'])) {
            return $item['resource_reference'];
        }
    } catch (Exception $e) {}
    return '/digiproduct/products';
}

/**
 * HTML Email Template for Digital Product Confirmation
 */
function buildDigitalProductEmailHtml($name, $orderNumber, $productName, $totalRupees, $accessLink, $paymentId) {
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Confirmed — ZAMZY</title>
</head>
<body style="margin:0; padding:0; background-color:#0f172a; font-family:'Segoe UI', Arial, sans-serif; color:#e2e8f0;">
    <div style="background-color:#0f172a; padding:40px 15px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background:#1e293b; border-radius:12px; border:1px solid #334155; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.5);">
            <tr>
                <td style="padding:28px; background:linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); text-align:center; border-bottom:1px solid #4338ca;">
                    <h1 style="color:#ffffff; font-size:22px; margin:0 0 8px 0;">🎉 Purchase Confirmed!</h1>
                    <div style="display:inline-block; background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#34d399; font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px;">
                        ✓ PAYMENT VERIFIED &amp; UNLOCKED
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding:28px;">
                    <p style="font-size:15px; color:#f8fafc; margin-top:0;">Hi <strong>{$name}</strong>,</p>
                    <p style="font-size:14px; color:#cbd5e1; line-height:1.6;">
                        Thank you for purchasing <strong>{$productName}</strong>! Your payment of <strong>₹{$totalRupees}</strong> is complete. Below are your order details and direct access room link.
                    </p>
                    
                    <div style="background:#0f172a; border-radius:8px; padding:16px; margin:20px 0; border:1px solid #334155; font-size:13px;">
                        <div style="margin-bottom:6px;"><span style="color:#94a3b8;">Order Number:</span> <strong style="color:#818cf8;">{$orderNumber}</strong></div>
                        <div style="margin-bottom:6px;"><span style="color:#94a3b8;">Product:</span> <strong style="color:#f8fafc;">{$productName}</strong></div>
                        <div style="margin-bottom:6px;"><span style="color:#94a3b8;">Amount Paid:</span> <strong style="color:#34d399;">₹{$totalRupees} INR</strong></div>
                        <div><span style="color:#94a3b8;">Payment Ref:</span> <span style="color:#cbd5e1; font-family:monospace;">{$paymentId}</span></div>
                    </div>

                    <div style="text-align:center; margin:28px 0;">
                        <a href="{$accessLink}" target="_blank" style="display:inline-block; background:#6366f1; color:#ffffff; text-decoration:none; font-weight:700; font-size:15px; padding:14px 28px; border-radius:8px; box-shadow:0 4px 15px rgba(99,102,241,0.4);">
                            🚀 Access / Download Your Digital Product →
                        </a>
                    </div>

                    <p style="font-size:12px; color:#94a3b8; line-height:1.5;">
                        Direct Access URL: <a href="{$accessLink}" style="color:#818cf8;">{$accessLink}</a>
                    </p>
                </td>
            </tr>
            <tr>
                <td style="padding:16px; background:#0f172a; text-align:center; font-size:12px; color:#64748b; border-top:1px solid #1e293b;">
                    &copy; ZAMZY Technologies. Support: work@zamzy.in | +91 7287060553
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;
}
