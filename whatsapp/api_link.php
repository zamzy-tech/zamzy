<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function teh_generate_random_key() {
    try {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(16));
        }
    } catch (Exception $e) {}
    try {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(16, $crypto_strong);
            if ($crypto_strong === true && $bytes !== false) {
                return bin2hex($bytes);
            }
        }
    } catch (Exception $e) {}
    return md5(uniqid(mt_rand(), true));
}

require_once __DIR__ . '/db.php';

session_start();

try {
    $settings_stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $settings_stmt->fetch();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

$success_msg = $_GET['staff_success'] ?? '';
$error_msg = $_GET['staff_error'] ?? '';

if (isset($_GET['cancel_reset'])) {
    unset($_SESSION['reset_otp'], $_SESSION['reset_phone'], $_SESSION['reset_expiry'], $_SESSION['reset_step']);
    header('Location: api_link.php');
    exit;
}

// Handle AJAX update connection status to SQLite only on change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_connection_status') {
    header('Content-Type: application/json');
    $connected = intval($_POST['connected'] ?? 0);
    $number = trim($_POST['number'] ?? '');
    $slot = intval($_POST['slot'] ?? 1);
    $api_key = $_SESSION['client_key'] ?? $_POST['api_key'] ?? '';
    
    if (!empty($api_key)) {
        try {
            // Find client id
            $cl_stmt = $pdo->prepare("SELECT id FROM api_keys WHERE api_key = ? LIMIT 1");
            $cl_stmt->execute([$api_key]);
            $client_id = $cl_stmt->fetchColumn();
            
            if ($client_id) {
                // Update client_devices
                $dev_upd = $pdo->prepare("REPLACE INTO client_devices (client_id, slot_number, whatsapp_linked_number, whatsapp_is_connected, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
                $dev_upd->execute([$client_id, $slot, $number, $connected]);
                
                // Backwards compatibility for primary slot 1
                if ($slot === 1) {
                    if ($connected === 1) {
                        $upd = $pdo->prepare("UPDATE api_keys SET whatsapp_is_connected = 1, whatsapp_linked_number = ? WHERE id = ?");
                        $upd->execute([$number, $client_id]);
                    } else {
                        $upd = $pdo->prepare("UPDATE api_keys SET whatsapp_is_connected = 0 WHERE id = ?");
                        $upd->execute([$client_id]);
                    }
                }
            }
            echo json_encode(['success' => true]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// 1. Handle AJAX Razorpay Payment logging & Expiry Extension
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_payment') {
    // Clear output buffers & disable displaying errors during AJAX response to ensure clean JSON output
    if (ob_get_length()) ob_clean();
    ini_set('display_errors', '0');
    error_reporting(0);
    
    header('Content-Type: application/json');
    $api_key = $_SESSION['client_key'] ?? $_POST['api_key'] ?? '';
    
    // Auto-login session recovery if session dropped but valid API Key was supplied
    if (empty($_SESSION['client_key']) && !empty($api_key)) {
        $_SESSION['client_key'] = $api_key;
    }
    
    $payment_id = trim($_POST['payment_id'] ?? '');
    $planName = trim($_POST['plan'] ?? '');
    $amount = doubleval($_POST['amount'] ?? 0);
    $duration = intval($_POST['duration'] ?? 1);
    $extra_scanners = intval($_POST['extra_scanners'] ?? 0);
    
    if (empty($api_key) || empty($payment_id)) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$api_key]);
        $cl = $stmt->fetch();
        
        if ($cl) {
            // Calculate new expiry date
            $current_expiry = $cl['expiry_date'];
            $base_time = (empty($current_expiry) || strtotime($current_expiry) < time()) ? time() : strtotime($current_expiry);
            $new_expiry = date('Y-m-d', strtotime("+$duration months", $base_time));
            
            // Set plan to unlimited credits
            $new_credits = -1; 
            
            // Calculate allowed scanners (Starter base = 1, Business base = 3)
            $base_scanners = (strpos(strtolower($planName), 'business') !== false) ? 3 : 1;
            $new_allowed_scanners = $base_scanners + $extra_scanners;
            
            $upd = $pdo->prepare("UPDATE api_keys SET expiry_date = ?, credits = ?, allowed_scanners = ?, status = 'active' WHERE id = ?");
            $upd->execute([$new_expiry, $new_credits, $new_allowed_scanners, $cl['id']]);
            
            // Increment coupon usage if code was supplied
            $coupon_code = trim($_POST['coupon_code'] ?? '');
            if (!empty($coupon_code)) {
                $upd_coupon = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
                $upd_coupon->execute([$coupon_code]);
            }
            
            // Log payment to transaction history table
            $ins_tx = $pdo->prepare("INSERT INTO client_payments (client_id, payment_id, plan_name, amount, duration, extra_scanners, coupon_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $ins_tx->execute([
                $cl['id'],
                $payment_id,
                $planName,
                $amount,
                $duration,
                $extra_scanners,
                $coupon_code
            ]);
            
            // Log transaction or send notification to admin via WhatsApp
            $admin_phone = $settings['whatsapp_linked_number'] ?? '';
            if (!empty($admin_phone) && !empty($settings['whatsapp_gateway_url'])) {
                $admin_msg = "💰 *Client Plan Renewed!*\n\n"
                           . "*Client:* " . $cl['client_name'] . " (" . $cl['client_phone'] . ")\n"
                           . "*Plan:* " . $planName . "\n"
                           . "*Duration:* " . $duration . " Month(s)\n"
                           . "*Extra Add-on Scanners:* " . $extra_scanners . " (Total Allowed: " . $new_allowed_scanners . ")\n"
                           . "*Amount Paid:* Rs " . $amount . "\n"
                           . "*Payment ID:* " . $payment_id . "\n"
                           . "*New Expiry:* " . date('d-M-Y', strtotime($new_expiry));
                           
                $payload = json_encode([
                    'to' => $admin_phone,
                    'message' => $admin_msg,
                    'token' => $settings['whatsapp_gateway_token'] ?? '',
                    'apikey' => $settings['whatsapp_gateway_token'] ?? ''
                ]);
                
                $ch = curl_init($settings['whatsapp_gateway_url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_exec($ch);
                curl_close($ch);
            }

            // Send confirmation receipt to client via WhatsApp (from admin gateway)
            $client_phone = $cl['client_phone'] ?? '';
            if (!empty($client_phone) && !empty($settings['whatsapp_gateway_url'])) {
                $client_receipt_msg = "🎉 *Subscription Renewal Successful!*\n\n"
                                    . "Dear *" . $cl['client_name'] . "*,\n\n"
                                    . "Thank you for your payment. Your developer account has been updated successfully.\n\n"
                                    . "*Plan:* " . $planName . "\n"
                                    . "*Duration:* " . $duration . " Month(s)\n"
                                    . "*Scanners Allowed:* " . $new_allowed_scanners . " Device(s)\n"
                                    . "*Amount Paid:* Rs " . $amount . "\n"
                                    . "*Transaction ID:* " . $payment_id . "\n"
                                    . "*New Expiry Date:* " . date('d-M-Y', strtotime($new_expiry)) . "\n\n"
                                    . "Best regards,\n"
                                    . "*" . ($settings['company_name'] ?? 'THE EXPERT HUB') . "*";
                
                $payload_client = json_encode([
                    'to' => $client_phone,
                    'message' => $client_receipt_msg,
                    'token' => $settings['whatsapp_gateway_token'] ?? '',
                    'apikey' => $settings['whatsapp_gateway_token'] ?? ''
                ]);
                
                $ch = curl_init($settings['whatsapp_gateway_url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_client);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_exec($ch);
                curl_close($ch);
            }
            
            echo json_encode(['success' => true, 'new_expiry' => date('d-M-Y', strtotime($new_expiry))]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Client account not found.']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// 2. Handle WhatsApp status proxy checks
if (isset($_GET['action']) && $_GET['action'] === 'check_status') {
    header('Content-Type: application/json');
    $api_key = trim($_GET['api_key'] ?? '');
    
    if (empty($api_key)) {
        echo json_encode(['status' => 'DISCONNECTED', 'error' => 'API key missing']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$api_key]);
        $client = $stmt->fetch();
        
        if (!$client || $client['status'] !== 'active') {
            echo json_encode(['status' => 'DISCONNECTED', 'error' => 'Invalid or suspended API key']);
            exit;
        }
        
        $gateway_url = $settings['whatsapp_gateway_url'] ?? '';
        $gateway_token = $settings['whatsapp_gateway_token'] ?? '';
        
        if (empty($gateway_url)) {
            echo json_encode(['status' => 'DISCONNECTED', 'info' => 'Admin WhatsApp Gateway URL is not configured. Please contact the administrator.']);
            exit;
        }
        
        // Construct status URL with session parameters to identify this client
        $status_url = str_replace('/send', '/status', $gateway_url);
        $status_url .= (strpos($status_url, '?') !== false ? '&' : '?') . 'session=' . urlencode($client['login_id'])
                     . '&id=' . urlencode($client['login_id'])
                     . '&session_id=' . urlencode($client['login_id'])
                     . '&instance=' . urlencode($client['login_id'])
                     . '&t=' . time();
        
        $ch = curl_init($status_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if (!empty($gateway_token)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $gateway_token",
                "Accept: application/json"
            ]);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $http_code >= 400) {
            echo json_encode(['status' => 'OFFLINE', 'error' => 'Gateway offline']);
            exit;
        }
        
        $data = json_decode($response, true);
        
        // Auto-update connection status in database
        if (isset($data['status']) && $data['status'] === 'CONNECTED') {
            $number = $data['number'] ?? 'Unknown';
            $upd = $pdo->prepare("UPDATE api_keys SET whatsapp_is_connected = 1, whatsapp_linked_number = ? WHERE id = ?");
            $upd->execute([$number, $client['id']]);
        } else {
            $upd = $pdo->prepare("UPDATE api_keys SET whatsapp_is_connected = 0 WHERE id = ?");
            $upd->execute([$client['id']]);
        }
        
        echo $response;
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'DISCONNECTED', 'error' => $e->getMessage()]);
        exit;
    }
}

// Handle AJAX coupon verification
if (isset($_GET['action']) && $_GET['action'] === 'verify_coupon') {
    header('Content-Type: application/json');
    $code = strtoupper(trim($_GET['code'] ?? ''));
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'error' => 'Coupon code is empty.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();
        
        if (!$coupon) {
            echo json_encode(['success' => false, 'error' => 'Invalid or inactive coupon code.']);
            exit;
        }
        
        // Check expiry date
        if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < time()) {
            echo json_encode(['success' => false, 'error' => 'This coupon has expired.']);
            exit;
        }
        
        // Check usage limit
        if ($coupon['usage_limit'] !== null && $coupon['used_count'] >= $coupon['usage_limit']) {
            echo json_encode(['success' => false, 'error' => 'This coupon has reached its usage limit.']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => floatval($coupon['discount_value'])
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// 3. Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $login_id = preg_replace('/[^0-9]/', '', $_POST['login_id'] ?? '');
    $login_password = trim($_POST['login_password'] ?? '');
    
    if (empty($login_id)) {
        $error_msg = 'Please enter your Login ID (Phone Number).';
    } elseif (empty($login_password)) {
        $error_msg = 'Please enter your password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE login_id = ? LIMIT 1");
            $stmt->execute([$login_id]);
            $client = $stmt->fetch();
            
            if ($client) {
                if ($client['status'] !== 'active') {
                    $error_msg = 'This account is suspended. Please contact the administrator.';
                } elseif (password_verify($login_password, $client['login_password'] ?? '')) {
                    $_SESSION['client_key'] = $client['api_key']; // Store the api_key in session
                } else {
                    $error_msg = 'Invalid Login ID or password. Please try again.';
                }
            } else {
                $error_msg = 'Invalid Login ID or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error_msg = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle Request OTP AJAX (Forgot Password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_otp_ajax') {
    header('Content-Type: application/json');
    $reset_phone = preg_replace('/[^0-9]/', '', $_POST['reset_phone'] ?? '');
    
    if (empty($reset_phone)) {
        echo json_encode(['success' => false, 'error' => 'Please enter your registered mobile number.']);
        exit;
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE login_id = ? LIMIT 1");
            $stmt->execute([$reset_phone]);
            $cl = $stmt->fetch();
            
            if ($cl) {
                $otp = strval(rand(100000, 999999));
                
                $_SESSION['reset_otp'] = $otp;
                $_SESSION['reset_phone'] = $reset_phone;
                $_SESSION['reset_expiry'] = time() + 600;
                $_SESSION['reset_step'] = 'verify';
                
                $gateway_url = $settings['whatsapp_gateway_url'] ?? '';
                $gateway_token = $settings['whatsapp_gateway_token'] ?? '';
                
                $otp_msg = "🔒 *Password Reset Verification Code*\n\n"
                         . "Your verification code to reset your password is *`{$otp}`*.\n"
                         . "This code is valid for 10 minutes. Do not share it with anyone.";
                         
                echo json_encode([
                    'success' => true,
                    'message_text' => $otp_msg,
                    'phone' => $reset_phone,
                    'gateway_url' => $gateway_url,
                    'gateway_token' => $gateway_token
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'error' => 'This mobile number is not registered.']);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }
}

// Handle Signup Request OTP AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup_request_otp_ajax') {
    header('Content-Type: application/json');
    $signup_phone    = preg_replace('/[^0-9]/', '', $_POST['signup_phone'] ?? '');
    $signup_name     = trim($_POST['signup_name'] ?? '');
    $signup_password = trim($_POST['signup_password'] ?? '');
    $signup_confirm  = trim($_POST['signup_confirm'] ?? '');

    if (empty($signup_phone) || strlen($signup_phone) < 10) {
        echo json_encode(['success' => false, 'error' => 'Please enter a valid mobile number (with country code, e.g. 919876543210).']);
        exit;
    } elseif (empty($signup_name)) {
        echo json_encode(['success' => false, 'error' => 'Please enter your full name.']);
        exit;
    } elseif (strlen($signup_password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
        exit;
    } elseif ($signup_password !== $signup_confirm) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
        exit;
    } else {
        try {
            // Check if number already registered
            $chk = $pdo->prepare("SELECT id FROM api_keys WHERE login_id = ? LIMIT 1");
            $chk->execute([$signup_phone]);
            if ($chk->fetch()) {
                echo json_encode(['success' => false, 'error' => 'This mobile number is already registered. Please log in instead.']);
                exit;
            } else {
                $otp = strval(rand(100000, 999999));
                
                $_SESSION['signup_otp']      = $otp;
                $_SESSION['signup_phone']    = $signup_phone;
                $_SESSION['signup_name']     = $signup_name;
                $_SESSION['signup_password'] = password_hash($signup_password, PASSWORD_BCRYPT);
                $_SESSION['signup_expiry']   = time() + 600;
                $_SESSION['signup_step']     = 'verify';
                
                $gateway_url   = $settings['whatsapp_gateway_url']   ?? '';
                $gateway_token = $settings['whatsapp_gateway_token']  ?? '';
                
                $otp_msg = "👋 *Welcome to Developer Hub!*\n\n"
                         . "Your registration OTP is *{$otp}*.\n"
                         . "This code is valid for 10 minutes. Do not share it with anyone.";
                         
                echo json_encode([
                    'success' => true,
                    'message_text' => $otp_msg,
                    'phone' => $signup_phone,
                    'gateway_url' => $gateway_url,
                    'gateway_token' => $gateway_token
                ]);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }
}

// Handle Request OTP (Forgot Password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_otp') {
    $reset_phone = preg_replace('/[^0-9]/', '', $_POST['reset_phone'] ?? '');
    
    if (empty($reset_phone)) {
        $error_msg = 'Please enter your registered mobile number.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE login_id = ? LIMIT 1");
            $stmt->execute([$reset_phone]);
            $cl = $stmt->fetch();
            
            if ($cl) {
                $otp = strval(rand(100000, 999999));
                $temp_session = [
                    'reset_otp' => $otp,
                    'reset_phone' => $reset_phone,
                    'reset_expiry' => time() + 600
                ];
                
                $gateway_url = $settings['whatsapp_gateway_url'] ?? '';
                $gateway_token = $settings['whatsapp_gateway_token'] ?? '';
                
                if (!empty($gateway_url)) {
                    $otp_msg = "🔒 *Password Reset Verification Code*\n\n"
                             . "Your verification code to reset your password is *`{$otp}`*.\n"
                             . "This code is valid for 10 minutes. Do not share it with anyone.";
                            
                    $payload = json_encode([
                        'to' => $reset_phone,
                        'message' => $otp_msg,
                        'token' => $gateway_token,
                        'apikey' => $gateway_token
                    ]);
                    
                    $ch = curl_init($gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json'
                    ]);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                    $res = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($res !== false && $http_code < 400) {
                        $_SESSION['reset_otp'] = $temp_session['reset_otp'];
                        $_SESSION['reset_phone'] = $temp_session['reset_phone'];
                        $_SESSION['reset_expiry'] = $temp_session['reset_expiry'];
                        $_SESSION['reset_step'] = 'verify';
                        $success_msg = 'A 6-digit verification code has been sent to your WhatsApp number.';
                    } else {
                        $error_msg = 'Failed to send WhatsApp verification code. Please check gateway status.';
                    }
                } else {
                    $error_msg = 'WhatsApp Gateway is offline. Please contact the administrator.';
                }
            } else {
                $error_msg = 'This mobile number is not registered.';
            }
        } catch (PDOException $e) {
            $error_msg = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle Verify OTP and Reset Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    $otp_entered = trim($_POST['otp_code'] ?? '');
    $new_pass = trim($_POST['new_password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');
    
    if (empty($otp_entered) || empty($new_pass) || empty($confirm_pass)) {
        $error_msg = 'Please fill in all verification and password fields.';
    } elseif ($new_pass !== $confirm_pass) {
        $error_msg = 'Passwords do not match.';
    } elseif (!isset($_SESSION['reset_otp']) || time() > $_SESSION['reset_expiry']) {
        $error_msg = 'Verification session has expired. Please request a new OTP.';
    } elseif ($otp_entered !== $_SESSION['reset_otp']) {
        $error_msg = 'Invalid OTP verification code.';
    } else {
        try {
            $pass_hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE api_keys SET login_password = ? WHERE login_id = ?");
            $upd->execute([$pass_hash, $_SESSION['reset_phone']]);
            
            $success_msg = 'Password changed successfully! You can now log in.';
            
            // Clear reset session
            unset($_SESSION['reset_otp'], $_SESSION['reset_phone'], $_SESSION['reset_expiry'], $_SESSION['reset_step']);
        } catch (PDOException $e) {
            $error_msg = 'Database error: ' . $e->getMessage();
        }
    }
}

// ── SIGNUP: Step 1 — Request OTP ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup_request_otp') {
    $signup_phone    = preg_replace('/[^0-9]/', '', $_POST['signup_phone'] ?? '');
    $signup_name     = trim($_POST['signup_name'] ?? '');
    $signup_password = trim($_POST['signup_password'] ?? '');
    $signup_confirm  = trim($_POST['signup_confirm'] ?? '');

    if (empty($signup_phone) || strlen($signup_phone) < 10) {
        $error_msg = 'Please enter a valid mobile number (with country code, e.g. 919876543210).';
    } elseif (empty($signup_name)) {
        $error_msg = 'Please enter your full name.';
    } elseif (strlen($signup_password) < 6) {
        $error_msg = 'Password must be at least 6 characters.';
    } elseif ($signup_password !== $signup_confirm) {
        $error_msg = 'Passwords do not match.';
    } else {
        try {
            // Check if number already registered
            $chk = $pdo->prepare("SELECT id FROM api_keys WHERE login_id = ? LIMIT 1");
            $chk->execute([$signup_phone]);
            if ($chk->fetch()) {
                $error_msg = 'This mobile number is already registered. Please log in instead.';
            } else {
                $otp = strval(rand(100000, 999999));
                $gateway_url   = $settings['whatsapp_gateway_url']   ?? '';
                $gateway_token = $settings['whatsapp_gateway_token']  ?? '';

                if (empty($gateway_url)) {
                    $error_msg = 'WhatsApp Gateway is not configured. Please contact the administrator.';
                } else {
                    $otp_msg = "👋 *Welcome to Developer Hub!*\n\n"
                             . "Your registration OTP is *{$otp}*.\n"
                             . "This code is valid for 10 minutes. Do not share it with anyone.";

                    $payload = json_encode([
                        'to'     => $signup_phone,
                        'message'=> $otp_msg,
                        'token'  => $gateway_token,
                        'apikey' => $gateway_token
                    ]);

                    $ch = curl_init($gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
                    curl_setopt($ch, CURLOPT_POST,            true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,      $payload);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_TIMEOUT,         6);
                    $res       = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($res !== false && $http_code < 400) {
                        $_SESSION['signup_otp']      = $otp;
                        $_SESSION['signup_phone']    = $signup_phone;
                        $_SESSION['signup_name']     = $signup_name;
                        $_SESSION['signup_password'] = password_hash($signup_password, PASSWORD_BCRYPT);
                        $_SESSION['signup_expiry']   = time() + 600;
                        $_SESSION['signup_step']     = 'verify';
                        $success_msg = "A 6-digit OTP has been sent to WhatsApp number +{$signup_phone}. Enter it below to complete registration.";
                    } else {
                        $error_msg = 'Failed to send WhatsApp OTP. Please check gateway status or try again.';
                    }
                }
            }
        } catch (PDOException $e) {
            $error_msg = 'Database error: ' . $e->getMessage();
        }
    }
}

// ── SIGNUP: Step 2 — Verify OTP & Create Account ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup_verify_otp') {
    $otp_entered = trim($_POST['signup_otp_code'] ?? '');

    if (empty($otp_entered)) {
        $error_msg = 'Please enter the OTP sent to your WhatsApp.';
    } elseif (!isset($_SESSION['signup_otp']) || !isset($_SESSION['signup_step']) || $_SESSION['signup_step'] !== 'verify') {
        $error_msg = 'Signup session not found. Please start signup again.';
    } elseif (time() > $_SESSION['signup_expiry']) {
        unset($_SESSION['signup_otp'], $_SESSION['signup_phone'], $_SESSION['signup_name'], $_SESSION['signup_password'], $_SESSION['signup_expiry'], $_SESSION['signup_step']);
        $error_msg = 'OTP has expired. Please start the signup process again.';
    } elseif ($otp_entered !== $_SESSION['signup_otp']) {
        $error_msg = 'Invalid OTP. Please check and try again.';
    } else {
        try {
            $new_api_key = teh_generate_random_key();
            $login_id    = $_SESSION['signup_phone'];
            $client_name = $_SESSION['signup_name'];
            $pass_hash   = $_SESSION['signup_password'];
            $trial_expiry = date('Y-m-d H:i:s', time() + 86400);

            $ins = $pdo->prepare("INSERT INTO api_keys (client_name, api_key, credits, status, client_phone, login_id, login_password, expiry_date) VALUES (?, ?, -1, 'active', ?, ?, ?, ?)");
            $ins->execute([$client_name, $new_api_key, $login_id, $login_id, $pass_hash, $trial_expiry]);

            // Seed default templates for new client
            $pdo->prepare("UPDATE api_keys SET
                template_otp     = 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.',
                template_invoice = 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}',
                template_general = '{message}'
            WHERE login_id = ?")->execute([$login_id]);

            // Auto-login the new user
            $_SESSION['client_key'] = $new_api_key;

            // Clear signup session
            unset($_SESSION['signup_otp'], $_SESSION['signup_phone'], $_SESSION['signup_name'], $_SESSION['signup_password'], $_SESSION['signup_expiry'], $_SESSION['signup_step']);

            // Notify admin via WhatsApp
            $admin_phone = $settings['whatsapp_linked_number'] ?? '';
            if (!empty($admin_phone) && !empty($settings['whatsapp_gateway_url'])) {
                $adm_msg = "🆕 *New Developer Signup!*\n\n*Name:* {$client_name}\n*Phone:* +{$login_id}";
                $ch = curl_init($settings['whatsapp_gateway_url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['to'=>$admin_phone,'message'=>$adm_msg,'token'=>$settings['whatsapp_gateway_token']??'','apikey'=>$settings['whatsapp_gateway_token']??'']));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_exec($ch); curl_close($ch);
            }

            $success_msg = "Account created! Welcome, {$client_name}! Redirecting to your dashboard...";
            header('Refresh: 1; url=api_link.php');
        } catch (PDOException $e) {
            $error_msg = 'Failed to create account: ' . $e->getMessage();
        }
    }
}

// Load authenticated client details
$client = null;
if (isset($_SESSION['client_key'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$_SESSION['client_key']]);
        $client = $stmt->fetch();
        if ($client) {
            // Pre-generate/sync API keys for all allowed scanner slots in client_devices table
            $scanners_count = intval($client['allowed_scanners'] ?? 1);
            for ($i = 1; $i <= $scanners_count; $i++) {
                $dev_chk = $pdo->prepare("SELECT * FROM client_devices WHERE client_id = ? AND slot_number = ? LIMIT 1");
                $dev_chk->execute([$client['id'], $i]);
                $dev = $dev_chk->fetch();
                
                if (!$dev) {
                    // Generate new API Key
                    $new_slot_key = 'teh_api_' . teh_generate_random_key();
                    if ($i === 1 && !empty($client['api_key'])) {
                        $new_slot_key = $client['api_key']; // Slot 1 defaults to the main API key
                    }
                    $ins = $pdo->prepare("INSERT INTO client_devices (client_id, slot_number, api_key, whatsapp_is_connected) VALUES (?, ?, ?, 0)");
                    $ins->execute([$client['id'], $i, $new_slot_key]);
                } elseif (empty($dev['api_key'])) {
                    $new_slot_key = 'teh_api_' . teh_generate_random_key();
                    if ($i === 1 && !empty($client['api_key'])) {
                        $new_slot_key = $client['api_key'];
                    }
                    $upd = $pdo->prepare("UPDATE client_devices SET api_key = ? WHERE id = ?");
                    $upd->execute([$new_slot_key, $dev['id']]);
                }
            }
        }
        if (!$client || $client['status'] !== 'active') {
            unset($_SESSION['client_key']);
            $client = null;
        }
    } catch (PDOException $e) {
        $error_msg = 'Database retrieval error: ' . $e->getMessage();
    }
}

// 4. Handle Save Message Templates (Client Dashboard)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_client_templates') {
    $template_otp = trim($_POST['template_otp'] ?? '');
    $template_invoice = trim($_POST['template_invoice'] ?? '');
    $template_general = trim($_POST['template_general'] ?? '');
    
    if ($client) {
        try {
            $stmt = $pdo->prepare("UPDATE api_keys SET 
                template_otp = ?, 
                template_invoice = ?, 
                template_general = ?
                WHERE id = ?");
            $stmt->execute([$template_otp, $template_invoice, $template_general, $client['id']]);
            $success_msg = 'Your API message templates have been updated successfully!';
            
            // Refresh details
            $client['template_otp'] = $template_otp;
            $client['template_invoice'] = $template_invoice;
            $client['template_general'] = $template_general;
        } catch (PDOException $e) {
            $error_msg = 'Failed to update templates: ' . $e->getMessage();
        }
    }
}

// 5. Handle Support Query submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact_submit') {
    $subject = trim($_POST['subject'] ?? '');
    $msg = trim($_POST['message'] ?? '');
    
    if (empty($subject) || empty($msg)) {
        $error_msg = 'Please fill in both subject and message fields.';
    } else {
        // Send support query to admin's WhatsApp
        $admin_phone = $settings['whatsapp_linked_number'] ?? '';
        if (!empty($admin_phone) && !empty($settings['whatsapp_gateway_url'])) {
            $admin_msg = "📩 *New Support Message!*\n\n"
                       . "*Client:* " . ($client['client_name'] ?? 'Unknown') . " (" . ($client['client_phone'] ?? '') . ")\n"
                       . "*Subject:* " . $subject . "\n"
                       . "*Message:* " . $msg;
                      
            $payload = json_encode([
                'to' => $admin_phone,
                'message' => $admin_msg,
                'token' => $settings['whatsapp_gateway_token'] ?? '',
                'apikey' => $settings['whatsapp_gateway_token'] ?? ''
            ]);
            
            $ch = curl_init($settings['whatsapp_gateway_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_exec($ch);
            curl_close($ch);
            
            $success_msg = 'Your message has been sent successfully. Support team will contact you shortly.';
        } else {
            $error_msg = 'Support channels are temporarily offline. Please try again later.';
        }
    }
}

// Handle Profile Password Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password_profile') {
    $current_pass = trim($_POST['current_password'] ?? '');
    $new_pass = trim($_POST['new_password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');
    
    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error_msg = 'Please fill in all password fields.';
    } elseif ($new_pass !== $confirm_pass) {
        $error_msg = 'New password and confirm password do not match.';
    } elseif (strlen($new_pass) < 6) {
        $error_msg = 'New password must be at least 6 characters long.';
    } else {
        // Verify current password
        $api_key = $_SESSION['client_key'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$api_key]);
        $cl = $stmt->fetch();
        
        if ($cl && password_verify($current_pass, $cl['password'])) {
            // Update password
            $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE api_keys SET password = ? WHERE id = ?");
            $upd->execute([$new_hash, $cl['id']]);
            $success_msg = 'Password changed successfully!';
        } else {
            $error_msg = 'Current password is incorrect.';
        }
    }
}

// ── Handle Chatbot Rules Backend Actions ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $client) {
    
    // Action: Add / Edit Rule
    if ($_POST['action'] === 'add_chatbot_rule' || $_POST['action'] === 'edit_chatbot_rule') {
        $rule_id = intval($_POST['rule_id'] ?? 0);
        $keyword = trim($_POST['keyword'] ?? '');
        $reply_text = trim($_POST['reply_text'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        if (empty($image_url)) {
            $image_url = null;
        }
        
        $buttons_input = trim($_POST['buttons_list'] ?? '');
        $buttons_array = [];
        if (!empty($buttons_input)) {
            $parts = explode(',', $buttons_input);
            foreach ($parts as $p) {
                $p = trim($p);
                if (empty($p)) continue;
                if (strpos($p, '|') !== false) {
                    $subparts = explode('|', $p);
                    $btn_text = trim($subparts[0]);
                    $btn_url = trim($subparts[1]);
                    $buttons_array[] = [
                        'type' => 'url',
                        'text' => $btn_text,
                        'url' => $btn_url
                    ];
                } else {
                    $buttons_array[] = [
                        'type' => 'reply',
                        'text' => $p,
                        'id' => strtolower($p)
                    ];
                }
            }
        }
        $buttons_json = !empty($buttons_array) ? json_encode($buttons_array) : null;
        
        if (empty($keyword) || empty($reply_text)) {
            $error_msg = 'Please fill in both the keyword and the response text.';
        } else {
            try {
                if ($_POST['action'] === 'edit_chatbot_rule' && $rule_id > 0) {
                    // Check duplicate keyword for another rule
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM chatbot_rules WHERE client_id = ? AND LOWER(keyword) = ? AND id != ?");
                    $chk->execute([$client['id'], strtolower($keyword), $rule_id]);
                    if ($chk->fetchColumn() > 0) {
                        $error_msg = "A rule for the keyword '{$keyword}' already exists.";
                    } else {
                        $upd = $pdo->prepare("UPDATE chatbot_rules SET keyword = ?, reply_text = ?, image_url = ?, buttons_json = ? WHERE id = ? AND client_id = ?");
                        $upd->execute([$keyword, $reply_text, $image_url, $buttons_json, $rule_id, $client['id']]);
                        $success_msg = 'Chatbot rule updated successfully!';
                    }
                } else {
                    // Check duplicate keyword for new rule
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM chatbot_rules WHERE client_id = ? AND LOWER(keyword) = ?");
                    $chk->execute([$client['id'], strtolower($keyword)]);
                    if ($chk->fetchColumn() > 0) {
                        $error_msg = "A rule for the keyword '{$keyword}' already exists.";
                    } else {
                        $ins = $pdo->prepare("INSERT INTO chatbot_rules (client_id, keyword, reply_text, image_url, buttons_json) VALUES (?, ?, ?, ?, ?)");
                        $ins->execute([$client['id'], $keyword, $reply_text, $image_url, $buttons_json]);
                        $success_msg = 'Chatbot rule added successfully!';
                    }
                }
            } catch (PDOException $e) {
                $error_msg = 'Failed to save rule: ' . $e->getMessage();
            }
        }
    }
    
    // Action: Delete Rule
    if ($_POST['action'] === 'delete_chatbot_rule') {
        $rule_id = intval($_POST['rule_id'] ?? 0);
        try {
            $del = $pdo->prepare("DELETE FROM chatbot_rules WHERE id = ? AND client_id = ?");
            $del->execute([$rule_id, $client['id']]);
            $success_msg = 'Chatbot rule deleted successfully!';
        } catch (PDOException $e) {
            $error_msg = 'Failed to delete rule: ' . $e->getMessage();
        }
    }
    
    // Action: Toggle Chatbot State
    if ($_POST['action'] === 'toggle_chatbot') {
        $enabled = intval($_POST['enabled'] ?? 0);
        try {
            $upd = $pdo->prepare("UPDATE api_keys SET chatbot_enabled = ? WHERE id = ?");
            $upd->execute([$enabled, $client['id']]);
            $client['chatbot_enabled'] = $enabled; // Update local variable for rendering
            $success_msg = $enabled ? 'Chatbot enabled successfully!' : 'Chatbot disabled successfully!';
        } catch (PDOException $e) {
            $error_msg = 'Failed to toggle chatbot state: ' . $e->getMessage();
        }
    }
}

// 6. Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['client_key']);
    header('Location: api_link.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZAMZY WhatsApp Gateway — Command Center &amp; Developer Console</title>
<meta name="description" content="Manage your ZAMZY WhatsApp clusters, REST API tokens, QR pairings, multi-tenant webhook listeners, and AI chatbot automation engine.">
<link rel="shortcut icon" href="zamzy_logo.png" type="image/png">
<link rel="icon" href="zamzy_logo.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<style>
  :root {
    --bg-main: #05060b;
    --bg-surface: #0a0c16;
    --bg-card: rgba(14, 17, 30, 0.85);
    --bg-elev: #141829;
    --border-color: rgba(255, 255, 255, 0.08);
    --border-color-soft: rgba(255, 255, 255, 0.04);
    
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    
    --cyan: #00ffcc;
    --cyan-glow: rgba(0, 255, 204, 0.22);
    --purple: #9d4edd;
    --purple-neon: #c77dff;
    --purple-glow: rgba(157, 78, 221, 0.25);
    --wa-green: #25D366;
    
    --lime: #00ffcc; /* Primary accent updated to ZAMZY Electric Cyan */
    --lime-deep: #00d6aa;
    --lime-glow: rgba(0, 255, 204, 0.25);
    
    --font-heading: 'Space Grotesk', 'Outfit', sans-serif;
    --font-title: 'Space Grotesk', 'Outfit', sans-serif;
    --font-body: 'Inter', sans-serif;
    --font-mono: 'IBM Plex Mono', monospace;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: var(--font-body);
    background-color: var(--bg-main);
    color: var(--text-primary);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 32px 20px;
    background-image: 
      radial-gradient(circle at 15% 15%, rgba(157, 78, 221, 0.12) 0%, transparent 45%),
      radial-gradient(circle at 85% 85%, rgba(0, 255, 204, 0.1) 0%, transparent 45%),
      linear-gradient(to bottom, #05060b, #070913);
    background-attachment: fixed;
  }
  
  /* Left Sidebar + Right Main Content Layout */
  .portal-layout {
    display: flex;
    gap: 28px;
    width: 100%;
    max-width: 1320px;
    align-items: flex-start;
    animation: fadeIn 0.4s ease-out;
  }
  
  .sidebar-menu {
    width: 280px;
    flex-shrink: 0;
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 28px 20px;
    display: flex;
    flex-direction: column;
    min-height: 640px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
    position: sticky;
    top: 24px;
  }
  
  .sidebar-brand {
    margin-bottom: 24px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--border-color-soft);
  }
  .brand-title {
    font-family: var(--font-heading);
    font-size: 19px;
    font-weight: 800;
    color: #FFFFFF;
    letter-spacing: -0.2px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .brand-sub {
    font-size: 9.5px;
    color: var(--lime);
    letter-spacing: 1.5px;
    font-weight: 700;
    margin-top: 6px;
    text-transform: uppercase;
  }
  
  .sidebar-section-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--lime);
    letter-spacing: 1.5px;
    margin-bottom: 12px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  
  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
  }
  
  .nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: transparent;
    border: 1px solid transparent;
    color: var(--text-secondary);
    padding: 12.5px 16px;
    font-family: var(--font-title);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    border-radius: 12px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    text-align: left;
    width: 100%;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .nav-item .nav-num {
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 500;
  }
  .nav-item:hover {
    background: var(--bg-card);
    color: #FFFFFF;
    border-color: var(--border-color-soft);
  }
  .nav-item.active {
    background: var(--lime);
    color: #0A0A0C;
    font-weight: 800;
    box-shadow: 0 4px 20px rgba(212, 255, 61, 0.25);
  }
  .nav-item.active .nav-num {
    color: #0A0A0C;
    font-weight: 800;
  }
  
  .sidebar-footer {
    margin-top: 24px;
    padding-top: 18px;
    border-top: 1px solid var(--border-color-soft);
  }
  .user-profile-mini {
    background: var(--bg-card);
    border: 1px solid var(--border-color-soft);
    padding: 12px 14px;
    border-radius: 12px;
    margin-bottom: 12px;
  }
  .user-profile-mini .u-name {
    font-size: 13px;
    font-weight: 700;
    color: #FFFFFF;
  }
  .user-profile-mini .u-role {
    font-size: 10.5px;
    color: var(--text-muted);
    margin-top: 2px;
  }
  .btn-logout-sidebar {
    display: block;
    text-align: center;
    padding: 11px;
    border-radius: 10px;
    background: rgba(248, 113, 113, 0.08);
    border: 1px solid rgba(248, 113, 113, 0.2);
    color: #f87171;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .btn-logout-sidebar:hover {
    background: #f87171;
    color: #FFFFFF;
  }

  .main-content-area {
    flex: 1;
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 36px 40px;
    min-height: 620px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    width: 100%;
  }

  /* Screenshot 2 Typography & Section Headers */
  .section-tag {
    font-size: 11px;
    font-weight: 700;
    color: var(--lime);
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .section-title {
    font-family: var(--font-heading);
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.5px;
    text-transform: uppercase;
    color: #FFFFFF;
    line-height: 1.05;
    margin-bottom: 28px;
  }
  
  .login-card {
    background-color: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 40px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.7);
    animation: fadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .login-card input {
    background: var(--bg-card);
    border: 1.5px solid var(--border-color);
    border-radius: 12px;
    padding: 14px 18px;
    font-size: 14.5px;
    color: var(--text-primary);
    outline: none;
    transition: all 0.25s ease;
  }
  .login-card input:focus {
    border-color: var(--lime);
    box-shadow: 0 0 0 3px var(--lime-glow);
    background: var(--bg-elev);
  }
  .login-card .btn-submit {
    background: var(--lime);
    color: var(--bg-main);
    border-radius: 12px;
    padding: 14px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.5px;
    border: none;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .login-card .btn-submit:hover {
    transform: translateY(-1.5px);
    background: var(--lime-deep);
    box-shadow: 0 6px 24px rgba(212, 255, 61, 0.2);
  }
  .login-card .btn-submit:active {
    transform: translateY(0);
  }
  .login-card p {
    line-height: 1.6;
    color: var(--text-secondary);
  }
  .login-card a {
    color: var(--lime);
    font-weight: 600;
    transition: color 0.2s ease;
  }
  .login-card a:hover {
    color: var(--lime-deep);
    text-decoration: underline;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .header-logo {
    text-align: center;
    margin-bottom: 28px;
  }
  .logo { font-size: 26px; font-weight: 700; color: var(--lime); letter-spacing: 0.5px; font-family: var(--font-heading); }
  .tagline { font-size: 10px; color: var(--text-muted); margin-top: 5px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
  
  .tab-panel { display: none; }
  .tab-panel.active { display: block; }
  
  .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
  label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
  
  input, textarea, select {
    background: var(--bg-card);
    border: 1.5px solid var(--border-color);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    color: var(--text-primary);
    outline: none;
    transition: all 0.2s;
    font-family: var(--font-body);
  }
  input:focus, textarea:focus, select:focus { border-color: var(--lime); box-shadow: 0 0 0 3px var(--lime-glow); }
  
  .btn-submit {
    background: var(--lime);
    color: var(--bg-main);
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
    text-align: center;
  }
  .btn-submit:hover { background: var(--lime-deep); transform: translateY(-1px); }
  
  .alert { padding: 12px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; margin-bottom: 20px; line-height: 1.4; }
  .alert.success { background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.25); color: #34d399; }
  .alert.error { background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.25); color: #f87171; }
  
  /* Dashboard layouts */
  .grid-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
  }
  .stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 20px;
  }
  .stat-card .label { font-size: 11px; text-transform: uppercase; color: var(--lime); font-weight: 700; letter-spacing: 0.5px; }
  .stat-card .val { font-size: 24px; font-weight: 800; color: #FFFFFF; margin-top: 6px; font-family: var(--font-title); letter-spacing: -0.3px; }
  .stat-card .desc { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
  
  /* Copyable API container */
  .api-key-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 12px 16px;
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--lime);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    word-break: break-all;
  }
  .btn-copy {
    background: rgba(212, 255, 61, 0.08);
    border: 1px solid rgba(212, 255, 61, 0.15);
    color: var(--lime);
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 11px;
    font-family: inherit;
    font-weight: 700;
    transition: all 0.2s;
  }
  .btn-copy:hover { background: var(--lime); color: var(--bg-main); }
  
  /* QR Code styling */
  .qr-card {
    border: 2px dashed var(--border-color);
    background: var(--bg-card);
    border-radius: 16px;
    padding: 32px;
    text-align: center;
    margin-top: 24px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 270px;
    transition: all 0.3s;
  }
  .qr-image {
    max-width: 190px;
    border-radius: 8px;
    background: #fff;
    padding: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
  }
  .qr-status-msg { font-size: 13px; font-weight: 600; margin-top: 14px; color: var(--text-muted); }
  
  /* Price plans layout */
  .renew-panel {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 24px 32px;
  }
  .dynamic-calculator {
    background: var(--bg-surface);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    border: 1.5px solid rgba(212, 255, 61, 0.15);
  }
  .calculator-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--lime);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .receipt-row {
    display: flex;
    justify-content: space-between;
    font-size: 13.5px;
    color: var(--text-secondary);
    margin-bottom: 8px;
  }
  .receipt-row.total {
    border-top: 1px solid var(--border-color);
    padding-top: 12px;
    margin-top: 12px;
    font-weight: 700;
    color: var(--text-primary);
    font-size: 16px;
  }
  
  .plans-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
  }
  @media (max-width: 650px) {
    .plans-grid { grid-template-columns: 1fr; }
  }
  .plan-info-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
  }
  .plan-info-card h4 { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
  .plan-info-card p { font-size: 11.5px; color: var(--text-secondary); }
  
  .btn-pay-now {
    display: block;
    width: 100%;
    background: var(--lime);
    color: var(--bg-main);
    border: none;
    border-radius: 10px;
    padding: 14px;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    text-align: center;
    box-shadow: 0 4px 20px rgba(212, 255, 61, 0.25);
    transition: all 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .btn-pay-now:hover { background: var(--lime-deep); transform: translateY(-1px); }
  
  /* Mobile Hamburger & Topbar */
  .mobile-topbar {
    display: none;
  }
  .mobile-menu-toggle {
    display: none;
  }

  @media (max-width: 900px) {
    .portal-layout {
      display: block;
      padding-top: 70px;
    }
    
    .mobile-topbar {
      display: flex;
      position: fixed;
      top: 0; left: 0; right: 0;
      height: 70px;
      background: var(--bg-surface);
      border-bottom: 1px solid var(--border-color);
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      z-index: 1000;
    }
    .mobile-topbar .brand-title {
      font-family: var(--font-heading);
      font-size: 19px;
      font-weight: 800;
      color: #FFFFFF;
      letter-spacing: -0.2px;
    }
    
    .mobile-menu-toggle {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      width: 22px;
      height: 16px;
      background: none;
      border: none;
      cursor: pointer;
      z-index: 1200;
      outline: none;
    }
    .mobile-menu-toggle span {
      display: block;
      width: 100%;
      height: 2px;
      background-color: var(--text-primary);
      border-radius: 2px;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    /* Overlay mobile navigation menu */
    .sidebar-menu {
      position: fixed;
      top: 0; left: 0;
      width: 100vw;
      height: 100vh;
      background-color: var(--bg-main) !important;
      display: flex !important;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 20px;
      padding: 100px 40px 40px 40px;
      z-index: 1100;
      min-height: auto !important;
      
      opacity: 0;
      pointer-events: none;
      transform: translateY(-20px);
      transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .sidebar-menu.menu-open {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0);
    }
    
    .sidebar-brand {
      border-bottom: none;
      margin-bottom: 12px;
    }
    .sidebar-nav {
      width: 100%;
      max-width: 300px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .sidebar-nav .nav-item {
      width: 100%;
      text-align: center;
      justify-content: center;
    }
    .sidebar-footer {
      margin-top: 30px;
      width: 100%;
      max-width: 300px;
    }
    
    /* Hamburger open state */
    .mobile-menu-toggle.menu-open span:nth-child(1) {
      transform: translateY(7px) rotate(45deg);
      background-color: var(--lime);
    }
    .mobile-menu-toggle.menu-open span:nth-child(2) {
      opacity: 0;
    }
    .mobile-menu-toggle.menu-open span:nth-child(3) {
      transform: translateY(-7px) rotate(-45deg);
      background-color: var(--lime);
    }
    
    .main-content-area {
      padding: 24px 16px;
    }
  }

  /* Documentation styling */
  .doc-section { margin-bottom: 20px; }
  .doc-section h4 { font-size: 13px; color: var(--lime); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
  .doc-section p { font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 10px; }
  
  .doc-table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0;
  }
  .doc-table th, .doc-table td {
    padding: 10px;
    text-align: left;
    font-size: 12px;
    border-bottom: 1px solid var(--border-color-soft);
  }
  .doc-table th { color: var(--text-muted); font-weight: 700; background: var(--bg-card); border-bottom: 1.5px solid var(--border-color); }
  .doc-table td code { color: var(--lime); background: rgba(212, 255, 61, 0.05); padding: 2px 6px; border-radius: 4px; font-size: 11px; border: 1px solid rgba(212, 255, 61, 0.12); }
</style>
</head>
<body>

<?php if ($client): ?>
<div class="portal-layout">
  <header class="mobile-topbar">
    <div class="brand-title" style="display:flex;align-items:center;gap:10px;">
      <img src="zamzy_logo.png" alt="ZAMZY" style="height:32px;width:auto;">
      <span style="font-family:var(--font-heading); font-weight:800; letter-spacing:0.5px; color:#ffffff;">ZAMZY</span>
    </div>
    <button class="mobile-menu-toggle" onclick="document.querySelector('.sidebar-menu').classList.toggle('menu-open'); document.querySelector('.mobile-menu-toggle').classList.toggle('menu-open');" aria-label="Toggle Menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </header>
  <aside class="sidebar-menu">
    <div class="sidebar-brand">
      <h1 class="brand-title" style="display:flex;align-items:center;gap:10px;font-size:20px;font-weight:800;color:#FFFFFF;letter-spacing:-0.2px;margin:0;">
        <img src="zamzy_logo.png" alt="ZAMZY" style="height:38px;width:auto;">
        <span style="font-family:var(--font-heading); letter-spacing:1px; background:linear-gradient(135deg, #ffffff 30%, #00ffcc 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">ZAMZY</span>
      </h1>
      <div class="brand-sub" style="color:var(--cyan); font-family:var(--font-mono); font-size:9px; letter-spacing:1.5px;">WHATSAPP GATEWAY · CLUSTER v4.2</div>
    </div>
    
    <div class="sidebar-section-label">▪ MAIN MENU</div>
    
    <nav class="sidebar-nav">
      <button class="nav-item active" onclick="switchTab(event, 'dashboardPanel')">
        <span class="nav-num">01</span> Dashboard
      </button>
      <button class="nav-item" onclick="switchTab(event, 'profilePanel')">
        <span class="nav-num">02</span> My Profile
      </button>
      <button class="nav-item" onclick="switchTab(event, 'docsPanel')">
        <span class="nav-num">03</span> API Documentation
      </button>
      <button class="nav-item" onclick="switchTab(event, 'pricingPanel')">
        <span class="nav-num">04</span> Price List
      </button>
      <button class="nav-item" onclick="switchTab(event, 'templatesPanel')">
        <span class="nav-num">05</span> Templates
      </button>
      <button class="nav-item" onclick="switchTab(event, 'contactPanel')">
        <span class="nav-num">06</span> Contact Us
      </button>
      <button class="nav-item" onclick="switchTab(event, 'chatbotPanel')">
        <span class="nav-num">07</span> Chatbot Rules
      </button>
      <a href="bulk_media_broadcast.php" class="nav-item" style="text-decoration:none; display:flex; align-items:center;">
        <span class="nav-num">08</span> 📢 Media Broadcast
      </a>
      <a href="chat_history.php" class="nav-item" style="text-decoration:none; display:flex; align-items:center;">
        <span class="nav-num">09</span> 💬 Chat History
      </a>
    </nav>
    
    <div class="sidebar-footer">
      <div class="user-profile-mini">
        <div class="u-name"><?= htmlspecialchars($client['client_name']) ?></div>
        <div class="u-role">Developer Account</div>
      </div>
      <a href="api_link.php?action=logout" class="btn-logout-sidebar">Logout Session</a>
    </div>
  </aside>

  <main class="main-content-area">
    <?php if (!empty($success_msg)): ?>
      <div class="alert success">✅ <?= $success_msg ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
      <div class="alert error">❌ <?= $error_msg ?></div>
    <?php endif; ?>

    <!-- Panel 1: Dashboard -->
    <div class="tab-panel active" id="dashboardPanel">
      <div class="section-tag">▪ 01 / OVERVIEW</div>
      <h2 class="section-title">DASHBOARD COMMAND CENTER.</h2>

      <div class="grid-stats">
        <div class="stat-card">
          <div class="label">Client Name</div>
          <div class="val"><?= htmlspecialchars($client['client_name']) ?></div>
          <div class="desc">Developer Account</div>
        </div>
        <div class="stat-card">
          <div class="label">Scanners Allowed</div>
          <div class="val"><?= intval($client['allowed_scanners'] ?? 1) ?> Device(s)</div>
          <div class="desc">Total active scanners</div>
        </div>
        <div class="stat-card">
          <div class="label">API Credits</div>
          <div class="val"><?= (intval($client['credits']) === -1) ? 'Unlimited' : number_format($client['credits']) ?></div>
          <div class="desc">Remaining balance</div>
        </div>
        <div class="stat-card">
          <div class="label">Subscription Expiry</div>
          <div class="val" id="expiry-countdown-val" data-expiry="<?= htmlspecialchars($client['expiry_date'] ?? '') ?>" style="font-size: 19.5px; font-family: 'Geist Mono', monospace; letter-spacing: -0.5px; margin-top: 8px;">
            <?= !empty($client['expiry_date']) ? date('d-M-Y', strtotime($client['expiry_date'])) : 'No Expiry' ?>
          </div>
          <div class="desc" id="expiry-countdown-desc">Renewal deadline date</div>
        </div>
      </div>

      <div style="background: rgba(15,23,42,0.25); border: 1px solid rgba(255,255,255,0.06); padding: 20px; border-radius: 16px; margin-bottom: 24px;">
        <label style="font-weight: 700; color: #38bdf8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 12px;">Your Bearer API Keys</label>
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <?php for($i = 1; $i <= intval($client['allowed_scanners'] ?? 1); $i++): 
            $dev_key_stmt = $pdo->prepare("SELECT api_key FROM client_devices WHERE client_id = ? AND slot_number = ? LIMIT 1");
            $dev_key_stmt->execute([$client['id'], $i]);
            $slot_key = $dev_key_stmt->fetchColumn() ?: ($i === 1 ? $client['api_key'] : 'teh_api_key_not_generated');
            
            $full_key = htmlspecialchars($slot_key);
            $masked_key = '';
            if ($slot_key !== 'teh_api_key_not_generated') {
                $len = strlen($slot_key);
                if ($len > 14) {
                    $masked_key = substr($slot_key, 0, 8) . '••••••••••••••••' . substr($slot_key, -6);
                } else {
                    $masked_key = substr($slot_key, 0, 4) . '••••' . substr($slot_key, -2);
                }
            } else {
                $masked_key = 'Key not generated';
            }
          ?>
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: rgba(15, 23, 42, 0.4); padding: 10px 16px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.05); transition: background 0.2s;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 12px; font-weight: 600; color: #94a3b8; min-width: 110px;">Device #<?= $i ?> Key:</span>
                <code id="displayApiKey_<?= $i ?>" style="font-family: 'Geist Mono', 'Courier New', monospace; font-size: 13px; color: #38bdf8; letter-spacing: 0.5px;" data-full-key="<?= $full_key ?>" data-masked-key="<?= htmlspecialchars($masked_key) ?>"><?= htmlspecialchars($masked_key) ?></code>
              </div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <?php if ($slot_key !== 'teh_api_key_not_generated'): ?>
                  <button type="button" onclick="toggleRevealKey(<?= $i ?>)" id="revealBtn_<?= $i ?>" style="padding: 5px 10px; font-size: 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); color: #e2e8f0; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                    👁️ Reveal
                  </button>
                  <button class="btn-copy" onclick="copyClientKeySlot(<?= $i ?>)" id="copyBtn_<?= $i ?>" style="padding: 5px 12px; font-size: 12px; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); color: #38bdf8; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 5px; transition: all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='rgba(56,189,248,0.1)'">
                    📋 Copy Key
                  </button>
                <?php else: ?>
                  <span style="font-size: 11px; color: #64748b; font-style: italic;">Not Linkable</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endfor; ?>
        </div>
        <span style="font-size:10px; color:#64748b; margin-top: 8px; display: block;">Pass the respective device key as a Bearer authorization token on your API integrations to send from that specific device.</span>
      </div>

      <div style="background: rgba(15,23,42,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 18px 24px; border-radius: 16px; margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        <div style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
          <div>
            <label style="font-weight: 700; color: #38bdf8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Manage Scanner Device</label>
            <select id="device_slot_selector" onchange="changeDeviceSlot(this.value)" style="padding: 10px 14px; border-radius: 8px; background: rgba(15,23,42,0.8); border: 1.5px solid rgba(255,255,255,0.08); color: #fff; font-size: 13.5px; font-family: inherit; width: 100%; max-width: 280px; min-width: 220px; cursor: pointer;">
              <?php for($i = 1; $i <= intval($client['allowed_scanners'] ?? 1); $i++): ?>
                <option value="<?= $i ?>">Device Slot #<?= $i ?> <?= $i === 1 ? '(Primary)' : '' ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <button
            id="alwaysDisconnectBtn"
            onclick="disconnectDevice()"
            style="padding: 10px 18px; background: rgba(248,113,113,0.1); border: 1.5px solid rgba(248,113,113,0.3); color: #f87171; border-radius: 8px; font-size: 13.5px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.2s;"
            onmouseover="this.style.background='rgba(248,113,113,0.2)'; this.style.borderColor='#f87171';"
            onmouseout="this.style.background='rgba(248,113,113,0.1)'; this.style.borderColor='rgba(248,113,113,0.3)';">
            🔌 Disconnect
          </button>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 4px;">
          <span style="font-weight: 700; color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">All Device Statuses:</span>
          <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 4px;" id="all_device_statuses_list">
            <?php for($i = 1; $i <= intval($client['allowed_scanners'] ?? 1); $i++): 
              $dev_chk = $pdo->prepare("SELECT * FROM client_devices WHERE client_id = ? AND slot_number = ? LIMIT 1");
              $dev_chk->execute([$client['id'], $i]);
              $dev = $dev_chk->fetch();
              $is_conn = $dev && intval($dev['whatsapp_is_connected']) === 1;
            ?>
              <span id="slot_badge_<?= $i ?>" class="slot-status-badge" style="font-size: 11.5px; font-weight: 600; padding: 4px 10px; border-radius: 6px; background: <?= $is_conn ? 'rgba(52,211,153,0.1)' : 'rgba(255,255,255,0.04)' ?>; color: <?= $is_conn ? '#34d399' : '#94a3b8' ?>; border: 1px solid <?= $is_conn ? 'rgba(52,211,153,0.2)' : 'rgba(255,255,255,0.06)' ?>;">
                Device #<?= $i ?>: <?= $is_conn ? '✅ Link Active (' . htmlspecialchars($dev['whatsapp_linked_number']) . ')' : '❌ Offline' ?>
              </span>
            <?php endfor; ?>
          </div>
        </div>
      </div>

      <!-- QR Code scanner container -->
      <div class="qr-card" id="qrContainer">
        <div class="badge-status" id="statusBadge">Checking Connection...</div>
        
        <div id="qrPlaceholder" style="display:none; flex-direction:column; align-items:center;">
          <div style="font-size:32px; margin-bottom:8px;">⚠️</div>
          <div style="font-size:13px; font-weight:600; color:#cbd5e1;">Device Disconnected</div>
          <div style="font-size:11px; color:#64748b; margin-top:4px;">No WhatsApp device linked. Fetching status...</div>
        </div>

        <div id="qrOffline" style="display:none; flex-direction:column; align-items:center;">
          <div style="font-size:32px; margin-bottom:8px;">❌</div>
          <div style="font-size:13px; font-weight:600; color:#f87171;">Gateway Offline</div>
          <div style="font-size:11px; color:#64748b; margin-top:4px;">Cannot reach the gateway connection. Please retry.</div>
        </div>

        <div id="qrLoader" style="display:none;">
          <div style="font-size:32px; animation: spin 1.5s linear infinite; margin-bottom:8px;">⏳</div>
          <div style="font-size:13px; font-weight:600;">Initializing instance...</div>
          <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>
        </div>

        <div id="qrCodeArea" style="display:none; flex-direction:column; align-items:center;">
          <img src="" id="qrImage" class="qr-image" alt="WhatsApp QR Code">
          <div class="qr-status-msg">Scan this QR code with WhatsApp on your phone to link</div>
        </div>

        <div id="qrConnected" style="display:none; flex-direction:column; align-items:center;">
          <div style="font-size:40px; margin-bottom:8px;">🚀</div>
          <div style="font-size:14px; font-weight:700; color:#34d399;">WhatsApp Connected!</div>
          <div style="font-size:12px; color:#94a3b8; margin-top:4px;">Linked Number: <strong id="linkedNumber" style="color:#fff;"></strong></div>
          <button
            id="disconnectBtn"
            onclick="disconnectDevice()"
            style="margin-top:18px; display:flex; align-items:center; gap:8px; padding:10px 22px; background:rgba(248,113,113,0.1); border:1.5px solid rgba(248,113,113,0.35); color:#f87171; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; font-family:inherit; transition:all 0.2s; letter-spacing:0.3px;"
            onmouseover="this.style.background='rgba(248,113,113,0.22)'; this.style.borderColor='#f87171';"
            onmouseout="this.style.background='rgba(248,113,113,0.1)'; this.style.borderColor='rgba(248,113,113,0.35)';">
            <span style="font-size:16px;">🔌</span> Disconnect Device
          </button>
          <div id="disconnectMsg" style="margin-top:10px; font-size:11.5px; color:#94a3b8; display:none;">Disconnecting...</div>
        </div>
      </div>
    </div>

    <!-- Panel 2: My Profile -->
    <div class="tab-panel" id="profilePanel">
      <div class="section-tag">▪ 02 / PROFILE</div>
      <h2 class="section-title">DEVELOPER ACCOUNT PROFILE.</h2>
      <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 24px; border-radius:18px; margin-bottom: 24px;">
        <h3 style="color:#fff; margin-bottom: 4px; font-size:17px; font-weight: 700;">👤 Developer Account Profile</h3>
        <p style="font-size:12px; color:#94a3b8; margin-bottom: 24px;">Your registered developer account configuration and active subscription details.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
          <!-- Left: Account Details -->
          <div>
            <h4 style="font-size: 13px; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Account Details</h4>
            <div style="display: flex; flex-direction: column; gap: 12px;">
              <div>
                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; display: block;">Developer Name</span>
                <span style="font-size: 14.5px; color: #fff; font-weight: 600;"><?= htmlspecialchars($client['client_name']) ?></span>
              </div>
              <div>
                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; display: block;">Registered Mobile / Login ID</span>
                <span style="font-size: 14.5px; color: #fff; font-weight: 600;"><?= htmlspecialchars($client['client_phone']) ?></span>
              </div>
              <div>
                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; display: block;">Developer API Key</span>
                <code style="font-size: 13px; color: #38bdf8; background: rgba(15,23,42,0.5); padding: 4px 8px; border-radius: 6px; display: inline-block; font-family: monospace; border: 1px solid rgba(255,255,255,0.04);"><?= htmlspecialchars($client['api_key']) ?></code>
              </div>
            </div>
          </div>
          
          <!-- Right: Subscription & Scanners -->
          <div>
            <h4 style="font-size: 13px; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Subscription & Service Limits</h4>
            <div style="display: flex; flex-direction: column; gap: 12px;">
              <div>
                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; display: block;">Subscription Expiry</span>
                <span style="font-size: 14.5px; color: #fff; font-weight: 600;"><?= !empty($client['expiry_date']) ? date('d-M-Y', strtotime($client['expiry_date'])) : 'No Expiry Set' ?></span>
              </div>
              <div>
                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; display: block;">Scanners Allowed</span>
                <span style="font-size: 14.5px; color: #fff; font-weight: 600;"><?= intval($client['allowed_scanners'] ?? 1) ?> Scanner Device(s)</span>
              </div>
              <div>
                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; display: block;">Account Status</span>
                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 4px 8px; border-radius: 6px; display: inline-block; background: <?= (strtotime($client['expiry_date'] ?? '') >= time() || empty($client['expiry_date'])) ? 'rgba(52,211,153,0.1); color: #34d399;' : 'rgba(248,113,113,0.1); color: #f87171;' ?>">
                  <?= (strtotime($client['expiry_date'] ?? '') >= time() || empty($client['expiry_date'])) ? '● Active' : '● Expired' ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Password Change Form Inside Card -->
      <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 24px; border-radius:18px;">
        <h3 style="color:#fff; margin-bottom: 4px; font-size:17px; font-weight: 700;">🔒 Change Login Password</h3>
        <p style="font-size:12px; color:#94a3b8; margin-bottom: 20px;">Update your password regularly to maintain account security.</p>
        
        <form method="POST">
          <input type="hidden" name="action" value="change_password_profile">
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div class="form-group">
              <label for="prof_curr_pass">Current Password *</label>
              <input type="password" name="current_password" id="prof_curr_pass" placeholder="••••••••" required style="padding:10px 14px;">
            </div>
            <div class="form-group">
              <label for="prof_new_pass">New Password *</label>
              <input type="password" name="new_password" id="prof_new_pass" placeholder="Min 6 characters" required style="padding:10px 14px;">
            </div>
            <div class="form-group">
              <label for="prof_conf_pass">Confirm New Password *</label>
              <input type="password" name="confirm_password" id="prof_conf_pass" placeholder="••••••••" required style="padding:10px 14px;">
            </div>
          </div>
          
          <button type="submit" class="btn-submit" style="padding: 12px 24px;">Update Password</button>
        </form>
      </div>

      <!-- Billing / Transaction History Card -->
      <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 24px; border-radius:18px; margin-top: 24px;">
        <h3 style="color:#fff; margin-bottom: 4px; font-size:17px; font-weight: 700;">💳 Transaction & Billing History</h3>
        <p style="font-size:12px; color:#94a3b8; margin-bottom: 20px;">List of your active plan subscriptions and renewals.</p>
        
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: 'Inter', sans-serif;">
            <thead>
              <tr style="border-bottom: 1.5px solid rgba(255,255,255,0.08); color: #94a3b8; font-size: 12px; font-weight: 700;">
                <th style="padding: 12px 10px;">Date</th>
                <th style="padding: 12px 10px;">Payment ID</th>
                <th style="padding: 12px 10px;">Plan / Description</th>
                <th style="padding: 12px 10px;">Months</th>
                <th style="padding: 12px 10px;">Coupon</th>
                <th style="padding: 12px 10px; text-align: right;">Amount Paid</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $tx_stmt = $pdo->prepare("SELECT * FROM client_payments WHERE client_id = ? ORDER BY id DESC");
              $tx_stmt->execute([$client['id']]);
              $txs = $tx_stmt->fetchAll();
              if (empty($txs)):
              ?>
                <tr>
                  <td colspan="6" style="padding: 20px 10px; text-align: center; color: #64748b; font-size: 13.5px;">No transactions found.</td>
                </tr>
              <?php else: foreach ($txs as $tx): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 13px; color: #cbd5e1;">
                  <td style="padding: 12px 10px;"><?= date('d-M-Y H:i', strtotime($tx['created_at'])) ?></td>
                  <td style="padding: 12px 10px;"><code style="font-family: monospace; background: rgba(255,255,255,0.03); padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($tx['payment_id']) ?></code></td>
                  <td style="padding: 12px 10px;"><?= htmlspecialchars($tx['plan_name']) ?></td>
                  <td style="padding: 12px 10px;"><?= intval($tx['duration']) ?> mo</td>
                  <td style="padding: 12px 10px;"><?= !empty($tx['coupon_code']) ? '<span style="color: #34d399; font-weight:600;">' . htmlspecialchars($tx['coupon_code']) . '</span>' : '-' ?></td>
                  <td style="padding: 12px 10px; text-align: right; font-weight: 700; color: #fff;">Rs <?= number_format($tx['amount'], 2) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Panel 3: API Documentation -->
    <div class="tab-panel" id="docsPanel">
      <div class="section-tag">▪ 03 / API REFERENCE</div>
      <h2 class="section-title">REST API INTEGRATION DOCS.</h2>
      <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 20px; border-radius:14px;">
        <h3 style="color:#fff; margin-bottom: 12px; font-size:16px;">WhatsApp API Endpoint Reference</h3>
        <p style="font-size: 13px; color:#94a3b8; margin-bottom: 18px; line-height: 1.5; font-family:'Inter',sans-serif;">
          Trigger messages programmatically by invoking standard HTTP POST requests to the gateway endpoint.
        </p>

        <div class="doc-section">
          <h4>HTTP Endpoint</h4>
          <p><code>POST <?= htmlspecialchars(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/api/whatsapp.php') ?></code></p>
        </div>

        <div class="doc-section" style="border: 1px dashed var(--lime); padding: 16px; border-radius: 8px; margin: 20px 0; background: rgba(212, 255, 61, 0.02);">
          <h4 style="color: var(--lime); display: flex; align-items: center; gap: 8px; font-weight: 700; margin-bottom: 8px;">⚡ Multiple Scanner (Device Slot) API Integration</h4>
          <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 12px;">
            You have connected <strong><?= intval($client['allowed_scanners'] ?? 1) ?> device(s)</strong>. You can route messages through a specific connected scanner using either of these three methods:
          </p>
          
          <h5 style="color: #fff; font-size: 13px; margin: 12px 0 6px 0; font-weight: 600;">Method A: Slot-Specific API Endpoints (Recommended)</h5>
          <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">
            Invoke the corresponding wrapper endpoint for the desired device slot. In this method, you can use your **Primary API Key** as the Bearer token for all requests:
          </p>
          <ul style="font-size: 12.5px; color: var(--text-secondary); padding-left: 20px; margin-bottom: 14px; list-style-type: square; line-height: 1.6;">
            <?php for($i = 1; $i <= intval($client['allowed_scanners'] ?? 1); $i++): ?>
              <li><strong>Device #<?= $i ?> Endpoint:</strong> <code>POST <?= htmlspecialchars(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/api/whatsapp' . ($i === 1 ? '' : $i) . '.php') ?></code></li>
            <?php endfor; ?>
          </ul>

          <h5 style="color: #fff; font-size: 13px; margin: 12px 0 6px 0; font-weight: 600;">Method B: Dedicated Slot Bearer API Keys</h5>
          <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">
            Submit your request to the primary endpoint <code>/api/whatsapp.php</code>, but pass the specific device's **Slot API Key** in the <code>Authorization: Bearer [Slot API Key]</code> header instead. The system automatically routes the message through the device linked to that slot:
          </p>
          <div style="background: rgba(15,23,42,0.3); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 12px;">
            <?php for($i = 1; $i <= intval($client['allowed_scanners'] ?? 1); $i++): 
              $dev_key_stmt = $pdo->prepare("SELECT api_key FROM client_devices WHERE client_id = ? AND slot_number = ? LIMIT 1");
              $dev_key_stmt->execute([$client['id'], $i]);
              $slot_key = $dev_key_stmt->fetchColumn() ?: ($i === 1 ? $client['api_key'] : 'teh_api_key_not_generated');
            ?>
              <div style="margin-bottom: 6px; font-size: 12px; font-family: monospace;">
                <span style="color: #94a3b8;">Device #<?= $i ?> Slot API Key:</span> <span style="color: var(--lime);"><?= htmlspecialchars($slot_key) ?></span>
              </div>
            <?php endfor; ?>
          </div>

          <h5 style="color: #fff; font-size: 13px; margin: 12px 0 6px 0; font-weight: 600;">Method C: "device" or "slot" Request Parameter</h5>
          <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 0;">
            Invoke the primary endpoint <code>/api/whatsapp.php</code> using your **Primary API Key**, and include the <code>"device": <?= intval($client['allowed_scanners'] > 1 ? 2 : 1) ?></code> parameter inside the JSON request body (or query string) to target that specific slot.
          </p>
        </div>

        <div class="doc-section">
          <h4>Required Headers</h4>
          <table class="doc-table">
            <thead>
              <tr>
                <th>Header Name</th>
                <th>Value</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><code>Authorization</code></td>
                <td><code>Bearer <?= htmlspecialchars(substr($client['api_key'], 0, 15) . '...') ?></code></td>
                <td>Required. Your bearer API Key.</td>
              </tr>
              <tr>
                <td><code>Content-Type</code></td>
                <td><code>application/json</code></td>
                <td>Required. Set payload structure to JSON.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="doc-section">
          <h4>JSON Request Parameters</h4>
          <table class="doc-table">
            <thead>
              <tr>
                <th>Parameter</th>
                <th>Type</th>
                <th>Mandatory</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><code>to</code></td>
                <td>String</td>
                <td><strong>Yes</strong></td>
                <td>Recipient phone number with country code (e.g. <code>919876543210</code>).</td>
              </tr>
              <tr>
                <td><code>message</code></td>
                <td>String</td>
                <td><strong>Yes</strong></td>
                <td>Text content of message. (If templates are defined, this content will format or wrap according to the template settings).</td>
              </tr>
              <tr>
                <td><code>type</code></td>
                <td>String</td>
                <td>No</td>
                <td>Options: <code>otp</code>, <code>promotion</code>, <code>invoice</code>, <code>report</code>, <code>general</code>. Default: <code>general</code>.</td>
              </tr>
              <tr>
                <td><code>pdf</code></td>
                <td>String</td>
                <td>No</td>
                <td>Base64 encoded PDF string if sending document dispatches.</td>
              </tr>
              <tr>
                <td><code>filename</code></td>
                <td>String</td>
                <td>No</td>
                <td>Filename for PDF attachment (defaults to <code>Invoice.pdf</code>).</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="doc-section">
          <h4>JSON Response Examples</h4>
          <p><strong>Success (200 OK):</strong></p>
          <p><code>{"success":true,"message":"Message dispatched successfully.","messageId":"ABEGFFFFGGHH"}</code></p>
          
          <p><strong>Failure (401/402/503):</strong></p>
          <p><code>{"success":false,"error":"Precondition Failed: Your WhatsApp device is not connected."}</code></p>
        </div>
      </div>
    </div>

    <!-- Panel 4: Price List & Dynamic Calculator -->
    <?php
    // Fetch dynamic service pricing plans
    $sp_plans = [];
    $addon_unit_price = 300;
    try {
        $sp_stmt = $pdo->query("SELECT * FROM service_pricing WHERE status = 'active' ORDER BY id ASC");
        $all_sp = $sp_stmt->fetchAll();
        foreach ($all_sp as $sp) {
            if ($sp['plan_key'] === 'addon_scanner') {
                $addon_unit_price = floatval($sp['monthly_price']);
            } else {
                $sp_plans[] = $sp;
            }
        }
    } catch (Exception $e) {}
    if (empty($sp_plans)) {
        // Fallback defaults if table is empty
        $sp_plans = [
            ['plan_key' => 'starter', 'plan_name' => 'Starter Plan', 'description' => '1 Device Scanner • Unlimited SMS', 'scanners_included' => 1, 'monthly_price' => 799, 'price_3_months' => 2097, 'price_6_months' => 3594, 'price_12_months' => 5988],
            ['plan_key' => 'business', 'plan_name' => 'Business Plan', 'description' => '3 Device Scanners • Unlimited SMS', 'scanners_included' => 3, 'monthly_price' => 1299, 'price_3_months' => 3597, 'price_6_months' => 6594, 'price_12_months' => 11988]
        ];
    }
    ?>
    <script>
      const ADDON_MONTHLY_RATE = <?= json_encode($addon_unit_price) ?>;
    </script>
    <div class="tab-panel" id="pricingPanel">
      <div class="section-tag">▪ 04 / PRICING</div>
      <h2 class="section-title">SUBSCRIPTION & RENEWALS.</h2>
      <div class="renew-panel">
        <h3 style="color:#fff; margin-bottom: 4px; font-size:17px;">🔒 Secure Subscription Renewals</h3>
        <p style="font-size:12.5px; color:#94a3b8; margin-bottom: 20px;">Renew your active package or purchase extra active WhatsApp scanner logins dynamically.</p>

        <div class="plans-grid">
          <?php foreach ($sp_plans as $idx => $sp): ?>
            <div class="plan-info-card" <?= $idx > 0 ? 'style="border-color: rgba(99, 102, 241, 0.2);"' : '' ?>>
              <h4><?= htmlspecialchars($sp['plan_name']) ?></h4>
              <p><?= htmlspecialchars($sp['description']) ?></p>
              <p style="font-weight:700; color:<?= $idx > 0 ? '#818cf8' : '#38bdf8' ?>; margin-top:4px;">From <?= number_format($sp['monthly_price']) ?> Rs/mo</p>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="form-group">
          <label>1. Select Base Plan</label>
          <select id="calc_plan" onchange="calculateRenewalTotal()">
            <?php foreach ($sp_plans as $sp): ?>
              <option value="<?= htmlspecialchars($sp['plan_key']) ?>" 
                      data-name="<?= htmlspecialchars($sp['plan_name']) ?> (<?= intval($sp['scanners_included']) ?> Scanner Base)" 
                      data-base="<?= floatval($sp['monthly_price']) ?>" 
                      data-opt-3="<?= floatval($sp['price_3_months']) ?>" 
                      data-opt-6="<?= floatval($sp['price_6_months']) ?>" 
                      data-opt-12="<?= floatval($sp['price_12_months']) ?>">
                <?= htmlspecialchars($sp['plan_name']) ?> — Unlimited SMS for <?= intval($sp['scanners_included']) ?> Scanner(s) (<?= number_format($sp['monthly_price']) ?> Rs/mo)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>2. Choose Renewal Duration</label>
          <select id="calc_duration" onchange="calculateRenewalTotal()">
            <option value="1">1 Month</option>
            <option value="3">3 Months (Special Discounted Rate)</option>
            <option value="6">6 Months (Special Discounted Rate)</option>
            <option value="12">12 Months (Special Discounted Rate)</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: 24px;" id="addonScannersGroup">
          <label>3. Add Extra Active Scanners Add-on</label>
          <select id="calc_addons" onchange="calculateRenewalTotal()">
            <option value="0">0 Extra Scanners</option>
            <option value="1">1 Extra Scanner (+<?= number_format($addon_unit_price * 1) ?> Rs/mo)</option>
            <option value="2">2 Extra Scanners (+<?= number_format($addon_unit_price * 2) ?> Rs/mo)</option>
            <option value="3">3 Extra Scanners (+<?= number_format($addon_unit_price * 3) ?> Rs/mo)</option>
            <option value="4">4 Extra Scanners (+<?= number_format($addon_unit_price * 4) ?> Rs/mo)</option>
            <option value="5">5 Extra Scanners (+<?= number_format($addon_unit_price * 5) ?> Rs/mo)</option>
          </select>
          <span style="font-size:10.5px; color:#64748b; margin-top:2px;">Extra WhatsApp logins cost <?= number_format($addon_unit_price) ?> Rs per scanner per month.</span>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
          <label>4. Apply Coupon Code</label>
          <div style="display: flex; gap: 8px;">
            <input type="text" id="coupon_code" placeholder="Enter Coupon Code" style="flex: 1; padding: 10px 14px; text-transform: uppercase;">
            <button type="button" class="btn-submit" onclick="applyCouponCode()" style="padding: 10px 20px; font-size:13px; font-weight:700; background:#38bdf8; border:none; border-radius:8px; color:#0f172a; cursor:pointer;">Apply</button>
          </div>
          <span id="coupon_message" style="font-size:11.5px; margin-top: 4px; display: block;"></span>
        </div>

        <div class="dynamic-calculator">
          <div class="calculator-title">🧾 Cost Calculations Breakdown</div>
          <div class="receipt-row">
            <span>Base Subscription Cost:</span>
            <span id="receipt_base_cost">Rs 799</span>
          </div>
          <div class="receipt-row" id="receipt_addon_row">
            <span>Add-on Scanners (x<span id="receipt_addon_count">0</span>):</span>
            <span id="receipt_addon_cost">Rs 0</span>
          </div>
          <div class="receipt-row" id="receipt_discount_row" style="display: none; color: #f87171;">
            <span>Coupon Discount (<span id="receipt_coupon_name"></span>):</span>
            <span>- Rs <span id="receipt_discount_amount">0</span></span>
          </div>
          <div class="receipt-row total">
            <span>Grand Total:</span>
            <span id="receipt_grand_total">Rs 799</span>
          </div>
        </div>

        <button type="button" class="btn-pay-now" onclick="payUnifiedRenewal()">Pay with Razorpay</button>
      </div>
    </div>

    <!-- Panel 5: Message Templates -->
    <div class="tab-panel" id="templatesPanel">
      <div class="section-tag">▪ 05 / TEMPLATES</div>
      <h2 class="section-title">MESSAGE TEMPLATE CONFIG.</h2>
      <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 20px; border-radius:14px;">
        <h3 style="color:#fff; margin-bottom: 8px; font-size:16px;">📝 API message Templates</h3>
        <p style="font-size:12px; color:#94a3b8; margin-bottom: 20px;">Customize the templates that format messages dispatched via your API keys. Leave blank to bypass formatting.</p>

        <form method="POST">
          <input type="hidden" name="action" value="save_client_templates">
          
          <div class="form-group">
            <label>OTP Verification Message Template</label>
            <textarea name="template_otp" rows="3" placeholder="Your OTP code is {otp_code}"><?= htmlspecialchars($client['template_otp'] ?? '') ?></textarea>
            <span style="font-size: 10.5px; color: #64748b; margin-top: 2px;">Replacements: <code>{otp_code}</code></span>
          </div>

          <div class="form-group" style="margin-top: 14px;">
            <label>Invoice Summary Template</label>
            <textarea name="template_invoice" rows="4" placeholder="Dear {client_name}, your invoice #{invoice_number} is ready. Total: Rs {grand_total}. View: {web_link}"><?= htmlspecialchars($client['template_invoice'] ?? '') ?></textarea>
            <span style="font-size: 10.5px; color: #64748b; margin-top: 2px;">Replacements: <code>{client_name}</code>, <code>{invoice_number}</code>, <code>{grand_total}</code>, <code>{web_link}</code></span>
          </div>

          <div class="form-group" style="margin-top: 14px;">
            <label>General / Promo Template Wrapper</label>
            <textarea name="template_general" rows="3" placeholder="⚡ Alert from Expert Hub: {message}"><?= htmlspecialchars($client['template_general'] ?? '') ?></textarea>
            <span style="font-size: 10.5px; color: #64748b; margin-top: 2px;">Replacements: <code>{message}</code></span>
          </div>

          <button type="submit" class="btn-submit" style="width:100%; margin-top:12px;">Save My Templates</button>
        </form>
      </div>
    </div>

    <!-- Panel 6: Contact Us -->
    <div class="tab-panel" id="contactPanel">
      <div class="section-tag">▪ 06 / SUPPORT</div>
      <h2 class="section-title">CONTACT SUPPORT TEAM.</h2>
      <p style="font-size:12px; color:#94a3b8; margin-bottom: 20px;">Have questions? Submit the form below. Our support agents will contact you shortly.</p>

      <form method="POST">
        <input type="hidden" name="action" value="contact_submit">
        <div class="form-group">
          <label for="support_subject">Subject</label>
          <input type="text" name="subject" id="support_subject" placeholder="e.g. Inquiries regarding billing or API connection" required>
        </div>
        <div class="form-group">
          <label for="support_message">Message Details</label>
          <textarea name="message" id="support_message" rows="5" placeholder="Type your message details here..." required></textarea>
        </div>
        <button type="submit" class="btn-submit" style="width:100%;">Submit Support Query</button>
      </form>
    </div>

    <!-- Panel 7: Chatbot Rules -->
    <div class="tab-panel" id="chatbotPanel">
      <div class="section-tag">▪ 07 / CHATBOT</div>
      <h2 class="section-title">WHATSAPP CHATBOT SETTINGS.</h2>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
        
        <!-- Left: Status & Add Rule Form -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
          
          <!-- Chatbot Enable/Disable Toggle Card -->
          <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 24px; border-radius:18px;">
            <h3 style="color:#fff; margin-bottom: 4px; font-size:16px; font-weight: 700;">🤖 Chatbot Status</h3>
            <p style="font-size:11.5px; color:#94a3b8; margin-bottom: 18px;">Enable or disable the automated keyword replies globally for this account.</p>
            
            <form method="POST" style="display: flex; align-items: center; justify-content: space-between; background: rgba(15,23,42,0.5); padding: 12px 18px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.03);">
              <input type="hidden" name="action" value="toggle_chatbot">
              <input type="hidden" name="enabled" value="<?= intval($client['chatbot_enabled'] ?? 1) ? '0' : '1' ?>">
              
              <div style="display: flex; flex-direction: column;">
                <span style="font-size:13px; font-weight: 700; color: #fff;">Chatbot Integration</span>
                <span style="font-size:11.5px; color: <?= intval($client['chatbot_enabled'] ?? 1) ? '#34d399' : '#f87171' ?>; font-weight: 600; margin-top: 2px;">
                  ● <?= intval($client['chatbot_enabled'] ?? 1) ? 'Currently Active' : 'Currently Inactive' ?>
                </span>
              </div>
              
              <button type="submit" class="btn-submit" style="padding: 8px 16px; font-size: 12px; background: <?= intval($client['chatbot_enabled'] ?? 1) ? 'rgba(248,113,113,0.1)' : 'var(--lime)' ?>; color: <?= intval($client['chatbot_enabled'] ?? 1) ? '#f87171' : 'var(--bg-main)' ?>; border: <?= intval($client['chatbot_enabled'] ?? 1) ? '1px solid rgba(248,113,113,0.3)' : 'none' ?>; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit;">
                <?= intval($client['chatbot_enabled'] ?? 1) ? 'Disable Bot' : 'Enable Bot' ?>
              </button>
            </form>
          </div>

          <!-- Add New Chatbot Rule Card -->
          <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 24px; border-radius:18px;" id="chatbotFormCard">
            <h3 style="color:#fff; margin-bottom: 4px; font-size:16px; font-weight: 700;" id="chatbotFormTitle">➕ Create New Rule</h3>
            <p style="font-size:11.5px; color:#94a3b8; margin-bottom: 20px;" id="chatbotFormDesc">Define keywords and responses. If the user's message contains the keyword, this reply will be triggered.</p>
            
            <form method="POST" id="chatbotRuleForm" style="display: flex; flex-direction: column; gap: 16px;">
              <input type="hidden" name="action" id="chatbotFormAction" value="add_chatbot_rule">
              <input type="hidden" name="rule_id" id="chatbotRuleId" value="">
              
              <div class="form-group" style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Trigger Keyword *</label>
                <input type="text" name="keyword" id="chatbotKeyword" placeholder="e.g. support, price, hello" required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 13.5px;">
                <span style="font-size:10.5px; color:#64748b; margin-top:4px; display:block;">Rules are case-insensitive and match exact phrases or words.</span>
              </div>
              
              <div class="form-group" style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Auto-Reply Message *</label>
                <textarea name="reply_text" id="chatbotReplyText" rows="4" placeholder="Enter the text to automatically send back to the user..." required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 13.5px; line-height: 1.4; resize: vertical;"></textarea>
                <span style="font-size:10.5px; color:#64748b; margin-top:4px; display:block;">You can use standard WhatsApp formatting (e.g. *bold*, _italics_).</span>
              </div>
              
              <div class="form-group" style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Image URL (Optional)</label>
                <input type="url" name="image_url" id="chatbotImageUrl" placeholder="https://example.com/banner.jpg" style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 13.5px;">
                <span style="font-size:10.5px; color:#64748b; margin-top:4px; display:block;">Provide a direct link to an image to send it as a media message with caption.</span>
              </div>

              <div class="form-group" style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Chatbot Buttons (Optional, comma-separated)</label>
                <input type="text" name="buttons_list" id="chatbotButtons" placeholder="e.g. Services, Website|https://tehub.in" style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 13.5px;">
                <span style="font-size:10.5px; color:#64748b; margin-top:4px; display:block;">Format: <code>ButtonName</code> or <code>ButtonName|LinkURL</code> for website link buttons. Max 3 buttons.</span>
              </div>
              
              <div style="display: flex; gap: 10px;" id="chatbotBtnContainer">
                <button type="submit" class="btn-submit" id="chatbotSubmitBtn" style="flex: 1; font-family: inherit; font-weight:700;">Save Chatbot Rule</button>
                <button type="button" class="btn-submit" id="chatbotCancelBtn" onclick="cancelChatbotEdit()" style="display: none; padding: 12px 20px; font-size: 13px; border-radius: 8px; font-weight: 700; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); cursor: pointer; font-family: inherit;">Cancel</button>
              </div>
            </form>

            <!-- Preset Templates Box -->
            <div style="background: rgba(15,23,42,0.4); padding: 18px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-top: 20px;">
              <h4 style="color: var(--lime); font-size: 11px; margin-bottom: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">💡 Preset Templates (Click to use)</h4>
              <div style="display: flex; flex-direction: column; gap: 8px;">
                <button type="button" onclick="applyChatbotTemplate('hello, hi, support', 'Hello! Thank you for reaching out to us. How can we assist you today?\n\n• Reply \'1\' for Services\n• Reply \'2\' for Pricing\n• Reply \'3\' for Support', 'Services, Pricing, Support')" style="text-align: left; padding: 10px 12px; border-radius: 8px; background: rgba(255,255,255,0.02); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.04); font-size: 12px; cursor: pointer; transition: all 0.2s; font-family: inherit; line-height: 1.4;">
                  👋 <strong>Welcome Message</strong> (hi, hello)
                </button>
                <button type="button" onclick="applyChatbotTemplate('price, plans, cost', 'Our pricing packages:\n\n• Starter: Rs 399/mo (1 active scanner)\n• Business: Rs 799/mo (3 active scanners)\n\nAll plans include unlimited API credits.', 'Starter, Business')" style="text-align: left; padding: 10px 12px; border-radius: 8px; background: rgba(255,255,255,0.02); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.04); font-size: 12px; cursor: pointer; transition: all 0.2s; font-family: inherit; line-height: 1.4;">
                  💳 <strong>Pricing Plans</strong> (price, cost)
                </button>
                <button type="button" onclick="applyChatbotTemplate('contact, help, email', 'Need help? You can connect with our support agents:\n\n📧 enquiry@theexperthub.in\n📞 +91 89392 20422\n\nWe respond within 2-4 business hours.', 'Website|https://tehub.in')" style="text-align: left; padding: 10px 12px; border-radius: 8px; background: rgba(255,255,255,0.02); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.04); font-size: 12px; cursor: pointer; transition: all 0.2s; font-family: inherit; line-height: 1.4;">
                  📞 <strong>Support Details</strong> (support, contact)
                </button>
              </div>
            </div>
          </div>

        </div>

        <!-- Right: Active Rules List -->
        <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 24px; border-radius:18px; display: flex; flex-direction: column;">
          <h3 style="color:#fff; margin-bottom: 4px; font-size:16px; font-weight: 700;">📋 Current Chatbot Rules</h3>
          <p style="font-size:11.5px; color:#94a3b8; margin-bottom: 20px;">List of keywords and their corresponding automated response templates.</p>
          
          <div style="flex-grow: 1; overflow-x: auto;">
            <?php
              $stmt_rules = $pdo->prepare("SELECT * FROM chatbot_rules WHERE client_id = ? ORDER BY id DESC");
              $stmt_rules->execute([$client['id']]);
              $rules_list = $stmt_rules->fetchAll();
            ?>
            <?php if (empty($rules_list)): ?>
              <div style="text-align: center; padding: 48px 16px; border: 1px dashed rgba(255,255,255,0.08); border-radius: 12px; background: rgba(15,23,42,0.25);">
                <div style="font-size: 28px; margin-bottom: 8px;">🤖</div>
                <div style="font-size: 13.5px; font-weight: 600; color: #cbd5e1;">No Chatbot Rules Configured</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Use the form on the left to add your first auto-reply rule.</div>
              </div>
            <?php else: ?>
              <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                  <tr style="border-bottom: 1.5px solid var(--border-color); background: rgba(15,23,42,0.4);">
                    <th style="padding: 12px 14px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; width: 140px;">Keyword</th>
                    <th style="padding: 12px 14px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Auto-Reply Message</th>
                    <th style="padding: 12px 14px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; width: 120px;">Image Header</th>
                    <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: center; width: 150px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rules_list as $rule): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                      <td style="padding: 14px 14px; font-size: 13px; font-family: 'Geist Mono', monospace; font-weight: 700; color: var(--lime); vertical-align: top;">
                        <?= htmlspecialchars($rule['keyword']) ?>
                      </td>
                      <td style="padding: 14px 14px; font-size: 13px; color: #cbd5e1; line-height: 1.45; vertical-align: top; white-space: pre-wrap; word-break: break-word;">
                        <?= htmlspecialchars($rule['reply_text']) ?>
                        <?php
                          $btn_strings = [];
                          if (!empty($rule['buttons_json'])) {
                              $btns = json_decode($rule['buttons_json'], true);
                              if (is_array($btns)) {
                                  foreach ($btns as $b) {
                                      if ($b['type'] === 'url') {
                                          $btn_strings[] = $b['text'] . '|' . $b['url'];
                                      } else {
                                          $btn_strings[] = $b['text'];
                                      }
                                  }
                              }
                          }
                          $buttons_display = implode(', ', $btn_strings);
                        ?>
                        <?php if (!empty($buttons_display)): ?>
                          <div style="font-size: 11px; margin-top: 6px; color: var(--lime); font-weight: 700; background: rgba(132,204,22,0.1); padding: 4px 8px; border-radius: 6px; border: 1px dashed rgba(132,204,22,0.25); display: inline-block;">
                            🔘 Buttons: <?= htmlspecialchars($buttons_display) ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 14px 14px; vertical-align: middle;">
                        <?php if (!empty($rule['image_url'])): ?>
                          <a href="<?= htmlspecialchars($rule['image_url']) ?>" target="_blank" style="display:flex; align-items:center; gap:6px; text-decoration:none; color:var(--lime); font-weight:600;">
                            <img src="<?= htmlspecialchars($rule['image_url']) ?>" style="width:32px; height:32px; object-fit:cover; border-radius:4px; border:1px solid rgba(255,255,255,0.1);" onerror="this.src='https://placehold.co/80x80?text=Img';">
                            <span style="font-size:11px;">Link</span>
                          </a>
                        <?php else: ?>
                          <span style="color:#64748b; font-size:12px;">None</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 14px 16px; text-align: center; vertical-align: middle;">
                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                          <button type="button" class="btn-action-small" onclick='editChatbotRule(<?= json_encode([
                            'id' => $rule['id'],
                            'keyword' => $rule['keyword'],
                            'reply_text' => $rule['reply_text'],
                            'image_url' => $rule['image_url'] ?? '',
                            'buttons_display' => $buttons_display
                          ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="white-space: nowrap; padding: 6px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 6px; font-size: 11.5px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='var(--lime)'; this.style.color='var(--bg-main)';" onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.color='#fff';">✏️ Edit</button>
                          <form method="POST" onsubmit="return confirm('Are you sure you want to delete this chatbot rule?');" style="display: inline; margin: 0;">
                            <input type="hidden" name="action" value="delete_chatbot_rule">
                            <input type="hidden" name="rule_id" value="<?= $rule['id'] ?>">
                            <button type="submit" style="padding: 6px 12px; background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25); color: #f87171; border-radius: 6px; font-size: 11.5px; font-weight: 700; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.background='rgba(248,113,113,0.2)'; this.style.borderColor='#f87171';"
                                    onmouseout="this.style.background='rgba(248,113,113,0.1)'; this.style.borderColor='rgba(248,113,113,0.25)';">
                              🗑 Delete
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>

  </main>
</div>
<?php endif; ?>

<?php if ($client && !empty($settings['whatsapp_gateway_url'])): ?>
<script>
const apiKey = <?= json_encode($client['api_key']) ?>;
const GATEWAY_URL = <?= json_encode($settings['whatsapp_gateway_url'] ?? '') ?>;
const LOGIN_ID = <?= json_encode($client['login_id'] ?? '') ?>;
let currentConnectedState = <?= intval($client['whatsapp_is_connected'] ?? 0) ?>;
let qrInterval = null;
let appliedCoupon = null;
let currentSlot = 1;
let slotStates = {};

// Chatbot helper functions
function editChatbotRule(rule) {
  document.getElementById('chatbotFormTitle').textContent = '✏️ Edit Chatbot Rule';
  document.getElementById('chatbotFormDesc').textContent = 'Modify the details of this automatic keyword response.';
  document.getElementById('chatbotFormAction').value = 'edit_chatbot_rule';
  document.getElementById('chatbotRuleId').value = rule.id;
  document.getElementById('chatbotKeyword').value = rule.keyword;
  document.getElementById('chatbotReplyText').value = rule.reply_text;
  document.getElementById('chatbotImageUrl').value = rule.image_url || '';
  document.getElementById('chatbotButtons').value = rule.buttons_display || '';
  
  document.getElementById('chatbotSubmitBtn').textContent = 'Save Changes';
  document.getElementById('chatbotCancelBtn').style.display = 'inline-block';
  
  // Scroll to form card
  document.getElementById('chatbotFormCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelChatbotEdit() {
  document.getElementById('chatbotFormTitle').textContent = '➕ Create New Rule';
  document.getElementById('chatbotFormDesc').textContent = 'Define keywords and responses. If the user\'s message contains the keyword, this reply will be triggered.';
  document.getElementById('chatbotFormAction').value = 'add_chatbot_rule';
  document.getElementById('chatbotRuleId').value = '';
  document.getElementById('chatbotRuleForm').reset();
  
  document.getElementById('chatbotSubmitBtn').textContent = 'Save Chatbot Rule';
  document.getElementById('chatbotCancelBtn').style.display = 'none';
}

function applyChatbotTemplate(keyword, replyText, buttonsList) {
  // Cancel any active edit first to reset state cleanly
  cancelChatbotEdit();
  
  document.getElementById('chatbotKeyword').value = keyword;
  document.getElementById('chatbotReplyText').value = replyText;
  document.getElementById('chatbotButtons').value = buttonsList || '';
  
  // Highlight and focus the form fields
  document.getElementById('chatbotKeyword').focus();
  document.getElementById('chatbotFormCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Parse clean JSON by trimming any prepended whitespace, BOM, or PHP startup warnings
function parseCleanJson(text) {
  const jsonStart = text.indexOf('{');
  if (jsonStart === -1) {
    throw new Error('No JSON payload found in response: ' + text);
  }
  return JSON.parse(text.substring(jsonStart));
}

// Tab switcher logic
function switchTab(evt, panelId) {
  const tabpanels = document.getElementsByClassName("tab-panel");
  for (let i = 0; i < tabpanels.length; i++) {
    tabpanels[i].className = tabpanels[i].className.replace(" active", "");
  }
  const tablinks = document.querySelectorAll(".tab-btn, .nav-item");
  for (let i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(panelId).className += " active";
  if (evt && evt.currentTarget) {
    evt.currentTarget.className += " active";
  }
  // Auto-close full screen mobile menu overlay
  const menu = document.querySelector('.sidebar-menu');
  const toggle = document.querySelector('.mobile-menu-toggle');
  if (menu) menu.classList.remove('menu-open');
  if (toggle) toggle.classList.remove('menu-open');
}

// Copy key to clipboard by slot number
function copyClientKeySlot(slot) {
  const codeEl = document.getElementById('displayApiKey_' + slot);
  const keyText = codeEl.getAttribute('data-full-key') || codeEl.textContent;
  navigator.clipboard.writeText(keyText).then(() => {
    const copyBtn = document.getElementById('copyBtn_' + slot);
    const originalText = copyBtn.innerHTML;
    copyBtn.innerHTML = '✅ Copied!';
    copyBtn.style.color = '#34d399';
    copyBtn.style.borderColor = 'rgba(52,211,153,0.3)';
    copyBtn.style.background = 'rgba(52,211,153,0.1)';
    setTimeout(() => {
      copyBtn.innerHTML = originalText;
      copyBtn.style.color = '#38bdf8';
      copyBtn.style.borderColor = 'rgba(56,189,248,0.25)';
      copyBtn.style.background = 'rgba(56,189,248,0.1)';
    }, 2000);
  }).catch(err => {
    alert("Could not copy key: " + err);
  });
}

function toggleRevealKey(slot) {
  const codeEl = document.getElementById('displayApiKey_' + slot);
  const revealBtn = document.getElementById('revealBtn_' + slot);
  const isMasked = codeEl.textContent === codeEl.getAttribute('data-masked-key');
  if (isMasked) {
    codeEl.textContent = codeEl.getAttribute('data-full-key');
    revealBtn.innerHTML = '🙈 Hide';
  } else {
    codeEl.textContent = codeEl.getAttribute('data-masked-key');
    revealBtn.innerHTML = '👁️ Reveal';
  }
}

// Dynamic plan cost calculator with Coupon and conditional Add-ons visibility
function calculateRenewalTotal() {
  const planSelect = document.getElementById('calc_plan');
  const durSelect = document.getElementById('calc_duration');
  const addonSelect = document.getElementById('calc_addons');
  const addonsGroup = document.getElementById('addonScannersGroup');
  
  const planVal = planSelect.value;
  const months = parseInt(durSelect.value);
  
  // Show Addons field only if Business Plan is selected
  let addons = 0;
  if (planVal === 'business') {
    addonsGroup.style.display = 'block';
    addons = parseInt(addonSelect.value);
  } else {
    addonsGroup.style.display = 'none';
    addonSelect.value = '0';
    addons = 0;
  }
  
  const selectedPlan = planSelect.options[planSelect.selectedIndex];
  
  // Calculate base plan cost
  let baseCost = 0;
  if (months === 1) {
    baseCost = parseFloat(selectedPlan.getAttribute('data-base'));
  } else if (months === 3) {
    baseCost = parseFloat(selectedPlan.getAttribute('data-opt-3'));
  } else if (months === 6) {
    baseCost = parseFloat(selectedPlan.getAttribute('data-opt-6'));
  } else if (months === 12) {
    baseCost = parseFloat(selectedPlan.getAttribute('data-opt-12'));
  }
  
  // Calculate add-on cost
  const addonRate = (typeof ADDON_MONTHLY_RATE !== 'undefined') ? ADDON_MONTHLY_RATE : 300;
  const addonCost = addons * addonRate * months;
  const subtotal = baseCost + addonCost;
  
  // Calculate Coupon Discount
  let discountAmount = 0;
  const discountRow = document.getElementById('receipt_discount_row');
  if (appliedCoupon) {
    if (appliedCoupon.discount_type === 'percentage') {
      discountAmount = subtotal * (appliedCoupon.discount_value / 100);
    } else {
      discountAmount = appliedCoupon.discount_value; // Flat discount
    }
    // Cap discount
    if (discountAmount > subtotal) {
      discountAmount = subtotal;
    }
    
    discountRow.style.display = 'flex';
    document.getElementById('receipt_coupon_name').textContent = appliedCoupon.code;
    document.getElementById('receipt_discount_amount').textContent = discountAmount.toFixed(2);
  } else {
    discountRow.style.display = 'none';
  }
  
  const grandTotal = Math.max(0, subtotal - discountAmount);
  
  // Update UI values
  document.getElementById('receipt_base_cost').textContent = `Rs ${baseCost.toLocaleString()}`;
  document.getElementById('receipt_addon_count').textContent = addons;
  document.getElementById('receipt_addon_cost').textContent = `Rs ${addonCost.toLocaleString()}`;
  document.getElementById('receipt_grand_total').textContent = `Rs ${grandTotal.toLocaleString()}`;
}

// Verify and apply Coupon code
function applyCouponCode() {
  const codeInput = document.getElementById('coupon_code');
  const msgSpan = document.getElementById('coupon_message');
  const code = codeInput.value.trim().toUpperCase();
  
  if (code === '') {
    appliedCoupon = null;
    msgSpan.style.color = '#cbd5e1';
    msgSpan.textContent = '';
    calculateRenewalTotal();
    return;
  }
  
  msgSpan.style.color = '#38bdf8';
  msgSpan.textContent = 'Verifying coupon code...';
  
  fetch(`api_link.php?action=verify_coupon&code=${encodeURIComponent(code)}`)
    .then(res => res.text())
    .then(parseCleanJson)
    .then(data => {
      if (data.success) {
        appliedCoupon = data;
        msgSpan.style.color = '#34d399';
        msgSpan.textContent = `Coupon "${data.code}" applied successfully! Discount: ${data.discount_type === 'percentage' ? data.discount_value + '%' : 'Rs ' + data.discount_value}`;
      } else {
        appliedCoupon = null;
        msgSpan.style.color = '#f87171';
        msgSpan.textContent = data.error || 'Invalid coupon code.';
      }
      calculateRenewalTotal();
    })
    .catch(err => {
      appliedCoupon = null;
      msgSpan.style.color = '#f87171';
      msgSpan.textContent = 'Error checking coupon code.';
      calculateRenewalTotal();
    });
}

// Trigger unified renewal payment via Razorpay
function payUnifiedRenewal() {
  const planSelect = document.getElementById('calc_plan');
  const durSelect = document.getElementById('calc_duration');
  const addonSelect = document.getElementById('calc_addons');
  
  const planVal = planSelect.value;
  const selectedPlan = planSelect.options[planSelect.selectedIndex];
  const months = parseInt(durSelect.value);
  const addons = (planVal === 'business') ? parseInt(addonSelect.value) : 0;
  
  const planName = selectedPlan.getAttribute('data-name');
  
  // Recalculate cost
  let baseCost = 0;
  if (months === 1) {
    baseCost = parseFloat(selectedPlan.getAttribute('data-base'));
  } else if (months === 3) {
    baseCost = parseFloat(selectedPlan.getAttribute('data-opt-3'));
  } else if (months === 6) {
    baseCost = parseFloat(selectedPlan.getAttribute('data-opt-6'));
  } else if (months === 12) {
    baseCost = parseFloat(selectedPlan.getAttribute('data-opt-12'));
  }
  
  const addonRate = (typeof ADDON_MONTHLY_RATE !== 'undefined') ? ADDON_MONTHLY_RATE : 300;
  const addonCost = addons * addonRate * months;
  const subtotal = baseCost + addonCost;
  
  let discountAmount = 0;
  if (appliedCoupon) {
    if (appliedCoupon.discount_type === 'percentage') {
      discountAmount = subtotal * (appliedCoupon.discount_value / 100);
    } else {
      discountAmount = appliedCoupon.discount_value;
    }
    if (discountAmount > subtotal) {
      discountAmount = subtotal;
    }
  }
  
  const totalAmount = Math.max(0, subtotal - discountAmount);
  
  // If payment total amount is 0 (due to 100% discount coupon), bypass Razorpay and checkout directly
  if (totalAmount <= 0) {
    const freePaymentId = 'FREE_COUPON_' + Math.random().toString(36).substr(2, 9).toUpperCase();
    
    // Show loading indicator
    const btn = document.querySelector('.btn-pay-now');
    const oldText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Processing...';
    
    fetch('api_link.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        'action': 'log_payment',
        'api_key': apiKey,
        'payment_id': freePaymentId,
        'plan': `${planName} (${addons} Add-on Scanners)`,
        'amount': 0,
        'duration': months,
        'extra_scanners': addons,
        'coupon_code': appliedCoupon ? appliedCoupon.code : ''
      })
    })
    .then(res => res.text())
    .then(parseCleanJson)
    .then(data => {
      btn.disabled = false;
      btn.textContent = oldText;
      if (data.success) {
        alert(`🎉 Subscription Extended Successfully!\nNew Expiry Date: ${data.new_expiry}`);
        window.location.reload();
      } else {
        alert('❌ Error extending subscription: ' + data.error);
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.textContent = oldText;
      alert('❌ Error connecting to database to log payment. Please contact support.');
    });
    return;
  }
  
  const rzpKey = <?= json_encode($settings['razorpay_key_id'] ?? 'rzp_test_YOUR_KEY_HERE') ?>;
  
  const options = {
    "key": rzpKey,
    "amount": Math.round(totalAmount * 100), // Paise
    "currency": "INR",
    "name": "THE EXPERT HUB",
    "description": `${planName} - ${months} Month(s)`,
    "handler": function (response) {
      // Payment successful callback
      fetch('api_link.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          'api_key': apiKey,
          'action': 'log_payment',
          'payment_id': response.razorpay_payment_id,
          'plan': `${planName} (${addons} Add-on Scanners)`,
          'amount': totalAmount,
          'duration': months,
          'extra_scanners': addons,
          'coupon_code': appliedCoupon ? appliedCoupon.code : ''
        })
      })
      .then(res => res.text())
      .then(parseCleanJson)
      .then(data => {
        if (data.success) {
          alert(`🎉 Payment Successful!\nYour account subscription has been extended successfully to: ${data.new_expiry}`);
          window.location.reload();
        } else {
          alert('❌ Payment recorded but error extending expiry: ' + data.error);
        }
      })
      .catch(err => {
        alert('❌ Error connecting to database to log payment. Please contact support.');
      });
    },
    "prefill": {
      "name": <?= json_encode($client['client_name']) ?>,
      "contact": <?= json_encode($client['client_phone']) ?>
    },
    "theme": {
      "color": "#38bdf8"
    }
  };
  
  const rzp = new Razorpay(options);
  rzp.open();
}

// Initial calculator call
calculateRenewalTotal();

// Change current slot to manage
function changeDeviceSlot(slotNum) {
  currentSlot = parseInt(slotNum);
  
  if (qrInterval) {
    clearInterval(qrInterval);
    qrInterval = null;
  }
  
  document.getElementById('linkedNumber').textContent = '';
  document.getElementById('qrImage').src = '';
  document.getElementById('statusBadge').textContent = 'Loading Device Slot #' + currentSlot + '...';
  document.getElementById('statusBadge').className = 'badge-status checking';
  
  pollStatus();
  qrInterval = setInterval(pollStatus, 3000);
}

// Connection check status polling loop
function startStatusPolling() {
  pollStatus();
  qrInterval = setInterval(pollStatus, 3000);
}

function pollStatus() {
  const badge = document.getElementById('statusBadge');
  const placeholder = document.getElementById('qrPlaceholder');
  const offline = document.getElementById('qrOffline');
  const loader = document.getElementById('qrLoader');
  const codeArea = document.getElementById('qrCodeArea');
  const connected = document.getElementById('qrConnected');
  const qrImage = document.getElementById('qrImage');
  const linkedNumber = document.getElementById('linkedNumber');
  const container = document.getElementById('qrContainer');
  
  if (!badge) return;

  const sessionSuffix = currentSlot > 1 ? ('_' + currentSlot) : '';
  let statusUrl = GATEWAY_URL.replace('/send', '/status');
  statusUrl += (statusUrl.indexOf('?') !== -1 ? '&' : '?') + 'session=' + encodeURIComponent(LOGIN_ID + sessionSuffix) + '&t=' + Date.now();

  fetch(statusUrl)
    .then(res => {
      if (!res.ok) throw new Error('Offline');
      return res.json();
    })
    .then(data => {
      placeholder.style.display = 'none';
      offline.style.display = 'none';
      loader.style.display = 'none';
      codeArea.style.display = 'none';
      connected.style.display = 'none';
      container.style.borderColor = 'rgba(255,255,255,0.08)';
      
      let nextState = 0;
      let num = '';
      
      if (data.status === 'DISCONNECTED') {
        badge.className = 'badge-status disconnected';
        badge.textContent = 'Disconnected';
        placeholder.style.display = 'flex';
        const descEl = placeholder.querySelector('div:last-child');
        if (descEl) {
          descEl.textContent = data.error || 'No WhatsApp device linked. Fetching status...';
          descEl.style.color = data.error ? '#f87171' : '#64748b';
        }
        nextState = 0;
      } 
      else if (data.status === 'INITIALIZING') {
        badge.className = 'badge-status';
        badge.textContent = 'Initializing';
        loader.style.display = 'block';
        nextState = 0;
      } 
      else if (data.status === 'QR_READY') {
        badge.className = 'badge-status qr-ready';
        badge.textContent = 'Scan QR Code';
        codeArea.style.display = 'flex';
        container.style.borderColor = '#00ffcc';
        container.style.boxShadow = '0 0 25px rgba(0, 255, 204, 0.15)';
        nextState = 0;
        
        if (data.qr && qrImage.src !== data.qr) {
          qrImage.src = data.qr;
        }
      } 
      else if (data.status === 'CONNECTED') {
        badge.className = 'badge-status connected';
        badge.textContent = 'Connected';
        connected.style.display = 'flex';
        num = data.number || 'Linked';
        linkedNumber.textContent = num;
        container.style.borderColor = '#34d399';
        nextState = 1;
        
        let chatBadge = document.getElementById('connected_chat_stats');
        if (!chatBadge) {
          chatBadge = document.createElement('div');
          chatBadge.id = 'connected_chat_stats';
          chatBadge.style.cssText = 'font-size: 13.5px; color: #38bdf8; font-weight: 700; margin-top: 10px; font-family: inherit; background: rgba(56, 189, 248, 0.08); padding: 8px 16px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.2); display: inline-block;';
          connected.appendChild(chatBadge);
        }
        let chatsVal = (data.chat_count && data.chat_count > 0) ? data.chat_count.toLocaleString() : ((data.contact_count && data.contact_count > 0) ? data.contact_count.toLocaleString() : 'Available & Ready');
        chatBadge.textContent = '💬 Available WhatsApp Chats: ' + chatsVal + ' Active Chats';
        
        clearInterval(qrInterval);
        qrInterval = setInterval(pollStatus, 15000); // Slow down status checks once connected
      }

      // Update slot status badge UI dynamically
      const slotBadge = document.getElementById('slot_badge_' + currentSlot);
      if (slotBadge) {
        if (nextState === 1) {
          slotBadge.style.background = 'rgba(52,211,153,0.1)';
          slotBadge.style.color = '#34d399';
          slotBadge.style.borderColor = 'rgba(52,211,153,0.2)';
          slotBadge.textContent = `Device #${currentSlot}: ✅ Link Active (${num})`;
        } else {
          slotBadge.style.background = 'rgba(255,255,255,0.04)';
          slotBadge.style.color = '#94a3b8';
          slotBadge.style.borderColor = 'rgba(255,255,255,0.06)';
          slotBadge.textContent = `Device #${currentSlot}: ❌ Offline`;
        }
      }

      // Sync connection state to SQLite DB ONLY ON CHANGE!
      if (nextState !== slotStates[currentSlot]) {
        slotStates[currentSlot] = nextState;
        fetch('api_link.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            'action': 'update_connection_status',
            'api_key': apiKey,
            'connected': nextState,
            'number': num,
            'slot': currentSlot
          })
        });
      }
    })
    .catch(err => {
      placeholder.style.display = 'none';
      offline.style.display = 'flex';
      loader.style.display = 'none';
      codeArea.style.display = 'none';
      connected.style.display = 'none';
      badge.className = 'badge-status disconnected';
      badge.textContent = 'Offline';
      container.style.borderColor = '#f87171';

      // Update slot status badge UI dynamically to Offline
      const slotBadge = document.getElementById('slot_badge_' + currentSlot);
      if (slotBadge) {
        slotBadge.style.background = 'rgba(255,255,255,0.04)';
        slotBadge.style.color = '#94a3b8';
        slotBadge.style.borderColor = 'rgba(255,255,255,0.06)';
        slotBadge.textContent = `Device #${currentSlot}: ❌ Offline`;
      }

      if (slotStates[currentSlot] === 1) {
        slotStates[currentSlot] = 0;
        fetch('api_link.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            'action': 'update_connection_status',
            'api_key': apiKey,
            'connected': 0,
            'slot': currentSlot
          })
        });
      }
    });
}

// ── Disconnect Device ──────────────────────────────────────────────────────
async function disconnectDevice() {
  const btn = document.getElementById('disconnectBtn');
  const alwaysBtn = document.getElementById('alwaysDisconnectBtn');
  const msg = document.getElementById('disconnectMsg');

  if (!confirm('Are you sure you want to disconnect Device Slot #' + currentSlot + ' from WhatsApp?')) return;

  const disableBtn = (b) => {
    if (b) {
      b.disabled = true;
      b.style.opacity = '0.5';
      b.style.cursor = 'not-allowed';
    }
  };
  const enableBtn = (b) => {
    if (b) {
      b.disabled = false;
      b.style.opacity = '1';
      b.style.cursor = 'pointer';
    }
  };

  disableBtn(btn);
  disableBtn(alwaysBtn);
  
  if (msg) {
    msg.style.display = 'block';
    msg.textContent = 'Disconnecting...';
    msg.style.color = '#cbd5e1';
  }

  try {
    const sessionSuffix = currentSlot > 1 ? ('_' + currentSlot) : '';
    const sessionId = LOGIN_ID + sessionSuffix;
    const disconnectUrl = GATEWAY_URL.replace('/send', '/disconnect');

    const res = await fetch(disconnectUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ session: sessionId, id: sessionId })
    });

    // Force update DB to disconnected
    await fetch('api_link.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        'action': 'update_connection_status',
        'api_key': apiKey,
        'connected': 0,
        'number': '',
        'slot': currentSlot
      })
    });

    // Reset UI
    slotStates[currentSlot] = 0;
    const connected = document.getElementById('qrConnected');
    const placeholder = document.getElementById('qrPlaceholder');
    const badge = document.getElementById('statusBadge');
    const container = document.getElementById('qrContainer');
    const slotBadge = document.getElementById('slot_badge_' + currentSlot);

    if (connected) connected.style.display = 'none';
    if (placeholder) placeholder.style.display = 'flex';
    if (badge) {
      badge.className = 'badge-status disconnected';
      badge.textContent = 'Disconnected';
    }
    if (container) container.style.borderColor = 'rgba(255,255,255,0.08)';

    if (slotBadge) {
      slotBadge.style.background = 'rgba(255,255,255,0.04)';
      slotBadge.style.color = '#94a3b8';
      slotBadge.style.borderColor = 'rgba(255,255,255,0.06)';
      slotBadge.textContent = `Device #${currentSlot}: ❌ Offline`;
    }

    if (msg) {
      msg.textContent = '✅ Disconnected successfully!';
      msg.style.color = '#34d399';
    }

    // Restart polling to resume QR
    clearInterval(qrInterval);
    setTimeout(() => {
      if (msg) msg.style.display = 'none';
      enableBtn(btn);
      enableBtn(alwaysBtn);
      pollStatus();
      qrInterval = setInterval(pollStatus, 4000);
    }, 2000);

  } catch (err) {
    if (msg) {
      msg.textContent = '❌ Disconnect failed. Try again.';
      msg.style.color = '#f87171';
    }
    enableBtn(btn);
    enableBtn(alwaysBtn);
  }
}

