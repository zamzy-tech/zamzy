<?php
require_once __DIR__ . '/auth.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: invoice.php');
    exit;
}
require_once __DIR__ . '/db.php';

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

// Fetch active settings
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

$success_msg = '';
$error_msg = '';

// Handle Admin Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_client') {
        $client_name = trim($_POST['client_name'] ?? '');
        $client_phone = trim($_POST['client_phone'] ?? '');
        $credits_type = $_POST['credits_type'] ?? 'limited';
        $credits_amount = intval($_POST['credits_amount'] ?? 0);

        // Clean phone number to form the Login ID
        $login_id = preg_replace('/[^0-9]/', '', $client_phone);

        if (empty($client_name)) {
            $error_msg = 'Client Name is required.';
        } elseif (empty($client_phone) || strlen($login_id) < 8) {
            $error_msg = 'A valid Client Phone Number (WhatsApp) is required.';
        } else {
            // Check if Login ID is already registered
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE login_id = ?");
            $check_stmt->execute([$login_id]);
            if ($check_stmt->fetchColumn() > 0) {
                $error_msg = "A client with this phone number ({$login_id}) is already registered.";
            } else {
                $credits = ($credits_type === 'unlimited') ? -1 : max(0, $credits_amount);
                
                // Generate a secure API Key prefixing teh_api_
                $api_key = 'teh_api_' . teh_generate_random_key();
                
                // Generate password: first letter of name (uppercase) + last 4 digits of phone number
                $first_letter = !empty($client_name) ? strtoupper(substr(trim($client_name), 0, 1)) : 'A';
                $last_four = substr(preg_replace('/[^0-9]/', '', $client_phone), -4);
                if (strlen($last_four) < 4) {
                    $last_four = str_pad($last_four, 4, '0', STR_PAD_LEFT);
                }
                $raw_password = $first_letter . $last_four;
                $password_hash = password_hash($raw_password, PASSWORD_BCRYPT);

                $expiry_date = trim($_POST['expiry_date'] ?? '');
                if (empty($expiry_date)) {
                    $expiry_date = date('Y-m-d', strtotime('+30 days'));
                }

                try {
                    $ins_stmt = $pdo->prepare("INSERT INTO api_keys (client_name, api_key, credits, status, client_phone, login_id, login_password, plain_password, expiry_date) VALUES (?, ?, ?, 'active', ?, ?, ?, ?, ?)");
                    $ins_stmt->execute([$client_name, $api_key, $credits, $client_phone, $login_id, $password_hash, $raw_password, $expiry_date]);

                    // Automatically determine the site's link portal URL
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                    $portal_url = $protocol . $_SERVER['HTTP_HOST'] . str_replace('api_management.php', 'api_link.php', $_SERVER['SCRIPT_NAME']);

                    // Send dispatch payload via Admin's WhatsApp Gateway
                    $gateway_sent = false;
                    $gateway_error_info = '';

                    if ($settings && ($settings['whatsapp_gateway_type'] ?? '') === 'gateway' && !empty($settings['whatsapp_gateway_url'])) {
                        $g_url = $settings['whatsapp_gateway_url'];
                        $g_token = $settings['whatsapp_gateway_token'] ?? '';
                        
                        $message_text = "*Dear $client_name,*\n\n"
                            . "Your WhatsApp API Account has been successfully created! 🎉\n\n"
                            . "Please log in to the Link Portal to connect your WhatsApp device:\n\n"
                            . "🌐 *Portal Link:* $portal_url\n"
                            . "👤 *Login ID (Phone):* $login_id\n"
                            . "🔑 *Password:* $raw_password\n"
                            . "⚡ *API Key:* $api_key\n\n"
                            . "Once connected, your API key will automatically route website messages through your own device.\n\n"
                            . "Thank you!";
                        
                        $ch = curl_init($g_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        
                        $payload_data = [
                            'to' => $login_id,
                            'phone' => $login_id,
                            'number' => $login_id,
                            'body' => $message_text,
                            'message' => $message_text,
                            'token' => $g_token,
                            'apikey' => $g_token
                        ];
                        
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_data));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Accept: application/json'
                        ]);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                        
                        $res = curl_exec($ch);
                        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $err = curl_error($ch);
                        curl_close($ch);
                        
                        if (!$err && $code < 400) {
                            $gateway_sent = true;
                        } else {
                            $gateway_error_info = $err ?: "HTTP Code $code";
                        }
                    }

                    if ($gateway_sent) {
                        $success_msg = "Successfully added API Client <strong>" . htmlspecialchars($client_name) . "</strong>!<br>"
                            . "🚀 <strong>Login credentials auto-sent via WhatsApp successfully!</strong><br><br>"
                            . "<strong>Login details for reference:</strong><br>"
                            . "• <strong>Portal Link:</strong> <a href='" . htmlspecialchars($portal_url) . "' target='_blank'>" . htmlspecialchars($portal_url) . "</a><br>"
                            . "• <strong>Login ID:</strong> <code>" . htmlspecialchars($login_id) . "</code><br>"
                            . "• <strong>Password:</strong> <code>" . htmlspecialchars($raw_password) . "</code><br>"
                            . "• <strong>API Key:</strong> <code>" . htmlspecialchars($api_key) . "</code>";
                    } else {
                        $success_msg = "Successfully added API Client <strong>" . htmlspecialchars($client_name) . "</strong>!<br>"
                            . "⚠️ <strong>Notice: Could not auto-send credentials via WhatsApp gateway</strong> (" . htmlspecialchars($gateway_error_info ?: 'Gateway offline/not configured') . ").<br>"
                            . "Please copy and share these credentials manually with the client:<br><br>"
                            . "• <strong>Portal Link:</strong> <a href='" . htmlspecialchars($portal_url) . "' target='_blank'>" . htmlspecialchars($portal_url) . "</a><br>"
                            . "• <strong>Login ID:</strong> <code>" . htmlspecialchars($login_id) . "</code><br>"
                            . "• <strong>Password:</strong> <code>" . htmlspecialchars($raw_password) . "</code><br>"
                            . "• <strong>API Key:</strong> <code>" . htmlspecialchars($api_key) . "</code>";
                    }

                } catch (PDOException $e) {
                    $error_msg = "Failed to register API Client: " . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'edit_credits') {
        $client_id = intval($_POST['client_id'] ?? 0);
        $credits_type = $_POST['credits_type'] ?? 'limited';
        $credits_amount = intval($_POST['credits_amount'] ?? 0);
        $expiry_date = trim($_POST['expiry_date'] ?? '');
        $credits = ($credits_type === 'unlimited') ? -1 : max(0, $credits_amount);

        if ($client_id > 0) {
            try {
                $upd_stmt = $pdo->prepare("UPDATE api_keys SET credits = ?, expiry_date = ?, is_trial = 0, expiry_alerts_sent = 0, last_expiry_alert_at = NULL WHERE id = ?");
                $upd_stmt->execute([$credits, $expiry_date, $client_id]);
                $success_msg = "Client settings updated successfully!";
            } catch (PDOException $e) {
                $error_msg = "Failed to update client: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $client_id = intval($_POST['client_id'] ?? 0);
        $current_status = $_POST['current_status'] ?? 'active';
        $new_status = ($current_status === 'active') ? 'suspended' : 'active';

        if ($client_id > 0) {
            try {
                $upd_stmt = $pdo->prepare("UPDATE api_keys SET status = ? WHERE id = ?");
                $upd_stmt->execute([$new_status, $client_id]);
                $success_msg = "Client status toggled to <strong>" . $new_status . "</strong>.";
            } catch (PDOException $e) {
                $error_msg = "Failed to change client status: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_client') {
        $client_id = intval($_POST['client_id'] ?? 0);
        if ($client_id > 0) {
            try {
                $del_stmt = $pdo->prepare("DELETE FROM api_keys WHERE id = ?");
                $del_stmt->execute([$client_id]);
                $success_msg = "API Client revoked successfully.";
            } catch (PDOException $e) {
                $error_msg = "Failed to delete API Client: " . $e->getMessage();
            }
        }
    } elseif ($action === 'login_as_client') {
        $client_id = intval($_POST['client_id'] ?? 0);
        if ($client_id > 0) {
            try {
                $stmt = $pdo->prepare("SELECT api_key FROM api_keys WHERE id = ? LIMIT 1");
                $stmt->execute([$client_id]);
                $key = $stmt->fetchColumn();
                if ($key) {
                    $_SESSION['client_key'] = $key;
                    header('Location: api_link.php');
                    exit;
                }
            } catch (PDOException $e) {
                $error_msg = "Failed to log in as client: " . $e->getMessage();
            }
        }
    } elseif ($action === 'create_coupon') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discount_type = trim($_POST['discount_type'] ?? 'percentage');
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $expiry_date = trim($_POST['expiry_date'] ?? '');
        $usage_limit = trim($_POST['usage_limit'] ?? '');
        $usage_limit_val = ($usage_limit === '') ? null : intval($usage_limit);
        
        if (empty($code)) {
            $error_msg = "Coupon code cannot be empty.";
        } elseif ($discount_value <= 0) {
            $error_msg = "Discount value must be greater than zero.";
        } else {
            try {
                $ins_stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, expiry_date, usage_limit) VALUES (?, ?, ?, ?, ?)");
                $ins_stmt->execute([
                    $code,
                    $discount_type,
                    $discount_value,
                    empty($expiry_date) ? null : $expiry_date,
                    $usage_limit_val
                ]);
                $success_msg = "Coupon code <strong>$code</strong> created successfully!";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'UNIQUE') !== false) {
                    $error_msg = "Coupon code already exists.";
                } else {
                    $error_msg = "Failed to create coupon: " . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'delete_coupon') {
        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        if ($coupon_id > 0) {
            try {
                $del_stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
                $del_stmt->execute([$coupon_id]);
                $success_msg = "Coupon code deleted successfully.";
            } catch (PDOException $e) {
                $error_msg = "Failed to delete coupon: " . $e->getMessage();
            }
        }
    }
}

// Fetch API Clients and Summary Data
try {
    $clients_stmt = $pdo->query("SELECT * FROM api_keys ORDER BY id DESC");
    $api_clients = $clients_stmt->fetchAll();
    
    // Auto-force 0 credits for expired clients
    foreach ($api_clients as &$c) {
        if (!empty($c['expiry_date']) && strtotime($c['expiry_date']) < time()) {
            if (intval($c['credits']) !== 0) {
                try {
                    $pdo->prepare("UPDATE api_keys SET credits = 0 WHERE id = ?")->execute([$c['id']]);
                    $c['credits'] = 0;
                } catch (PDOException $e) {}
            }
        }
    }
    unset($c);
    
    // Fetch all coupons
    $coupons_stmt = $pdo->query("SELECT * FROM coupons ORDER BY id DESC");
    $coupons = $coupons_stmt->fetchAll();

    // Summary Metrics
    $total_clients = count($api_clients);
    
    $total_success = intval($pdo->query("SELECT COUNT(*) FROM api_logs WHERE status = 'success'")->fetchColumn());
    $total_failed = intval($pdo->query("SELECT COUNT(*) FROM api_logs WHERE status = 'failed'")->fetchColumn());
    $total_messages = $total_success + $total_failed;

    // Fetch last 50 logs
    $logs_stmt = $pdo->query("
        SELECT l.*, k.client_name 
        FROM api_logs l 
        LEFT JOIN api_keys k ON l.api_key_id = k.id 
        ORDER BY l.id DESC 
        LIMIT 50
    ");
    $logs = $logs_stmt->fetchAll();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>API Management Console – <?= htmlspecialchars($settings['company_name']) ?></title>
<link rel="stylesheet" href="admin_style.css">
<style>
  /* Metrics Grid overrides */
  .metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
  }
  .metric-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    gap: 8px;
    border-left: 5px solid var(--lime);
    position: relative;
  }
  .metric-card.success { border-left-color: #34d399; }
  .metric-card.failed { border-left-color: #f87171; }
  .metric-label { font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.6px; }
  .metric-value { font-size: 28px; font-weight: 700; color: var(--text-primary); font-family: var(--font-mono); }
  
  /* Cards & Forms */
  .card-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }
  .doc-btn {
    background: rgba(212, 255, 61, 0.08);
    border: 1px solid var(--lime);
    color: var(--lime);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 6px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .doc-btn:hover { background: var(--lime); color: var(--bg-main); }
  
  .grid-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: flex-end; }
  .form-group { display: flex; flex-direction: column; gap: 6px; }
  
  .btn-primary {
    background-color: var(--lime);
    color: var(--bg-main);
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s ease;
    font-family: inherit;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .btn-primary:hover {
    background-color: var(--lime-deep);
    box-shadow: 0 4px 14px var(--lime-glow);
  }
  
  /* Badges & Statuses */
  .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
  .badge.active, .badge.status-active { background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.25); color: #34d399; }
  .badge.suspended { background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.25); color: #f87171; }
  .badge.unlimited { background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.25); color: #a855f7; }
  
  .badge.status-success { background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.25); color: #34d399; }
  .badge.status-failed { background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.25); color: #f87171; }
  
  /* API Key display */
  .api-key-container { display: flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 12px; color: var(--lime); background: var(--bg-card); padding: 6px 10px; border-radius: 6px; border: 1.5px solid var(--border-color); width: max-content; }
  .btn-copy { background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 2px; transition: color 0.2s; }
  .btn-copy:hover { color: var(--lime); }
  
  /* Row Forms & Action buttons */
  .actions-cell { display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; }
  .btn-action {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 6px;
    transition: all 0.2s;
    font-family: inherit;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .btn-action.edit { color: var(--lime); border-color: rgba(212, 255, 61, 0.3); }
  .btn-action.edit:hover { background: rgba(212, 255, 61, 0.15); box-shadow: 0 2px 8px rgba(212, 255, 61, 0.2); }
  .btn-action.toggle { color: #f59e0b; border-color: rgba(245, 158, 11, 0.3); }
  .btn-action.toggle:hover { background: rgba(245, 158, 11, 0.15); box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2); }
  .btn-action.delete { color: #f87171; border-color: rgba(239, 68, 68, 0.3); }
  .btn-action.delete:hover { background: rgba(239, 68, 68, 0.15); box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2); }
  
  /* Modal for editing credits */
  .overlay-modal {
    position: fixed;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(6px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 2000;
  }
  .modal-content {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 28px 32px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.8);
  }
  .modal-content h3 {
    color: var(--text-primary) !important;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
    margin-bottom: 20px;
  }
  .modal-buttons { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
</style>
  <link rel="stylesheet" href="2fa_dashboard_theme.css">
</head>
<body>

<header>
  <div class="logo-block" style="display: flex; align-items: center; gap: 10px;">
    <img src="zamzy_logo.png" alt="ZAMZY" style="height: 32px; width: auto;">
    <div>
      <div class="logo" style="font-size: 18px; font-weight: 800; color: #fff; margin: 0; line-height: 1;">ZAM<span style="color:var(--cyan);">ZY</span></div>
      <div class="tagline" style="font-size: 9.5px; color: var(--cyan); margin-top: 3px; font-family: var(--font-mono);">Cluster Admin &amp; API Management</div>
    </div>
  </div>
  <nav>
    <a href="invoice.php">Generate Invoice</a>
    <a href="history.php">Billing History</a>
    <a href="clients.php">Clients</a>
    <a href="accounting.php">Accounting</a>
    <a href="pricing.php">Pricing</a>
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
      <a href="settings.php">Settings</a>
      <a href="api_management.php" class="active">API Management</a>
    <?php endif; ?>
    <a href="logout.php" class="btn-logout">Logout</a>
  </nav>
</header>

<div class="container">

  <?php if (!empty($success_msg)): ?>
    <div class="alert success">✅ <?= $success_msg ?></div>
  <?php endif; ?>
  <?php if (!empty($error_msg)): ?>
    <div class="alert error">❌ <?= htmlspecialchars($error_msg) ?></div>
  <?php endif; ?>

  <!-- Summary Metrics -->
  <div class="metrics-grid">
    <div class="metric-card">
      <div class="metric-label">Registered API Clients</div>
      <div class="metric-value"><?= $total_clients ?></div>
    </div>
    <div class="metric-card success">
      <div class="metric-label">Delivered API Messages</div>
      <div class="metric-value"><?= $total_success ?></div>
    </div>
    <div class="metric-card failed">
      <div class="metric-label">Failed Transmissions</div>
      <div class="metric-value"><?= $total_failed ?></div>
    </div>
    <div class="metric-card">
      <div class="metric-label">Total Logs Recorded</div>
      <div class="metric-value"><?= $total_messages ?></div>
    </div>
  </div>

  <!-- Registration Form Card -->
  <div class="card">
    <div class="card-title">Register New API Client Key</div>
    <form method="POST" class="grid-form">
      <input type="hidden" name="action" value="add_client">
      
      <div class="form-group">
        <label for="client_name">Client / Business Name *</label>
        <input type="text" name="client_name" id="client_name" placeholder="e.g. Acme Corp" required>
      </div>

      <div class="form-group">
        <label for="client_phone">Client Phone (WhatsApp) *</label>
        <input type="tel" name="client_phone" id="client_phone" placeholder="e.g. +91 98765 43210" required>
      </div>

      <div class="form-group">
        <label for="credits_type">Credit Mode</label>
        <select name="credits_type" id="credits_type" onchange="toggleCreditsInput(this, 'add_credits_val')">
          <option value="limited">Credit Allowance</option>
          <option value="unlimited">Unlimited Credits</option>
        </select>
      </div>

      <div class="form-group" id="add_credits_val">
        <label for="credits_amount">Initial Credits</label>
        <input type="number" name="credits_amount" id="credits_amount" value="100" min="0">
      </div>

      <div class="form-group">
        <label for="expiry_date">Expiry Date</label>
        <input type="date" name="expiry_date" id="expiry_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
      </div>

      <button type="submit" class="btn-primary">Register Client</button>
    </form>
  </div>

  <!-- Client Management Table -->
  <div class="card">
    <div class="card-title">
      <span>API Client Credentials</span>
      <a href="api_docs.php" target="_blank" class="doc-btn">📖 View Developer Documentation</a>
    </div>
    
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Client Name / Phone</th>
            <th>Portal Credentials / API Key</th>
            <th>Credits Remaining</th>
            <th>Expiry Date</th>
            <th>WhatsApp Gateway</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($api_clients)): ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">No API clients registered yet. Register one above.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($api_clients as $client): ?>
              <tr>
                <td>
                  <strong style="color: var(--text-primary);"><?= htmlspecialchars($client['client_name']) ?></strong>
                  <div style="font-size:11px; color: var(--text-muted); margin-top:2px;">
                    📞 <?= htmlspecialchars($client['client_phone'] ?? 'N/A') ?>
                  </div>
                </td>
                <td>
                  <div style="font-size: 12px; color: var(--text-secondary);">
                    <div><strong style="color: var(--text-muted);">Login ID:</strong> <code style="color: var(--lime); background: var(--bg-card); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-color);"><?= htmlspecialchars($client['login_id'] ?? '') ?></code></div>
                    <div class="api-key-container" style="margin-top: 4px;">
                      <span id="key_<?= $client['id'] ?>"><?= htmlspecialchars($client['api_key']) ?></span>
                      <button class="btn-copy" onclick="copyKey('key_<?= $client['id'] ?>')" title="Copy Key">📋</button>
                    </div>
                  </div>
                </td>
                <td>
                  <?php if (intval($client['credits']) === -1): ?>
                    <span class="badge unlimited">Unlimited</span>
                  <?php else: ?>
                    <strong style="color: var(--lime); font-family: var(--font-mono);"><?= number_format($client['credits']) ?></strong>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?= !empty($client['expiry_date']) ? date('d-M-Y', strtotime($client['expiry_date'])) : '<span style="color: var(--text-muted); font-style:italic;">No Expiry</span>' ?></strong>
                </td>
                <td>
                  <?php if (!empty($client['whatsapp_gateway_url'])): ?>
                    <div style="font-size: 12px; color: var(--text-secondary);">
                      <span class="badge <?= intval($client['whatsapp_is_connected']) === 1 ? 'active' : 'suspended' ?>">
                        <?= intval($client['whatsapp_is_connected']) === 1 ? 'Connected' : 'Disconnected' ?>
                      </span>
                      <div style="margin-top: 4px; font-family: var(--font-mono); font-size:11px; color: var(--text-muted);" title="<?= htmlspecialchars($client['whatsapp_gateway_url']) ?>">
                        <?= htmlspecialchars($client['whatsapp_linked_number'] ?: 'No number linked') ?>
                      </div>
                    </div>
                  <?php else: ?>
                    <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Not Configured</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= htmlspecialchars($client['status']) ?>">
                    <?= htmlspecialchars($client['status']) ?>
                  </span>
                </td>
                <td class="actions-cell">
                  <form method="POST" style="display:inline;" action="api_management.php">
                    <input type="hidden" name="action" value="login_as_client">
                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                    <button type="submit" class="btn-action edit" style="background: rgba(212, 255, 61, 0.1); border-color: rgba(212, 255, 61, 0.3); color: #D4FF3D;" onmouseover="this.style.background='rgba(212, 255, 61, 0.2)';" onmouseout="this.style.background='rgba(212, 255, 61, 0.1)';">
                      🔑 Login as Client
                    </button>
                  </form>
                  <button class="btn-action edit" onclick="openEditModal(<?= $client['id'] ?>, '<?= htmlspecialchars($client['client_name'], ENT_QUOTES) ?>', <?= $client['credits'] ?>, '<?= htmlspecialchars($client['expiry_date'] ?? '') ?>')">
                    ✍️ Update Plan
                  </button>
                  
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Toggle status for this client?');">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($client['status']) ?>">
                    <button type="submit" class="btn-action toggle">
                      🚫 Toggle Active
                    </button>
                  </form>
                  
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently revoke this API client? All active credentials will stop working.');">
                    <input type="hidden" name="action" value="delete_client">
                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                    <button type="submit" class="btn-action delete">
                      🗑️ Revoke
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Manage Coupon Codes Card -->
  <div class="card" style="margin-bottom: 30px;">
    <div class="card-title">🎫 Manage Coupon Codes</div>
    
    <form method="POST" style="margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; align-items: flex-end; background: var(--bg-card); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
      <input type="hidden" name="action" value="create_coupon">
      
      <div class="form-group" style="margin-bottom: 0;">
        <label>Coupon Code</label>
        <input type="text" name="code" placeholder="e.g. SAVE20" required>
      </div>
      
      <div class="form-group" style="margin-bottom: 0;">
        <label>Discount Type</label>
        <select name="discount_type" required>
          <option value="percentage">Percentage (%)</option>
          <option value="flat">Flat Amount (Rs)</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label>Discount Value</label>
        <input type="number" name="discount_value" step="0.01" placeholder="e.g. 20" required>
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label>Usage Limit (Optional)</label>
        <input type="number" name="usage_limit" placeholder="Unlimited">
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label>Expiry Date (Optional)</label>
        <input type="date" name="expiry_date">
      </div>

      <button type="submit" class="btn-primary" style="height: 44px; padding: 0 20px; display: inline-flex; align-items: center; justify-content: center;">Create Coupon</button>
    </form>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Code</th>
            <th>Discount Type</th>
            <th>Value</th>
            <th>Expiry Date</th>
            <th>Usage</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($coupons)): ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">No coupon codes created yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($coupons as $coupon): ?>
              <tr>
                <td style="font-weight: 700; color: var(--lime); font-family: var(--font-mono);"><?= htmlspecialchars($coupon['code']) ?></td>
                <td><?= ucfirst(htmlspecialchars($coupon['discount_type'])) ?></td>
                <td><strong style="color: var(--text-primary);"><?= $coupon['discount_type'] === 'percentage' ? htmlspecialchars($coupon['discount_value']) . '%' : 'Rs ' . number_format($coupon['discount_value'], 2) ?></strong></td>
                <td><?= !empty($coupon['expiry_date']) ? date('d-M-Y', strtotime($coupon['expiry_date'])) : '<span style="color: var(--text-muted);">Never Expires</span>' ?></td>
                <td><?= $coupon['used_count'] ?> / <?= ($coupon['usage_limit'] === null) ? 'Unlimited' : $coupon['usage_limit'] ?></td>
                <td>
                  <span class="badge status-active">
                    <?= htmlspecialchars($coupon['status']) ?>
                  </span>
                </td>
                <td>
                  <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this coupon code?')">
                    <input type="hidden" name="action" value="delete_coupon">
                    <input type="hidden" name="coupon_id" value="<?= $coupon['id'] ?>">
                    <button type="submit" class="btn-action delete">🗑️ Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Realtime API Logs Card -->
  <div class="card">
    <div class="card-title">Live API Transaction Audit Logs (Last 50)</div>
    
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Client Name</th>
            <th>Message Type</th>
            <th>Recipient</th>
            <th>Credits Used</th>
            <th>Status</th>
            <th>Details / Gateway Logs</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">No API transactions recorded yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap; font-family: var(--font-mono);">
                  <?= date('d M Y, h:i A', strtotime($log['created_at'])) ?>
                </td>
                <td style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($log['client_name'] ?? 'Revoked Client') ?></td>
                <td>
                  <strong style="text-transform: uppercase; font-size: 11px; color: var(--lime); letter-spacing: 0.5px;">
                    <?= htmlspecialchars($log['message_type']) ?>
                  </strong>
                </td>
                <td style="font-family: var(--font-mono); color: var(--text-secondary);"><?= htmlspecialchars($log['recipient_phone']) ?></td>
                <td style="font-weight: 700; color: var(--text-primary);"><?= intval($log['credits_used']) ?></td>
                <td>
                  <span class="badge status-<?= $log['status'] ?>">
                    <?= htmlspecialchars($log['status']) ?>
                  </span>
                </td>
                <td style="font-size: 12px; color: var(--text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['response_message'] ?? '') ?>">
                  <?= htmlspecialchars($log['response_message'] ?? '') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Edit Credits Modal -->
<div class="overlay-modal" id="editCreditsModal">
  <div class="modal-content">
    <h3 id="modalTitle">Update Credits</h3>
    <form method="POST">
      <input type="hidden" name="action" value="edit_credits">
      <input type="hidden" name="client_id" id="modal_client_id">
      
      <div class="form-group" style="margin-bottom: 16px;">
        <label for="modal_credits_type">Credit Mode</label>
        <select name="credits_type" id="modal_credits_type" onchange="toggleCreditsInput(this, 'modal_credits_val')">
          <option value="limited">Credit Allowance</option>
          <option value="unlimited">Unlimited Credits</option>
        </select>
      </div>

      <div class="form-group" id="modal_credits_val" style="margin-bottom: 16px;">
        <label for="modal_credits_amount">Credits Balance</label>
        <input type="number" name="credits_amount" id="modal_credits_amount" min="0">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label for="modal_expiry_date">Expiry Date</label>
        <input type="date" name="expiry_date" id="modal_expiry_date">
      </div>

      <div class="modal-buttons">
        <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleCreditsInput(selectObj, elementId) {
  const target = document.getElementById(elementId);
  if (selectObj.value === 'unlimited') {
    target.style.display = 'none';
  } else {
    target.style.display = 'flex';
  }
}

function copyKey(id) {
  const keyText = document.getElementById(id).textContent;
  navigator.clipboard.writeText(keyText).then(() => {
    alert("API Key copied to clipboard!");
  }).catch(err => {
    alert("Could not copy API Key: " + err);
  });
}

function openEditModal(id, name, credits, expiryDate) {
  document.getElementById('modal_client_id').value = id;
  document.getElementById('modalTitle').innerHTML = "Update Settings for <strong>" + name + "</strong>";
  
  const selectType = document.getElementById('modal_credits_type');
  const amountInput = document.getElementById('modal_credits_amount');
  const expiryInput = document.getElementById('modal_expiry_date');
  
  expiryInput.value = expiryDate || '';
  
  if (credits === -1) {
    selectType.value = 'unlimited';
    amountInput.value = '100'; // placeholder
    document.getElementById('modal_credits_val').style.display = 'none';
  } else {
    selectType.value = 'limited';
    amountInput.value = credits;
    document.getElementById('modal_credits_val').style.display = 'flex';
  }
  
  document.getElementById('editCreditsModal').style.display = 'flex';
}

function closeEditModal() {
  document.getElementById('editCreditsModal').style.display = 'none';
}
</script>
<?php include_once __DIR__ . '/send_fast_widget.php'; ?>
</body>
</html>
