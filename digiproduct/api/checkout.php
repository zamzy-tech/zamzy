<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $productId = !empty($input['productId']) ? (int)$input['productId'] : 0;
    $productSlug = trim($input['productSlug'] ?? '');
    $customerName = trim($input['customerName'] ?? ($input['name'] ?? ''));
    $customerEmail = trim($input['customerEmail'] ?? ($input['email'] ?? ''));
    $customerPhone = trim($input['customerPhone'] ?? ($input['phone'] ?? ''));
    $addonProductId = !empty($input['addonProductId']) ? (int)$input['addonProductId'] : null;
    $addonSlugs = $input['addons'] ?? [];
    $couponCode = trim($input['couponCode'] ?? '');

    if (empty($customerName) || empty($customerEmail) || empty($customerPhone)) {
        http_response_code(400);
        echo json_encode(['error' => 'Full Name, Email Address, and Phone Number are required.']);
        exit;
    }

    $dbFile = __DIR__ . '/../data/zamzy.db';
    $db = null;
    if (file_exists($dbFile)) {
        try {
            $db = new PDO('sqlite:' . $dbFile);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            $db = null;
        }
    }

    // Default fallback catalog if DB query returns nothing
    $fallbackCatalog = [
        1 => ['id' => 1, 'name' => '5L+ USA Business Prospects', 'price' => 24900, 'slug' => 'usa-business-prospects'],
        2 => ['id' => 2, 'name' => 'India Business Leads Database', 'price' => 19900, 'slug' => 'india-business-leads'],
        3 => ['id' => 3, 'name' => 'ZAMZY Business Prospecting Bundle', 'price' => 34900, 'slug' => 'business-bundle'],
        4 => ['id' => 4, 'name' => 'Meta Ads Mastery Playbook & Templates', 'price' => 4900, 'slug' => 'meta-ads-mastery']
    ];

    $mainProduct = null;

    if ($db) {
        if ($productId > 0) {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND active = 1");
            $stmt->execute([':id' => $productId]);
            $mainProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$mainProduct && !empty($productSlug)) {
            $stmt = $db->prepare("SELECT * FROM products WHERE slug = :slug AND active = 1");
            $stmt->execute([':slug' => $productSlug]);
            $mainProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!$mainProduct) {
        if ($productId > 0 && isset($fallbackCatalog[$productId])) {
            $mainProduct = $fallbackCatalog[$productId];
        } else {
            foreach ($fallbackCatalog as $fp) {
                if (!empty($productSlug) && $fp['slug'] === $productSlug) {
                    $mainProduct = $fp;
                    break;
                }
            }
        }
    }

    if (!$mainProduct) {
        $mainProduct = $fallbackCatalog[1];
    }

    $subtotal = (int)$mainProduct['price'];

    // Resolve Addon
    $addonProduct = null;
    $addonTotal = 0;

    if ($db) {
        if ($addonProductId > 0) {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND active = 1");
            $stmt->execute([':id' => $addonProductId]);
            $addonProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($addonSlugs) && is_array($addonSlugs) && count($addonSlugs) > 0) {
            $stmt = $db->prepare("SELECT * FROM products WHERE slug = :slug AND active = 1");
            $stmt->execute([':slug' => $addonSlugs[0]]);
            $addonProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!$addonProduct && ($addonProductId == 4 || (is_array($addonSlugs) && in_array('meta-ads-mastery', $addonSlugs)))) {
        $addonProduct = $fallbackCatalog[4];
    }

    if ($addonProduct) {
        $addonTotal = (int)$addonProduct['price'];
    }

    // Calculate discount
    $discountTotal = 0;
    $couponId = null;

    if (!empty($couponCode)) {
        $codeUpper = strtoupper($couponCode);
        if ($db) {
            $stmt = $db->prepare("SELECT * FROM coupons WHERE UPPER(coupon_code) = :code AND active = 1");
            $stmt->execute([':code' => $codeUpper]);
            $cp = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cp) {
                $couponId = (int)$cp['id'];
                if (($cp['discount_type'] ?? '') === 'flat') {
                    $discountTotal = (int)$cp['discount_value'];
                } else {
                    $discountTotal = (int)floor(($subtotal + $addonTotal) * ((int)$cp['discount_value'] / 100));
                }
            }
        }
        if (!$couponId && $codeUpper === 'ZAMZY10') {
            $discountTotal = (int)round(($subtotal + $addonTotal) * 0.10);
        }
    }

    $totalAmount = max(100, $subtotal + $addonTotal - $discountTotal);

    // Generate unique Order Number
    $orderNumber = 'ZM' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

    // Save customer and order in database if DB available
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT id FROM customers WHERE email = :email");
            $stmt->execute([':email' => strtolower($customerEmail)]);
            $cust = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cust) {
                $stmt = $db->prepare("INSERT INTO customers (name, email, phone) VALUES (:name, :email, :phone)");
                $stmt->execute([':name' => $customerName, ':email' => strtolower($customerEmail), ':phone' => $customerPhone]);
                $customerId = $db->lastInsertId();
            } else {
                $customerId = $cust['id'];
                $stmt = $db->prepare("UPDATE customers SET name = :name, phone = :phone, updated_at = datetime('now') WHERE id = :id");
                $stmt->execute([':name' => $customerName, ':phone' => $customerPhone, ':id' => $customerId]);
            }

            $stmt = $db->prepare("
                INSERT INTO orders (order_number, customer_id, subtotal, addon_total, discount_total, total, currency, payment_status, order_status, coupon_id, ip_address)
                VALUES (:order_number, :customer_id, :subtotal, :addon_total, :discount_total, :total, 'INR', 'CREATED', 'CREATED', :coupon_id, :ip)
            ");
            $stmt->execute([
                ':order_number' => $orderNumber,
                ':customer_id' => $customerId,
                ':subtotal' => $subtotal,
                ':addon_total' => $addonTotal,
                ':discount_total' => $discountTotal,
                ':total' => $totalAmount,
                ':coupon_id' => $couponId,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
            $orderId = $db->lastInsertId();

            // Save Order Items
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, price, quantity, is_addon) VALUES (:order_id, :product_id, :price, 1, :is_addon)");
            $stmt->execute([':order_id' => $orderId, ':product_id' => $mainProduct['id'], ':price' => $subtotal, ':is_addon' => 0]);
            if ($addonProduct) {
                $stmt->execute([':order_id' => $orderId, ':product_id' => $addonProduct['id'], ':price' => $addonTotal, ':is_addon' => 1]);
            }
        } catch (Exception $e) {
            error_log('[CHECKOUT_DB_ERROR] ' . $e->getMessage());
        }
    }

    // Load Razorpay Credentials directly from SQLite DB or env
    $razorpayKeyId = getenv('PAYMENT_API_KEY') ?: '';
    $razorpayKeySecret = getenv('PAYMENT_SECRET') ?: '';

    if ($db && (empty($razorpayKeyId) || strpos($razorpayKeyId, 'rzp_') !== 0)) {
        try {
            $stmt = $db->query("SELECT key, value FROM site_settings WHERE key IN ('razorpay_key_id', 'razorpay_key_secret', 'payment_api_key', 'payment_secret')");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($settings['razorpay_key_id'])) $razorpayKeyId = trim($settings['razorpay_key_id']);
            elseif (!empty($settings['payment_api_key'])) $razorpayKeyId = trim($settings['payment_api_key']);
            if (!empty($settings['razorpay_key_secret'])) $razorpayKeySecret = trim($settings['razorpay_key_secret']);
            elseif (!empty($settings['payment_secret'])) $razorpayKeySecret = trim($settings['payment_secret']);
        } catch (Exception $e) {}
    }

    $razorpayOrderId = null;

    // Create live Razorpay order via cURL
    if (!empty($razorpayKeyId) && !empty($razorpayKeySecret) && strpos($razorpayKeyId, 'rzp_') === 0) {
        $ch = curl_init("https://api.razorpay.com/v1/orders");
        $rzpPayload = json_encode([
            'amount' => $totalAmount,
            'currency' => 'INR',
            'receipt' => $orderNumber,
            'notes' => [
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'product_name' => $mainProduct['name']
            ]
        ]);
        curl_setopt($ch, CURLOPT_USERPWD, $razorpayKeyId . ':' . $razorpayKeySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rzpPayload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
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

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Checkout warning: ' . $e->getMessage()
    ]);
}



