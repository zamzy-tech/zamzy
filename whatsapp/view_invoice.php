<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    die("Invalid invoice request.");
}

try {
    // 1. Fetch Invoice
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
    $stmt->execute([$id]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        die("Invoice not found.");
    }

    // 2. Fetch Invoice Items
    $stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();

    // 3. Fetch Company Branding/Settings
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$emails_array = array_filter(explode(',', $invoice['emails']));
$invoice_number = htmlspecialchars($invoice['invoice_number']);
$invoice_date = date('d M Y', strtotime($invoice['invoice_date']));
$due_date = $invoice['due_date'] ? date('d M Y', strtotime($invoice['due_date'])) : '';
$client_name = htmlspecialchars($invoice['client_name']);
$company_name = htmlspecialchars($invoice['company_name']);
$phone = htmlspecialchars($invoice['phone']);
$advance = floatval($invoice['advance_amount']);
$pending = floatval($invoice['pending_amount']);
$grand_total = floatval($invoice['grand_total']);
$notes = htmlspecialchars($invoice['notes']);

// Access credentials
$web_link = htmlspecialchars($invoice['web_link']);
$source_link = htmlspecialchars($invoice['source_link']);
$admin_link = htmlspecialchars($invoice['admin_link']);
$admin_id = htmlspecialchars($invoice['admin_id']);
$admin_pass = htmlspecialchars($invoice['admin_pass']);
$email_link = htmlspecialchars($invoice['email_link']);
$email_id = htmlspecialchars($invoice['email_id']);
$email_pass = htmlspecialchars($invoice['email_pass']);

$has_creds = $web_link || $admin_link || $email_link;

// Build line items
$items_html = '';
foreach ($items as $item) {
    $desc   = htmlspecialchars($item['description']);
    $qty    = floatval($item['qty']);
    $price  = floatval($item['price']);
    $expiry = !empty($item['expiry_date']) ? date('d M Y', strtotime($item['expiry_date'])) : '&mdash;';
    $total  = $qty * $price;
    $items_html .= "
    <tr class='item-row'>
      <td class='item-desc'>$desc</td>
      <td class='item-qty' align='center'>$qty</td>
      <td class='item-price' align='right'>Rs." . number_format($price, 2) . "</td>
      <td class='item-expiry' align='center'>$expiry</td>
      <td class='item-total' align='right'>Rs." . number_format($total, 2) . "</td>
    </tr>";
}

// Credentials rows
$cred_row = function($icon, $label, $link, $id, $pass, $extra = '') {
    $id_row   = $id   ? "<span class='cred-label'>ID:</span> <strong class='cred-val'>" . htmlspecialchars($id) . "</strong><br>" : '';
    $pass_row = $pass ? "<span class='cred-label'>Password:</span> <strong class='cred-pass'>" . htmlspecialchars($pass) . "</strong>" : '';
    $link_html = $link ? "<a href='" . htmlspecialchars($link) . "' class='cred-link' target='_blank'>" . htmlspecialchars($link) . "</a>" : '&mdash;';
    return "
        <tr class='cred-row'>
          <td class='cred-section'>$icon $label</td>
          <td class='cred-link-cell'>$link_html $extra</td>
          <td class='cred-detail-cell'>$id_row $pass_row</td>
        </tr>";
};

$source_extra = $source_link ? "<br><span class='cred-label'>Source File: </span><a href='" . htmlspecialchars($source_link) . "' class='cred-link' target='_blank'>" . htmlspecialchars($source_link) . "</a>" : '';

$creds_html = '';
if ($has_creds) {
    $creds_html .= "
  <tr class='creds-block-container'>
    <td style='padding:12px 28px' class='creds-wrapper-cell'>
      <table width='100%' cellpadding='0' cellspacing='0' class='creds-table'>
        <tr class='creds-header-row'>
          <td colspan='3' class='creds-header-title'>Access Credentials</td>
        </tr>
        <tr class='creds-sub-header-row'>
          <td class='creds-sub-title' style='width:22%'>Section</td>
          <td class='creds-sub-title' style='width:44%'>Link</td>
          <td class='creds-sub-title' style='width:34%'>Login Details</td>
        </tr>";

    if ($web_link)   $creds_html .= $cred_row('🌐', 'Website',      $web_link,   '',         '',          $source_extra);
    if ($admin_link) $creds_html .= $cred_row('🔧', 'Admin Panel',  $admin_link, $admin_id,  $admin_pass, '');
    if ($email_link) $creds_html .= $cred_row('✉️', 'Email',        $email_link, $email_id,  $email_pass, '');

    $creds_html .= "
      </table>
    </td>
  </tr>";
}

$notes_html = $notes ? "
  <tr class='notes-block-container'>
    <td colspan='2' class='notes-cell'>
      <p class='notes-title'>Notes</p>
      <p class='notes-body'>" . nl2br($notes) . "</p>
    </td>
  </tr>" : '';

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Invoice <?= $invoice_number ?></title>
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
      padding: 40px 16px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    /* Actions container */
    .actions-container {
      width: 100%;
      max-width: 720px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .btn-action {
      background: var(--lime);
      color: var(--bg-main);
      font-weight: 700;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 13px;
      cursor: pointer;
      border: none;
      transition: all 0.25s ease;
      font-family: inherit;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .btn-action:hover {
      background: var(--lime-deep);
      box-shadow: 0 4px 14px rgba(212, 255, 61, 0.25);
    }
    .btn-back {
      background: transparent;
      color: var(--text-secondary);
      border: 1px solid var(--border-color);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 13px;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 8px;
      transition: all 0.25s ease;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-back:hover {
      color: var(--text-primary);
      background: var(--border-color-soft);
    }

    .invoice-card {
      width: 100%;
      max-width: 720px;
      background-color: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
    }

    /* Sub-tables */
    .invoice-header-table {
      background: var(--bg-card);
      border-bottom: 1px solid var(--border-color);
      padding: 24px 28px;
      width: 100%;
    }
    .invoice-logo { font-size: 22px; font-weight: 700; color: var(--lime); font-family: var(--font-heading); }
    .invoice-tagline { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px; }
    .invoice-title-text { font-size: 22px; font-weight: 800; color: rgba(244, 244, 240, 0.15); letter-spacing: 2px; }
    .invoice-num { font-size: 13.5px; color: var(--lime); font-weight: 700; font-family: var(--font-mono); margin-top: 4px; }

    .invoice-dates-table {
      background: var(--bg-surface);
      border-bottom: 1px solid var(--border-color);
      padding: 12px 28px;
      width: 100%;
    }
    .date-lbl { font-size: 12.5px; color: var(--text-secondary); }
    .date-val { color: var(--text-primary); font-weight: 700; }

    .bill-to-box {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 18px;
      margin-bottom: 20px;
      width: 100%;
    }
    .bill-lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
    .bill-name { font-size: 17px; font-weight: 700; color: var(--text-primary); margin-top: 4px; font-family: var(--font-heading); }
    .bill-comp { font-size: 13px; color: var(--text-secondary); margin-top: 2px; }
    .bill-phone { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

    /* Table styling */
    .items-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid var(--border-color);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 20px;
    }
    .items-table th {
      background: var(--bg-card);
      color: var(--text-muted);
      font-weight: 700;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 10px 14px;
      border-bottom: 1.5px solid var(--border-color);
    }
    .items-table td {
      padding: 11px 14px;
      font-size: 13px;
      border-bottom: 1px solid var(--border-color-soft);
      color: var(--text-secondary);
    }
    .items-table tr:last-child td { border-bottom: none; }
    
    .total-card-table {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 16px 20px;
      margin-left: auto;
      width: 100%;
      max-width: 320px;
    }
    .total-row { display: flex; justify-content: space-between; font-size: 13px; color: var(--text-secondary); padding: 5px 0; }
    .total-row.due { color: #f87171; font-weight: 700; font-size: 15px; border-top: 1.5px solid var(--border-color); margin-top: 8px; padding-top: 8px; }

    /* Credentials */
    .creds-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid var(--border-color);
      border-radius: 12px;
      overflow: hidden;
      background: var(--bg-card);
    }
    .creds-header-title {
      background: var(--bg-surface);
      border-bottom: 1px solid var(--border-color);
      padding: 10px 16px;
      font-size: 11px;
      font-weight: 700;
      color: var(--lime);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .creds-sub-header-row {
      background: var(--bg-elev);
      border-bottom: 1px solid var(--border-color);
    }
    .creds-sub-title {
      padding: 8px 16px;
      font-size: 10.5px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .cred-row {
      border-bottom: 1px solid var(--border-color-soft);
    }
    .cred-row:last-child { border-bottom: none; }
    .cred-section {
      padding: 12px 16px;
      font-size: 13px;
      font-weight: 700;
      color: var(--text-primary);
      vertical-align: top;
      white-space: nowrap;
    }
    .cred-link-cell {
      padding: 12px 16px;
      vertical-align: top;
    }
    .cred-link {
      color: var(--lime);
      font-size: 13px;
      word-break: break-all;
      font-family: var(--font-mono);
    }
    .cred-link:hover { text-decoration: underline; }
    .cred-detail-cell {
      padding: 12px 16px;
      font-size: 13px;
      vertical-align: top;
    }
    .cred-label { font-size: 11px; color: var(--text-muted); }
    .cred-val { color: var(--text-primary); }
    .cred-pass { color: #f87171; font-family: var(--font-mono); }

    /* Notes */
    .notes-cell {
      background: rgba(212, 255, 61, 0.03) !important;
      border-top: 2px solid var(--lime);
      padding: 16px 24px;
    }
    .notes-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--lime); margin-bottom: 6px; letter-spacing: 0.5px; }
    .notes-body { font-size: 13px; color: var(--text-secondary); line-height: 1.5; }

    /* Footer */
    .footer-table {
      background: var(--bg-card);
      border-top: 1px solid var(--border-color);
      padding: 20px 28px;
      width: 100%;
    }
    .footer-title { color: var(--text-primary); font-size: 13.5px; font-weight: 700; font-family: var(--font-heading); }
    .footer-text { font-size: 12px; color: var(--text-secondary); line-height: 1.8; margin-top: 4px; }
    .footer-thanks { font-size: 11.5px; color: var(--text-muted); font-family: var(--font-heading); text-transform: uppercase; letter-spacing: 0.5px; }

    @media print {
      body {
        background: #fff !important;
        color: #000 !important;
        padding: 0 !important;
      }
      .invoice-card {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        max-width: 100% !important;
      }
      .btn-action, .btn-back, .actions-container, .print-hidden {
        display: none !important;
      }
      
      /* Print overrides for light backgrounds and high-contrast dark text */
      .invoice-header-table, .invoice-dates-table, .bill-to-box, .items-table th, .items-table td, .total-card-table, .creds-table, .creds-header-title, .creds-sub-header-row, .cred-row, .notes-cell, .footer-table {
        background: transparent !important;
        color: #000 !important;
        border-color: #d4d4d8 !important;
      }
      .invoice-logo { color: #000 !important; }
      .invoice-tagline, .date-lbl, .bill-lbl, .items-table th, .creds-sub-title, .cred-label, .notes-title, .footer-thanks { color: #52525b !important; }
      .invoice-title-text { color: #e4e4e7 !important; }
      .invoice-num, .cred-link { color: #000 !important; font-weight: 700 !important; }
      .cred-pass { color: #000 !important; font-weight: 700 !important; }
      .total-row.due { color: #000 !important; font-weight: 800 !important; border-top-color: #000 !important; }
      .notes-cell { border-top-color: #000 !important; }
    }
  </style>
</head>
<body>

<div class="actions-container">
  <a href="api_link.php" class="btn-back">← Back to Dashboard</a>
  <button onclick="window.print()" class="btn-action">🖨️ Print Invoice</button>
</div>

<div class="invoice-card">
  <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse">

    <!-- Header -->
    <tr>
      <td>
        <table width="100%" cellpadding="0" cellspacing="0" class="invoice-header-table">
          <tr>
            <td>
              <p class="invoice-logo">⚡ <?= htmlspecialchars($settings['company_name']) ?></p>
              <p class="invoice-tagline">
                <?= htmlspecialchars($settings['company_tagline'] ?? '') ?>
              </p>
            </td>
            <td align="right">
              <p class="invoice-title-text">INVOICE</p>
              <p class="invoice-num"><?= $invoice_number ?></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Sub-header dates -->
    <tr>
      <td>
        <table width="100%" cellpadding="0" cellspacing="0" class="invoice-dates-table">
          <tr>
            <td class="date-lbl">Invoice Date: <strong class="date-val"><?= $invoice_date ?></strong></td>
            <?php if ($due_date): ?>
              <td align="right" class="date-lbl">Due Date: <strong class="date-val"><?= $due_date ?></strong></td>
            <?php endif; ?>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Body -->
    <tr>
      <td style="padding:22px 28px">
        
        <!-- Bill To -->
        <div class="bill-to-box">
          <p class="bill-lbl">Bill To</p>
          <p class="bill-name"><?= $client_name ?></p>
          <?php if ($company_name): ?>
            <p class="bill-comp"><?= $company_name ?></p>
          <?php endif; ?>
          <p class="bill-phone">📞 <?= $phone ?></p>
        </div>

        <!-- Line items table -->
        <table width="100%" cellpadding="0" cellspacing="0" class="items-table">
          <thead>
            <tr>
              <th align="left">Description</th>
              <th align="center" style="width:10%">Qty</th>
              <th align="right" style="width:22%">Unit Price</th>
              <th align="center" style="width:20%">Expiry</th>
              <th align="right" style="width:22%">Total</th>
            </tr>
          </thead>
          <tbody>
            <?= $items_html ?>
          </tbody>
        </table>

        <!-- Totals -->
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td width="50%"></td>
            <td width="50%">
              <table width="100%" cellpadding="0" cellspacing="0" class="total-card-table">
                <tr class="total-row">
                  <td>Grand Total</td>
                  <td align="right">Rs.<?= number_format($grand_total, 2) ?></td>
                </tr>
                <tr class="total-row">
                  <td style="color:#34d399">Advance Received</td>
                  <td align="right" style="color:#34d399">Rs.<?= number_format($advance, 2) ?></td>
                </tr>
                <tr class="total-row due">
                  <td>Balance Due</td>
                  <td align="right">Rs.<?= number_format($pending, 2) ?></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

      </td>
    </tr>

    <!-- Optional credentials block -->
    <?= $creds_html ?>

    <!-- Optional notes block -->
    <?= $notes_html ?>

    <!-- Footer -->
    <tr>
      <td>
        <table width="100%" cellpadding="0" cellspacing="0" class="footer-table">
          <tr>
            <td>
              <p class="footer-title"><?= htmlspecialchars($settings['company_name']) ?></p>
              <p class="footer-text">
                📞 <?= htmlspecialchars($settings['company_phone']) ?> &nbsp;|&nbsp; ✉️ <?= htmlspecialchars($settings['company_email']) ?><br>
                🌐 <?= htmlspecialchars($settings['company_website'] ?? '') ?>
              </p>
            </td>
            <td align="right" class="footer-thanks">
              Thank you for your business
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>
</div>

</body>
</html>