// Run polling loop
startStatusPolling();

// Subscription Expiry Countdown Timer
document.addEventListener('DOMContentLoaded', function() {
  const expiryEl = document.getElementById('expiry-countdown-val');
  const descEl = document.getElementById('expiry-countdown-desc');
  if (!expiryEl) return;
  
  const expiryStr = expiryEl.getAttribute('data-expiry');
  if (!expiryStr || expiryStr.trim() === '') {
    expiryEl.textContent = 'No Expiry';
    expiryEl.style.color = '#ffffff';
    return;
  }
  
  let expiryTime = new Date(expiryStr).getTime();
  if (expiryStr.length <= 10) {
    expiryTime = new Date(expiryStr + 'T23:59:59').getTime();
  }
  
  function updateCountdown() {
    const now = new Date().getTime();
    const diff = expiryTime - now;
    
    if (diff <= 0) {
      expiryEl.textContent = 'EXPIRED';
      expiryEl.style.color = '#f87171';
      descEl.textContent = 'Deadline: ' + formatDateString(expiryStr);
      descEl.style.color = '#ef4444';
      return;
    }
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    
    let parts = [];
    if (days > 0) parts.push(String(days).padStart(2, '0') + 'd');
    parts.push(String(hours).padStart(2, '0') + 'h');
    parts.push(String(minutes).padStart(2, '0') + 'm');
    parts.push(String(seconds).padStart(2, '0') + 's');
    
    expiryEl.textContent = parts.join(' ');
    
    if (diff < 1000 * 60 * 60 * 24) { // Less than 1 day
      expiryEl.style.color = '#fbbf24'; // Warning yellow
      descEl.style.color = '#fbbf24';
    } else {
      expiryEl.style.color = '#34d399'; // Safe green
    }
    
    descEl.textContent = 'Expires: ' + formatDateString(expiryStr);
  }
  
  function formatDateString(str) {
    const d = new Date(str);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return String(d.getDate()).padStart(2, '0') + '-' + months[d.getMonth()] + '-' + d.getFullYear();
  }
  
  updateCountdown();
  setInterval(updateCountdown, 1000);
});
</script>
<?php else: ?>
<!-- 1. Authentication Form (Login ID and Password) -->
<div style="min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 24px; background-color: var(--bg-main);">
  <div class="login-card" style="width: 100%; max-width: 440px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 30px 70px rgba(0,0,0,0.5); text-align: center;">
    <div class="logo-area" style="margin-bottom: 28px; text-align: center;">
      <img src="zamzy_logo.png" alt="ZAMZY" style="height: 60px; width: auto; margin-bottom: 12px; display: block; margin-left: auto; margin-right: auto;">
      <h1 class="logo" style="font-size: 26px; font-weight: 800; color: #ffffff; font-family: var(--font-heading); letter-spacing: 1px;">
        ZAM<span style="color: var(--cyan);">ZY</span>
      </h1>
      <div class="tagline" style="font-size: 10px; color: var(--cyan); margin-top: 4px; text-transform: uppercase; letter-spacing: 2px; font-family: var(--font-mono); font-weight: 600;">WhatsApp Developer Gateway</div>
    </div>

    <?php if (!empty($success_msg)): ?>
      <div class="alert success" style="margin-bottom: 20px; text-align: left; background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.25); color: #34d399; padding: 12px 16px; border-radius: 8px; font-size: 13px;">
        <?= htmlspecialchars($success_msg) ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($error_msg)): ?>
      <div class="alert error" style="margin-bottom: 20px; text-align: left; background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25); color: #f87171; padding: 12px 16px; border-radius: 8px; font-size: 13px;">
        <?= htmlspecialchars($error_msg) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['reset_step']) && $_SESSION['reset_step'] === 'verify'): ?>
      <!-- 1b. Verify Reset State Form -->
      <p style="text-align:center; color: var(--text-secondary); font-size:13.5px; margin-bottom:20px; line-height: 1.5;">
        Enter the 6-digit verification code sent to your WhatsApp device, and define your new login password.
      </p>
      <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
        <input type="hidden" name="action" value="verify_otp">
        
        <div class="form-group" style="text-align: left;">
          <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">6-Digit OTP Code</label>
          <input type="text" name="otp_code" id="otp_code" placeholder="Enter 6-digit OTP" required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px;">
        </div>

        <div class="form-group" style="text-align: left;">
          <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">New Password</label>
          <input type="password" name="new_password" id="new_password" placeholder="Min 6 characters" required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px;">
        </div>

        <div class="form-group" style="text-align: left;">
          <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px;">
        </div>

        <button type="submit" class="btn-submit" style="width: 100%; padding: 14px; background: var(--lime); color: var(--bg-main); border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit;">Reset &amp; Authenticate</button>
        <a href="api_link.php?cancel_reset=1" style="color: var(--text-muted); font-size: 12px; text-decoration: none; margin-top: 10px; display: inline-block;">← Back to Login</a>
      </form>

    <?php else: ?>
      <!-- 1a. Standard Login Form -->
      <p style="text-align:center; color: var(--text-secondary); font-size:13.5px; margin-bottom:20px; line-height: 1.5;">
        Sign in using your Login ID (Registered Phone Number) and secure password.
      </p>

      <div style="background: rgba(212,255,61,0.05); border: 1px solid rgba(212,255,61,0.18); padding: 12px 14px; border-radius: 12px; font-size: 12.5px; color: #d4ff3d; margin-bottom: 20px; line-height: 1.4; text-align: left; display: flex; align-items: flex-start; gap: 8px;">
        <span style="font-size: 16px; margin-top: 1px;">🎁</span>
        <div>
          <strong>24-Hour Free Trial Available:</strong> Sign up now to evaluate all gateway features before purchasing a subscription.
        </div>
      </div>
      
      <form method="POST" style="display: flex; flex-direction: column; gap: 18px;">
        <input type="hidden" name="action" value="login">
        
        <div class="form-group" style="text-align: left;">
          <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Login ID (Phone Number)</label>
          <input type="text" name="login_id" id="login_id" placeholder="e.g. 919876543210" required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px;">
        </div>

        <div class="form-group" style="text-align: left;">
          <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Password</label>
          <div class="password-container" style="position: relative;">
            <input type="password" name="login_password" id="login_password" placeholder="Enter password" required style="width: 100%; padding: 12px; padding-right: 70px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px;">
            <button type="button" class="toggle-password" onclick="togglePass()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); font-size: 11px; cursor: pointer; font-weight: 700; text-transform: uppercase; font-family: inherit;">Show</button>
          </div>
        </div>

        <button type="submit" class="btn-submit" style="width: 100%; padding: 14px; background: var(--lime); color: var(--bg-main); border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px var(--lime-glow); font-family: inherit;">Authenticate Hub</button>
      </form>

      <!-- Forgot Password + Signup links -->
      <div style="margin-top: 24px; border-top: 1px solid var(--border-color-soft); padding-top: 18px; text-align: center; display: flex; flex-direction: column; gap: 10px;">
        <a href="#" onclick="showForgotResetForm(event)" style="color: var(--lime); text-decoration: none; font-size: 12.5px; font-weight: 600;">🔒 Forgot your password? Reset via WhatsApp</a>
        <a href="#" onclick="showSignupModal(event)" style="color: #94a3b8; text-decoration: none; font-size: 12.5px;">Don't have an account? <strong style="color: var(--lime);">Sign Up</strong></a>
      </div>

      <!-- ── Forgot Password Modal ───────────────────────────────────────── -->
      <div id="resetRequestModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.82); z-index: 2000; justify-content: center; align-items: center; padding: 20px;">
        <div class="login-card" style="margin: 0; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 16px; width: 100%; max-width: 400px; padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
          <h3 style="color: #fff; margin-bottom: 10px; font-size: 17px; font-weight: 700; font-family: var(--font-heading);">Reset Password</h3>
          <p style="font-size: 12.5px; color: var(--text-secondary); margin-bottom: 20px; line-height: 1.5; text-align: center;">Enter your registered mobile number below. We will send a 6-digit OTP verification code to your WhatsApp device.</p>
          <form id="resetRequestForm" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
            <input type="hidden" name="action" value="request_otp">
            <div class="form-group" style="text-align: left;">
              <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Registered WhatsApp Number</label>
              <input type="text" name="reset_phone" id="reset_phone" placeholder="e.g. 919876543210" required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
              <button type="button" onclick="closeForgotResetForm()" style="background: none; border: 1px solid var(--border-color); color: var(--text-secondary); padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 12.5px; font-family: inherit;">Cancel</button>
              <button type="submit" style="background: var(--lime); color: var(--bg-main); border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 12.5px; font-family: inherit;">Send OTP</button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── Signup Modal ───────────────────────────────────────────────── -->
      <div id="signupModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.85); z-index: 2000; justify-content: center; align-items: center; padding: 20px; overflow-y: auto;">
        <div style="margin: auto; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 20px; width: 100%; max-width: 420px; padding: 36px; box-shadow: 0 30px 70px rgba(0,0,0,0.6);">

          <!-- Signup Step 1: Registration form -->
          <div id="signupStep1">
            <div style="text-align: center; margin-bottom: 24px;">
              <img src="zamzy_logo.png" alt="ZAMZY" style="height:50px;width:auto;margin-bottom:8px;display:block;margin-left:auto;margin-right:auto;">
              <div style="font-size: 22px; font-weight: 800; color: #ffffff; font-family: var(--font-heading); letter-spacing: 0.5px;">ZAM<span style="color:var(--cyan);">ZY</span></div>
              <div style="font-size: 10px; color: var(--cyan); margin-top: 4px; text-transform: uppercase; letter-spacing: 1.5px; font-family:var(--font-mono);">Developer Registration</div>
            </div>
            <div id="signupStep1Error" style="display:none; background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25); color: #f87171; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;"></div>
            <form id="signupForm1" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
              <input type="hidden" name="action" value="signup_request_otp">
              <div style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Full Name</label>
                <input type="text" name="signup_name" id="signup_name" placeholder="e.g. John Doe" required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px; box-sizing: border-box;">
              </div>
              <div style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">WhatsApp Mobile Number</label>
                <input type="text" name="signup_phone" id="signup_phone" placeholder="e.g. 919876543210" required style="width: 100%; padding: 12px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px; box-sizing: border-box;">
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">Include country code. OTP will be sent to this number.</div>
              </div>
              <div style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Password</label>
                <div style="position: relative;">
                  <input type="password" name="signup_password" id="signup_password" placeholder="Min 6 characters" required style="width: 100%; padding: 12px; padding-right: 70px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px; box-sizing: border-box;">
                  <button type="button" onclick="toggleSignupPass('signup_password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);font-size:11px;cursor:pointer;font-weight:700;text-transform:uppercase;font-family:inherit;">Show</button>
                </div>
              </div>
              <div style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Confirm Password</label>
                <div style="position: relative;">
                  <input type="password" name="signup_confirm" id="signup_confirm" placeholder="Re-enter password" required style="width: 100%; padding: 12px; padding-right: 70px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: inherit; outline: none; font-size: 14px; box-sizing: border-box;">
                  <button type="button" onclick="toggleSignupPass('signup_confirm', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);font-size:11px;cursor:pointer;font-weight:700;text-transform:uppercase;font-family:inherit;">Show</button>
                </div>
              </div>
              <button type="submit" id="signupSendOtpBtn" style="width:100%;padding:14px;background:var(--lime);color:var(--bg-main);border:none;border-radius:12px;font-weight:700;cursor:pointer;font-size:15px;font-family:inherit;box-shadow:0 4px 14px var(--lime-glow);transition:all 0.2s;">Send WhatsApp OTP →</button>
            </form>
            <div style="margin-top: 16px; text-align: center;">
              <a href="#" onclick="closeSignupModal(event)" style="color: var(--text-muted); font-size: 12px; text-decoration: none;">← Back to Login</a>
            </div>
          </div>

          <!-- Signup Step 2: OTP Verification (shown after PHP sets signup_step=verify) -->
          <?php if (isset($_SESSION['signup_step']) && $_SESSION['signup_step'] === 'verify'): ?>
          <script>document.addEventListener('DOMContentLoaded', function(){ openSignupOtpStep(); });</script>
          <div id="signupStep2" style="display:none;">
            <div style="text-align: center; margin-bottom: 24px;">
              <div style="font-size: 22px; margin-bottom: 6px;">📱</div>
              <div style="font-size: 20px; font-weight: 700; color: var(--lime); font-family: var(--font-heading);">Verify OTP</div>
              <div style="font-size: 12.5px; color: var(--text-secondary); margin-top: 8px; line-height: 1.5;">Enter the 6-digit OTP sent to your WhatsApp number.</div>
            </div>
            <?php if (!empty($success_msg)): ?>
            <div style="background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.25); color: #34d399; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;"><?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
            <div style="background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25); color: #f87171; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
              <input type="hidden" name="action" value="signup_verify_otp">
              <div style="text-align: left;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">6-Digit OTP Code</label>
                <input type="text" name="signup_otp_code" id="signup_otp_code" placeholder="Enter OTP from WhatsApp" required maxlength="6" autofocus style="width: 100%; padding: 14px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-family: monospace; outline: none; font-size: 20px; letter-spacing: 6px; text-align: center; box-sizing: border-box;">
              </div>
              <button type="submit" style="width:100%;padding:14px;background:var(--lime);color:var(--bg-main);border:none;border-radius:12px;font-weight:700;cursor:pointer;font-size:15px;font-family:inherit;box-shadow:0 4px 14px var(--lime-glow);">✅ Verify &amp; Create Account</button>
            </form>
            <div style="margin-top: 16px; text-align: center;">
              <a href="api_link.php?cancel_reset=1" style="color: var(--text-muted); font-size: 12px; text-decoration: none;">← Start Over</a>
            </div>
          </div>
          <?php else: ?>
          <div id="signupStep2" style="display:none;"></div>
          <?php endif; ?>

        </div>
      </div>

    <?php endif; ?>
  </div>
