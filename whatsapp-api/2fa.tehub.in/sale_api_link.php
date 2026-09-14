<?php
require_once __DIR__ . '/db.php';

session_start();

try {
    $settings_stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $settings_stmt->fetch();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

$success_msg = '';
$error_msg = '';

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
                    $new_slot_key = 'teh_api_' . bin2hex(random_bytes(16));
                    if ($i === 1 && !empty($client['api_key'])) {
                        $new_slot_key = $client['api_key']; // Slot 1 defaults to the main API key
                    }
                    $ins = $pdo->prepare("INSERT INTO client_devices (client_id, slot_number, api_key, whatsapp_is_connected) VALUES (?, ?, ?, 0)");
                    $ins->execute([$client['id'], $i, $new_slot_key]);
                } elseif (empty($dev['api_key'])) {
                    $new_slot_key = 'teh_api_' . bin2hex(random_bytes(16));
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
    
    // Action: Add Rule
    if ($_POST['action'] === 'add_chatbot_rule') {
        $keyword = trim($_POST['keyword'] ?? '');
        $reply_text = trim($_POST['reply_text'] ?? '');
        
        if (empty($keyword) || empty($reply_text)) {
            $error_msg = 'Please fill in both the keyword and the response text.';
        } else {
            try {
                // Check for duplicate keyword
                $chk = $pdo->prepare("SELECT COUNT(*) FROM chatbot_rules WHERE client_id = ? AND LOWER(keyword) = ?");
                $chk->execute([$client['id'], strtolower($keyword)]);
                if ($chk->fetchColumn() > 0) {
                    $error_msg = "A rule for the keyword '{$keyword}' already exists.";
                } else {
                    $ins = $pdo->prepare("INSERT INTO chatbot_rules (client_id, keyword, reply_text) VALUES (?, ?, ?)");
                    $ins->execute([$client['id'], $keyword, $reply_text]);
                    $success_msg = 'Chatbot rule added successfully!';
                }
            } catch (PDOException $e) {
                $error_msg = 'Failed to add rule: ' . $e->getMessage();
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
<title>Developer Client Hub – THE EXPERT HUB</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&family=Geist+Mono:wght@400;500&display=swap');

  :root {
    --bg-main: #0A0A0C;
    --bg-surface: #121215;
    --bg-card: #18181C;
    --bg-elev: #232328;
    --border-color: rgba(244, 244, 240, 0.08);
    --border-color-soft: rgba(244, 244, 240, 0.04);
    
    --text-primary: #F4F4F0;
    --text-secondary: #C9C9C2;
    --text-muted: #80807A;
    
    --lime: #D4FF3D;
    --lime-deep: #9CCB1F;
    --lime-glow: rgba(212, 255, 61, 0.20);
    
    --font-heading: 'Outfit', 'Inter Tight', sans-serif;
    --font-body: 'Inter Tight', 'Inter', sans-serif;
    --font-mono: 'Geist Mono', monospace;
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
    padding: 24px 16px;
  }
  
  .portal-card {
    background-color: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 36px 44px;
    width: 100%;
    max-width: 900px;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.7);
    animation: fadeIn 0.4s ease-out;
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
  
  /* Tabs Navbar */
  .tabs-nav {
    display: flex;
    gap: 8px;
    background: var(--bg-card);
    padding: 6px;
    border-radius: 12px;
    margin-bottom: 32px;
    border: 1px solid var(--border-color-soft);
  }
  .tab-btn {
    flex: 1;
    background: transparent;
    border: none;
    color: var(--text-secondary);
    padding: 11px 14px;
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s;
    text-align: center;
  }
  .tab-btn:hover { color: var(--text-primary); background: var(--border-color-soft); }
  .tab-btn.active {
    background: var(--lime);
    color: var(--bg-main);
    box-shadow: 0 4px 14px rgba(212, 255, 61, 0.25);
    font-weight: 700;
  }
  
  .tab-panel { display: none; }
  .tab-panel.active { display: block; }
  
  .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
  label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
  
  input, textarea, select {
    background: var(--bg-card);
    border: 1.5px solid var(--border-color);
    border-radius: 8px;
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
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
    text-align: center;
  }
  .btn-submit:hover { background: var(--lime-deep); transform: translateY(-1px); }
  
  .alert { padding: 12px 18px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 20px; line-height: 1.4; }
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
    padding: 18px;
  }
  .stat-card .label { font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px; }
  .stat-card .val { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-top: 4px; font-family: var(--font-heading); }
  .stat-card .desc { font-size: 11px; color: var(--text-secondary); margin-top: 4px; }
  
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
    padding: 5px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 11px;
    font-family: inherit;
    font-weight: 600;
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
    border-radius: 8px;
    padding: 14px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    text-align: center;
    box-shadow: 0 4px 14px rgba(212, 255, 61, 0.2);
    transition: all 0.2s;
  }
  .btn-pay-now:hover { background: var(--lime-deep); transform: translateY(-1px); }
  
  .btn-logout {
    display: block;
    text-align: center;
    font-size: 12px;
    color: #f87171;
    text-decoration: none;
    margin-top: 24px;
    font-weight: 600;
  }
  .btn-logout:hover { text-decoration: underline; }
  
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

<div class="<?= $client ? 'portal-card' : 'login-card' ?>">
    <img src="teh_logo.png" alt="THE EXPERT HUB" style="height: 72px; width: auto; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
    <div class="logo" style="font-size: 22px; letter-spacing: 1px;">THE EXPERT HUB</div>
    <div class="tagline">Client Developer Dashboard</div>

  <?php if (!empty($success_msg)): ?>
    <div class="alert success">✅ <?= $success_msg ?></div>
  <?php endif; ?>
  <?php if (!empty($error_msg)): ?>
    <div class="alert error">❌ <?= $error_msg ?></div>
  <?php endif; ?>

  <?php if (!$client): ?>
    <?php if (isset($_SESSION['reset_step']) && $_SESSION['reset_step'] === 'verify'): ?>
      <!-- 1b. Verify Reset State Form -->
      <p style="text-align:center; color:#94a3b8; font-size:14px; margin-bottom:20px;">Enter the 6-digit verification code sent to your WhatsApp device, and define your new login password.</p>
      <form method="POST">
        <input type="hidden" name="action" value="verify_otp">
        
        <div class="form-group">
          <label for="otp_code">6-Digit OTP Code</label>
          <input type="text" name="otp_code" id="otp_code" placeholder="Enter 6-digit OTP" required>
        </div>

        <div class="form-group">
          <label for="new_password">New Password</label>
          <input type="password" name="new_password" id="new_password" placeholder="Min 6 characters" required>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm New Password</label>
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
        </div>

        <button type="submit" class="btn-submit" style="width: 100%;">Change Password &amp; Log In</button>
        
        <div style="text-align: center; margin-top: 16px;">
          <a href="api_link.php?cancel_reset=1" style="color: #f87171; font-size: 13px; text-decoration: none;">Cancel Reset</a>
        </div>
      </form>

    <?php elseif (isset($_GET['forgot'])): ?>
      <!-- 1c. Request Reset State Form -->
      <p style="text-align:center; color:#94a3b8; font-size:14px; margin-bottom:20px;">Enter your registered mobile number. We will send a 6-digit OTP code to your WhatsApp device to verify your identity.</p>
      <form method="POST">
        <input type="hidden" name="action" value="request_otp">
        
        <div class="form-group">
          <label for="reset_phone">Registered Mobile Number</label>
          <input type="text" name="reset_phone" id="reset_phone" placeholder="e.g. 919876543210" required>
        </div>
        
        <button type="submit" class="btn-submit" style="width: 100%;">Send Verification Code</button>
        
        <div style="text-align: center; margin-top: 16px;">
          <a href="api_link.php" style="color: #94a3b8; font-size: 13px; text-decoration: none;">Back to Login</a>
        </div>
      </form>

    <?php else: ?>
      <!-- 1a. Default Login Form -->
      <p style="text-align:center; color:#94a3b8; font-size:14px; margin-bottom:20px;">Please enter your Login ID (Phone Number) and Password sent to your WhatsApp device to manage and connect your account.</p>
      <form method="POST">
        <input type="hidden" name="action" value="login">
        
        <div class="form-group">
          <label for="login_id">Login ID (Phone Number)</label>
          <input type="text" name="login_id" id="login_id" placeholder="e.g. 919876543210" required>
        </div>

        <div class="form-group">
          <label for="login_password">Password</label>
          <input type="password" name="login_password" id="login_password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-submit" style="width: 100%;">Log In</button>
        
        <div style="text-align: center; margin-top: 16px;">
          <a href="api_link.php?forgot=1" style="color: #38bdf8; font-size: 13px; text-decoration: none;">Forgot Password / Change Password?</a>
        </div>
      </form>
    <?php endif; ?>

  <?php else: ?>
    <!-- 2. Header Tabs Navbar -->
    <div class="tabs-nav">
      <button class="tab-btn active" onclick="switchTab(event, 'dashboardPanel')">Dashboard</button>
      <button class="tab-btn" onclick="switchTab(event, 'profilePanel')">My Profile</button>
      <button class="tab-btn" onclick="switchTab(event, 'docsPanel')">API Documentation</button>
      <button class="tab-btn" onclick="switchTab(event, 'pricingPanel')">Price List</button>
      <button class="tab-btn" onclick="switchTab(event, 'templatesPanel')">Templates</button>
      <button class="tab-btn" onclick="switchTab(event, 'contactPanel')">Contact Us</button>
      <button class="tab-btn" onclick="switchTab(event, 'chatbotPanel')">Chatbot Rules</button>
      <a href="bulk_media_broadcast.php" class="tab-btn" style="text-decoration:none; display:inline-block;">📢 Media Broadcast</a>
      <a href="chat_history.php" class="tab-btn" style="text-decoration:none; display:inline-block;">💬 Chat History</a>
    </div>

    <!-- Panel 1: Dashboard -->
    <div class="tab-panel active" id="dashboardPanel">
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

      <!-- Device slot selection if multiple allowed -->
      <div style="background: rgba(15,23,42,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 18px 24px; border-radius: 16px; margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        <div>
          <label style="font-weight: 700; color: #38bdf8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Manage Scanner Device</label>
          <select id="device_slot_selector" onchange="changeDeviceSlot(this.value)" style="padding: 10px 14px; border-radius: 8px; background: rgba(15,23,42,0.8); border: 1.5px solid rgba(255,255,255,0.08); color: #fff; font-size: 13.5px; font-family: inherit; width: 100%; max-width: 280px; min-width: 220px; cursor: pointer;">
            <?php for($i = 1; $i <= intval($client['allowed_scanners'] ?? 1); $i++): ?>
              <option value="<?= $i ?>">Device Slot #<?= $i ?> <?= $i === 1 ? '(Primary)' : '' ?></option>
            <?php endfor; ?>
          </select>
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
        </div>
      </div>
    </div>

    <!-- Panel 2: My Profile -->
    <div class="tab-panel" id="profilePanel">
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

    <!-- Panel 2: API Documentation -->
    <div class="tab-panel" id="docsPanel">
      <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 20px; border-radius:14px;">
        <h3 style="color:#fff; margin-bottom: 12px; font-size:16px;">WhatsApp API Endpoint Reference</h3>
        <p style="font-size: 13px; color:#94a3b8; margin-bottom: 18px; line-height: 1.5; font-family:'Inter',sans-serif;">
          Trigger messages programmatically by invoking standard HTTP POST requests to the gateway endpoint.
        </p>

        <div class="doc-section">
          <h4>HTTP Endpoint</h4>
          <p><code>POST <?= htmlspecialchars(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/api/whatsapp.php') ?></code></p>
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

    <!-- Panel 3: Price List & Dynamic Calculator -->
    <div class="tab-panel" id="pricingPanel">
      <div class="renew-panel">
        <h3 style="color:#fff; margin-bottom: 4px; font-size:17px;">🔒 Secure Subscription Renewals</h3>
        <p style="font-size:12.5px; color:#94a3b8; margin-bottom: 20px;">Renew your active package or purchase extra active WhatsApp scanner logins dynamically.</p>

        <div class="plans-grid">
          <div class="plan-info-card">
            <h4>Starter Plan</h4>
            <p>1 Device Scanner • Unlimited SMS</p>
            <p style="font-weight:700; color:#38bdf8; margin-top:4px;">From 799 Rs/mo</p>
          </div>
          <div class="plan-info-card" style="border-color: rgba(99, 102, 241, 0.2);">
            <h4>Business Plan</h4>
            <p>3 Device Scanners • Unlimited SMS</p>
            <p style="font-weight:700; color:#818cf8; margin-top:4px;">From 1299 Rs/mo</p>
          </div>
        </div>

        <div class="form-group">
          <label>1. Select Base Plan</label>
          <select id="calc_plan" onchange="calculateRenewalTotal()">
            <option value="starter" data-name="Starter Plan (1 Scanner Base)" data-base="799" data-opt-3="2097" data-opt-6="3594" data-opt-12="5988">Starter Plan — Unlimited SMS for 1 Scanner (799 Rs/mo)</option>
            <option value="business" data-name="Business Plan (3 Scanners Base)" data-base="1299" data-opt-3="3597" data-opt-6="6594" data-opt-12="11988">Business Plan — Unlimited SMS for 3 Scanners (1299 Rs/mo)</option>
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
            <option value="1">1 Extra Scanner (+300 Rs/mo)</option>
            <option value="2">2 Extra Scanners (+600 Rs/mo)</option>
            <option value="3">3 Extra Scanners (+900 Rs/mo)</option>
            <option value="4">4 Extra Scanners (+1200 Rs/mo)</option>
            <option value="5">5 Extra Scanners (+1500 Rs/mo)</option>
          </select>
          <span style="font-size:10.5px; color:#64748b; margin-top:2px;">Extra WhatsApp logins cost 300 Rs per scanner per month.</span>
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

    <!-- Panel 4: Message Templates -->
    <div class="tab-panel" id="templatesPanel">
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

    <!-- Panel 5: Contact Us -->
    <div class="tab-panel" id="contactPanel">
      <h3 style="color:#fff; margin-bottom: 6px; font-size:16px;">Contact Support</h3>
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
      <div class="section-tag" style="font-weight: 700; color: var(--lime); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">🤖 Chatbot Settings</div>
      <h3 style="color:#fff; margin-bottom: 6px; font-size:16px;">WhatsApp Chatbot Rules</h3>
      <p style="font-size:12px; color:#94a3b8; margin-bottom: 20px;">Manage automated keyword-based replies. The bot will automatically respond to messages matching these keywords.</p>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 24px; text-align: left;">
        
        <!-- Left: Status & Add Rule Form -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
          
          <!-- Chatbot Enable/Disable Toggle Card -->
          <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 20px; border-radius:12px;">
            <h4 style="color:#fff; margin-bottom: 4px; font-size:14px; font-weight: 700;">Chatbot Status</h4>
            <p style="font-size:11px; color:#94a3b8; margin-bottom: 14px;">Toggle chatbot auto-replies on/off globally.</p>
            
            <form method="POST" style="display: flex; align-items: center; justify-content: space-between; background: rgba(15,23,42,0.5); padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.03);">
              <input type="hidden" name="action" value="toggle_chatbot">
              <input type="hidden" name="enabled" value="<?= intval($client['chatbot_enabled'] ?? 1) ? '0' : '1' ?>">
              
              <div style="display: flex; flex-direction: column;">
                <span style="font-size:12px; font-weight: 700; color: #fff;">Chatbot Integration</span>
                <span style="font-size:11px; color: <?= intval($client['chatbot_enabled'] ?? 1) ? '#34d399' : '#f87171' ?>; font-weight: 600; margin-top: 2px;">
                  ● <?= intval($client['chatbot_enabled'] ?? 1) ? 'Active' : 'Inactive' ?>
                </span>
              </div>
              
              <button type="submit" class="btn-submit" style="padding: 6px 12px; font-size: 11px; background: <?= intval($client['chatbot_enabled'] ?? 1) ? 'rgba(248,113,113,0.1)' : 'var(--lime)' ?>; color: <?= intval($client['chatbot_enabled'] ?? 1) ? '#f87171' : 'var(--bg-main)' ?>; border: <?= intval($client['chatbot_enabled'] ?? 1) ? '1px solid rgba(248,113,113,0.3)' : 'none' ?>; border-radius: 6px; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                <?= intval($client['chatbot_enabled'] ?? 1) ? 'Disable' : 'Enable' ?>
              </button>
            </form>
          </div>

          <!-- Add New Chatbot Rule Card -->
          <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 20px; border-radius:12px;">
            <h4 style="color:#fff; margin-bottom: 4px; font-size:14px; font-weight: 700;">Create New Rule</h4>
            <form method="POST" style="display: flex; flex-direction: column; gap: 12px; margin-top: 14px;">
              <input type="hidden" name="action" value="add_chatbot_rule">
              
              <div class="form-group">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Trigger Keyword *</label>
                <input type="text" name="keyword" placeholder="e.g. support, price, hello" required style="width: 100%; padding: 10px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 6px; color: #fff;">
              </div>
              
              <div class="form-group">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Auto-Reply Message *</label>
                <textarea name="reply_text" rows="3" placeholder="Enter auto-reply text..." required style="width: 100%; padding: 10px; background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 6px; color: #fff; line-height: 1.4; resize: vertical;"></textarea>
              </div>
              
              <button type="submit" class="btn-submit" style="width:100%; font-weight:700;">Save Rule</button>
            </form>
          </div>

        </div>

        <!-- Right: Active Rules List -->
        <div style="background: rgba(15,23,42,0.3); border:1px solid rgba(255,255,255,0.05); padding: 20px; border-radius:12px; display: flex; flex-direction: column;">
          <h4 style="color:#fff; margin-bottom: 4px; font-size:14px; font-weight: 700;">Active Rules</h4>
          <p style="font-size:11px; color:#94a3b8; margin-bottom: 14px;">Trigger keywords and response actions.</p>
          
          <div style="flex-grow: 1; overflow-x: auto;">
            <?php
              $stmt_rules = $pdo->prepare("SELECT * FROM chatbot_rules WHERE client_id = ? ORDER BY id DESC");
              $stmt_rules->execute([$client['id']]);
              $rules_list = $stmt_rules->fetchAll();
            ?>
            <?php if (empty($rules_list)): ?>
              <div style="text-align: center; padding: 32px 12px; border: 1px dashed rgba(255,255,255,0.08); border-radius: 8px; background: rgba(15,23,42,0.2);">
                <div style="font-size: 20px; margin-bottom: 4px;">🤖</div>
                <div style="font-size: 12px; font-weight: 600; color: #cbd5e1;">No Rules Configured</div>
              </div>
            <?php else: ?>
              <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                  <tr style="border-bottom: 1px solid var(--border-color); background: rgba(15,23,42,0.25);">
                    <th style="padding: 10px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; width: 100px;">Keyword</th>
                    <th style="padding: 10px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Message</th>
                    <th style="padding: 10px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; text-align: right; width: 70px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rules_list as $rule): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                      <td style="padding: 10px; font-size: 12px; font-family: monospace; font-weight: 700; color: var(--lime); vertical-align: top;">
                        <?= htmlspecialchars($rule['keyword']) ?>
                      </td>
                      <td style="padding: 10px; font-size: 12px; color: #cbd5e1; line-height: 1.4; vertical-align: top; white-space: pre-wrap; word-break: break-all;">
                        <?= htmlspecialchars($rule['reply_text']) ?>
                      </td>
                      <td style="padding: 10px; text-align: right; vertical-align: top;">
                        <form method="POST" onsubmit="return confirm('Delete this rule?');" style="display: inline;">
                          <input type="hidden" name="action" value="delete_chatbot_rule">
                          <input type="hidden" name="rule_id" value="<?= $rule['id'] ?>">
                          <button type="submit" style="padding: 4px 8px; background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.2); color: #f87171; border-radius: 4px; font-size: 10.5px; font-weight: 700; cursor: pointer;">
                            Delete
                          </button>
                        </form>
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

    <a href="api_link.php?action=logout" class="btn-logout">Logout Developer Session</a>
  <?php endif; ?>
</div>

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
  const tablinks = document.getElementsByClassName("tab-btn");
  for (let i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(panelId).className += " active";
  evt.currentTarget.className += " active";
}

// Copy key to clipboard by slot number
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
  
  // Calculate add-on cost (300 Rs per month per addon)
  const addonCost = addons * 300 * months;
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
  
  const addonCost = addons * 300 * months;
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
        container.style.borderColor = '#fbbf24';
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
<?php endif; ?>

</body>
</html>
