<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$staff_success_msg = $_GET['staff_success'] ?? '';
$staff_error_msg = $_GET['staff_error'] ?? '';
$staff_warning_msg = $_GET['staff_warning'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'staff') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_lead') {
        $name = trim($_POST['lead_name'] ?? '');
        $phone = trim($_POST['lead_phone'] ?? '');
        $address = trim($_POST['lead_address'] ?? '');
        $created_by = $_SESSION['user_id'] ?? 0;
        
        if (empty($name) || empty($phone)) {
            $staff_error_msg = 'Name and Phone Number are required.';
        } else {
            try {
                $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                $ins = $pdo->prepare("INSERT INTO leads (name, phone, address, status, created_by) VALUES (?, ?, ?, 'pending', ?)");
                $ins->execute([$name, $clean_phone, $address, $created_by]);
                $staff_success_msg = 'Lead uploaded successfully!';
            } catch (PDOException $e) {
                $staff_error_msg = 'Failed to upload lead: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($action === 'bulk_upload_leads') {
        $created_by = $_SESSION['user_id'] ?? 0;
        
        if (isset($_FILES['leads_csv']) && $_FILES['leads_csv']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['leads_csv']['tmp_name'];
            
            if (($handle = fopen($tmpName, "r")) !== FALSE) {
                // Get header row
                $headers = fgetcsv($handle, 1000, ",");
                
                // Map columns based on headers (flexible mapping)
                $nameIdx = 0;
                $phoneIdx = 1;
                $addressIdx = 2;
                
                if ($headers) {
                    foreach ($headers as $idx => $header) {
                        $hdr = strtolower(trim($header));
                        if (strpos($hdr, 'name') !== false) {
                            $nameIdx = $idx;
                        } elseif (strpos($hdr, 'phone') !== false || strpos($hdr, 'number') !== false || strpos($hdr, 'mobile') !== false || strpos($hdr, 'contact') !== false) {
                            $phoneIdx = $idx;
                        } elseif (strpos($hdr, 'address') !== false || strpos($hdr, 'location') !== false) {
                            $addressIdx = $idx;
                        }
                    }
                }
                
                $success_count = 0;
                $row_count = 0;
                
                $pdo->beginTransaction();
                try {
                    $ins = $pdo->prepare("INSERT INTO leads (name, phone, address, status, created_by) VALUES (?, ?, ?, 'pending', ?)");
                    
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $row_count++;
                        
                        $name = isset($data[$nameIdx]) ? trim($data[$nameIdx]) : '';
                        $phone = isset($data[$phoneIdx]) ? trim($data[$phoneIdx]) : '';
                        $address = isset($data[$addressIdx]) ? trim($data[$addressIdx]) : '';
                        
                        if (!empty($name) && !empty($phone)) {
                            $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                            $ins->execute([$name, $clean_phone, $address, $created_by]);
                            $success_count++;
                        }
                    }
                    $pdo->commit();
                    $staff_success_msg = "Successfully imported {$success_count} leads from CSV!";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $staff_error_msg = "Database import failed: " . $e->getMessage();
                }
                
                fclose($handle);
            } else {
                $staff_error_msg = "Failed to open uploaded CSV file.";
            }
        } else {
            $staff_error_msg = "Please select a valid CSV file to upload.";
        }
    }
    
    elseif ($action === 'submit_lead_status_ajax') {
        header('Content-Type: application/json');
        $lead_id = intval($_POST['lead_id'] ?? 0);
        $status_choice = trim($_POST['status_choice'] ?? '');
        
        if ($lead_id > 0 && in_array($status_choice, ['no_response', 'interested', 'not_interested'])) {
            try {
                // Fetch active settings for gateway config
                $settings_stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
                $settings = $settings_stmt->fetch();

                // Fetch lead details
                $ld_stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ? LIMIT 1");
                $ld_stmt->execute([$lead_id]);
                $lead = $ld_stmt->fetch();
                
                if ($lead) {
                    $name = $lead['name'];
                    $clean_phone = $lead['phone'];
                    
                    // Formulate message based on choice
                    $msg_text = '';
                    if ($status_choice === 'no_response') {
                        $msg_text = "Hi {$name}, we tried calling you regarding our services, but we couldn't get in touch. Please let us know a convenient time to speak with you.";
                    } elseif ($status_choice === 'interested') {
                        $msg_text = "Hi {$name}, thank you for showing interest in our services! We are excited to assist you. One of our representatives will contact you shortly with more details.";
                    } elseif ($status_choice === 'not_interested') {
                        $msg_text = "Hi {$name}, thank you for your time. If you ever change your mind or need our services in the future, feel free to reach out to us anytime.";
                    }
                    
                    // Append signature
                    $msg_text .= "\n\nURL : WWW.TEHUB.IN\nCONTACT : 9566777266\nMAIL : TEH@TEHUB.IN";
                    
                    // Update database status
                    $upd = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
                    $upd->execute([$status_choice, $lead_id]);
                    
                    echo json_encode([
                        'success' => true,
                        'message_text' => $msg_text,
                        'phone' => $clean_phone,
                        'gateway_url' => $settings['whatsapp_gateway_url'] ?? '',
                        'gateway_token' => $settings['whatsapp_gateway_token'] ?? ''
                    ]);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Lead not found.']);
                    exit;
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
            exit;
        }
    }
}

// Fetch active settings and SMTP accounts
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    
    $smtp_stmt = $pdo->query("SELECT * FROM smtp_accounts ORDER BY id ASC");
    $smtp_accounts = $smtp_stmt->fetchAll();
    
    // Determine next invoice number dynamically
    $next_invoice_number = '';
    $last_invoice_stmt = $pdo->query("SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1");
    $last_invoice = $last_invoice_stmt->fetch();
    if ($last_invoice) {
        $last_num = $last_invoice['invoice_number'];
        if (preg_match('/^(.*?)([0-9]+)$/', $last_num, $matches)) {
            $prefix = $matches[1];
            $number = intval($matches[2]);
            $padding_length = strlen($matches[2]);
            $next_num_str = str_pad(strval($number + 1), $padding_length, '0', STR_PAD_LEFT);
            $next_invoice_number = $prefix . $next_num_str;
        } else {
            $next_invoice_number = $last_num . '-1';
        }
    } else {
        // Fallback seed continuing from TEH-2026-133 as requested
        $next_invoice_number = 'TEH-2026-133';
    }
    
    // Fetch all registered clients for autocomplete
    $clients_stmt = $pdo->query("SELECT * FROM clients ORDER BY client_name ASC");
    $all_clients = $clients_stmt->fetchAll();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

$success    = isset($_GET['sent']);
$registered = isset($_GET['registered']);
$error      = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice Generator – <?= htmlspecialchars($settings['company_name']) ?></title>
<meta name="description" content="Generate and manage professional invoices for your clients. Integrated with email and WhatsApp delivery options.">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<link rel="stylesheet" href="admin_style.css">
<style>
  #autofillBanner {
    background: rgba(212, 255, 61, 0.05) !important;
    border: 1px solid rgba(212, 255, 61, 0.25) !important;
    color: var(--lime) !important;
  }
  .email-row { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; }
  .email-row input { flex: 1; }
  .btn-icon { background: none; border: none; cursor: pointer; font-size: 18px; color: #f87171; line-height: 1; padding: 4px; }
  .btn-add-email { background: none; border: 1.5px dashed var(--lime); color: var(--lime); border-radius: 7px; padding: 7px 14px; font-size: 13px; cursor: pointer; margin-top: 4px; transition: all .2s; font-weight: 600; }
  .btn-add-email:hover { background: var(--border-color-soft); }
  
  /* Line Items */
  .items-table { width: 100%; border-collapse: collapse; }
  .items-table th { background: var(--bg-card); color: var(--text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; padding: 10px 10px; text-align: left; }
  .items-table td { padding: 8px 6px; vertical-align: middle; }
  .items-table tr:not(:last-child) td { border-bottom: 1px solid var(--border-color-soft); }
  .items-table input { border: 1.5px solid var(--border-color); border-radius: 6px; padding: 7px 9px; font-size: 13px; width: 100%; background: var(--bg-card); color: var(--text-primary); }
  .items-table input:focus { border-color: var(--lime); }
  
  .col-desc { width: 35%; }
  .col-qty { width: 10%; }
  .col-price { width: 15%; }
  .col-expiry { width: 18%; }
  .col-total { width: 14%; text-align: right; font-weight: 600; color: var(--lime); padding-right: 8px; font-family: var(--font-mono); }
  .col-del { width: 8%; text-align: center; }
  .btn-add-item { background: rgba(212, 255, 61, 0.08); border: 1.5px dashed var(--lime); color: var(--lime); border-radius: 7px; padding: 9px 18px; font-size: 13px; cursor: pointer; margin-top: 12px; transition: all .2s; font-weight: 600; }
  .btn-add-item:hover { background: var(--lime); color: var(--bg-main); }
  
  .totals-box { margin-top: 16px; display: flex; justify-content: flex-end; }
  .totals-inner { min-width: 280px; }
  .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13.5px; color: var(--text-secondary); }
  .totals-row.grand { font-size: 15px; font-weight: 700; color: var(--text-primary); border-top: 1.5px solid var(--border-color); margin-top: 6px; padding-top: 10px; }
  .totals-row span:last-child { font-weight: 600; font-family: var(--font-mono); }
  
  /* Summary amounts */
  .amounts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .amount-box { border-radius: 10px; padding: 16px 20px; text-align: center; background: var(--bg-card); }
  .amount-box.advance { border: 1.5px solid rgba(52, 211, 153, 0.25); }
  .amount-box.pending { border: 1.5px solid rgba(248, 113, 113, 0.25); }
  .amount-box .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
  .amount-box.advance .label { color: #34d399; }
  .amount-box.pending .label { color: #f87171; }
  .amount-box input { border: none; background: transparent; text-align: center; font-size: 22px; font-weight: 700; width: 100%; outline: none; font-family: var(--font-heading); }
  .amount-box.advance input { color: #34d399; }
  .amount-box.pending input { color: #f87171; }
  
  /* Submit buttons */
  .btn-pdf { background: #10b981; color: var(--bg-main); border: none; border-radius: 9px; padding: 14px 28px; font-size: 14px; font-weight: 700; cursor: pointer; letter-spacing: .5px; transition: all .2s; display: inline-flex; align-items: center; gap: 10px; }
  .btn-pdf:hover { background: #059669; transform: translateY(-1px); }
  .btn-whatsapp { background: #25d366; color: var(--bg-main); border: none; border-radius: 9px; padding: 14px 28px; font-size: 14px; font-weight: 700; cursor: pointer; letter-spacing: .5px; transition: all .2s; display: inline-flex; align-items: center; gap: 10px; }
  .btn-whatsapp:hover { background: #059669; transform: translateY(-1px); }
  .btn-register { background: #6366f1; color: #fff; border: none; border-radius: 9px; padding: 14px 28px; font-size: 14px; font-weight: 700; cursor: pointer; letter-spacing: .5px; transition: all .2s; display: inline-flex; align-items: center; gap: 10px; }
  .btn-register:hover { background: #4f46e5; transform: translateY(-1px); }
  .btn-both { background: var(--lime); color: var(--bg-main); border: none; border-radius: 9px; padding: 14px 28px; font-size: 14px; font-weight: 700; cursor: pointer; letter-spacing: .5px; transition: all .2s ease; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px var(--lime-glow); }
  .btn-both:hover { transform: translateY(-1px); background: var(--lime-deep); box-shadow: 0 6px 16px var(--lime-glow); }
  .submit-row { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; flex-wrap: wrap; }
  
  /* Upload */
  .upload-box { border: 2px dashed var(--border-color); border-radius: 9px; padding: 24px; text-align: center; cursor: pointer; transition: all .2s; background: var(--bg-card); }
  .upload-box:hover { border-color: var(--lime); background: var(--bg-elev); }
  .upload-icon { font-size: 28px; margin-bottom: 6px; }
  .upload-text { font-size: 14px; font-weight: 600; color: var(--text-primary); }
  .upload-sub { font-size: 12px; color: var(--text-muted); margin-top: 3px; }
  .file-chip { display: inline-flex; align-items: center; gap: 6px; background: var(--bg-elev); border: 1px solid var(--border-color); border-radius: 20px; padding: 4px 12px; font-size: 12px; color: var(--lime); margin: 3px; }
  .file-chip span { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  @media(max-width:640px){.grid-2,.grid-3,.amounts-grid{grid-template-columns:1fr}.card{padding:18px 14px}}
</style>
  <link rel="stylesheet" href="2fa_dashboard_theme.css">
</head>
<body>

<header>
  <div class="logo-block" style="display: flex; align-items: center; gap: 10px; margin-bottom: 0;">
    <img src="teh_logo.png" alt="THE EXPERT HUB Logo" style="height: 32px; width: auto;">
    <div class="logo" style="font-size: 18px; font-weight: 800; color: #fff; margin: 0; line-height: 1;"><?= htmlspecialchars($settings['company_name']) ?></div>
  </div>
  <nav>
    <a href="invoice.php" class="active">Generate Invoice</a>
    <a href="history.php">Billing History</a>
    <a href="clients.php">Clients</a>
    <a href="accounting.php">Accounting</a>
    <a href="pricing.php">Pricing</a>
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
      <a href="settings.php">Settings</a>
      <a href="api_management.php">API Management</a>
    <?php endif; ?>
    <a href="logout.php" class="btn-logout">Logout</a>
  </nav>
</header>

<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'staff'): ?>
  <!-- Staff calling dashboard HTML -->
  <div class="container">
    
    <div class="admin-header" style="margin-bottom: 24px;">
      <h1>Lead Calling & Follow-Up Panel</h1>
      <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Welcome back, <strong><?= htmlspecialchars($_SESSION['display_name']) ?></strong> (Staff Privileges)</p>
    </div>

    <?php if (!empty($staff_success_msg)): ?>
      <div class="alert success" style="background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.25); color: #34d399; padding: 12px 16px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px;">
        ✅ <?= htmlspecialchars($staff_success_msg) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($staff_error_msg)): ?>
      <div class="alert error" style="background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25); color: #f87171; padding: 12px 16px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px;">
        ❌ <?= htmlspecialchars($staff_error_msg) ?>
      </div>
    <?php endif; ?>

    <div class="grid-2" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
      
      <!-- Upload New Lead Card -->
      <div class="card" style="background: var(--bg-surface); border: 1px solid var(--border-color); padding: 24px; border-radius: 16px;">
        <h3 style="color:#fff; margin-bottom: 6px; font-size:16px; font-weight: 700;">➕ Upload Leads</h3>
        <p style="font-size:12px; color:var(--text-muted); margin-bottom: 16px;">Add a single prospect or upload a CSV file to import leads in bulk.</p>
        
        <!-- Tab Swapper -->
        <div style="display: flex; gap: 8px; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 4px; border-radius: 8px; border: 1px solid var(--border-color);">
          <button type="button" id="btnSingleForm" onclick="showUploadMode('single')" style="flex:1; border:none; padding:8px; font-size:11.5px; font-weight:700; border-radius:6px; background:rgba(212,255,61,0.1); color:var(--lime); cursor:pointer; font-family:inherit; transition: all 0.2s;">Single Lead</button>
          <button type="button" id="btnBulkForm" onclick="showUploadMode('bulk')" style="flex:1; border:none; padding:8px; font-size:11.5px; font-weight:700; border-radius:6px; background:transparent; color:var(--text-muted); cursor:pointer; font-family:inherit; transition: all 0.2s;">Bulk Import (CSV)</button>
        </div>

        <!-- Mode 1: Single Lead Form -->
        <form method="POST" action="invoice.php" id="singleLeadForm" style="display: flex; flex-direction: column; gap: 16px;">
          <input type="hidden" name="action" value="add_lead">
          
          <div class="form-group" style="text-align: left; margin-bottom: 12px;">
            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Client Name *</label>
            <input type="text" name="lead_name" placeholder="Full Name" required style="width: 100%; padding: 10px; background: var(--bg-elev); border: 1px solid var(--border-color); border-radius: 6px; color: #fff; font-family: inherit; font-size: 13px;">
          </div>

          <div class="form-group" style="text-align: left; margin-bottom: 12px;">
            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Phone Number (WhatsApp) *</label>
            <input type="text" name="lead_phone" placeholder="e.g. +91 98765 43210" required style="width: 100%; padding: 10px; background: var(--bg-elev); border: 1px solid var(--border-color); border-radius: 6px; color: #fff; font-family: inherit; font-size: 13px;">
          </div>

          <div class="form-group" style="text-align: left; margin-bottom: 12px;">
            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Client Address</label>
            <textarea name="lead_address" rows="3" placeholder="Enter client address details..." style="width: 100%; padding: 10px; background: var(--bg-elev); border: 1px solid var(--border-color); border-radius: 6px; color: #fff; font-family: inherit; font-size: 13px; line-height: 1.4; resize: vertical;"></textarea>
          </div>
          
          <button type="submit" class="btn-submit" style="width:100%; font-family: inherit; font-weight:700; background: var(--lime); color: var(--bg-main); border: none; border-radius: 6px; padding: 10px; cursor: pointer; transition: all 0.2s;">Add Lead Details</button>
        </form>

        <!-- Mode 2: Bulk Lead Form -->
        <form method="POST" action="invoice.php" id="bulkLeadForm" enctype="multipart/form-data" style="display: none; flex-direction: column; gap: 16px;">
          <input type="hidden" name="action" value="bulk_upload_leads">
          
          <div class="form-group" style="text-align: left; margin-bottom: 12px;">
            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Select CSV File *</label>
            <input type="file" name="leads_csv" accept=".csv" required style="width: 100%; padding: 10px; background: var(--bg-elev); border: 1px dashed var(--border-color); border-radius: 6px; color: #fff; font-family: inherit; font-size: 13px;">
          </div>
          
          <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--border-color); padding: 12px; border-radius: 8px; font-size: 11.5px; color: var(--text-muted); line-height: 1.5;">
            💡 <strong>CSV File Format Requirements:</strong><br>
            * Must contain a header row.<br>
            * Columns should include: <strong>Name</strong>, <strong>Phone</strong> (or mobile/number), and optional <strong>Address</strong>.<br>
            * <a href="data:text/csv;charset=utf-8,Name,Phone,Address%0AJohn%20Doe,%2B919876543210,123%20Street%20Name%0AJane%20Smith,%2B919876543211,456%20Avenue" download="leads_template.csv" style="color: var(--lime); font-weight: 700; text-decoration: underline;">Download Sample CSV Template</a>
          </div>
          
          <button type="submit" class="btn-submit" style="width:100%; font-family: inherit; font-weight:700; background: var(--lime); color: var(--bg-main); border: none; border-radius: 6px; padding: 10px; cursor: pointer; transition: all 0.2s;">Upload & Import Leads</button>
        </form>
      </div>

      <script>
      function showUploadMode(mode) {
        const btnSingle = document.getElementById('btnSingleForm');
        const btnBulk = document.getElementById('btnBulkForm');
        const formSingle = document.getElementById('singleLeadForm');
        const formBulk = document.getElementById('bulkLeadForm');
        
        if (mode === 'single') {
          btnSingle.style.background = 'rgba(212,255,61,0.1)';
          btnSingle.style.color = 'var(--lime)';
          btnBulk.style.background = 'transparent';
          btnBulk.style.color = 'var(--text-muted)';
          formSingle.style.display = 'flex';
          formBulk.style.display = 'none';
        } else {
          btnBulk.style.background = 'rgba(212,255,61,0.1)';
          btnBulk.style.color = 'var(--lime)';
          btnSingle.style.background = 'transparent';
          btnSingle.style.color = 'var(--text-muted)';
          formSingle.style.display = 'none';
          formBulk.style.display = 'flex';
        }
      }

      function handleStatusUpdate(leadId, statusChoice, btn) {
        const row = btn.closest('tr');
        const buttons = row.querySelectorAll('button');
        buttons.forEach(b => b.disabled = true);
        
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '⏳ Sending...';
        
        const formData = new FormData();
        formData.append('action', 'submit_lead_status_ajax');
        formData.append('lead_id', leadId);
        formData.append('status_choice', statusChoice);
        
        fetch('invoice.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const msgText = data.message_text;
            const phone = data.phone;
            const gatewayUrl = data.gateway_url;
            const gatewayToken = data.gateway_token;
            
            if (gatewayUrl) {
              const payload = {
                to: phone,
                phone: phone,
                number: phone,
                body: msgText,
                message: msgText,
                token: gatewayToken,
                apikey: gatewayToken
              };
              
              fetch(gatewayUrl, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
              })
              .then(gwRes => {
                window.location.href = 'invoice.php?staff_success=' + encodeURIComponent('Lead status updated and WhatsApp message sent successfully!');
              })
              .catch(gwErr => {
                console.error(gwErr);
                window.location.href = 'invoice.php?staff_warning=' + encodeURIComponent('Status updated in DB, but WhatsApp message failed to deliver.');
              });
            } else {
              const waUrl = 'https://api.whatsapp.com/send?phone=' + phone + '&text=' + encodeURIComponent(msgText);
              window.open(waUrl, '_blank');
              window.location.href = 'invoice.php?staff_success=' + encodeURIComponent('Status updated in DB. WhatsApp link opened in a new tab.');
            }
          } else {
            alert('Error: ' + (data.error || 'Failed to update status'));
            btn.innerHTML = originalHtml;
            buttons.forEach(b => b.disabled = false);
          }
        })
        .catch(err => {
          console.error(err);
          alert('Failed to connect to the server.');
          btn.innerHTML = originalHtml;
          buttons.forEach(b => b.disabled = false);
        });
      }
      </script>

      <!-- Active Call List Card -->
      <div class="card" style="background: var(--bg-surface); border: 1px solid var(--border-color); padding: 24px; border-radius: 16px; display: flex; flex-direction: column;">
        <h3 style="color:#fff; margin-bottom: 6px; font-size:16px; font-weight: 700;">📋 Assigned Leads & Follow-ups</h3>
        <p style="font-size:12px; color:var(--text-muted); margin-bottom: 20px;">Review lead details, call manually, and click the status button below to send automatic WhatsApp updates.</p>
        
        <div style="flex-grow: 1; overflow-x: auto;">
          <?php
            $leads_stmt = $pdo->prepare("SELECT * FROM leads WHERE created_by = ? ORDER BY id DESC");
            $leads_stmt->execute([$_SESSION['user_id'] ?? 0]);
            $staff_leads = $leads_stmt->fetchAll();
          ?>
          <?php if (empty($staff_leads)): ?>
            <div style="text-align: center; padding: 40px 10px; border: 1px dashed var(--border-color); border-radius: 8px; background: rgba(0,0,0,0.1);">
              <div style="font-size: 24px; margin-bottom: 6px;">📞</div>
              <div style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">No Leads Uploaded Yet</div>
              <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Enter a new lead on the left to start calling.</div>
            </div>
          <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
              <thead>
                <tr style="border-bottom: 1.5px solid var(--border-color); background: rgba(0,0,0,0.15);">
                  <th style="padding: 10px; font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Lead Details</th>
                  <th style="padding: 10px; font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; text-align: center; width: 110px;">Status</th>
                  <th style="padding: 10px; font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; text-align: center; width: 330px;">Action / Send Message</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($staff_leads as $lead): ?>
                  <tr style="border-bottom: 1px solid var(--border-color-soft);" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px 10px; font-size: 13px; color: var(--text-primary);">
                      <strong style="color: #fff; font-size: 13.5px;"><?= htmlspecialchars($lead['name']) ?></strong><br>
                      <span style="font-size:11.5px; color: var(--lime); font-family: var(--font-mono); font-weight: 600;"><?= htmlspecialchars($lead['phone']) ?></span><br>
                      <?php if (!empty($lead['address'])): ?>
                        <span style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px; line-height: 1.3;">📍 <?= htmlspecialchars($lead['address']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px 10px; text-align: center; vertical-align: middle;">
                      <?php if ($lead['status'] === 'pending'): ?>
                        <span style="display:inline-block; font-size:10px; padding:3px 8px; border-radius:12px; font-weight:700; background:rgba(255,255,255,0.05); color:#94a3b8; border:1px solid rgba(255,255,255,0.1);">Pending</span>
                      <?php elseif ($lead['status'] === 'no_response'): ?>
                        <span style="display:inline-block; font-size:10px; padding:3px 8px; border-radius:12px; font-weight:700; background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3);">No Response</span>
                      <?php elseif ($lead['status'] === 'interested'): ?>
                        <span style="display:inline-block; font-size:10px; padding:3px 8px; border-radius:12px; font-weight:700; background:rgba(52,211,153,0.15); color:#34d399; border:1px solid rgba(52,211,153,0.3);">Interested</span>
                      <?php elseif ($lead['status'] === 'not_interested'): ?>
                        <span style="display:inline-block; font-size:10px; padding:3px 8px; border-radius:12px; font-weight:700; background:rgba(244,63,94,0.15); color:#f43f5e; border:1px solid rgba(244,63,94,0.3);">Not Interested</span>
                      <?php endif; ?>
                        <td style="padding: 12px 10px; text-align: center; vertical-align: middle;">
                      <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                        <!-- Button 1: No Response -->
                        <button type="button" onclick="handleStatusUpdate(<?= $lead['id'] ?>, 'no_response', this)" style="padding: 6px 10px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); color: #e2e8f0; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.08)';" onmouseout="this.style.background='rgba(255,255,255,0.04)';">
                          📞 No Response
                        </button>

                        <!-- Button 2: Interested -->
                        <button type="button" onclick="handleStatusUpdate(<?= $lead['id'] ?>, 'interested', this)" style="padding: 6px 10px; background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.2); color: #34d399; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(52,211,153,0.18)';" onmouseout="this.style.background='rgba(52,211,153,0.1)';">
                          👍 Interested
                        </button>

                        <!-- Button 3: Not Interested -->
                        <button type="button" onclick="handleStatusUpdate(<?= $lead['id'] ?>, 'not_interested', this)" style="padding: 6px 10px; background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.2); color: #f43f5e; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(244,63,94,0.18)';" onmouseout="this.style.background='rgba(244,63,94,0.1)';">
                          👎 Not Interested
                        </button>
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
<?php else: ?>
  <div class="container">

  <?php if(!empty($success)): ?>
    <div class="alert success">✅ Invoice generated and emailed successfully to the client!</div>
  <?php elseif(!empty($registered)): ?>
    <div class="alert success">✅ Invoice registered successfully in the billing ledger!</div>
  <?php elseif(!empty($error)): ?>
    <div class="alert error">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="send_invoice.php" id="invoiceForm" enctype="multipart/form-data">

    <!-- Client Details -->
    <div class="card">
      <div class="card-title">Client Information</div>
      
      <!-- Autofill Glassmorphic Alert Banner -->
      <div id="autofillBanner" style="display: none; background: rgba(43, 108, 176, 0.08); border: 1.5px solid rgba(43, 108, 176, 0.2); border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; font-size: 13.5px; font-weight: 600; color: #2b6cb0; align-items: center; justify-content: space-between; gap: 12px; transition: all 0.3s ease;">
        <span>✨ Registered client profile found! Do you want to autofill company, phone, and email details?</span>
        <div style="display: flex; gap: 8px; flex-shrink: 0;">
          <button type="button" onclick="triggerAutofill()" style="background: #2b6cb0; color:#fff; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; font-family: inherit;">Yes, Autofill</button>
          <button type="button" onclick="dismissAutofill()" style="background: transparent; color:#4a5568; border:1px solid #cbd5e0; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; font-family: inherit;">No</button>
        </div>
      </div>

      <div class="grid-2" style="margin-bottom:16px">
        <div class="form-group">
          <label>Client Name *</label>
          <input type="text" name="client_name" id="clientNameInput" placeholder="Full Name" list="clientsList" autocomplete="off" required>
          <datalist id="clientsList">
            <?php foreach ($all_clients as $c): ?>
              <option value="<?= htmlspecialchars($c['client_name']) ?>"></option>
            <?php endforeach; ?>
          </datalist>
        </div>
        <div class="form-group">
          <label>Company Name</label>
          <input type="text" name="company_name" placeholder="Company / Organisation">
        </div>
      </div>
      <div class="grid-3">
        <div class="form-group">
          <label>Phone Number *</label>
          <input type="tel" name="phone" placeholder="+91 XXXXX XXXXX" required>
        </div>
        <div class="form-group">
          <label>WhatsApp Number</label>
          <input type="tel" name="whatsapp" placeholder="e.g. +91 98765 43210">
        </div>
        <div class="form-group">
          <label>Email Address(es) *</label>
          <div id="emailList">
            <div class="email-row">
              <input type="email" name="emails[]" placeholder="client@example.com" required>
            </div>
          </div>
          <button type="button" class="btn-add-email" onclick="addEmail()">+ Add Another Email</button>
        </div>
      </div>
    </div>

    <!-- Invoice Details -->
    <div class="card">
      <div class="card-title">Invoice Details</div>
      
      <!-- Sender Account Selector Dropdown -->
      <div class="form-group" style="margin-bottom: 20px;">
        <label>Send Outgoing Email From *</label>
        <select name="smtp_account_id" required>
          <?php foreach ($smtp_accounts as $acct): ?>
            <option value="<?= $acct['id'] ?>" <?= $acct['is_default'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($acct['display_name']) ?> (<?= htmlspecialchars($acct['smtp_username']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid-3">
        <div class="form-group">
          <label>Invoice Number *</label>
          <input type="text" name="invoice_number" placeholder="TEH-2026-133" value="<?= htmlspecialchars($next_invoice_number) ?>" required>
        </div>
        <div class="form-group">
          <label>Invoice Date *</label>
          <input type="date" name="invoice_date" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label>Due Date</label>
          <input type="date" name="due_date">
        </div>
      </div>
    </div>

    <!-- Line Items -->
    <div class="card">
      <div class="card-title">Line Items</div>
      <table class="items-table">
        <thead>
          <tr>
            <th class="col-desc">Description</th>
            <th class="col-qty">Qty</th>
            <th class="col-price">Unit Price (₹)</th>
            <th class="col-expiry">Expiry Date</th>
            <th class="col-total">Total</th>
            <th class="col-del"></th>
          </tr>
        </thead>
        <tbody id="lineItems">
          <tr>
            <td><input type="text" name="items[0][desc]" placeholder="Service / Product description" required></td>
            <td><input type="number" name="items[0][qty]" value="1" min="1" class="qty" oninput="calcRow(this)"></td>
            <td><input type="number" name="items[0][price]" placeholder="0.00" step="0.01" class="price" oninput="calcRow(this)"></td>
            <td><input type="date" name="items[0][expiry]"></td>
            <td class="col-total"><span class="row-total">₹0.00</span></td>
            <td class="col-del"><button type="button" class="btn-icon" onclick="removeRow(this)" title="Remove">✕</button></td>
          </tr>
        </tbody>
      </table>
      <button type="button" class="btn-add-item" onclick="addItem()">+ Add Line Item</button>

      <div class="totals-box">
        <div class="totals-inner">
          <div class="totals-row"><span>Subtotal</span><span id="subtotal">₹0.00</span></div>
          <div class="totals-row grand"><span>Grand Total</span><span id="grandTotal">₹0.00</span></div>
        </div>
      </div>
    </div>

    <!-- Payment Summary -->
    <div class="card">
      <div class="card-title">Payment Summary</div>
      <div class="amounts-grid">
        <div class="amount-box advance">
          <div class="label">Advance Received (₹)</div>
          <input type="number" name="advance_amount" id="advanceAmt" placeholder="0.00" step="0.01" value="0" oninput="calcPending()">
        </div>
        <div class="amount-box pending">
          <div class="label">Pending Balance Due (₹)</div>
          <input type="number" name="pending_amount" id="pendingAmt" placeholder="0.00" step="0.01" readonly>
        </div>
      </div>
    </div>

    <!-- Access Credentials -->
    <div class="card">
      <div class="card-title">Access Credentials <span style="font-size:11px;color:#718096;font-weight:400;text-transform:none;letter-spacing:0">(optional – sent securely in email)</span></div>

      <div style="margin-bottom:18px">
        <div style="font-size:12px;font-weight:700;color:#2b6cb0;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;padding:6px 10px;background:#ebf4ff;border-radius:6px">🌐 Website</div>
        <div class="grid-2">
          <div class="form-group">
            <label>Website Link</label>
            <input type="text" name="web_link" placeholder="https://yoursite.com">
          </div>
          <div class="form-group">
            <label>Source File Link (Drive)</label>
            <input type="text" name="source_link" placeholder="https://drive.google.com/...">
          </div>
        </div>
      </div>

      <div style="margin-bottom:18px">
        <div style="font-size:12px;font-weight:700;color:#2b6cb0;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;padding:6px 10px;background:#ebf4ff;border-radius:6px">🔧 Admin Panel</div>
        <div class="grid-3">
          <div class="form-group">
            <label>Admin Link</label>
            <input type="text" name="admin_link" placeholder="https://yoursite.com/admin">
          </div>
          <div class="form-group">
            <label>ID / Username</label>
            <input type="text" name="admin_id" placeholder="admin">
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="text" name="admin_pass" placeholder="••••••••">
          </div>
        </div>
      </div>

      <div>
        <div style="font-size:12px;font-weight:700;color:#2b6cb0;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;padding:6px 10px;background:#ebf4ff;border-radius:6px">✉️ Email Account</div>
        <div class="grid-3">
          <div class="form-group">
            <label>Email Link</label>
            <input type="text" name="email_link" placeholder="https://mail.yoursite.com">
          </div>
          <div class="form-group">
            <label>Email ID</label>
            <input type="text" name="email_id" placeholder="you@yoursite.com">
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="text" name="email_pass" placeholder="••••••••">
          </div>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div class="card">
      <div class="card-title">Additional Notes</div>
      <div class="form-group">
        <textarea name="notes" rows="3" placeholder="Payment terms, bank details, or any other notes for the client..."><?= htmlspecialchars($settings['company_notes_default'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- Attachments -->
    <div class="card">
      <div class="card-title">Attachments <span style="font-size:11px;color:#718096;font-weight:400;text-transform:none;letter-spacing:0">(optional – files attached individually)</span></div>
      <div class="form-group">
        <label>Select Files</label>
        <div class="upload-box" id="uploadBox" onclick="document.getElementById('attachInput').click()">
          <div class="upload-icon">📁</div>
          <div class="upload-text">Click to select files or drag &amp; drop here</div>
          <div class="upload-sub">Any file type • Multiple files allowed</div>
        </div>
        <input type="file" id="attachInput" name="attachments[]" multiple style="display:none" onchange="showFiles(this)">
        <div id="fileList" style="margin-top:10px"></div>
      </div>
    </div>

    <!-- Hidden inputs for form transport -->
    <input type="hidden" name="invoice_pdf_base64" id="invoice_pdf_base64">
    <input type="hidden" name="register_only" id="register_only" value="0">

    <div class="submit-row">
      <button type="button" class="btn-pdf" onclick="downloadPDF()">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        Download PDF
      </button>
      <button type="button" class="btn-whatsapp" onclick="sendWhatsApp()">
        <svg fill="currentColor" viewBox="0 0 448 512" style="width: 20px; height: 20px;"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
        Send via WhatsApp
      </button>
      <button type="button" class="btn-register" onclick="registerBillOnly()">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        Register Bill Only
      </button>
      <button type="button" class="btn-submit" onclick="sendEmailOnly()">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Send Invoice to Client
      </button>
      <button type="button" class="btn-both" onclick="sendBothEmailAndWhatsApp()">
        📧 &amp; 💬 Send Email &amp; WhatsApp
      </button>
    </div>
  </form>

</div>

<script>
// Dynamic Branding Configurations from Database
const COMPANY_NAME = <?= json_encode($settings['company_name']) ?>;
const COMPANY_TAGLINE = <?= json_encode($settings['company_tagline'] ?? '') ?>;
const COMPANY_PHONE = <?= json_encode($settings['company_phone'] ?? '') ?>;
const COMPANY_EMAIL = <?= json_encode($settings['company_email'] ?? '') ?>;
const COMPANY_WEBSITE = <?= json_encode($settings['company_website'] ?? '') ?>;
const WHATSAPP_GATEWAY_TYPE = <?= json_encode($settings['whatsapp_gateway_type'] ?? 'browser') ?>;
const TEMPLATE_INVOICE_CREATE = <?= json_encode($settings['template_invoice_create'] ?? '') ?>;
const TEMPLATE_PAYMENT_RECEIVE = <?= json_encode($settings['template_payment_receive'] ?? '') ?>;
const TEMPLATE_ESTIMATE = <?= json_encode($settings['template_estimate'] ?? '') ?>;

// Client Database & Smart Autofill Logic
const REGISTERED_CLIENTS = <?= json_encode($all_clients) ?>;
let matchedClient = null;
const clientInput = document.getElementById('clientNameInput');
const autofillBanner = document.getElementById('autofillBanner');

function checkClientMatch() {
  const val = clientInput.value.trim().toLowerCase();
  matchedClient = REGISTERED_CLIENTS.find(c => c.client_name.toLowerCase() === val);
  if (matchedClient) {
    autofillBanner.style.display = 'flex';
  } else {
    autofillBanner.style.display = 'none';
  }
}

if (clientInput) {
  clientInput.addEventListener('input', checkClientMatch);
  clientInput.addEventListener('change', checkClientMatch);
}

function triggerAutofill() {
  if (matchedClient) {
    // Populate company
    const compInput = document.querySelector('[name="company_name"]');
    if (compInput) compInput.value = matchedClient.company_name || '';
    
    // Populate phone
    const phoneInput = document.querySelector('[name="phone"]');
    if (phoneInput) phoneInput.value = matchedClient.phone || '';
    
    // Populate whatsapp
    const whatsappInput = document.querySelector('[name="whatsapp"]');
    if (whatsappInput) whatsappInput.value = matchedClient.whatsapp || '';
    
    // Populate emails
    const emailList = document.getElementById('emailList');
    if (emailList && matchedClient.emails) {
      emailList.innerHTML = ''; // Clear existing inputs
      const emailsArr = matchedClient.emails.split(',').map(e => e.trim()).filter(e => e !== '');
      emailsArr.forEach((email, idx) => {
        const div = document.createElement('div');
        div.className = 'email-row';
        if (idx === 0) {
          div.innerHTML = `<input type="email" name="emails[]" value="${email}" placeholder="client@example.com" required>`;
        } else {
          div.innerHTML = `<input type="email" name="emails[]" value="${email}" placeholder="another@example.com">
            <button type="button" class="btn-icon" onclick="this.parentElement.remove()">✕</button>`;
        }
        emailList.appendChild(div);
      });
    }
    
    // Trigger visual highlight transition feedback on inputs
    const highlightTargets = [
      document.querySelector('[name="company_name"]'),
      document.querySelector('[name="phone"]'),
      document.querySelector('[name="whatsapp"]'),
      ...(document.querySelectorAll('[name="emails[]"]') || [])
    ];
    highlightTargets.forEach(el => {
      if (el) {
        el.style.borderColor = '#38a169';
        el.style.boxShadow = '0 0 0 3px rgba(56, 161, 105, 0.25)';
        el.style.transition = 'all 0.2s';
        setTimeout(() => {
          el.style.borderColor = '';
          el.style.boxShadow = '';
        }, 1500);
      }
    });
  }
  dismissAutofill();
}

function dismissAutofill() {
  if (autofillBanner) {
    autofillBanner.style.display = 'none';
  }
}

let itemIndex = 1;

function addEmail() {
  const div = document.createElement('div');
  div.className = 'email-row';
  div.innerHTML = `<input type="email" name="emails[]" placeholder="another@example.com">
    <button type="button" class="btn-icon" onclick="this.parentElement.remove()">✕</button>`;
  document.getElementById('emailList').appendChild(div);
}

function addItem() {
  const i = itemIndex++;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="text" name="items[${i}][desc]" placeholder="Description" required></td>
    <td><input type="number" name="items[${i}][qty]" value="1" min="1" class="qty" oninput="calcRow(this)"></td>
    <td><input type="number" name="items[${i}][price]" placeholder="0.00" step="0.01" class="price" oninput="calcRow(this)"></td>
    <td><input type="date" name="items[${i}][expiry]"></td>
    <td class="col-total"><span class="row-total">₹0.00</span></td>
    <td class="col-del"><button type="button" class="btn-icon" onclick="removeRow(this)">✕</button></td>`;
  document.getElementById('lineItems').appendChild(tr);
}

function removeRow(btn) {
  const rows = document.querySelectorAll('#lineItems tr');
  if (rows.length > 1) { btn.closest('tr').remove(); calcTotals(); }
}

function calcRow(input) {
  const tr = input.closest('tr');
  const qty = parseFloat(tr.querySelector('.qty').value) || 0;
  const price = parseFloat(tr.querySelector('.price').value) || 0;
  tr.querySelector('.row-total').textContent = '₹' + (qty * price).toFixed(2);
  calcTotals();
}

function calcTotals() {
  let sub = 0;
  document.querySelectorAll('#lineItems tr').forEach(tr => {
    const qty = parseFloat(tr.querySelector('.qty').value) || 0;
    const price = parseFloat(tr.querySelector('.price').value) || 0;
    sub += qty * price;
  });
  document.getElementById('subtotal').textContent = '₹' + sub.toFixed(2);
  document.getElementById('grandTotal').textContent = '₹' + sub.toFixed(2);
  calcPending();
}

function calcPending() {
  const grand = parseFloat(document.getElementById('grandTotal').textContent.replace('₹','')) || 0;
  const adv = parseFloat(document.getElementById('advanceAmt').value) || 0;
  document.getElementById('pendingAmt').value = Math.max(0, grand - adv).toFixed(2);
}

function showFiles(input) {
  const list = document.getElementById('fileList');
  list.innerHTML = '';
  Array.from(input.files).forEach(f => {
    const size = f.size > 1048576 ? (f.size/1048576).toFixed(1)+' MB' : (f.size/1024).toFixed(0)+' KB';
    const chip = document.createElement('span');
    chip.className = 'file-chip';
    chip.innerHTML = `📄 <span title="${f.name}">${f.name}</span> <small style="color:#718096">${size}</small>`;
    list.appendChild(chip);
  });
  if(input.files.length) {
    document.querySelector('.upload-text').textContent = input.files.length + ' file(s) selected';
    document.querySelector('.upload-box').style.borderColor = '#48bb78';
    document.querySelector('.upload-box').style.background = '#f0fff4';
  }
}

function sendWhatsApp() {
  const f = document.getElementById('invoiceForm');
  const get = n => f.querySelector('[name="' + n + '"]')?.value || '';
  
  const clientName = get('client_name');
  if (!clientName) {
    alert("Please fill in the Client Name before sending via WhatsApp.");
    f.querySelector('[name="client_name"]')?.focus();
    return;
  }
  
  const phone = get('phone');
  const whatsapp = get('whatsapp');
  const targetNumber = (whatsapp || phone || '').trim();
  
  if (!targetNumber) {
    alert("Please fill in either the Phone Number or WhatsApp Number before sending.");
    f.querySelector('[name="phone"]')?.focus();
    return;
  }
  
  const cleanPhone = targetNumber.replace(/[^0-9]/g, '');
  const invNum = get('invoice_number') || 'INVOICE';
  const invDate = get('invoice_date');
  const dueDate = get('due_date');
  const advance = parseFloat(get('advance_amount')) || 0;
  const pending = parseFloat(get('pending_amount')) || 0;
  
  let grandTotal = 0;
  let itemsSummary = '';
  document.querySelectorAll('#lineItems tr').forEach(function(tr) {
    const desc = tr.querySelector('[name*="[desc]"]')?.value || '';
    const qty = parseFloat(tr.querySelector('.qty')?.value) || 0;
    const price = parseFloat(tr.querySelector('.price')?.value) || 0;
    const total = qty * price;
    grandTotal += total;
    if (desc) {
      itemsSummary += `_• ${desc} (Qty: ${qty})_ — *Rs.${total.toFixed(2)}*\n`;
    }
  });
  
  const fmtDate = v => v ? new Date(v).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'}) : '';
  const notes = get('notes');
  
  const formatTemplate = (tpl, vars) => {
    let res = tpl;
    for (const [key, val] of Object.entries(vars)) {
      res = res.replaceAll('{' + key + '}', val);
    }
    return res;
  };
  
  let msg = '';
  if (typeof TEMPLATE_INVOICE_CREATE !== 'undefined' && TEMPLATE_INVOICE_CREATE) {
    msg = formatTemplate(TEMPLATE_INVOICE_CREATE, {
      'client_name': clientName,
      'company_name': COMPANY_NAME,
      'invoice_number': invNum,
      'invoice_date': fmtDate(invDate),
      'due_date': dueDate ? fmtDate(dueDate) : '',
      'grand_total': grandTotal.toFixed(2),
      'advance_amount': advance.toFixed(2),
      'pending_amount': pending.toFixed(2),
      'web_link': '',
      'amount_paid': advance.toFixed(2),
      'notes': notes || ''
    });
  } else {
    msg = `*Dear ${clientName},*\n\n`
            + `⚡ *Invoice Summary from ${COMPANY_NAME}* ⚡\n\n`
            + `*Invoice Number:* ${invNum}\n`
            + `*Invoice Date:* ${fmtDate(invDate)}\n`
            + (dueDate ? `*Due Date:* ${fmtDate(dueDate)}\n` : '')
            + `\n*Line Items:*\n${itemsSummary}`
            + `\n*Grand Total:* Rs.${grandTotal.toFixed(2)}\n`
            + `*Advance Paid:* Rs.${advance.toFixed(2)}\n`
            + `*Balance Due:* *Rs.${pending.toFixed(2)}*\n`;
            
    if (notes) {
      msg += `\n*Notes:* _${notes}_\n`;
    }
    msg += `\nThank you for your business! If you have any questions, please feel free to reach out.`;
  }
  
  if (typeof WHATSAPP_GATEWAY_TYPE !== 'undefined' && WHATSAPP_GATEWAY_TYPE === 'gateway') {
    showOverlayLoader("Sending WhatsApp Message", "Generating in-memory PDF invoice...");
    
    generateInvoicePdfBase64().then(pdfBase64 => {
      document.getElementById('globalOverlayText').innerHTML = "Transmitting PDF invoice to <strong>" + targetNumber + "</strong>...";
      
      const formData = new FormData();
      formData.append('phone', cleanPhone);
      formData.append('message', msg);
      formData.append('pdf', pdfBase64);
      formData.append('filename', 'Invoice-' + invNum + '.pdf');
      
      return fetch('send_whatsapp_background.php', {
        method: 'POST',
        body: formData
      });
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        updateOverlaySuccess("Dispatched!", 'Invoice PDF and summary successfully sent to WhatsApp.');
      } else {
        throw new Error(data.error || 'Unknown gateway response');
      }
    })
    .catch(err => {
      updateOverlayFailure("Dispatch Failed", `<span style="display:block; margin-bottom: 12px; font-weight:600; color:#e53e3e; font-size:12px;">${err.message}</span>
        Background send failed. Would you like to fallback to a manual browser redirect?`);
        
      const box = document.querySelector('#globalOverlay > div');
      if (box) {
        const fallbackBtn = document.createElement('button');
        fallbackBtn.style.background = '#128c7e';
        fallbackBtn.style.color = '#fff';
        fallbackBtn.style.border = 'none';
        fallbackBtn.style.padding = '8px 16px';
        fallbackBtn.style.borderRadius = '6px';
        fallbackBtn.style.fontSize = '12px';
        fallbackBtn.style.fontWeight = '700';
        fallbackBtn.style.cursor = 'pointer';
        fallbackBtn.style.fontFamily = 'inherit';
        fallbackBtn.style.marginTop = '15px';
        fallbackBtn.style.marginLeft = '10px';
        fallbackBtn.textContent = '🌐 Try Manual Redirect';
        fallbackBtn.onclick = () => {
          const overlay = document.getElementById('globalOverlay');
          if (overlay) overlay.remove();
          const waUrl = "https://api.whatsapp.com/send?phone=" + encodeURIComponent(cleanPhone) + "&text=" + encodeURIComponent(msg);
          window.open(waUrl, '_blank');
        };
        box.appendChild(fallbackBtn);
      }
    });
  } else {
    const waUrl = "https://api.whatsapp.com/send?phone=" + encodeURIComponent(cleanPhone) + "&text=" + encodeURIComponent(msg);
    window.open(waUrl, '_blank');
  }
}

// Reusable Glassmorphic Loader Overlay Helpers
function showOverlayLoader(title, text) {
  if (!document.getElementById('waAnimationStyle')) {
    const style = document.createElement('style');
    style.id = 'waAnimationStyle';
    style.textContent = `
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    `;
    document.head.appendChild(style);
  }

  let overlay = document.getElementById('globalOverlay');
  if (overlay) overlay.remove();

  overlay = document.createElement('div');
  overlay.id = 'globalOverlay';
  overlay.style.position = 'fixed';
  overlay.style.top = '0';
  overlay.style.left = '0';
  overlay.style.width = '100vw';
  overlay.style.height = '100vh';
  overlay.style.background = 'rgba(26, 54, 93, 0.4)';
  overlay.style.backdropFilter = 'blur(10px)';
  overlay.style.display = 'flex';
  overlay.style.alignItems = 'center';
  overlay.style.justifyContent = 'center';
  overlay.style.zIndex = '99999';
  overlay.style.transition = 'all 0.3s ease';

  const box = document.createElement('div');
  box.style.background = '#fff';
  box.style.padding = '32px';
  box.style.borderRadius = '16px';
  box.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
  box.style.textAlign = 'center';
  box.style.maxWidth = '400px';
  box.style.width = '90%';

  box.innerHTML = `
    <div id="globalOverlaySpinner" style="border: 4px solid #e2e8f0; border-top: 4px solid #1a365d; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
    <h3 id="globalOverlayTitle" style="color: #1a365d; font-size: 18px; font-weight: 700; margin-bottom: 8px;">${title}</h3>
    <p id="globalOverlayText" style="font-size: 13.5px; color: #4a5568;">${text}</p>
    <button id="globalOverlayCloseBtn" style="display: none; background: #e53e3e; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; font-family: inherit; margin-top: 15px;">Cancel</button>
  `;
  overlay.appendChild(box);
  document.body.appendChild(overlay);
}

function updateOverlaySuccess(title, text, autoDismissMs = 2200) {
  const spinner = document.getElementById('globalOverlaySpinner');
  if (spinner) {
    spinner.outerHTML = `
      <div style="width: 50px; height: 50px; background: #c6f6d5; color: #22543d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 20px; font-weight: bold;">
        ✓
      </div>
    `;
  }
  const titleEl = document.getElementById('globalOverlayTitle');
  if (titleEl) {
    titleEl.textContent = title;
    titleEl.style.color = '#22543d';
  }
  const textEl = document.getElementById('globalOverlayText');
  if (textEl) {
    textEl.innerHTML = text;
  }

  if (autoDismissMs > 0) {
    setTimeout(() => {
      const overlay = document.getElementById('globalOverlay');
      if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
      }
    }, autoDismissMs);
  }
}

function updateOverlayFailure(title, text) {
  const spinner = document.getElementById('globalOverlaySpinner');
  if (spinner) {
    spinner.outerHTML = `
      <div style="width: 50px; height: 50px; background: #fed7d7; color: #742a2a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 20px;">
        ✕
      </div>
    `;
  }
  const titleEl = document.getElementById('globalOverlayTitle');
  if (titleEl) {
    titleEl.textContent = title;
    titleEl.style.color = '#c53030';
  }
  const textEl = document.getElementById('globalOverlayText');
  if (textEl) {
    textEl.innerHTML = text;
  }

  const closeBtn = document.getElementById('globalOverlayCloseBtn');
  if (closeBtn) {
    closeBtn.style.display = 'inline-block';
    closeBtn.onclick = () => {
      const overlay = document.getElementById('globalOverlay');
      if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
      }
    };
  }
}

function downloadPDF() {
  const html = getInvoiceHtml(false);
  const w = window.open('', '_blank');
  w.document.open();
  w.document.write(html);
  w.document.close();
}

// Generate Raw HTML Invoice Structure
function getInvoiceHtml(omitPrintScript = false) {
  const f = document.getElementById('invoiceForm');
  const get = n => f.querySelector('[name="' + n + '"]')?.value || '';
  const fmtDate = v => v ? new Date(v).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '';
  const invNum = get('invoice_number') || 'INVOICE';
  const invDate = fmtDate(get('invoice_date'));
  const dueDate = fmtDate(get('due_date'));
  const clientName = get('client_name');
  const company = get('company_name');
  const phone = get('phone');
  const advance = parseFloat(get('advance_amount')) || 0;
  const pending = parseFloat(get('pending_amount')) || 0;

  let sub = 0, rowsHtml = '';
  document.querySelectorAll('#lineItems tr').forEach(function(tr) {
    const desc = tr.querySelector('[name*="[desc]"]')?.value || '';
    const qty = parseFloat(tr.querySelector('.qty')?.value) || 0;
    const price = parseFloat(tr.querySelector('.price')?.value) || 0;
    const expiry = tr.querySelector('[name*="[expiry]"]')?.value || '';
    const total = qty * price;
    sub += total;
    rowsHtml += '<tr>'
      + '<td style="padding:9px 12px;border-bottom:1px solid #e2e8f0;font-size:13px">' + desc + '</td>'
      + '<td style="padding:9px 12px;border-bottom:1px solid #e2e8f0;font-size:13px;text-align:center">' + qty + '</td>'
      + '<td style="padding:9px 12px;border-bottom:1px solid #e2e8f0;font-size:13px;text-align:right">Rs.' + price.toFixed(2) + '</td>'
      + '<td style="padding:9px 12px;border-bottom:1px solid #e2e8f0;font-size:13px;text-align:center">' + (expiry ? fmtDate(expiry) : '&mdash;') + '</td>'
      + '<td style="padding:9px 12px;border-bottom:1px solid #e2e8f0;font-size:13px;text-align:right;font-weight:700">Rs.' + total.toFixed(2) + '</td>'
      + '</tr>';
  });

  const notes = get('notes');
  const notesHtml = notes
    ? '<tr><td colspan="2" style="background:#fffbeb;border-top:2px solid #f6e05e;padding:14px 20px">'
      + '<p style="font-size:11px;font-weight:700;text-transform:uppercase;color:#744210;margin-bottom:4px">Notes</p>'
      + '<p style="font-size:13px;color:#2d3748">' + notes.replace(/\n/g,'<br>') + '</p></td></tr>'
    : '';

  const webLink = get('web_link');
  const sourceLink = get('source_link');
  const adminLink = get('admin_link');
  const adminId = get('admin_id');
  const adminPass = get('admin_pass');
  const emailLink = get('email_link');
  const emailId = get('email_id');
  const emailPass = get('email_pass');

  let credsHtml = '';
  if (webLink || adminLink || emailLink) {
    credsHtml += '<tr><td style="background:#fff;padding:12px 28px">'
      + '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden">'
      + '<tr><td colspan="3" style="background:#1a365d;padding:8px 12px;font-size:11px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.6px">Access Credentials</td></tr>'
      + '<tr style="background:#ebf4ff">'
      + '<td style="padding:6px 12px;font-size:11px;font-weight:700;color:#2b6cb0;text-transform:uppercase;width:22%">Section</td>'
      + '<td style="padding:6px 12px;font-size:11px;font-weight:700;color:#2b6cb0;text-transform:uppercase;width:44%">Link</td>'
      + '<td style="padding:6px 12px;font-size:11px;font-weight:700;color:#2b6cb0;text-transform:uppercase;width:34%">Login Details</td>'
      + '</tr>';

    const makeCredRow = (icon, label, link, id, pass, extra = '') => {
      const idRow = id ? '<span style="font-size:11px;color:#718096">ID:</span> <strong style="color:#1a365d">' + id + '</strong><br>' : '';
      const passRow = pass ? '<span style="font-size:11px;color:#718096">Password:</span> <strong style="color:#c53030;font-family:monospace">' + pass + '</strong>' : '';
      const linkHtml = link ? '<a href="' + link + '" style="color:#2b6cb0;word-break:break-all;font-size:13px">' + link + '</a>' : '&mdash;';
      return '<tr style="border-top:1px solid #e2e8f0">'
        + '<td style="padding:9px 12px;font-size:13px;font-weight:700;color:#1a365d;vertical-align:top;white-space:nowrap">' + icon + ' ' + label + '</td>'
        + '<td style="padding:9px 12px;vertical-align:top">' + linkHtml + extra + '</td>'
        + '<td style="padding:9px 12px;font-size:13px;vertical-align:top">' + idRow + passRow + '</td>'
        + '</tr>';
    };

    const sourceExtra = sourceLink ? '<br><span style="font-size:11px;color:#718096">Source File: </span><a href="' + sourceLink + '" style="font-size:12px;color:#2b6cb0;word-break:break-all">' + sourceLink + '</a>' : '';

    if (webLink)   credsHtml += makeCredRow('🌐', 'Website', webLink, '', '', sourceExtra);
    if (adminLink) credsHtml += makeCredRow('🔧', 'Admin Panel', adminLink, adminId, adminPass);
    if (emailLink) credsHtml += makeCredRow('✉️', 'Email', emailLink, emailId, emailPass);

    credsHtml += '</table></td></tr>';
  }

  let html = '<!DOCTYPE html><html><head><meta charset="UTF-8">'
    + '<title>Invoice ' + invNum + '</title>'
    + '<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Arial,sans-serif;background:#fff;color:#1a202c}'
    + '@media print{@page{margin:10mm}body{margin:0}}'
    + '</style>  <link rel="stylesheet" href="2fa_dashboard_theme.css">
</head><body>'
    + '<table width="100%" cellpadding="0" cellspacing="0" style="max-width:720px;margin:20px auto;border-collapse:collapse">'
    + '<tr><td style="background:linear-gradient(135deg,#1a365d,#2b6cb0);border-radius:10px 10px 0 0;padding:22px 28px">'
    + '<table width="100%" cellpadding="0" cellspacing="0"><tr>'
    + '<td><p style="font-size:20px;font-weight:700;color:#fff;margin:0">⚡ ' + COMPANY_NAME + '</p><p style="font-size:11px;color:rgba(255,255,255,.7);margin:3px 0 0">' + COMPANY_TAGLINE + '</p></td>'
    + '<td align="right"><p style="font-size:22px;font-weight:800;color:rgba(255,255,255,.2);letter-spacing:2px;margin:0">INVOICE</p><p style="font-size:13px;color:#90cdf4;font-weight:600;margin:3px 0 0">' + invNum + '</p></td>'
    + '</tr></table></td></tr>'
    + '<tr><td style="background:#1a365d;padding:10px 28px">'
    + '<table width="100%" cellpadding="0" cellspacing="0"><tr>'
    + '<td style="font-size:13px;color:#90cdf4">Invoice Date: <strong style="color:#fff">' + invDate + '</strong></td>'
    + (dueDate ? '<td align="right" style="font-size:13px;color:#90cdf4">Due Date: <strong style="color:#fff">' + dueDate + '</strong></td>' : '<td></td>')
    + '</tr></table></td></tr>'
    + '<tr><td style="background:#fff;padding:22px 28px;border:1px solid #e2e8f0;border-top:none">'
    + '<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px"><tr>'
    + '<td style="background:#ebf4ff;border-left:4px solid #2b6cb0;border-radius:8px;padding:14px 18px">'
    + '<p style="font-size:11px;font-weight:700;text-transform:uppercase;color:#2b6cb0;margin:0">Bill To</p>'
    + '<p style="font-size:16px;font-weight:700;color:#1a365d;margin:4px 0 0">' + clientName + '</p>'
    + (company ? '<p style="font-size:13px;color:#4a5568;margin:2px 0 0">' + company + '</p>' : '')
    + '<p style="font-size:13px;color:#718096;margin:2px 0 0">&#128222; ' + phone + '</p>'
    + '</td></tr></table>'
    + '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:18px">'
    + '<thead><tr style="background:#ebf4ff">'
    + '<th style="padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;color:#2b6cb0;text-align:left">Description</th>'
    + '<th style="padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;color:#2b6cb0;text-align:center">Qty</th>'
    + '<th style="padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;color:#2b6cb0;text-align:right">Unit Price</th>'
    + '<th style="padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;color:#2b6cb0;text-align:center">Expiry</th>'
    + '<th style="padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;color:#2b6cb0;text-align:right">Total</th>'
    + '</tr></thead><tbody>' + rowsHtml + '</tbody></table>'
    + '<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:4px"><tr><td width="55%"></td>'
    + '<td width="45%"><table width="100%" cellpadding="0" cellspacing="0" style="background:#f7fafc;border-radius:8px;padding:14px 18px">'
    + '<tr><td style="font-size:13px;color:#718096;padding:4px 0">Grand Total</td><td align="right" style="font-size:13px;font-weight:600;color:#2d3748;padding:4px 0">Rs.' + sub.toFixed(2) + '</td></tr>'
    + '<tr><td style="font-size:13px;color:#276749;padding:4px 0">Advance Received</td><td align="right" style="font-size:13px;font-weight:600;color:#276749;padding:4px 0">Rs.' + advance.toFixed(2) + '</td></tr>'
    + '<tr><td colspan="2" style="padding:4px 0"><hr style="border:none;border-top:2px solid #2b6cb0"></td></tr>'
    + '<tr><td style="font-size:15px;font-weight:700;color:#c53030;padding:4px 0">Balance Due</td><td align="right" style="font-size:15px;font-weight:800;color:#c53030;padding:4px 0">Rs.' + pending.toFixed(2) + '</td></tr>'
    + '</table></td></tr>'
    + credsHtml
    + notesHtml
    + '</table>'
    + '</td></tr>'
    + '<tr><td style="background:#1a365d;border-radius:0 0 10px 10px;padding:16px 28px">'
    + '<table width="100%" cellpadding="0" cellspacing="0"><tr>'
    + '<td style="font-size:12px;color:rgba(255,255,255,.75);line-height:1.8"><strong style="color:#fff;font-size:13px">' + COMPANY_NAME + '</strong><br>&#128222; ' + COMPANY_PHONE + ' &nbsp;|&nbsp; &#9993; ' + COMPANY_EMAIL + '<br>&#127760; ' + COMPANY_WEBSITE + '</td>'
    + '<td align="right" style="font-size:11px;color:rgba(255,255,255,.5)">Thank you for your business!</td>'
    + '</tr></table></td></tr>'
    + '</table>';

  if (!omitPrintScript) {
    html += '<scr' + 'ipt>window.onload=function(){window.print();}</' + 'script>';
  }
  
  html += '</body></html>';
  return html;
}

// Generate in-memory PDF using html2pdf.js and return Base64 DataURI Promise
function generateInvoicePdfBase64() {
  return new Promise((resolve, reject) => {
    try {
      const htmlContent = getInvoiceHtml(true);
      const container = document.createElement('div');
      container.style.position = 'absolute';
      container.style.left = '-9999px';
      container.style.top = '-9999px';
      container.innerHTML = htmlContent;
      document.body.appendChild(container);

      const opt = {
        margin:       10,
        filename:     'Invoice.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      html2pdf().set(opt).from(container).outputPdf('datauristring').then(base64Uri => {
        document.body.removeChild(container);
        resolve(base64Uri);
      }).catch(err => {
        if (container.parentNode) {
          document.body.removeChild(container);
        }
        reject(err);
      });
    } catch (e) {
      reject(e);
    }
  });
}

// Combined Double Dispatch Coordinator (Email & WhatsApp)
function sendBothEmailAndWhatsApp() {
  const f = document.getElementById('invoiceForm');
  if (!f.reportValidity()) {
    return;
  }

  const clientName = f.querySelector('[name="client_name"]')?.value || '';
  const phone = f.querySelector('[name="phone"]')?.value || '';
  const whatsapp = f.querySelector('[name="whatsapp"]')?.value || '';
  const targetNumber = (whatsapp || phone || '').trim();
  
  if (!targetNumber) {
    alert("Please fill in either the Phone Number or WhatsApp Number before choosing combined sending.");
    f.querySelector('[name="phone"]')?.focus();
    return;
  }

  showOverlayLoader("Sending Email & WhatsApp", "Step 1 of 2: Generating PDF and dispatching on WhatsApp...");

  // First generate and send the WhatsApp PDF
  generateInvoicePdfBase64().then(pdfBase64 => {
    // Format message
    const get = n => f.querySelector('[name="' + n + '"]')?.value || '';
    const cleanPhone = targetNumber.replace(/[^0-9]/g, '');
    const invNum = get('invoice_number') || 'INVOICE';
    const invDate = get('invoice_date');
    const dueDate = get('due_date');
    const advance = parseFloat(get('advance_amount')) || 0;
    const pending = parseFloat(get('pending_amount')) || 0;
    
    let grandTotal = 0;
    let itemsSummary = '';
    document.querySelectorAll('#lineItems tr').forEach(function(tr) {
      const desc = tr.querySelector('[name*="[desc]"]')?.value || '';
      const qty = parseFloat(tr.querySelector('.qty')?.value) || 0;
      const price = parseFloat(tr.querySelector('.price')?.value) || 0;
      const total = qty * price;
      grandTotal += total;
      if (desc) {
        itemsSummary += `_• ${desc} (Qty: ${qty})_ — *Rs.${total.toFixed(2)}*\n`;
      }
    });
    
    const fmtDate = v => v ? new Date(v).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'}) : '';
    const notes = get('notes');
    
    let msg = `*Dear ${clientName},*\n\n`
            + `⚡ *Invoice Summary from ${COMPANY_NAME}* ⚡\n\n`
            + `*Invoice Number:* ${invNum}\n`
            + `*Invoice Date:* ${fmtDate(invDate)}\n`
            + (dueDate ? `*Due Date:* ${fmtDate(dueDate)}\n` : '')
            + `\n*Line Items:*\n${itemsSummary}`
            + `\n*Grand Total:* Rs.${grandTotal.toFixed(2)}\n`
            + `*Advance Paid:* Rs.${advance.toFixed(2)}\n`
            + `*Balance Due:* *Rs.${pending.toFixed(2)}*\n`;
            
    if (notes) {
      msg += `\n*Notes:* _${notes}_\n`;
    }
    
    msg += `\nThank you for your business! If you have any questions, please feel free to reach out.`;

    document.getElementById('globalOverlayText').innerHTML = "Step 1 of 2: Transmitting PDF to WhatsApp...";

    const formData = new FormData();
    formData.append('phone', cleanPhone);
    formData.append('message', msg);
    formData.append('pdf', pdfBase64);
    formData.append('filename', 'Invoice-' + invNum + '.pdf');

    // Keep the pdfBase64 locally so we can set it in form hidden field
    window.lastCompiledPdfBase64 = pdfBase64;

    return fetch('send_whatsapp_background.php', {
      method: 'POST',
      body: formData
    });
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success) {
      throw new Error(data.error || 'WhatsApp gateway error');
    }
    
    // WhatsApp succeeded! Now execute Step 2: Email and Database Save
    document.getElementById('globalOverlayText').innerHTML = "Step 2 of 2: Dispatched on WhatsApp! Now logging invoice and sending Email...";
    
    // Store in hidden input field
    document.getElementById('invoice_pdf_base64').value = window.lastCompiledPdfBase64 || '';
    
    setTimeout(() => {
      f.submit();
    }, 1000);
  })
  .catch(err => {
    // If WhatsApp failed, ask if they want to proceed with Email anyway
    updateOverlayFailure("WhatsApp Step Failed", `<span style="display:block; margin-bottom: 8px; font-weight:600; color:#e53e3e; font-size:12px;">${err.message}</span>
      WhatsApp dispatch failed. Would you like to skip WhatsApp and send Email only, or try manual WhatsApp fallback?`);

    const box = document.querySelector('#globalOverlay > div');
    if (box) {
      // 1. Skip WhatsApp and submit Email
      const skipBtn = document.createElement('button');
      skipBtn.style.background = '#2b6cb0';
      skipBtn.style.color = '#fff';
      skipBtn.style.border = 'none';
      skipBtn.style.padding = '8px 16px';
      skipBtn.style.borderRadius = '6px';
      skipBtn.style.fontSize = '12px';
      skipBtn.style.fontWeight = '700';
      skipBtn.style.cursor = 'pointer';
      skipBtn.style.fontFamily = 'inherit';
      skipBtn.style.marginTop = '15px';
      skipBtn.style.marginLeft = '10px';
      skipBtn.textContent = '📧 Send Email Only';
      skipBtn.onclick = () => {
        showOverlayLoader("Sending Email", "Logging invoice and sending SMTP Email...");
        document.getElementById('invoice_pdf_base64').value = window.lastCompiledPdfBase64 || '';
        setTimeout(() => {
          f.submit();
        }, 800);
      };
      box.appendChild(skipBtn);
    }
  });
}

// === Explicit Button Handlers (no more unreliable document.activeElement) ===

// "Send Invoice to Client" — compiles PDF, attaches it, then submits the form
function sendEmailOnly() {
  const f = document.getElementById('invoiceForm');
  if (!f.reportValidity()) return;

  // Reset register_only flag
  document.getElementById('register_only').value = '0';

  showOverlayLoader("Preparing Dispatch", "Compiling beautiful PDF invoice for email attachment...");

  generateInvoicePdfBase64().then(pdfBase64 => {
    document.getElementById('invoice_pdf_base64').value = pdfBase64;
    document.getElementById('globalOverlayText').textContent = "Logging transaction and transmitting SMTP Email...";
    
    setTimeout(() => {
      f.submit();
    }, 600);
  }).catch(err => {
    console.error("PDF generation failed:", err);
    updateOverlayFailure("PDF Generation Failed", 
      '<span style="display:block;margin-bottom:8px;font-size:12px;color:#e53e3e;font-weight:600">' + err.message + '</span>' +
      'The invoice email will be sent without a PDF attachment.');
    
    const box = document.querySelector('#globalOverlay > div');
    if (box) {
      const retryBtn = document.createElement('button');
      retryBtn.style.cssText = 'background:#2b6cb0;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;margin-top:15px;margin-right:8px';
      retryBtn.textContent = '🔄 Retry with PDF';
      retryBtn.onclick = () => { const o = document.getElementById('globalOverlay'); if(o) o.remove(); sendEmailOnly(); };
      box.appendChild(retryBtn);

      const skipBtn = document.createElement('button');
      skipBtn.style.cssText = 'background:#718096;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;margin-top:15px';
      skipBtn.textContent = '📧 Send Without PDF';
      skipBtn.onclick = () => {
        showOverlayLoader("Sending Email", "Transmitting invoice email without PDF attachment...");
        setTimeout(() => { f.submit(); }, 400);
      };
      box.appendChild(skipBtn);
    }
  });
}

// "Register Bill Only" — saves to ledger without sending any email
function registerBillOnly() {
  const f = document.getElementById('invoiceForm');
  if (!f.reportValidity()) return;

  document.getElementById('register_only').value = '1';
  document.getElementById('invoice_pdf_base64').value = '';

  showOverlayLoader("Registering Invoice", "Saving invoice to your billing ledger...");
  
  setTimeout(() => {
    f.submit();
  }, 400);
}

// Intercept Enter-key form submissions and redirect to sendEmailOnly()
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
  e.preventDefault();
  sendEmailOnly();
});

// Drag & drop support
const box = document.getElementById('uploadBox');
box.addEventListener('dragover', e => { e.preventDefault(); box.style.borderColor='#2b6cb0'; });
box.addEventListener('dragleave', () => { box.style.borderColor='#cbd5e0'; });
box.addEventListener('drop', e => {
  e.preventDefault();
  const dt = new DataTransfer();
  Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
  const inp = document.getElementById('attachInput');
  inp.files = dt.files;
  showFiles(inp);
});
</script>
<?php endif; ?>
<?php include_once __DIR__ . '/send_fast_widget.php'; ?>
</body>
</html>
