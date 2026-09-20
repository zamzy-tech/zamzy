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
        2 => ['id' => 2, 'name' => 'India Business Leads Database', 'price' => 24900, 'slug' => 'india-business-leads'],
        3 => ['id' => 3, 'name' => 'Complete Mega Growth Package & Database Bundle', 'price' => 44900, 'slug' => 'business-bundle'],
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

    // Resolve Addons (Support multiple addon products)
    $addonProducts = [];
    $addonTotal = 0;

    $reqSlugs = [];
    if (!empty($input['addonSlugs']) && is_array($input['addonSlugs'])) {
        $reqSlugs = $input['addonSlugs'];
    } elseif (!empty($input['addonSlug'])) {
        $reqSlugs[] = $input['addonSlug'];
    } elseif (!empty($addonSlugs) && is_array($addonSlugs)) {
        $reqSlugs = $addonSlugs;
    }

    $reqIds = [];
    if (!empty($input['addonIds']) && is_array($input['addonIds'])) {
        $reqIds = $input['addonIds'];
    } elseif (!empty($input['addonProductId']) && (int)$input['addonProductId'] > 0) {
        $reqIds[] = (int)$input['addonProductId'];
    } elseif (!empty($addonProductId) && (int)$addonProductId > 0) {
        $reqIds[] = (int)$addonProductId;
    }

    if ($db) {
        foreach ($reqIds as $aId) {
            $aId = (int)$aId;
            if ($aId <= 0 || $aId == $mainProduct['id']) continue;
            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND active = 1");
            $stmt->execute([':id' => $aId]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($p && !isset($addonProducts[$p['id']])) {
                $addonProducts[$p['id']] = $p;
                $addonTotal += (int)$p['price'];
            }
        }
        foreach ($reqSlugs as $aSlug) {
            $aSlug = strtolower(trim($aSlug));
            if (!$aSlug || $aSlug === $mainProduct['slug']) continue;
            $stmt = $db->prepare("SELECT * FROM products WHERE slug = :slug AND active = 1");
            $stmt->execute([':slug' => $aSlug]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($p && !isset($addonProducts[$p['id']])) {
                $addonProducts[$p['id']] = $p;
                $addonTotal += (int)$p['price'];
            }
        }
    }

    // Fallback addon resolution if DB empty
    if (empty($addonProducts)) {
        if (in_array(4, $reqIds) || in_array('meta-ads-mastery', $reqSlugs) || $addonProductId == 4) {
            $addonProducts[4] = $fallbackCatalog[4];
            $addonTotal += (int)$fallbackCatalog[4]['price'];
        }
    }

    // Calculate discount
    $discountTotal = 0;
    $couponId = null;

    if (!empty($couponCode)) {
        $codeUpper = strtoupper($couponCode);
        if ($db) {
            try {
                $stmt = $db->prepare("SELECT * FROM coupons WHERE (UPPER(coupon_code) = :code OR UPPER(code) = :code) AND (active = 1 OR is_active = 1)");
                $stmt->execute([':code' => $codeUpper]);
                $cp = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                try {
                    $stmt = $db->prepare("SELECT * FROM coupons WHERE UPPER(coupon_code) = :code AND active = 1");
                    $stmt->execute([':code' => $codeUpper]);
                    $cp = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e2) {
                    $cp = null;
                }
            }
            if ($cp) {
                $couponId = (int)$cp['id'];
                $cType = strtoupper($cp['discount_type'] ?? 'PERCENTAGE');
                $cVal = (int)($cp['discount_value'] ?? 0);
                if (strpos($cType, 'PERCENT') !== false) {
                    $discountTotal = (int)round(($subtotal + $addonTotal) * ($cVal / 100));
                } else {
                    $discountTotal = ($cVal <= 1000 && ($subtotal + $addonTotal) > 1000) ? $cVal * 100 : $cVal;
                }
            }
        }
        if (!$couponId) {
            $fallbacks = [
                'SAS'      => 0.50,
                'EX100'    => 1.00,
                'ZAMZY100' => 1.00,
                'ZAMZY10'  => 0.10,
                'LAUNCH50' => 0.50,
                'SAVE50'   => 0.50,
            ];
            if (isset($fallbacks[$codeUpper])) {
                $discountTotal = (int)round(($subtotal + $addonTotal) * $fallbacks[$codeUpper]);
            }
        }
    }

    $totalAmount = max(0, $subtotal + $addonTotal - $discountTotal);

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

            try {
                $db->exec("ALTER TABLE orders ADD COLUMN customer_name TEXT");
                $db->exec("ALTER TABLE orders ADD COLUMN customer_email TEXT");
                $db->exec("ALTER TABLE orders ADD COLUMN customer_phone TEXT");
                $db->exec("ALTER TABLE orders ADD COLUMN product_name TEXT");
            } catch (Exception $e) {}

            $stmt = $db->prepare("
                INSERT INTO orders (order_number, customer_id, customer_name, customer_email, customer_phone, product_name, subtotal, addon_total, discount_total, total, currency, payment_status, order_status, coupon_id, ip_address)
                VALUES (:order_number, :customer_id, :customer_name, :customer_email, :customer_phone, :product_name, :subtotal, :addon_total, :discount_total, :total, 'INR', 'CREATED', 'CREATED', :coupon_id, :ip)
            ");
            $stmt->execute([
                ':order_number' => $orderNumber,
                ':customer_id' => $customerId,
                ':customer_name' => $customerName,
                ':customer_email' => strtolower($customerEmail),
                ':customer_phone' => $customerPhone,
                ':product_name' => $mainProduct['name'],
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
            foreach ($addonProducts as $addP) {
                $stmt->execute([':order_id' => $orderId, ':product_id' => $addP['id'], ':price' => (int)$addP['price'], ':is_addon' => 1]);
            }
        } catch (Exception $e) {
            error_log('[CHECKOUT_DB_ERROR] ' . $e->getMessage());
        }
    }

    // Load Razorpay Credentials directly from SQLite DB or env
    $razorpayKeyId = getenv('RAZORPAY_KEY_ID') ?: getenv('PAYMENT_API_KEY') ?: '';
    $razorpayKeySecret = getenv('RAZORPAY_KEY_SECRET') ?: getenv('PAYMENT_SECRET') ?: '';
    if ($db) {
        try {
            $stmt = $db->query("SELECT key, value FROM site_settings WHERE key IN ('razorpay_key_id', 'payment_api_key', 'razorpay_key_secret', 'payment_secret')");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($settings['razorpay_key_id'])) $razorpayKeyId = trim($settings['razorpay_key_id']);
            elseif (!empty($settings['payment_api_key'])) $razorpayKeyId = trim($settings['payment_api_key']);
            if (!empty($settings['razorpay_key_secret'])) $razorpayKeySecret = trim($settings['razorpay_key_secret']);
            elseif (!empty($settings['payment_secret'])) $razorpayKeySecret = trim($settings['payment_secret']);
        } catch (Exception $e) {}
    }

    $razorpayOrderId = null;
    $isLiveRazorpayKey = (!empty($razorpayKeyId) && strpos($razorpayKeyId, 'rzp_') === 0 && $razorpayKeyId !== 'rzp_test_default');

    // Create live Razorpay order via cURL if valid keys are present
    if ($isLiveRazorpayKey && !empty($razorpayKeySecret)) {
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

    if ($totalAmount === 0) {
        if ($db) {
            try {
                require_once __DIR__ . '/payment.php';
                $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
                $stmt->execute([$orderNumber]);
                $ordRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($ordRow) {
                    processOrderFulfillment($db, $ordRow, 'FREE_COUPON_100');
                }
            } catch (Exception $e) {}
        }
        echo json_encode([
            'success' => true,
            'orderNumber' => $orderNumber,
            'amount' => 0,
            'currency' => 'INR',
            'paymentMode' => 'free',
            'isFree' => true,
            'razorpayOrderId' => null,
            'razorpayKeyId' => null,
            'message' => '100% Free coupon applied. Access granted.'
        ]);
    } elseif ($isLiveRazorpayKey) {
        echo json_encode([
            'success' => true,
            'orderNumber' => $orderNumber,
            'amount' => $totalAmount,
            'currency' => 'INR',
            'paymentMode' => 'razorpay',
            'razorpayOrderId' => $razorpayOrderId,
            'razorpayKeyId' => $razorpayKeyId
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'orderNumber' => $orderNumber,
            'amount' => $totalAmount,
            'currency' => 'INR',
            'paymentMode' => 'mock',
            'razorpayOrderId' => null,
            'razorpayKeyId' => null,
            'message' => 'Payment mode set to test/mock as Razorpay API keys are unconfigured.'
        ]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Checkout warning: ' . $e->getMessage()
    ]);
}




