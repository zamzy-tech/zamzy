<?php
require_once __DIR__ . '/auth.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$success_msg = '';
$error_msg = '';
$active_tab = $_GET['tab'] ?? 'company';

// Fetch current company settings
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

// Detect environment for smart defaults and recommendations
$detected_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_online = ($detected_host !== 'localhost' && $detected_host !== '127.0.0.1');
$recommended_gateway_url = 'http://localhost:3000/send';
if ($is_online) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $recommended_gateway_url = $protocol . '://' . $detected_host . '/whatsapp/send';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_company') {
        $active_tab = 'company';
        $company_name = trim($_POST['company_name'] ?? '');
        $company_phone = trim($_POST['company_phone'] ?? '');
        $company_email = trim($_POST['company_email'] ?? '');
        $company_website = trim($_POST['company_website'] ?? '');
        $company_tagline = trim($_POST['company_tagline'] ?? '');
        $company_notes_default = trim($_POST['company_notes_default'] ?? '');
        $razorpay_key_id = trim($_POST['razorpay_key_id'] ?? '');
        $razorpay_key_secret = trim($_POST['razorpay_key_secret'] ?? '');

        if (!$company_name || !$company_phone || !$company_email) {
            $error_msg = 'Please fill in all required company fields.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE settings SET 
                    company_name = ?, company_phone = ?, company_email = ?, 
                    company_website = ?, company_tagline = ?, company_notes_default = ?,
                    razorpay_key_id = ?, razorpay_key_secret = ?
                    WHERE id = ?");
                $stmt->execute([
                    $company_name, $company_phone, $company_email,
                    $company_website, $company_tagline, $company_notes_default,
                    $razorpay_key_id, $razorpay_key_secret,
                    $settings['id']
                ]);
                $success_msg = 'Company profile updated successfully!';
                
                // Refresh local settings array
                $settings['company_name'] = $company_name;
                $settings['company_phone'] = $company_phone;
                $settings['company_email'] = $company_email;
                $settings['company_website'] = $company_website;
                $settings['company_tagline'] = $company_tagline;
                $settings['company_notes_default'] = $company_notes_default;
                $settings['razorpay_key_id'] = $razorpay_key_id;
                $settings['razorpay_key_secret'] = $razorpay_key_secret;
            } catch (PDOException $e) {
                $error_msg = 'Failed to update company profile: ' . $e->getMessage();
            }
        }
    } 
    
    elseif ($action === 'add_smtp') {
        $active_tab = 'smtp';
        $display_name  = trim($_POST['display_name'] ?? '');
        $smtp_host     = trim($_POST['smtp_host'] ?? '');
        $smtp_port     = intval($_POST['smtp_port'] ?? 587);
        $smtp_username = trim($_POST['smtp_username'] ?? '');
        $smtp_password = $_POST['smtp_password'] ?? '';
        $smtp_secure   = $_POST['smtp_secure'] ?? 'ssl';
        $is_default    = isset($_POST['is_default']) ? 1 : 0;

        if (!$display_name || !$smtp_host || !$smtp_username || !$smtp_password) {
            $error_msg = 'Please fill in all required SMTP fields.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // If this is set as default, reset previous defaults
                if ($is_default === 1) {
                    $pdo->exec("UPDATE smtp_accounts SET is_default = 0");
                }
                
                // Check if this is the first account, set default if so
                $account_count = $pdo->query("SELECT COUNT(*) FROM smtp_accounts")->fetchColumn();
                if ($account_count == 0) {
                    $is_default = 1;
                }

                $stmt = $pdo->prepare("INSERT INTO smtp_accounts (
                    display_name, smtp_host, smtp_port, smtp_username, smtp_password, smtp_secure, is_default
                ) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$display_name, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_secure, $is_default]);
                
                $pdo->commit();
                $success_msg = 'New SMTP sender profile added successfully!';
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error_msg = 'Failed to save SMTP profile: ' . $e->getMessage();
            }
        }
    } 
    
    elseif ($action === 'make_default_smtp') {
        $active_tab = 'smtp';
        $account_id = intval($_POST['account_id'] ?? 0);
        
        if ($account_id) {
            try {
                $pdo->beginTransaction();
                $pdo->exec("UPDATE smtp_accounts SET is_default = 0");
                
                $stmt = $pdo->prepare("UPDATE smtp_accounts SET is_default = 1 WHERE id = ?");
                $stmt->execute([$account_id]);
                
                $pdo->commit();
                $success_msg = 'Default SMTP sender account updated successfully!';
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error_msg = 'Failed to update default SMTP: ' . $e->getMessage();
            }
        }
    } 
    
    elseif ($action === 'delete_smtp') {
        $active_tab = 'smtp';
        $account_id = intval($_POST['account_id'] ?? 0);
        
        if ($account_id) {
            try {
                // Count available profiles
                $count = $pdo->query("SELECT COUNT(*) FROM smtp_accounts")->fetchColumn();
                
                if ($count <= 1) {
                    $error_msg = 'Deletion denied: You must retain at least one configured SMTP sender account.';
                } else {
                    $pdo->beginTransaction();
                    
                    // Check if we are deleting the default account
                    $stmt = $pdo->prepare("SELECT is_default FROM smtp_accounts WHERE id = ?");
                    $stmt->execute([$account_id]);
                    $is_default = $stmt->fetchColumn();

                    $stmt = $pdo->prepare("DELETE FROM smtp_accounts WHERE id = ?");
                    $stmt->execute([$account_id]);
                    
                    // If deleted was default, make another account the default
                    if ($is_default) {
                        $next_id = $pdo->query("SELECT id FROM smtp_accounts LIMIT 1")->fetchColumn();
                        $stmt = $pdo->prepare("UPDATE smtp_accounts SET is_default = 1 WHERE id = ?");
                        $stmt->execute([$next_id]);
                    }

                    $pdo->commit();
                    $success_msg = 'SMTP sender profile deleted successfully.';
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error_msg = 'Failed to delete SMTP profile: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($action === 'test_smtp') {
        $active_tab = 'smtp';
        $account_id = intval($_POST['test_account_id'] ?? 0);
        $test_email = trim($_POST['test_email'] ?? '');
        
        if (!$account_id || empty($test_email)) {
            $error_msg = 'Please provide a valid test recipient email address and select an SMTP profile.';
        } else {
            try {
                // Fetch SMTP details
                $stmt = $pdo->prepare("SELECT * FROM smtp_accounts WHERE id = ?");
                $stmt->execute([$account_id]);
                $test_account = $stmt->fetch();
                
                if (!$test_account) {
                    $error_msg = 'SMTP profile not found.';
                } else {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = $test_account['smtp_host'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $test_account['smtp_username'];
                        $mail->Password   = $test_account['smtp_password'];
                        $mail->SMTPSecure = ($test_account['smtp_secure'] === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : 
                                            (($test_account['smtp_secure'] === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : '');
                        $mail->Port       = $test_account['smtp_port'];
                        $mail->CharSet    = 'UTF-8';

                        $mail->setFrom($test_account['smtp_username'], $settings['company_name'] . ' System');
                        $mail->addAddress($test_email);

                        $mail->isHTML(false);
                        $mail->Subject = 'SMTP Sender Connection Test';
                        $mail->Body    = "Success! The SMTP profile [" . $test_account['display_name'] . " - " . $test_account['smtp_username'] . "] works perfectly!";

                        $mail->send();
                        $success_msg = "✅ SMTP test email dispatched successfully using profile [" . htmlspecialchars($test_account['display_name']) . "] to: " . htmlspecialchars($test_email);
                    } catch (Exception $e) {
                        $error_msg = "❌ SMTP Handshake failed: " . $mail->ErrorInfo;
                    }
                }
            } catch (PDOException $e) {
                $error_msg = 'Database error: ' . $e->getMessage();
            }
        }
    }
    elseif ($action === 'save_whatsapp') {
        $active_tab = 'whatsapp';
        $gateway_type = trim($_POST['whatsapp_gateway_type'] ?? 'browser');
        $gateway_url = trim($_POST['whatsapp_gateway_url'] ?? '');
        $gateway_token = trim($_POST['whatsapp_gateway_token'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE settings SET 
                whatsapp_gateway_type = ?, 
                whatsapp_gateway_url = ?, 
                whatsapp_gateway_token = ?
                WHERE id = ?");
            $stmt->execute([$gateway_type, $gateway_url, $gateway_token, $settings['id']]);
            $success_msg = 'WhatsApp settings updated successfully!';
            
            // Refresh local settings array
            $settings['whatsapp_gateway_type'] = $gateway_type;
            $settings['whatsapp_gateway_url'] = $gateway_url;
            $settings['whatsapp_gateway_token'] = $gateway_token;
        } catch (PDOException $e) {
            $error_msg = 'Failed to update WhatsApp settings: ' . $e->getMessage();
        }
    } 
    
    elseif ($action === 'connect_whatsapp') {
        $active_tab = 'whatsapp';
        $linked_number = trim($_POST['linked_number'] ?? '+91 98765 43210');
        try {
            $stmt = $pdo->prepare("UPDATE settings SET 
                whatsapp_is_connected = 1, 
                whatsapp_linked_number = ?
                WHERE id = ?");
            $stmt->execute([$linked_number, $settings['id']]);
            $success_msg = '🎉 WhatsApp account linked successfully!';
            
            // Refresh local settings array
            $settings['whatsapp_is_connected'] = 1;
            $settings['whatsapp_linked_number'] = $linked_number;
        } catch (PDOException $e) {
            $error_msg = 'Failed to link account: ' . $e->getMessage();
        }
    } 
    
    elseif ($action === 'disconnect_whatsapp') {
        $active_tab = 'whatsapp';
        
        // Notify Node server if online to sign out & clear cache
        $gw_url = !empty($settings['whatsapp_gateway_url']) ? $settings['whatsapp_gateway_url'] : $recommended_gateway_url;
        $disconnect_url = str_replace('/send', '/disconnect', $gw_url);

        
        try {
            $ch = curl_init($disconnect_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            // Ignore if Node.js server is offline
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE settings SET 
                whatsapp_is_connected = 0, 
                whatsapp_linked_number = NULL
                WHERE id = ?");
            $stmt->execute([$settings['id']]);
            $success_msg = 'WhatsApp account unlinked successfully.';
            
            // Refresh local settings array
            $settings['whatsapp_is_connected'] = 0;
            $settings['whatsapp_linked_number'] = null;
        } catch (PDOException $e) {
            $error_msg = 'Failed to unlink account: ' . $e->getMessage();
        }
    } 
    
    elseif ($action === 'save_templates') {
        $active_tab = 'templates';
        $template_invoice_create = trim($_POST['template_invoice_create'] ?? '');
        $template_payment_receive = trim($_POST['template_payment_receive'] ?? '');
        $template_estimate = trim($_POST['template_estimate'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE settings SET 
                template_invoice_create = ?, 
                template_payment_receive = ?, 
                template_estimate = ?
                WHERE id = ?");
            $stmt->execute([
                $template_invoice_create,
                $template_payment_receive,
                $template_estimate,
                $settings['id']
            ]);
            $success_msg = 'WhatsApp message templates updated successfully!';
            
            // Refresh local settings array
            $settings['template_invoice_create'] = $template_invoice_create;
            $settings['template_payment_receive'] = $template_payment_receive;
            $settings['template_estimate'] = $template_estimate;
        } catch (PDOException $e) {
            $error_msg = 'Failed to update templates: ' . $e->getMessage();
        }
    } 
    
    elseif ($action === 'save_razorpay') {
        $active_tab = 'razorpay';
        $razorpay_key_id = trim($_POST['razorpay_key_id'] ?? '');
        $razorpay_key_secret = trim($_POST['razorpay_key_secret'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE settings SET 
                razorpay_key_id = ?, 
                razorpay_key_secret = ?
                WHERE id = ?");
            $stmt->execute([
                $razorpay_key_id,
                $razorpay_key_secret,
                $settings['id']
            ]);
            $success_msg = 'Razorpay Integration API details updated successfully!';
            
            // Refresh local settings array
            $settings['razorpay_key_id'] = $razorpay_key_id;
            $settings['razorpay_key_secret'] = $razorpay_key_secret;
        } catch (PDOException $e) {
            $error_msg = 'Failed to update Razorpay details: ' . $e->getMessage();
        }
    } 
    
    elseif ($action === 'test_whatsapp_gateway') {
        $active_tab = 'whatsapp';
        $test_number = trim($_POST['test_number'] ?? '');
        $test_message = trim($_POST['test_message'] ?? '');
        
        if (empty($test_number) || empty($test_message)) {
            $error_msg = 'Please fill in all test fields.';
        } else {
            $gateway_type = $settings['whatsapp_gateway_type'] ?? 'browser';
            $gateway_url = $settings['whatsapp_gateway_url'] ?? '';
            $gateway_token = $settings['whatsapp_gateway_token'] ?? '';
            
            if ($gateway_type === 'browser') {
                $error_msg = 'Cannot send fast test message while Manual Web Redirect is active. Please switch gateway type to Background API Gateway first.';
            } elseif (empty($gateway_url)) {
                $error_msg = 'Background API Gateway URL is missing.';
            } else {
                $clean_phone = preg_replace('/[^0-9]/', '', $test_number);
                
                $ch = curl_init($gateway_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                
                $payload = json_encode([
                    'to' => $clean_phone,
                    'phone' => $clean_phone,
                    'number' => $clean_phone,
                    'body' => $test_message,
                    'message' => $test_message,
                    'token' => $gateway_token,
                    'apikey' => $gateway_token
                ]);
                
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_error($ch);
                curl_close($ch);
                
                if ($err) {
                    $error_msg = "Background dispatch failed: " . $err;
                } elseif ($http_code >= 400) {
                    $error_msg = "Background gateway returned HTTP Status " . $http_code . ". Response: " . htmlspecialchars(substr($response, 0, 150));
                } else {
                    $success_msg = "🚀 Fast Test Message sent successfully via Background Gateway! Response: " . htmlspecialchars(substr($response, 0, 100));
                }
            }
        }
    }

    elseif ($action === 'change_password') {
        $active_tab = 'security';
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (!$current_pass || !$new_pass || !$confirm_pass) {
            $error_msg = 'Please complete all password fields.';
        } elseif ($new_pass !== $confirm_pass) {
            $error_msg = 'The new passwords do not match.';
        } elseif (strlen($new_pass) < 6) {
            $error_msg = 'New password must be at least 6 characters long.';
        } else {
            try {
                // Verify current password against stored hash
                $stmt = $pdo->query("SELECT admin_password FROM settings LIMIT 1");
                $stored_hash = $stmt->fetchColumn();

                if (password_verify($current_pass, $stored_hash)) {
                    $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
                    $update_stmt = $pdo->prepare("UPDATE settings SET admin_password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $settings['id']]);
                    $success_msg = 'Administrative security password updated successfully!';
                } else {
                    $error_msg = 'Current administrative password is incorrect.';
                }
            } catch (PDOException $e) {
                $error_msg = 'Failed to update administrative password: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($action === 'add_chatbot_rule' || $action === 'edit_chatbot_rule') {
        $active_tab = 'chatbot';
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
            $error_msg = 'Please fill in all chatbot rule fields.';
        } else {
            try {
                if ($action === 'edit_chatbot_rule' && $rule_id > 0) {
                    // Check if duplicate keyword exists for ANOTHER rule
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM chatbot_rules WHERE client_id = 0 AND LOWER(keyword) = ? AND id != ?");
                    $chk->execute([strtolower($keyword), $rule_id]);
                    if ($chk->fetchColumn() > 0) {
                        $error_msg = 'A rule for this keyword already exists.';
                    } else {
                        $upd = $pdo->prepare("UPDATE chatbot_rules SET keyword = ?, reply_text = ?, image_url = ?, buttons_json = ? WHERE id = ? AND client_id = 0");
                        $upd->execute([$keyword, $reply_text, $image_url, $buttons_json, $rule_id]);
                        $success_msg = 'Admin chatbot rule updated successfully!';
                    }
                } else {
                    // Add new rule
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM chatbot_rules WHERE client_id = 0 AND LOWER(keyword) = ?");
                    $chk->execute([strtolower($keyword)]);
                    if ($chk->fetchColumn() > 0) {
                        $error_msg = 'A rule for this keyword already exists.';
                    } else {
                        $ins = $pdo->prepare("INSERT INTO chatbot_rules (client_id, keyword, reply_text, image_url, buttons_json) VALUES (0, ?, ?, ?, ?)");
                        $ins->execute([$keyword, $reply_text, $image_url, $buttons_json]);
                        $success_msg = 'Admin chatbot rule added successfully!';
                    }
                }
            } catch (PDOException $e) {
                $error_msg = 'Failed to save chatbot rule: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($action === 'delete_chatbot_rule') {
        $active_tab = 'chatbot';
        $rule_id = intval($_POST['rule_id'] ?? 0);
        try {
            $del = $pdo->prepare("DELETE FROM chatbot_rules WHERE id = ? AND client_id = 0");
            $del->execute([$rule_id]);
            $success_msg = 'Admin chatbot rule deleted successfully!';
        } catch (PDOException $e) {
            $error_msg = 'Failed to delete rule: ' . $e->getMessage();
        }
    }
    
    elseif ($action === 'toggle_chatbot') {
        $active_tab = 'chatbot';
        $enabled = intval($_POST['enabled'] ?? 0);
        try {
            $upd = $pdo->prepare("UPDATE settings SET chatbot_enabled = ? WHERE id = ?");
            $upd->execute([$enabled, $settings['id']]);
            $settings['chatbot_enabled'] = $enabled;
            $success_msg = $enabled ? 'Admin chatbot enabled successfully!' : 'Admin chatbot disabled successfully!';
        } catch (PDOException $e) {
            $error_msg = 'Failed to toggle chatbot state: ' . $e->getMessage();
        }
    }

    elseif ($action === 'add_staff' || $action === 'edit_staff') {
        $active_tab = 'staff';
        $staff_id = intval($_POST['staff_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($name)) {
            $error_msg = 'Please fill in both Username and Display Name.';
        } else {
            try {
                if ($action === 'edit_staff' && $staff_id > 0) {
                    // Check duplicate username for another staff member
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM staff_users WHERE username = ? AND id != ?");
                    $chk->execute([$username, $staff_id]);
                    if ($chk->fetchColumn() > 0) {
                        $error_msg = "Username '{$username}' is already in use by another staff member.";
                    } else {
                        if (!empty($password)) {
                            $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                            $upd = $pdo->prepare("UPDATE staff_users SET username = ?, name = ?, password = ? WHERE id = ?");
                            $upd->execute([$username, $name, $pass_hash, $staff_id]);
                        } else {
                            $upd = $pdo->prepare("UPDATE staff_users SET username = ?, name = ? WHERE id = ?");
                            $upd->execute([$username, $name, $staff_id]);
                        }
                        $success_msg = 'Staff user updated successfully!';
                    }
                } else {
                    // Add new staff member
                    if (empty($password)) {
                        $error_msg = 'Password is required for new staff members.';
                    } else {
                        $chk = $pdo->prepare("SELECT COUNT(*) FROM staff_users WHERE username = ?");
                        $chk->execute([$username]);
                        if ($chk->fetchColumn() > 0) {
                            $error_msg = "Username '{$username}' is already in use.";
                        } else {
                            $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                            $ins = $pdo->prepare("INSERT INTO staff_users (username, name, password, status) VALUES (?, ?, ?, 'active')");
                            $ins->execute([$username, $name, $pass_hash]);
                            $success_msg = 'Staff user created successfully!';
                        }
                    }
                }
            } catch (PDOException $e) {
                $error_msg = 'Database error: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($action === 'delete_staff') {
        $active_tab = 'staff';
        $staff_id = intval($_POST['staff_id'] ?? 0);
        try {
            $del = $pdo->prepare("DELETE FROM staff_users WHERE id = ?");
            $del->execute([$staff_id]);
            $success_msg = 'Staff user deleted successfully!';
        } catch (PDOException $e) {
            $error_msg = 'Failed to delete staff user: ' . $e->getMessage();
        }
    }
    
    elseif ($action === 'toggle_staff_status') {
        $active_tab = 'staff';
        $staff_id = intval($_POST['staff_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');
        $new_status = ($status === 'active') ? 'inactive' : 'active';
        try {
            $upd = $pdo->prepare("UPDATE staff_users SET status = ? WHERE id = ?");
            $upd->execute([$new_status, $staff_id]);
            $success_msg = "Staff status updated to " . ucfirst($new_status) . " successfully!";
        } catch (PDOException $e) {
            $error_msg = 'Failed to toggle staff status: ' . $e->getMessage();
        }
    }
}

// Fetch all SMTP accounts for listing
try {
    $smtp_stmt = $pdo->query("SELECT * FROM smtp_accounts ORDER BY id ASC");
    $smtp_accounts = $smtp_stmt->fetchAll();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

// Fetch admin chatbot rules
try {
    $rules_stmt = $pdo->query("SELECT * FROM chatbot_rules WHERE client_id = 0 ORDER BY id DESC");
    $chatbot_rules = $rules_stmt->fetchAll();
} catch (PDOException $e) {
    $chatbot_rules = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings – The Expert Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin_style.css">
<style>
  /* Settings Grid Layout */
  .settings-layout {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 32px;
  }
  @media(max-width: 768px) {
    .settings-layout {
      grid-template-columns: 1fr;
    }
  }

  /* Sidebar Navigation */
  .settings-sidebar {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .tab-btn {
    background: none;
    border: none;
    padding: 12px 18px;
    text-align: left;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-secondary);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .tab-btn:hover {
    background: var(--border-color-soft);
    color: var(--text-primary);
  }
  .tab-btn.active {
    background: var(--lime);
    color: var(--bg-main);
    font-weight: 700;
    box-shadow: 0 4px 12px var(--lime-glow);
  }

  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }
  .grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }
  @media(max-width: 640px) {
    .grid-2, .grid-3 {
      grid-template-columns: 1fr;
    }
  }

  .btn-save {
    background-color: var(--lime);
    color: var(--bg-main);
    border: none;
    border-radius: 8px;
    padding: 12px 28px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s ease;
    display: inline-block;
  }
  .btn-save:hover {
    background-color: var(--lime-deep);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px var(--lime-glow);
  }

  /* SMTP Directory */
  .table-responsive {
    overflow-x: auto;
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-surface);
  }
  th {
    background: var(--bg-card) !important;
    color: var(--text-muted) !important;
    border-bottom: 1.5px solid var(--border-color) !important;
  }
  .badge {
    background: rgba(212, 255, 61, 0.1);
    color: var(--lime);
    border: 1px solid rgba(212, 255, 61, 0.25);
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    text-transform: uppercase;
  }
  .btn-action-small {
    background: var(--bg-elev);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 11px;
    cursor: pointer;
    font-weight: 600;
    color: var(--text-secondary);
    transition: all 0.2s;
  }
  .btn-action-small:hover {
    background: var(--lime);
    color: var(--bg-main);
    border-color: var(--lime);
  }
  .btn-action-small.default {
    background: rgba(212, 255, 61, 0.08);
    border-color: var(--lime);
    color: var(--lime);
  }
  .btn-action-small.default:hover {
    background: var(--lime);
    color: var(--bg-main);
  }
  .btn-action-small.delete {
    border-color: rgba(239, 68, 68, 0.25);
    color: #f87171;
  }
  .btn-action-small.delete:hover {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
  }

  /* SMTP Test block */
  .smtp-test-block {
    background: var(--bg-card);
    border: 1.5px solid var(--border-color);
    border-radius: 10px;
    padding: 20px;
    margin-top: 16px;
  }
  .smtp-test-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--lime);
    text-transform: uppercase;
    margin-bottom: 8px;
  }
  .smtp-test-desc {
    font-size: 12.5px;
    color: var(--text-secondary);
    margin-bottom: 14px;
  }
  
  /* Scanner animations */
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  @keyframes scanLine {
    0% { top: 0%; }
    50% { top: 100%; }
    100% { top: 0%; }
  }
</style>
</head>
<body>

<header>
  <div class="logo-block">
    <div class="logo">⚡ The Expert Hub</div>
    <div class="tagline">Admin Invoice Control</div>
  </div>
  <nav>
    <a href="index.php">Generate Invoice</a>
    <a href="history.php">Billing History</a>
    <a href="clients.php">Clients</a>
    <a href="accounting.php">Accounting</a>
    <a href="pricing.php">Pricing</a>
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
      <a href="settings.php" class="active">Settings</a>
      <a href="api_management.php">API Management</a>
    <?php endif; ?>
    <a href="logout.php" class="btn-logout">Logout</a>
  </nav>
</header>

<div class="container">
  
  <div class="admin-header">
    <h1>Administrative Settings</h1>
  </div>

  <?php if (!empty($success_msg)): ?>
    <div class="alert success"><?= htmlspecialchars($success_msg) ?></div>
  <?php elseif (!empty($error_msg)): ?>
    <div class="alert error"><?= htmlspecialchars($error_msg) ?></div>
  <?php endif; ?>

  <div class="settings-layout">
    
    <!-- Sidebar Navigation -->
    <div class="settings-sidebar">
      <button class="tab-btn <?= $active_tab === 'company' ? 'active' : '' ?>" onclick="switchTab('company', this)">
        🏢 Company Profile
      </button>
      <button class="tab-btn <?= $active_tab === 'smtp' ? 'active' : '' ?>" onclick="switchTab('smtp', this)">
        ✉️ Outgoing SMTP Mail
      </button>
      <button class="tab-btn <?= $active_tab === 'whatsapp' ? 'active' : '' ?>" onclick="switchTab('whatsapp', this)">
        💬 WhatsApp Scanner
      </button>
      <button class="tab-btn <?= $active_tab === 'security' ? 'active' : '' ?>" onclick="switchTab('security', this)">
        🔒 Portal Security
      </button>
      <button class="tab-btn <?= $active_tab === 'templates' ? 'active' : '' ?>" onclick="switchTab('templates', this)">
        📝 Message Templates
      </button>
      <button class="tab-btn <?= $active_tab === 'razorpay' ? 'active' : '' ?>" onclick="switchTab('razorpay', this)">
        💳 Razorpay Settings
      </button>
      <button class="tab-btn <?= $active_tab === 'chatbot' ? 'active' : '' ?>" onclick="switchTab('chatbot', this)">
        🤖 Admin Chatbot
      </button>
      <button class="tab-btn <?= $active_tab === 'staff' ? 'active' : '' ?>" onclick="switchTab('staff', this)">
        👥 Staff Accounts
      </button>
    </div>

    <!-- Active Form Container -->
    <div class="settings-content">
      
      <!-- Tab 1: Company Profile -->
      <div id="companyTab" style="display: <?= $active_tab === 'company' ? 'block' : 'none' ?>;">
        <div class="card">
          <div class="card-title">🏢 Company Profile & Branding</div>
          <form method="POST" action="settings.php?tab=company">
            <input type="hidden" name="action" value="save_company">
            
            <div class="grid-2">
              <div class="form-group">
                <label>Company Name *</label>
                <input type="text" name="company_name" value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label>Contact Phone Number *</label>
                <input type="text" name="company_phone" value="<?= htmlspecialchars($settings['company_phone'] ?? '') ?>" required>
              </div>
            </div>

            <div class="grid-2">
              <div class="form-group">
                <label>Billing/Support Email *</label>
                <input type="email" name="company_email" value="<?= htmlspecialchars($settings['company_email'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label>Website URL</label>
                <input type="text" name="company_website" value="<?= htmlspecialchars($settings['company_website'] ?? '') ?>">
              </div>
            </div>

            <div class="form-group">
              <label>Tagline (invoice header text)</label>
              <input type="text" name="company_tagline" value="<?= htmlspecialchars($settings['company_tagline'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Default Additional Notes (Payment terms, defaults, etc.)</label>
              <textarea name="company_notes_default" rows="4"><?= htmlspecialchars($settings['company_notes_default'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-top: 24px;">
              <h3 style="color: #cbd5e1; font-size: 15px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 6px;">Razorpay Gateway Settings (For Client Purchases)</h3>
            </div>
            
            <div class="row">
              <div class="col">
                <div class="form-group">
                  <label>Razorpay Key ID</label>
                  <input type="text" name="razorpay_key_id" value="<?= htmlspecialchars($settings['razorpay_key_id'] ?? 'rzp_test_YOUR_KEY_HERE') ?>">
                </div>
              </div>
              <div class="col">
                <div class="form-group">
                  <label>Razorpay Key Secret</label>
                  <input type="password" name="razorpay_key_secret" value="<?= htmlspecialchars($settings['razorpay_key_secret'] ?? '') ?>" placeholder="Leave empty to keep unchanged">
                </div>
              </div>
            </div>

            <button type="submit" class="btn-save" style="margin-top: 16px;">Save Company Settings</button>
          </form>
        </div>
      </div>

      <!-- Tab 2: SMTP Settings -->
      <div id="smtpTab" style="display: <?= $active_tab === 'smtp' ? 'block' : 'none' ?>;">
        
        <!-- 1. SMTP Account Listing -->
        <div class="card">
          <div class="card-title">✉️ SMTP Sender Accounts</div>
          
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Display Name</th>
                  <th>Server</th>
                  <th>Username / From Email</th>
                  <th>Secure</th>
                  <th>Default</th>
                  <th style="text-align:right">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($smtp_accounts as $account): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($account['display_name']) ?></strong></td>
                    <td><?= htmlspecialchars($account['smtp_host']) ?>:<?= $account['smtp_port'] ?></td>
                    <td><?= htmlspecialchars($account['smtp_username']) ?></td>
                    <td style="text-transform: uppercase"><?= htmlspecialchars($account['smtp_secure']) ?></td>
                    <td>
                      <?php if ($account['is_default']): ?>
                        <span class="badge">Default</span>
                      <?php else: ?>
                        &mdash;
                      <?php endif; ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap">
                      <?php if (!$account['is_default']): ?>
                        <form method="POST" action="settings.php?tab=smtp" style="display:inline-block">
                          <input type="hidden" name="action" value="make_default_smtp">
                          <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
                          <button type="submit" class="btn-action-small default">Make Default</button>
                        </form>
                      <?php endif; ?>
                      
                      <form method="POST" action="settings.php?tab=smtp" style="display:inline-block" onsubmit="return confirm('Delete this SMTP profile? It cannot be undone.')">
                        <input type="hidden" name="action" value="delete_smtp">
                        <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
                        <button type="submit" class="btn-action-small delete" <?= count($smtp_accounts) <= 1 ? 'disabled style="opacity:0.5; cursor:not-allowed"' : '' ?>>Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Connection Verification -->
          <div class="smtp-test-block">
            <div class="smtp-test-title">🧪 Verify Selected SMTP Connection</div>
            <div class="smtp-test-desc">Send a verification email to test the credentials of any account in your list.</div>
            <form method="POST" action="settings.php?tab=smtp" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
              <input type="hidden" name="action" value="test_smtp">
              
              <select name="test_account_id" style="flex:1; min-width:180px;" required>
                <option value="" disabled selected>Select profile to test...</option>
                <?php foreach ($smtp_accounts as $account): ?>
                  <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['display_name']) ?> (<?= htmlspecialchars($account['smtp_username']) ?>)</option>
                <?php endforeach; ?>
              </select>
              
              <input type="email" name="test_email" placeholder="recipient@example.com" style="flex:1.2; min-width:200px;" required>
              <button type="submit" class="btn-save" style="background:#2b6cb0; white-space:nowrap; padding:10px 20px;">Send Test Email</button>
            </form>
          </div>
        </div>

        <!-- 2. Add SMTP Profile -->
        <div class="card">
          <div class="card-title">➕ Add SMTP Sender Profile</div>
          <form method="POST" action="settings.php?tab=smtp">
            <input type="hidden" name="action" value="add_smtp">

            <div class="form-group">
              <label>Account Display Name *</label>
              <input type="text" name="display_name" required placeholder="e.g. Billing Team, Inquiry Desk">
            </div>

            <div class="grid-2">
              <div class="form-group">
                <label>SMTP Host Server *</label>
                <input type="text" name="smtp_host" required placeholder="e.g. mail.yourdomain.com">
              </div>
              <div class="form-group">
                <label>SMTP Port *</label>
                <input type="number" name="smtp_port" value="465" required placeholder="465">
              </div>
            </div>

            <div class="grid-2">
              <div class="form-group">
                <label>SMTP Username / Sender Email *</label>
                <input type="email" name="smtp_username" required placeholder="billing@yourdomain.com">
              </div>
              <div class="form-group">
                <label>SMTP Secure Protocol</label>
                <select name="smtp_secure">
                  <option value="ssl">SSL (Port 465 recommended)</option>
                  <option value="tls">TLS (Port 587 recommended)</option>
                  <option value="none">None (Not secure)</option>
                </select>
              </div>
            </div>

            <div class="form-group" style="margin-bottom:12px">
              <label>SMTP Password *</label>
              <input type="password" name="smtp_password" required placeholder="••••••••••••••">
            </div>

            <div class="form-group" style="flex-direction:row; align-items:center; gap:8px;">
              <input type="checkbox" name="is_default" id="is_default" style="width:auto; cursor:pointer">
              <label for="is_default" style="cursor:pointer; text-transform:none">Make this account my default sender</label>
            </div>

            <button type="submit" class="btn-save" style="margin-top:10px">Add SMTP Account</button>
          </form>
        </div>

      </div>

      <!-- Tab 4: WhatsApp Settings -->
      <div id="whatsappTab" style="display: <?= $active_tab === 'whatsapp' ? 'block' : 'none' ?>;">
        
        <?php if ($is_online && (empty($settings['whatsapp_gateway_url']) || strpos($settings['whatsapp_gateway_url'], 'localhost') !== false || strpos($settings['whatsapp_gateway_url'], '127.0.0.1') !== false)): ?>
          <!-- Live Environment Smart Suggestion Banner -->
          <div class="alert" style="background: rgba(43, 108, 176, 0.08); border: 1.5px solid rgba(43, 108, 176, 0.2); color: #2b6cb0; display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; border-radius: 12px; padding: 16px 20px;">
            <div>
              <h4 style="font-weight: 700; font-size: 14px; margin-bottom: 4px; color: #1a365d;">🌐 Live Environment Detected</h4>
              <p style="font-size: 12.5px; opacity: 0.9; color: #4a5568;">It looks like your billing portal is running live online. We recommend updating your WhatsApp Gateway URL to connect with your online microservice.</p>
            </div>
            <button type="button" onclick="applyRecommendedGateway('<?= htmlspecialchars($recommended_gateway_url) ?>')" class="btn-save" style="background: #2b6cb0; white-space: nowrap; font-size: 12px; padding: 8px 16px; border-radius: 6px; font-weight: 700;">
              Apply Live URL
            </button>
          </div>
        <?php endif; ?>
        
        <!-- 1. Device Status Card -->
        <div class="card">
          <div class="card-title">💬 WhatsApp Device Connection &amp; Scanner</div>
          
          <!-- Live Connected State container -->
          <div id="deviceConnectedContainer" style="display: none; background: #f0fff4; border: 1.5px solid #9ae6b4; border-radius: 12px; padding: 24px; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 16px;">
              <div style="width: 56px; height: 56px; background: #c6f6d5; color: #22543d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 4px 10px rgba(38, 161, 105, 0.2);">
                📲
              </div>
              <div>
                <h3 style="color: #22543d; font-size: 16px; font-weight: 700;">Device Connected &amp; Ready</h3>
                <p style="font-size: 13px; color: #276749; margin-top: 2px;">Linked Number: <strong id="deviceLinkedNumber"></strong></p>
                <p style="font-size: 11px; color: #4a5568; margin-top: 4px;">Status: <span class="badge" style="background: #25d366; color: #fff;">Active Link</span> • Signal: Strong</p>
              </div>
            </div>
            
            <form method="POST" action="settings.php?tab=whatsapp">
              <input type="hidden" name="action" value="disconnect_whatsapp">
              <button type="submit" class="btn-save" style="background: #fff5f5; border: 1.5px solid #feb2b2; color: #e53e3e; font-weight: 600;">
                🗑️ Disconnect Device
              </button>
            </form>
          </div>

          <!-- Live Disconnected / Scan QR State container -->
          <div id="deviceDisconnectedContainer" style="display: none;">
            <div style="background: #fff5f5; border: 1.5px solid #feb2b2; border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; color: #c53030;">
              <span style="font-size: 20px;">⚠️</span>
              <div style="font-size: 13px; font-weight: 600;">
                WhatsApp Device Disconnected. Please scan the QR Code below to link your device and enable automated "Send Fast" features.
              </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 280px 1fr; gap: 32px; align-items: center;">
              <!-- QR Code Container -->
              <div style="text-align: center;">
                <div id="qrCodeContainer" style="background: #edf2f7; border: 2px dashed #cbd5e0; border-radius: 12px; width: 250px; height: 250px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.3s ease;">
                  <!-- QR Mockup / Placeholder -->
                  <div id="qrPlaceholder" style="padding: 16px; display: flex; flex-direction: column; align-items: center; text-align: center;">
                    <div style="font-size: 40px; margin-bottom: 12px;">💬</div>
                    <button type="button" onclick="startQRGeneration()" class="btn-save" style="font-size: 12px; padding: 8px 16px; background: #128c7e;">Generate QR Code</button>
                  </div>
                  
                  <div id="qrLoader" style="display: none; text-align: center; padding: 16px;">
                    <div style="border: 4px solid #e2e8f0; border-top: 4px solid #128c7e; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 12px;"></div>
                    <p style="font-size: 12px; font-weight: 600; color: #4a5568;">Initializing Session...</p>
                  </div>
                  
                  <div id="qrActive" style="display: none; width: 100%; height: 100%; position: relative;">
                    <img id="qrImage" src="" style="width: 100%; height: 100%; object-fit: contain; padding: 10px; background: #fff;" alt="WhatsApp Scan QR">
                    <!-- Scanner overlay line -->
                    <div style="position: absolute; left: 0; right: 0; height: 3px; background: #25d366; box-shadow: 0 0 8px #25d366; animation: scanLine 2s linear infinite;"></div>
                  </div>

                  <div id="qrOffline" style="display: none; padding: 16px; text-align: center; color: #c53030;">
                    <div style="font-size: 32px; margin-bottom: 8px;">⚠️</div>
                    <p style="font-size: 12px; font-weight: 700; margin-bottom: 6px;">WhatsApp Service Offline</p>
                    <p style="font-size: 10px; color: #718096; line-height: 1.4;">Make sure your online node gateway or local service is running.</p>
                  </div>
                </div>
                
                <div id="qrTimer" style="display: none; font-size: 11px; color: #718096; margin-top: 8px; font-weight: 600;">
                  QR Code is active. Please scan now.
                </div>
              </div>
              
              <!-- Scan Guidelines -->
              <div>
                <h4 style="color: #1a365d; font-size: 15px; font-weight: 700; margin-bottom: 12px;">Linking Guidelines:</h4>
                <ol style="margin-left: 20px; font-size: 13px; color: #4a5568; line-height: 1.8;">
                  <li>Open <strong>WhatsApp</strong> on your mobile phone.</li>
                  <li>Tap <strong>Menu</strong> or <strong>Settings</strong> and select <strong>Linked Devices</strong>.</li>
                  <li>Tap on <strong>Link a Device</strong>.</li>
                  <li>Point your phone camera at the screen to scan the QR code.</li>
                </ol>
                
                <!-- Auto Coupling Form (Hidden) -->
                <form id="autoConnectForm" method="POST" action="settings.php?tab=whatsapp" style="display: none;">
                  <input type="hidden" name="action" value="connect_whatsapp">
                  <input type="hidden" name="linked_number" id="autoLinkedNumber" value="">
                </form>
              </div>
            </div>
          </div>
        </div>
        
        <!-- 2. "Send Fast" Background Gateway Configuration Form -->
        <div class="card">
          <div class="card-title">🚀 "Send Fast" Automated API Gateway</div>
          <form method="POST" action="settings.php?tab=whatsapp">
            <input type="hidden" name="action" value="save_whatsapp">
            
            <div class="form-group">
              <label>WhatsApp Dispatch Method</label>
              <select name="whatsapp_gateway_type" id="gatewayTypeSelect" onchange="toggleGatewayFields()" style="margin-top: 6px;">
                <option value="browser" <?= ($settings['whatsapp_gateway_type'] ?? 'browser') === 'browser' ? 'selected' : '' ?>>🌐 Manual Web Redirect (Opens WhatsApp Web Browser Tab)</option>
                <option value="gateway" <?= ($settings['whatsapp_gateway_type'] ?? 'browser') === 'gateway' ? 'selected' : '' ?>>🚀 Background API Gateway (Zero tabs, Instant Automated Send!)</option>
              </select>
            </div>
            
            <div id="gatewayConfigFields" style="display: <?= ($settings['whatsapp_gateway_type'] ?? 'browser') === 'gateway' ? 'block' : 'none' ?>; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 20px;">
              <div class="form-group">
                <label>Gateway API Endpoint URL *</label>
                <input type="text" name="whatsapp_gateway_url" value="<?= htmlspecialchars($settings['whatsapp_gateway_url'] ?? 'http://localhost:3000/send') ?>" autocomplete="off" placeholder="e.g. http://localhost:3000/send">
                <small style="font-size: 10px; color: #718096; margin-top: 2px;">
                  Endpoint URL for POSTing JSON payloads containing 'to' and 'body'.<br>
                  💡 <strong>Local WhatsApp Scanner:</strong> Set this to <code>http://localhost:3000/send</code> (no token required).
                </small>
              </div>
              <div class="form-group" style="margin-bottom: 0;">
                <label>Instance Token / API Key</label>
                <input type="password" name="whatsapp_gateway_token" value="<?= htmlspecialchars($settings['whatsapp_gateway_token'] ?? '') ?>" autocomplete="new-password" placeholder="e.g. your-api-token (leave empty for local scanner)">
                <small style="font-size: 10px; color: #718096; margin-top: 2px;">Access token or API key for authenticating to your remote/local gateway server.</small>
              </div>
            </div>
            
            <button type="submit" class="btn-save">Save WhatsApp Settings</button>
          </form>
        </div>
        
        <?php if (($settings['whatsapp_gateway_type'] ?? 'browser') === 'gateway'): ?>
          <!-- 3. "Send Fast" Testing Console -->
          <div class="card" style="border: 1.5px solid #cbd5e0; background: #edf2f7;">
            <div class="card-title" style="color: #2b6cb0; border-bottom-color: #cbd5e0;">🧪 "Send Fast" Background Testing Tool</div>
            <form method="POST" action="settings.php?tab=whatsapp">
              <input type="hidden" name="action" value="test_whatsapp_gateway">
              
              <div class="grid-2">
                <div class="form-group">
                  <label>Test Recipient Phone Number *</label>
                  <input type="text" name="test_number" required placeholder="e.g. +91 98765 43210">
                </div>
                <div class="form-group">
                  <label>Test Message Content *</label>
                  <input type="text" name="test_message" value="Hello! This is a test from mmmail background WhatsApp dispatch gateway. It works perfectly! 🚀" required>
                </div>
              </div>
              
              <button type="submit" class="btn-save" style="background: #2b6cb0;">Send Background Test Message</button>
            </form>
          </div>
        <?php endif; ?>
        
      </div>

      <!-- Tab 3: Security Settings -->
      <div id="securityTab" style="display: <?= $active_tab === 'security' ? 'block' : 'none' ?>;">
        <div class="card">
          <div class="card-title">🔒 Portal Security Password</div>
          <form method="POST" action="settings.php?tab=security">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
              <label>Current Administrator Password *</label>
              <input type="password" name="current_password" required placeholder="••••••••">
            </div>

            <div class="grid-2">
              <div class="form-group">
                <label>New Password *</label>
                <input type="password" name="new_password" required placeholder="••••••••">
              </div>
              <div class="form-group">
                <label>Confirm New Password *</label>
                <input type="password" name="confirm_password" required placeholder="••••••••">
              </div>
            </div>

            <button type="submit" class="btn-save">Update Password</button>
          </form>
        </div>
      </div>

      <!-- Tab 5: Message Templates -->
      <div id="templatesTab" style="display: <?= $active_tab === 'templates' ? 'block' : 'none' ?>;">
        <div class="card">
          <div class="card-title">📝 WhatsApp Message Templates</div>
          <p style="font-size: 13px; color: #718096; margin-bottom: 20px; line-height: 1.5;">
            Configure custom message templates sent to clients. You can use dynamic variables which will be auto-replaced before dispatching:<br>
            <code>{client_name}</code>, <code>{company_name}</code>, <code>{invoice_number}</code>, <code>{invoice_date}</code>, <code>{due_date}</code>, <code>{grand_total}</code>, <code>{advance_amount}</code>, <code>{pending_amount}</code>, <code>{web_link}</code>, <code>{amount_paid}</code>.
          </p>
          
          <form method="POST" action="settings.php?tab=templates">
            <input type="hidden" name="action" value="save_templates">

            <div class="form-group" style="margin-bottom: 20px;">
              <label style="font-weight: 700; color: #2d3748;">Invoice Creation Template</label>
              <textarea name="template_invoice_create" rows="5" placeholder="Dear {client_name}, your invoice #{invoice_number} for Rs {grand_total} is ready..."><?= htmlspecialchars($settings['template_invoice_create'] ?? '') ?></textarea>
              <span style="font-size: 11px; color: #718096; margin-top: 4px;">Sent to client when an invoice is created/sent.</span>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
              <label style="font-weight: 700; color: #2d3748;">Payment Received / Receipt Template</label>
              <textarea name="template_payment_receive" rows="5" placeholder="Dear {client_name}, we have received your payment of Rs {amount_paid} for Invoice #{invoice_number}..."><?= htmlspecialchars($settings['template_payment_receive'] ?? '') ?></textarea>
              <span style="font-size: 11px; color: #718096; margin-top: 4px;">Sent to client when a payment is logged against their invoice.</span>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
              <label style="font-weight: 700; color: #2d3748;">Estimate / Quote Template</label>
              <textarea name="template_estimate" rows="5" placeholder="Dear {client_name}, here is your estimate for Rs {grand_total}..."><?= htmlspecialchars($settings['template_estimate'] ?? '') ?></textarea>
              <span style="font-size: 11px; color: #718096; margin-top: 4px;">Sent when issuing estimate summaries.</span>
            </div>

            <button type="submit" class="btn-save">Save Message Templates</button>
          </form>
        </div>
      </div>

      <!-- Tab 6: Razorpay Integration -->
      <div id="razorpayTab" style="display: <?= $active_tab === 'razorpay' ? 'block' : 'none' ?>;">
        <div class="card">
          <div class="card-title">💳 Razorpay API Integration Settings</div>
          <p style="font-size: 13.5px; color: #718096; margin-bottom: 20px; line-height: 1.5;">
            Configure your merchant Razorpay API Credentials to receive automated client renewals.
          </p>
          
          <form method="POST" action="settings.php?tab=razorpay">
            <input type="hidden" name="action" value="save_razorpay">

            <div class="form-group" style="margin-bottom: 20px;">
              <label style="font-weight: 700; color: #2d3748;">Razorpay Key ID</label>
              <input type="text" name="razorpay_key_id" placeholder="rzp_live_..." value="<?= htmlspecialchars($settings['razorpay_key_id'] ?? '') ?>" required style="width:100%; max-width:500px; padding:10px; border:1px solid #cbd5e0; border-radius:6px; font-family:inherit;">
              <span style="font-size: 11px; color: #718096; margin-top: 4px;">Public API Key ID. Used on checkout buttons.</span>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
              <label style="font-weight: 700; color: #2d3748;">Razorpay Key Secret</label>
              <input type="text" name="razorpay_key_secret" placeholder="Enter Key Secret" value="<?= htmlspecialchars($settings['razorpay_key_secret'] ?? '') ?>" required style="width:100%; max-width:500px; padding:10px; border:1px solid #cbd5e0; border-radius:6px; font-family:inherit;">
              <span style="font-size: 11px; color: #718096; margin-top: 4px;">Private API Key Secret. Kept secure on server side.</span>
            </div>

            <button type="submit" class="btn-save">Save Razorpay Settings</button>
          </form>
        </div>
      </div>

      <!-- Tab 7: Admin Chatbot -->
      <div id="chatbotTab" style="display: <?= $active_tab === 'chatbot' ? 'block' : 'none' ?>;">
        <div class="card" style="padding: 24px;">
          <div class="card-title">🤖 Administrative WhatsApp Chatbot</div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">

            <!-- Status & Toggle Card -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px;">
              <h3 style="color: var(--text-primary); margin-bottom: 6px; font-size: 14px; font-weight: 700;">🟢 Chatbot Status</h3>
              <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px; line-height: 1.5;">Enable or disable automated keyword replies globally for the admin number.</p>

              <form method="POST" action="settings.php?tab=chatbot" style="display:flex; justify-content:space-between; align-items:center; background: var(--bg-elev); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 8px; gap: 12px;">
                <input type="hidden" name="action" value="toggle_chatbot">
                <input type="hidden" name="enabled" value="<?= intval($settings['chatbot_enabled'] ?? 1) ? '0' : '1' ?>">
                <div>
                  <div style="font-size: 13px; font-weight: 700; color: var(--text-primary);">Chatbot Integration</div>
                  <div style="font-size: 12px; color: <?= intval($settings['chatbot_enabled'] ?? 1) ? '#34d399' : '#f87171' ?>; font-weight: 600; margin-top: 2px;">
                    ● <?= intval($settings['chatbot_enabled'] ?? 1) ? 'Currently Active' : 'Currently Inactive' ?>
                  </div>
                </div>
                <button type="submit" style="flex-shrink:0; padding: 7px 14px; font-size: 12px; background: <?= intval($settings['chatbot_enabled'] ?? 1) ? 'rgba(248,113,113,0.15)' : 'rgba(52,211,153,0.15)' ?>; color: <?= intval($settings['chatbot_enabled'] ?? 1) ? '#f87171' : '#34d399' ?>; font-weight: 700; border-radius: 6px; cursor: pointer; border: 1px solid <?= intval($settings['chatbot_enabled'] ?? 1) ? 'rgba(248,113,113,0.3)' : 'rgba(52,211,153,0.3)' ?>; font-family: inherit; white-space: nowrap;">
                  <?= intval($settings['chatbot_enabled'] ?? 1) ? 'Disable Bot' : 'Enable Bot' ?>
                </button>
              </form>
            </div>

            <!-- Create New Rule Card -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px;" id="chatbotFormCard">
              <h3 style="color: var(--text-primary); margin-bottom: 6px; font-size: 14px; font-weight: 700;" id="chatbotFormTitle">➕ Create New Rule</h3>
              <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;" id="chatbotFormDesc">Define a new automatic response for a specific trigger word or phrase.</p>

              <form method="POST" action="settings.php?tab=chatbot" id="chatbotRuleForm" style="display: flex; flex-direction: column; gap: 12px;">
                <input type="hidden" name="action" id="chatbotFormAction" value="add_chatbot_rule">
                <input type="hidden" name="rule_id" id="chatbotRuleId" value="">

                <div class="form-group">
                  <label>Trigger Keyword *</label>
                  <input type="text" name="keyword" id="chatbotKeyword" placeholder="e.g. price" required>
                </div>

                <div class="form-group">
                  <label>Reply Message Text *</label>
                  <textarea name="reply_text" id="chatbotReplyText" rows="3" placeholder="Enter auto-reply text here..." required style="resize: vertical;"></textarea>
                </div>

                <div class="form-group">
                  <label>Image URL (Optional)</label>
                  <input type="url" name="image_url" id="chatbotImageUrl" placeholder="https://example.com/banner.jpg">
                </div>

                <div class="form-group">
                  <label>Chatbot Buttons (Optional, comma-separated)</label>
                  <input type="text" name="buttons_list" id="chatbotButtons" placeholder="e.g. Services, Website|https://tehub.in">
                  <span style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">Format: <code style="color:var(--lime); font-size:10.5px;">ButtonName</code> or <code style="color:var(--lime); font-size:10.5px;">ButtonName|LinkURL</code>. Max 3 buttons.</span>
                </div>

                <div style="display: flex; gap: 10px;" id="chatbotBtnContainer">
                  <button type="submit" class="btn-save" id="chatbotSubmitBtn" style="margin: 0; flex: 1;">Add Rule</button>
                  <button type="button" class="btn-action-small" id="chatbotCancelBtn" onclick="cancelChatbotEdit()" style="display: none; padding: 12px 20px; font-size: 14px; border-radius: 8px; font-weight: 700; height: 100%; color: var(--text-primary);">Cancel</button>
                </div>
              </form>
            </div>
          </div>

          <!-- Active Rules Table -->
          <div style="margin-top: 8px;">
            <h3 style="color: var(--text-primary); margin-bottom: 14px; font-size: 14px; font-weight: 700; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">📋 Active Chatbot Rules</h3>

            <?php if (empty($chatbot_rules)): ?>
              <div style="text-align: center; padding: 40px; background: var(--bg-card); border-radius: 12px; border: 1px dashed var(--border-color); color: var(--text-muted); font-size: 13.5px;">
                No chatbot rules defined yet. Use the form above to add your first rule!
              </div>
            <?php else: ?>
              <div style="overflow-x: auto; border-radius: 10px; border: 1px solid var(--border-color);">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                  <thead>
                    <tr style="background: var(--bg-elev);">
                      <th style="padding: 12px 16px; text-align: left; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; border-bottom: 1px solid var(--border-color); white-space: nowrap; width: 150px;">Trigger Keyword</th>
                      <th style="padding: 12px 16px; text-align: left; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; border-bottom: 1px solid var(--border-color);">Auto-Reply Message</th>
                      <th style="padding: 12px 16px; text-align: center; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; border-bottom: 1px solid var(--border-color); white-space: nowrap; width: 90px;">Image</th>
                      <th style="padding: 12px 16px; text-align: center; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; border-bottom: 1px solid var(--border-color); white-space: nowrap; width: 150px;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($chatbot_rules as $r): ?>
                      <?php
                        $btn_strings = [];
                        if (!empty($r['buttons_json'])) {
                            $btns = json_decode($r['buttons_json'], true);
                            if (is_array($btns)) {
                                foreach ($btns as $b) {
                                    $btn_strings[] = ($b['type'] === 'url') ? $b['text'] . '|' . $b['url'] : $b['text'];
                                }
                            }
                        }
                        $buttons_display = implode(', ', $btn_strings);
                      ?>
                      <tr style="border-bottom: 1px solid var(--border-color-soft);" onmouseover="this.style.background='var(--bg-elev)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 14px 16px; vertical-align: middle;">
                          <code style="background: var(--bg-elev); color: var(--lime); padding: 4px 10px; border-radius: 6px; border: 1px solid rgba(212,255,61,0.2); font-family: monospace; font-size: 12.5px; font-weight: 700; display: inline-block; white-space: nowrap;"><?= htmlspecialchars($r['keyword']) ?></code>
                        </td>
                        <td style="padding: 14px 16px; color: var(--text-secondary); line-height: 1.6; vertical-align: middle; font-size: 12.5px;">
                          <div style="max-height: 90px; overflow-y: auto; white-space: pre-line;"><?= htmlspecialchars($r['reply_text']) ?></div>
                          <?php if (!empty($buttons_display)): ?>
                            <div style="font-size: 11px; margin-top: 6px; color: #34d399; font-weight: 700; background: rgba(52,211,153,0.08); padding: 3px 8px; border-radius: 6px; border: 1px dashed rgba(52,211,153,0.25); display: inline-block;">
                              🔘 <?= htmlspecialchars($buttons_display) ?>
                            </div>
                          <?php endif; ?>
                        </td>
                        <td style="padding: 14px 16px; text-align: center; vertical-align: middle;">
                          <?php if (!empty($r['image_url'])): ?>
                            <a href="<?= htmlspecialchars($r['image_url']) ?>" target="_blank">
                              <img src="<?= htmlspecialchars($r['image_url']) ?>" style="width: 38px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color); display: block; margin: 0 auto;" onerror="this.src='https://placehold.co/80x80?text=Img';">
                            </a>
                          <?php else: ?>
                            <span style="color: var(--border-color); font-size: 16px;">—</span>
                          <?php endif; ?>
                        </td>
                        <td style="padding: 14px 16px; text-align: center; vertical-align: middle;">
                          <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                            <button type="button" class="btn-action-small" onclick='editChatbotRule(<?= json_encode([
                              'id' => $r['id'],
                              'keyword' => $r['keyword'],
                              'reply_text' => $r['reply_text'],
                              'image_url' => $r['image_url'] ?? '',
                              'buttons_display' => $buttons_display
                            ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="white-space: nowrap; padding: 5px 10px; font-size: 11.5px; border-radius: 6px; font-weight: 700;">✏️ Edit</button>
                            <form method="POST" action="settings.php?tab=chatbot" onsubmit="return confirm('Delete this chatbot rule?');" style="margin: 0;">
                              <input type="hidden" name="action" value="delete_chatbot_rule">
                              <input type="hidden" name="rule_id" value="<?= $r['id'] ?>">
                              <button type="submit" class="btn-action-small delete" style="white-space: nowrap;">🗑 Delete</button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Tab 8: Staff Accounts -->
      <div id="staffTab" style="display: <?= $active_tab === 'staff' ? 'block' : 'none' ?>;">
        <div class="card" style="padding: 24px;">
          <div class="card-title">👥 Staff Accounts Management</div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">

            <!-- Left: Add/Edit Staff Form -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px;" id="staffFormCard">
              <h3 style="color: var(--text-primary); margin-bottom: 6px; font-size: 14px; font-weight: 700;" id="staffFormTitle">➕ Create Staff Account</h3>
              <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;" id="staffFormDesc">Create a new login credential for staff members to access the billing system.</p>

              <form method="POST" action="settings.php?tab=staff" id="staffUserForm" style="display: flex; flex-direction: column; gap: 12px;">
                <input type="hidden" name="action" id="staffFormAction" value="add_staff">
                <input type="hidden" name="staff_id" id="staffUserId" value="">

                <div class="form-group" style="text-align: left; margin-bottom: 12px;">
                  <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Login Username *</label>
                  <input type="text" name="username" id="staffUsername" placeholder="e.g. johndoe" required style="width: 100%; padding: 10px; background: var(--bg-elev); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-family: inherit; font-size: 13px;">
                </div>

                <div class="form-group" style="text-align: left; margin-bottom: 12px;">
                  <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Display Name *</label>
                  <input type="text" name="name" id="staffDisplayName" placeholder="e.g. John Doe" required style="width: 100%; padding: 10px; background: var(--bg-elev); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-family: inherit; font-size: 13px;">
                </div>

                <div class="form-group" style="text-align: left; margin-bottom: 12px;">
                  <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;" id="staffPasswordLabel">Account Password *</label>
                  <input type="password" name="password" id="staffPassword" placeholder="Enter secure password" required style="width: 100%; padding: 10px; background: var(--bg-elev); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-family: inherit; font-size: 13px;">
                  <span style="font-size:10px; color:var(--text-muted); margin-top:4px; display:block;" id="staffPasswordHint">For editing, leave password blank to keep the current one.</span>
                </div>

                <div style="display: flex; gap: 8px; margin-top: 10px;">
                  <button type="submit" class="btn-submit" id="staffSubmitBtn" style="flex: 1; font-family: inherit; font-weight: 700; font-size: 13px; padding: 10px; background: var(--lime); color: var(--bg-main); border: none; border-radius: 6px; cursor: pointer;">Create Account</button>
                  <button type="button" class="btn-submit" id="staffCancelBtn" onclick="cancelStaffEdit()" style="display: none; font-family: inherit; font-weight: 700; font-size: 13px; padding: 10px; background: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer;">Cancel</button>
                </div>
              </form>
            </div>

            <!-- Right: Staff Accounts List -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px; display: flex; flex-direction: column;">
              <h3 style="color: var(--text-primary); margin-bottom: 6px; font-size: 14px; font-weight: 700;">📋 Existing Staff Members</h3>
              <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">List of users with staff privileges authorized to access the invoice dashboard.</p>

              <div style="flex-grow: 1; overflow-x: auto;">
                <?php
                  $staff_list = [];
                  try {
                      $stmt_staff = $pdo->query("SELECT * FROM staff_users ORDER BY id DESC");
                      $staff_list = $stmt_staff->fetchAll();
                  } catch (PDOException $e) {
                      // Handled gracefully
                  }
                ?>
                <?php if (empty($staff_list)): ?>
                  <div style="text-align: center; padding: 40px 10px; border: 1px dashed var(--border-color); border-radius: 8px; background: rgba(0,0,0,0.1);">
                    <div style="font-size: 24px; margin-bottom: 6px;">👥</div>
                    <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">No Staff Accounts Configured</div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Create your first staff login using the form.</div>
                  </div>
                <?php else: ?>
                  <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                      <tr style="border-bottom: 1.5px solid var(--border-color); background: rgba(0,0,0,0.15);">
                        <th style="padding: 10px; font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Name</th>
                        <th style="padding: 10px; font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Username</th>
                        <th style="padding: 10px; font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Status</th>
                        <th style="padding: 10px; font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: center; width: 140px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($staff_list as $su): ?>
                        <tr style="border-bottom: 1px solid var(--border-color-soft);" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                          <td style="padding: 12px 10px; font-size: 13px; font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($su['name']) ?></td>
                          <td style="padding: 12px 10px; font-size: 12px; font-family: var(--font-mono); color: var(--lime);"><?= htmlspecialchars($su['username']) ?></td>
                          <td style="padding: 12px 10px; text-align: center; vertical-align: middle;">
                            <form method="POST" action="settings.php?tab=staff" style="margin: 0; display: inline;">
                              <input type="hidden" name="action" value="toggle_staff_status">
                              <input type="hidden" name="staff_id" value="<?= $su['id'] ?>">
                              <input type="hidden" name="status" value="<?= $su['status'] ?>">
                              <button type="submit" style="border: none; background: none; cursor: pointer; padding: 0;">
                                <?php if ($su['status'] === 'active'): ?>
                                  <span style="display: inline-block; background: rgba(52,211,153,0.15); color: #34d399; font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: 700; border: 1px solid rgba(52,211,153,0.3);">Active</span>
                                <?php else: ?>
                                  <span style="display: inline-block; background: rgba(248,113,113,0.15); color: #f87171; font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: 700; border: 1px solid rgba(248,113,113,0.3);">Inactive</span>
                                <?php endif; ?>
                              </button>
                            </form>
                          </td>
                          <td style="padding: 12px 10px; text-align: center; vertical-align: middle;">
                            <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                              <button type="button" class="btn-action-small" onclick='editStaffUser(<?= json_encode([
                                'id' => $su['id'],
                                'name' => $su['name'],
                                'username' => $su['username']
                              ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="white-space: nowrap; padding: 5px 10px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); color: #fff; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='var(--lime)'; this.style.color='var(--bg-main)';" onmouseout="this.style.background='rgba(255,255,255,0.04)'; this.style.color='#fff';">✏️ Edit</button>
                              
                              <form method="POST" action="settings.php?tab=staff" onsubmit="return confirm('Are you sure you want to delete this staff user account?');" style="display: inline; margin: 0;">
                                <input type="hidden" name="action" value="delete_staff">
                                <input type="hidden" name="staff_id" value="<?= $su['id'] ?>">
                                <button type="submit" style="padding: 5px 10px; background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25); color: #f87171; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(248,113,113,0.2)'; this.style.borderColor='#f87171';" onmouseout="this.style.background='rgba(248,113,113,0.1)'; this.style.borderColor='rgba(248,113,113,0.25)';">🗑 Delete</button>
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
      </div>

    </div>

  </div>

</div>

<script>
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
  
  // Smooth scroll to form card
  document.getElementById('chatbotFormCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelChatbotEdit() {
  document.getElementById('chatbotFormTitle').textContent = '➕ Create New Rule';
  document.getElementById('chatbotFormDesc').textContent = 'Define a new automatic response for a specific trigger word or phrase.';
  document.getElementById('chatbotFormAction').value = 'add_chatbot_rule';
  document.getElementById('chatbotRuleId').value = '';
  document.getElementById('chatbotRuleForm').reset();
  
  document.getElementById('chatbotSubmitBtn').textContent = 'Add Rule';
  document.getElementById('chatbotCancelBtn').style.display = 'none';
}

const GATEWAY_URL = <?= json_encode($settings['whatsapp_gateway_url'] ?? '') ?>;
let qrInterval = null;
let isPolling = false;

function switchTab(tabId, el) {
  // Hide all tabs
  if (document.getElementById('companyTab')) document.getElementById('companyTab').style.display = 'none';
  if (document.getElementById('smtpTab')) document.getElementById('smtpTab').style.display = 'none';
  if (document.getElementById('whatsappTab')) document.getElementById('whatsappTab').style.display = 'none';
  if (document.getElementById('securityTab')) document.getElementById('securityTab').style.display = 'none';
  if (document.getElementById('templatesTab')) document.getElementById('templatesTab').style.display = 'none';
  if (document.getElementById('razorpayTab')) document.getElementById('razorpayTab').style.display = 'none';
  if (document.getElementById('chatbotTab')) document.getElementById('chatbotTab').style.display = 'none';
  if (document.getElementById('staffTab')) document.getElementById('staffTab').style.display = 'none';

  // Deactivate all buttons
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

  // Determine which button was clicked or should be active
  let targetBtn = el;
  if (!targetBtn) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
      if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes("'" + tabId + "'")) {
        targetBtn = btn;
      }
    });
  }
  if (targetBtn) {
    targetBtn.classList.add('active');
  }

  // Show active tab
  if (tabId === 'company') {
    if (document.getElementById('companyTab')) document.getElementById('companyTab').style.display = 'block';
    history.pushState(null, '', 'settings.php?tab=company');
  } else if (tabId === 'smtp') {
    if (document.getElementById('smtpTab')) document.getElementById('smtpTab').style.display = 'block';
    history.pushState(null, '', 'settings.php?tab=smtp');
  } else if (tabId === 'whatsapp') {
    if (document.getElementById('whatsappTab')) document.getElementById('whatsappTab').style.display = 'block';
    history.pushState(null, '', 'settings.php?tab=whatsapp');
  } else if (tabId === 'security') {
    if (document.getElementById('securityTab')) document.getElementById('securityTab').style.display = 'block';
    history.pushState(null, '', 'settings.php?tab=security');
  } else if (tabId === 'templates') {
    if (document.getElementById('templatesTab')) document.getElementById('templatesTab').style.display = 'block';
    history.pushState(null, '', 'settings.php?tab=templates');
  } else if (tabId === 'razorpay') {
    if (document.getElementById('razorpayTab')) document.getElementById('razorpayTab').style.display = 'block';
    history.pushState(null, '', 'settings.php?tab=razorpay');
  } else if (tabId === 'chatbot') {
    if (document.getElementById('chatbotTab')) document.getElementById('chatbotTab').style.display = 'block';
    history.pushState(null, '', 'settings.php?tab=chatbot');
  } else if (tabId === 'staff') {
    if (document.getElementById('staffTab')) document.getElementById('staffTab').style.display = 'block';
    history.pushState(null, '', 'settings.php?tab=staff');
  }

  // Manage status polling based on active tab
  if (tabId === 'whatsapp') {
    startStatusPolling();
  } else {
    stopStatusPolling();
  }
}

// Staff helper functions
function editStaffUser(su) {
  document.getElementById('staffFormTitle').textContent = '✏️ Edit Staff Account';
  document.getElementById('staffFormDesc').textContent = 'Modify login details for this staff member.';
  document.getElementById('staffFormAction').value = 'edit_staff';
  document.getElementById('staffUserId').value = su.id;
  document.getElementById('staffUsername').value = su.username;
  document.getElementById('staffDisplayName').value = su.name;
  
  // Make password optional for editing
  const passInput = document.getElementById('staffPassword');
  passInput.required = false;
  passInput.placeholder = 'Leave blank to keep current password';
  document.getElementById('staffPasswordLabel').textContent = 'Account Password (Optional)';
  
  document.getElementById('staffSubmitBtn').textContent = 'Save Changes';
  document.getElementById('staffCancelBtn').style.display = 'inline-block';
  
  // Smooth scroll
  document.getElementById('staffFormCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelStaffEdit() {
  document.getElementById('staffFormTitle').textContent = '➕ Create Staff Account';
  document.getElementById('staffFormDesc').textContent = 'Create a new login credential for staff members to access the billing system.';
  document.getElementById('staffFormAction').value = 'add_staff';
  document.getElementById('staffUserId').value = '';
  document.getElementById('staffUserForm').reset();
  
  const passInput = document.getElementById('staffPassword');
  passInput.required = true;
  passInput.placeholder = 'Enter secure password';
  document.getElementById('staffPasswordLabel').textContent = 'Account Password *';
  
  document.getElementById('staffSubmitBtn').textContent = 'Create Account';
  document.getElementById('staffCancelBtn').style.display = 'none';
}

function toggleGatewayFields() {
  const select = document.getElementById('gatewayTypeSelect');
  const fields = document.getElementById('gatewayConfigFields');
  if (select && fields) {
    if (select.value === 'gateway') {
      fields.style.display = 'block';
    } else {
      fields.style.display = 'none';
    }
  }
}

function startStatusPolling() {
  if (isPolling) return;
  isPolling = true;
  
  // Poll immediately on activation, and then every 2 seconds
  pollWhatsAppStatus();
  qrInterval = setInterval(pollWhatsAppStatus, 2000);
}

function stopStatusPolling() {
  if (qrInterval) {
    clearInterval(qrInterval);
    qrInterval = null;
  }
  isPolling = false;
}

function pollWhatsAppStatus() {
  const container = document.getElementById('qrCodeContainer');
  const placeholder = document.getElementById('qrPlaceholder');
  const loader = document.getElementById('qrLoader');
  const active = document.getElementById('qrActive');
  const timer = document.getElementById('qrTimer');
  const offline = document.getElementById('qrOffline');
  const qrImage = document.getElementById('qrImage');
  const autoForm = document.getElementById('autoConnectForm');
  const autoNum = document.getElementById('autoLinkedNumber');
  
  const connectedContainer = document.getElementById('deviceConnectedContainer');
  const disconnectedContainer = document.getElementById('deviceDisconnectedContainer');
  const deviceLinkedNumber = document.getElementById('deviceLinkedNumber');

  if (!connectedContainer || !disconnectedContainer) return;

  // Use AbortController to prevent hanging fetch requests
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 3000); // 3 second timeout

  let statusUrl = 'http://localhost:3000/status?session=default';
  if (typeof GATEWAY_URL !== 'undefined' && GATEWAY_URL) {
    statusUrl = GATEWAY_URL.replace('/send', '/status');
    statusUrl += (statusUrl.indexOf('?') !== -1 ? '&' : '?') + 'session=default';
  }

  fetch(statusUrl, { signal: controller.signal })
    .then(response => {
      clearTimeout(timeoutId);
      if (!response.ok) throw new Error('Service Offline');
      return response.json();
    })
    .then(data => {
      if (offline) offline.style.display = 'none';
      
      const isDbConnected = <?= empty($settings['whatsapp_is_connected']) ? 'false' : 'true' ?>;

      if (data.status === 'CONNECTED') {
        connectedContainer.style.display = 'flex';
        disconnectedContainer.style.display = 'none';
        if (deviceLinkedNumber) deviceLinkedNumber.textContent = data.number || 'Connected';
        
        // Auto couple settings if DB thinks disconnected
        if (!isDbConnected && autoForm && autoNum) {
          autoNum.value = data.number || '+91 00000 00000';
          autoForm.submit();
        }
      } else {
        connectedContainer.style.display = 'none';
        disconnectedContainer.style.display = 'block';
        
        // Auto reset DB flag if DB thinks connected
        if (isDbConnected) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = 'settings.php?tab=whatsapp';
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'action';
          input.value = 'disconnect_whatsapp';
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
          return;
        }

        if (data.status === 'DISCONNECTED') {
          if (placeholder) placeholder.style.display = 'flex';
          if (loader) loader.style.display = 'none';
          if (active) active.style.display = 'none';
          if (timer) timer.style.display = 'none';
          if (container) container.style.borderColor = '#cbd5e0';
        } 
        else if (data.status === 'INITIALIZING') {
          if (placeholder) placeholder.style.display = 'none';
          if (loader) loader.style.display = 'block';
          if (active) active.style.display = 'none';
          if (timer) timer.style.display = 'none';
          if (container) container.style.borderColor = '#cbd5e0';
        } 
        else if (data.status === 'QR_READY') {
          if (placeholder) placeholder.style.display = 'none';
          if (loader) loader.style.display = 'none';
          if (active) active.style.display = 'block';
          if (timer) timer.style.display = 'block';
          if (container) container.style.borderColor = '#128c7e';
          
          if (qrImage && data.qr && qrImage.src !== data.qr) {
            qrImage.src = data.qr;
          }
        }
      }
    })
    .catch(err => {
      clearTimeout(timeoutId);
      const isDbConnected = <?= empty($settings['whatsapp_is_connected']) ? 'false' : 'true' ?>;
      if (isDbConnected) {
        connectedContainer.style.display = 'flex';
        disconnectedContainer.style.display = 'none';
        if (deviceLinkedNumber) deviceLinkedNumber.textContent = <?= json_encode($settings['whatsapp_linked_number']) ?> || 'Connected';
      } else {
        connectedContainer.style.display = 'none';
        disconnectedContainer.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
        if (loader) loader.style.display = 'none';
        if (active) active.style.display = 'none';
        if (timer) timer.style.display = 'none';
        if (offline) offline.style.display = 'block';
        if (container) container.style.borderColor = '#e53e3e';
      }
    });
}

function applyRecommendedGateway(url) {
  const select = document.getElementById('gatewayTypeSelect');
  if (select) {
    select.value = 'gateway';
    toggleGatewayFields();
  }
  const urlInput = document.querySelector('[name="whatsapp_gateway_url"]');
  if (urlInput) {
    urlInput.value = url;
    // Highlight the field briefly
    urlInput.style.borderColor = '#38a169';
    urlInput.style.boxShadow = '0 0 0 3px rgba(56, 161, 105, 0.25)';
    setTimeout(() => {
      urlInput.style.borderColor = '';
      urlInput.style.boxShadow = '';
    }, 1500);
  }
}

function startQRGeneration() {
  startStatusPolling();
}

// Start polling if tab is already WhatsApp on DOM load
window.addEventListener('DOMContentLoaded', () => {
  const activeTab = '<?= $active_tab ?>';
  if (activeTab === 'whatsapp') {
    startStatusPolling();
  }
});
</script>
</body>
</html>