</div>

<script>
function togglePass() {
  const p = document.getElementById('login_password');
  const btn = document.querySelector('.toggle-password');
  if (p.type === 'password') { p.type = 'text'; btn.textContent = 'Hide'; }
  else { p.type = 'password'; btn.textContent = 'Show'; }
}
function showForgotResetForm(e) {
  if (e) e.preventDefault();
  document.getElementById('resetRequestModal').style.display = 'flex';
}
function closeForgotResetForm() {
  document.getElementById('resetRequestModal').style.display = 'none';
}
function showSignupModal(e) {
  if (e) e.preventDefault();
  document.getElementById('signupModal').style.display = 'flex';
}
function closeSignupModal(e) {
  if (e) e.preventDefault();
  document.getElementById('signupModal').style.display = 'none';
}
function openSignupOtpStep() {
  document.getElementById('signupModal').style.display = 'flex';
  document.getElementById('signupStep1').style.display = 'none';
  document.getElementById('signupStep2').style.display = 'block';
}
function toggleSignupPass(fieldId, btn) {
  const f = document.getElementById(fieldId);
  if (f.type === 'password') { f.type = 'text'; btn.textContent = 'Hide'; }
  else { f.type = 'password'; btn.textContent = 'Show'; }
}
// Client-side validation and AJAX submission for Reset Password & Signup OTPs
document.addEventListener('DOMContentLoaded', function() {
  const resetForm = document.getElementById('resetRequestForm');
  if (resetForm) {
    resetForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const phoneInput = document.getElementById('reset_phone');
      const phone = phoneInput.value.replace(/[^0-9]/g, '');
      const btn = this.querySelector('button[type="submit"]');
      const originalText = btn.textContent;
      
      btn.textContent = '⏳ Sending OTP...';
      btn.disabled = true;
      
      const formData = new FormData();
      formData.append('action', 'request_otp_ajax');
      formData.append('reset_phone', phone);
      
      fetch('api_link.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const msgText = data.message_text;
          const cleanPhone = data.phone;
          const gatewayUrl = data.gateway_url;
          const gatewayToken = data.gateway_token;
          
          if (gatewayUrl) {
            // Create hidden iframe if not exists to submit form cross-origin without CORS blocking
            let iframe = document.getElementById('gw_submit_iframe');
            if (!iframe) {
              iframe = document.createElement('iframe');
              iframe.id = 'gw_submit_iframe';
              iframe.name = 'gw_submit_iframe';
              iframe.style.display = 'none';
              document.body.appendChild(iframe);
            }
            
            // Create temporary form
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = gatewayUrl;
            tempForm.target = 'gw_submit_iframe';
            
            const fields = {
              to: cleanPhone,
              phone: cleanPhone,
              number: cleanPhone,
              body: msgText,
              message: msgText,
              token: gatewayToken,
              apikey: gatewayToken
            };
            
            for (const k in fields) {
              const input = document.createElement('input');
              input.type = 'hidden';
              input.name = k;
              input.value = fields[k];
              tempForm.appendChild(input);
            }
            
            document.body.appendChild(tempForm);
            
            // Redirect after 1.5 seconds or iframe load
            let redirected = false;
            const doRedirect = () => {
              if (!redirected) {
                redirected = true;
                window.location.href = 'api_link.php?staff_success=' + encodeURIComponent('A 6-digit verification code has been sent to your WhatsApp number.');
              }
            };
            
            iframe.onload = doRedirect;
            tempForm.submit();
            document.body.removeChild(tempForm);
            
            // Fallback timeout
            setTimeout(doRedirect, 1500);
            
          } else {
            const waUrl = 'https://api.whatsapp.com/send?phone=' + cleanPhone + '&text=' + encodeURIComponent(msgText);
            window.open(waUrl, '_blank');
            window.location.href = 'api_link.php?staff_success=' + encodeURIComponent('OTP link opened in a new tab.');
          }
        } else {
          alert('Error: ' + (data.error || 'Failed to request OTP'));
          btn.textContent = originalText;
          btn.disabled = false;
        }
      })
      .catch(err => {
        console.error(err);
        alert('Failed to connect to the server.');
        btn.textContent = originalText;
        btn.disabled = false;
      });
    });
  }

  const signupForm = document.getElementById('signupForm1');
  if (signupForm) {
    signupForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const name = document.getElementById('signup_name').value;
      const phone = document.getElementById('signup_phone').value.replace(/[^0-9]/g, '');
      const pass = document.getElementById('signup_password').value;
      const conf = document.getElementById('signup_confirm').value;
      const errEl = document.getElementById('signupStep1Error');
      const btn = document.getElementById('signupSendOtpBtn');
      const originalText = btn.textContent;
      
      if (phone.length < 10) {
        errEl.textContent = 'Please enter a valid mobile number with country code (min 10 digits).';
        errEl.style.display = 'block'; return;
      }
      if (pass.length < 6) {
        errEl.textContent = 'Password must be at least 6 characters.';
        errEl.style.display = 'block'; return;
      }
      if (pass !== conf) {
        errEl.textContent = 'Passwords do not match.';
        errEl.style.display = 'block'; return;
      }
      
      errEl.style.display = 'none';
      btn.textContent = '⏳ Sending OTP...';
      btn.disabled = true;
      
      const formData = new FormData();
      formData.append('action', 'signup_request_otp_ajax');
      formData.append('signup_phone', phone);
      formData.append('signup_name', name);
      formData.append('signup_password', pass);
      formData.append('signup_confirm', conf);
      
      fetch('api_link.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const msgText = data.message_text;
          const cleanPhone = data.phone;
          const gatewayUrl = data.gateway_url;
          const gatewayToken = data.gateway_token;
          
          if (gatewayUrl) {
            // Create hidden iframe if not exists to submit form cross-origin without CORS blocking
            let iframe = document.getElementById('gw_submit_iframe');
            if (!iframe) {
              iframe = document.createElement('iframe');
              iframe.id = 'gw_submit_iframe';
              iframe.name = 'gw_submit_iframe';
              iframe.style.display = 'none';
              document.body.appendChild(iframe);
            }
            
            // Create temporary form
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = gatewayUrl;
            tempForm.target = 'gw_submit_iframe';
            
            const fields = {
              to: cleanPhone,
              phone: cleanPhone,
              number: cleanPhone,
              body: msgText,
              message: msgText,
              token: gatewayToken,
              apikey: gatewayToken
            };
            
            for (const k in fields) {
              const input = document.createElement('input');
              input.type = 'hidden';
              input.name = k;
              input.value = fields[k];
              tempForm.appendChild(input);
            }
            
            document.body.appendChild(tempForm);
            
            // Redirect after 1.5 seconds or iframe load
            let redirected = false;
            const doRedirect = () => {
              if (!redirected) {
                redirected = true;
                window.location.href = 'api_link.php?staff_success=' + encodeURIComponent('A 6-digit OTP has been sent to WhatsApp.');
              }
            };
            
            iframe.onload = doRedirect;
            tempForm.submit();
            document.body.removeChild(tempForm);
            
            // Fallback timeout
            setTimeout(doRedirect, 1500);
            
          } else {
            const waUrl = 'https://api.whatsapp.com/send?phone=' + cleanPhone + '&text=' + encodeURIComponent(msgText);
            window.open(waUrl, '_blank');
            window.location.href = 'api_link.php?staff_success=' + encodeURIComponent('OTP link opened in a new tab.');
          }
        } else {
          errEl.textContent = data.error || 'Failed to request OTP';
          errEl.style.display = 'block';
          btn.textContent = originalText;
          btn.disabled = false;
        }
      })
      .catch(err => {
        console.error(err);
        errEl.textContent = 'Failed to connect to the server.';
        errEl.style.display = 'block';
        btn.textContent = originalText;
        btn.disabled = false;
      });
    });
  }
});
</script>
<?php endif; ?>

</body>
</html>
