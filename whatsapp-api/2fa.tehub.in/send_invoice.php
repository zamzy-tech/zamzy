<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

// Fetch Active Settings and SMTP Details
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

$smtp_account_id = intval($_POST['smtp_account_id'] ?? 0);
$smtp_account = null;
if ($smtp_account_id > 0) {
    $smtp_stmt = $pdo->prepare("SELECT * FROM smtp_accounts WHERE id = ?");
    $smtp_stmt->execute([$smtp_account_id]);
    $smtp_account = $smtp_stmt->fetch();
}
// Fall back to default account if not found
if (!$smtp_account) {
    $smtp_stmt = $pdo->query("SELECT * FROM smtp_accounts WHERE is_default = 1 LIMIT 1");
    $smtp_account = $smtp_stmt->fetch();
}
// If still not found, fall back to first available
if (!$smtp_account) {
    $smtp_stmt = $pdo->query("SELECT * FROM smtp_accounts ORDER BY id ASC LIMIT 1");
    $smtp_account = $smtp_stmt->fetch();
}
if (!$smtp_account) {
    die("SMTP server configuration missing. Please set up an SMTP profile in Settings.");
}

// Collect POST data
$client_name    = trim($_POST['client_name'] ?? '');
$company_name   = trim($_POST['company_name'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$whatsapp       = trim($_POST['whatsapp'] ?? '');
$emails         = array_filter(array_map('trim', $_POST['emails'] ?? []));
$invoice_number = trim($_POST['invoice_number'] ?? '');
$invoice_date   = trim($_POST['invoice_date'] ?? '');
$due_date       = trim($_POST['due_date'] ?? '');
$advance        = floatval($_POST['advance_amount'] ?? 0);
$pending        = floatval($_POST['pending_amount'] ?? 0);
$notes          = trim($_POST['notes'] ?? '');
$items          = $_POST['items'] ?? [];

$web_link    = trim($_POST['web_link'] ?? '');
$source_link = trim($_POST['source_link'] ?? '');
$admin_link  = trim($_POST['admin_link'] ?? '');
$admin_id    = trim($_POST['admin_id'] ?? '');
$admin_pass  = trim($_POST['admin_pass'] ?? '');
$email_link  = trim($_POST['email_link'] ?? '');
$email_id    = trim($_POST['email_id'] ?? '');
$email_pass  = trim($_POST['email_pass'] ?? '');

// Check if invoice number already exists to prevent duplicate dispatches and DB integrity errors
try {
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number = ?");
    $check_stmt->execute([$invoice_number]);
    if ($check_stmt->fetchColumn() > 0) {
        header('Location: invoice.php?error=' . urlencode("Invoice number '$invoice_number' already exists. Please use a unique invoice number."));
        exit;
    }
} catch (PDOException $e) {
    header('Location: invoice.php?error=' . urlencode("Database validation failed: " . $e->getMessage()));
    exit;
}

// Calculate grand total
$grand_total = 0;
foreach ($items as $item) {
    $grand_total += floatval($item['qty']) * floatval($item['price']);
}

// Build line items HTML
$items_html = '';
foreach ($items as $item) {
    $desc   = htmlspecialchars($item['desc'] ?? '');
    $qty    = floatval($item['qty'] ?? 1);
    $price  = floatval($item['price'] ?? 0);
    $expiry = !empty($item['expiry']) ? date('d M Y', strtotime($item['expiry'])) : '&mdash;';
    $total  = $qty * $price;
    $items_html .= "
    <tr>
      <td style='padding:12px 14px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#2d3748'>$desc</td>
      <td style='padding:12px 14px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#2d3748;text-align:center'>$qty</td>
      <td style='padding:12px 14px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#2d3748;text-align:right'>Rs." . number_format($price, 2) . "</td>
      <td style='padding:12px 14px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#718096;text-align:center'>$expiry</td>
      <td style='padding:12px 14px;border-bottom:1px solid #e2e8f0;font-size:14px;font-weight:700;color:#1a365d;text-align:right'>Rs." . number_format($total, 2) . "</td>
    </tr>";
}

// Build credentials block
$has_creds = $web_link || $admin_link || $email_link;

$cred_row = function($icon, $label, $link, $id, $pass, $extra = '') {
    $id_row   = $id   ? "<span style='font-size:11px;color:#718096'>ID:</span> <strong style='color:#1a365d'>" . htmlspecialchars($id) . "</strong><br>" : '';
    $pass_row = $pass ? "<span style='font-size:11px;color:#718096'>Password:</span> <strong style='color:#c53030;font-family:monospace'>" . htmlspecialchars($pass) . "</strong>" : '';
    $link_html = $link ? "<a href='" . htmlspecialchars($link) . "' style='color:#2b6cb0;word-break:break-all;font-size:13px'>" . htmlspecialchars($link) . "</a>" : '&mdash;';
    return "
        <tr style='border-top:1px solid #e2e8f0'>
          <td style='padding:12px 14px;font-size:13px;font-weight:700;color:#1a365d;vertical-align:top;white-space:nowrap'>$icon $label</td>
          <td style='padding:12px 14px;vertical-align:top'>$link_html $extra</td>
          <td style='padding:12px 14px;font-size:13px;vertical-align:top'>$id_row $pass_row</td>
        </tr>";
};

$source_extra = $source_link ? "<br><span style='font-size:11px;color:#718096'>Source File: </span><a href='" . htmlspecialchars($source_link) . "' style='font-size:12px;color:#2b6cb0;word-break:break-all'>" . htmlspecialchars($source_link) . "</a>" : '';

$creds_html = '';
if ($has_creds) {
    $creds_html .= "
  <tr>
    <td style='background:#fff;padding:0 32px 28px'>
      <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e2e8f0;border-radius:10px;overflow:hidden'>
        <tr>
          <td colspan='3' style='background:#1a365d;padding:11px 16px;font-size:12px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.6px'>Access Credentials</td>
        </tr>
        <tr style='background:#ebf4ff'>
          <td style='padding:9px 14px;font-size:11px;font-weight:700;color:#2b6cb0;text-transform:uppercase;letter-spacing:.4px;width:22%'>Section</td>
          <td style='padding:9px 14px;font-size:11px;font-weight:700;color:#2b6cb0;text-transform:uppercase;letter-spacing:.4px;width:44%'>Link</td>
          <td style='padding:9px 14px;font-size:11px;font-weight:700;color:#2b6cb0;text-transform:uppercase;letter-spacing:.4px;width:34%'>Login Details</td>
        </tr>";

    if ($web_link)   $creds_html .= $cred_row('&#127760;', 'Website',      $web_link,   '',         '',          $source_extra);
    if ($admin_link) $creds_html .= $cred_row('&#128295;', 'Admin Panel',  $admin_link, $admin_id,  $admin_pass, '');
    if ($email_link) $creds_html .= $cred_row('&#9993;',   'Email',        $email_link, $email_id,  $email_pass, '');

    $creds_html .= "
      </table>
    </td>
  </tr>";
}

// Build notes block
$notes_html = $notes ? "
  <tr>
    <td style='background:#fffbeb;padding:16px 32px;border-top:2px solid #f6e05e'>
      <p style='margin:0 0 6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#744210'>Notes</p>
      <p style='margin:0;font-size:13px;color:#2d3748;line-height:1.6'>" . nl2br(htmlspecialchars($notes)) . "</p>
    </td>
  </tr>" : '';

// Full HTML email using dynamic company configuration
$html = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'>  <link rel="stylesheet" href="2fa_dashboard_theme.css">
</head>
<body style='margin:0;padding:0;background:#f0f4f8;font-family:Segoe UI,Arial,sans-serif'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f4f8;padding:30px 0'>
<tr><td align='center'>
<table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;width:100%'>

  <tr>
    <td style='background:linear-gradient(135deg,#1a365d,#2b6cb0);border-radius:12px 12px 0 0;padding:28px 32px'>
      <table width='100%' cellpadding='0' cellspacing='0'>
        <tr>
          <td>
            <p style='margin:0;font-size:22px;font-weight:700;color:#fff;letter-spacing:.5px'>&#9889; " . htmlspecialchars($settings['company_name']) . "</p>
            <p style='margin:4px 0 0;font-size:12px;color:rgba(255,255,255,.75)'>" . htmlspecialchars($settings['company_tagline'] ?? '') . "</p>
          </td>
          <td align='right'>
            <p style='margin:0;font-size:26px;font-weight:800;color:rgba(255,255,255,.25);letter-spacing:2px'>INVOICE</p>
            <p style='margin:4px 0 0;font-size:13px;color:#90cdf4;font-weight:600'>$invoice_number</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td style='background:#1a365d;padding:14px 32px'>
      <table width='100%' cellpadding='0' cellspacing='0'>
        <tr>
          <td style='font-size:13px;color:#90cdf4'>
            Invoice Date: <strong style='color:#fff'>" . date('d M Y', strtotime($invoice_date)) . "</strong>
          </td>
          <td align='right' style='font-size:13px;color:#90cdf4'>
            " . ($due_date ? "Due Date: <strong style='color:#fff'>" . date('d M Y', strtotime($due_date)) . "</strong>" : '') . "
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td style='background:#fff;padding:28px 32px'>
      <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:24px'>
        <tr>
          <td style='background:#ebf4ff;border-radius:8px;padding:16px 20px;border-left:4px solid #2b6cb0'>
            <p style='margin:0 0 4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#2b6cb0'>Bill To</p>
            <p style='margin:0;font-size:16px;font-weight:700;color:#1a365d'>" . htmlspecialchars($client_name) . "</p>
            " . ($company_name ? "<p style='margin:3px 0 0;font-size:14px;color:#4a5568'>" . htmlspecialchars($company_name) . "</p>" : '') . "
            <p style='margin:3px 0 0;font-size:13px;color:#718096'>&#128222; " . htmlspecialchars($phone) . "</p>
          </td>
        </tr>
      </table>

      <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:20px'>
        <thead>
          <tr style='background:#ebf4ff'>
            <th style='padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#2b6cb0;text-align:left'>Description</th>
            <th style='padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#2b6cb0;text-align:center'>Qty</th>
            <th style='padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#2b6cb0;text-align:right'>Unit Price</th>
            <th style='padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#2b6cb0;text-align:center'>Expiry</th>
            <th style='padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#2b6cb0;text-align:right'>Total</th>
          </tr>
        </thead>
        <tbody>
          $items_html
        </tbody>
      </table>

      <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:4px'>
        <tr>
          <td width='55%'></td>
          <td width='45%'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background:#f7fafc;border-radius:8px;padding:16px 20px'>
              <tr>
                <td style='font-size:13px;color:#718096;padding:4px 0'>Grand Total</td>
                <td style='font-size:13px;color:#2d3748;font-weight:600;text-align:right;padding:4px 0'>Rs." . number_format($grand_total, 2) . "</td>
              </tr>
              <tr>
                <td style='font-size:13px;color:#276749;padding:4px 0'>Advance Received</td>
                <td style='font-size:13px;color:#276749;font-weight:600;text-align:right;padding:4px 0'>Rs." . number_format($advance, 2) . "</td>
              </tr>
              <tr>
                <td colspan='2' style='padding:6px 0 0'><hr style='border:none;border-top:2px solid #2b6cb0;margin:0'></td>
              </tr>
              <tr>
                <td style='font-size:15px;font-weight:700;color:#c53030;padding:8px 0 0'>Balance Due</td>
                <td style='font-size:15px;font-weight:800;color:#c53030;text-align:right;padding:8px 0 0'>Rs." . number_format($pending, 2) . "</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  $notes_html

  $creds_html

  <tr>
    <td style='background:#1a365d;border-radius:0 0 12px 12px;padding:20px 32px'>
      <table width='100%' cellpadding='0' cellspacing='0'>
        <tr>
          <td style='font-size:12px;color:rgba(255,255,255,.7);line-height:1.8'>
            <strong style='color:#fff;font-size:13px'>" . htmlspecialchars($settings['company_name']) . "</strong><br>
            &#128222; " . htmlspecialchars($settings['company_phone']) . "<br>
            &#9993; " . htmlspecialchars($settings['company_email']) . "<br>
            &#127760; " . htmlspecialchars($settings['company_website'] ?? '') . "
          </td>
          <td align='right' style='font-size:11px;color:rgba(255,255,255,.5)'>
            Thank you for your business!
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>
</td></tr>
</table>
<?php include_once __DIR__ . '/send_fast_widget.php'; ?>
</body>
</html>";

// Handle attachments
$uploaded  = $_FILES['attachments'] ?? [];
$has_files = !empty($uploaded['name'][0]);

$register_only = isset($_POST['register_only']) && $_POST['register_only'] == '1';

if (!$register_only) {
    // Send via PHPMailer using dynamic SMTP settings
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtp_account['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_account['smtp_username'];
        $mail->Password   = $smtp_account['smtp_password'];
        $mail->SMTPSecure = ($smtp_account['smtp_secure'] === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : 
                            (($smtp_account['smtp_secure'] === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : '');
        $mail->Port       = $smtp_account['smtp_port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtp_account['smtp_username'], $settings['company_name']);
        $mail->addReplyTo($settings['company_email'], $settings['company_name']);

        foreach ($emails as $email) {
            $mail->addAddress($email, $client_name);
        }

        $mail->isHTML(true);
        $mail->Subject = "Invoice $invoice_number - " . $settings['company_name'];
        $mail->Body    = $html;
        $mail->AltBody = "Invoice $invoice_number from " . $settings['company_name'] . ".\nAmount Due: Rs." . number_format($pending, 2) . "\nContact: " . $settings['company_email'];

        if ($has_files) {
            foreach ($uploaded['tmp_name'] as $i => $tmp) {
                if ($uploaded['error'][$i] === UPLOAD_ERR_OK) {
                    $mail->addAttachment($tmp, basename($uploaded['name'][$i]));
                }
            }
        }

        // Attach the auto-generated invoice PDF if provided via base64
        $pdf_base64 = $_POST['invoice_pdf_base64'] ?? '';
        if (!empty($pdf_base64)) {
            if (strpos($pdf_base64, ',') !== false) {
                $pdf_base64 = explode(',', $pdf_base64)[1];
            }
            $pdf_data = base64_decode($pdf_base64);
            if ($pdf_data !== false) {
                $mail->addStringAttachment($pdf_data, "Invoice-$invoice_number.pdf", 'base64', 'application/pdf');
            }
        }

        // Attempt mail dispatch
        $mail->send();
        $email_sent = true;
    } catch (\Exception $e) {
        $error = "Could not send invoice email: " . $mail->ErrorInfo . " " . $e->getMessage();
    }
}

// Log the transaction inside SQLite Database if no email failure occurred
if (empty($error)) {
    try {
        $pdo->beginTransaction();
        
        $emails_str = implode(',', $emails);
        $ins_inv = $pdo->prepare("INSERT INTO invoices (
            invoice_number, invoice_date, due_date, client_name, company_name, phone, whatsapp, emails, 
            grand_total, advance_amount, pending_amount, notes,
            web_link, source_link, admin_link, admin_id, admin_pass, email_link, email_id, email_pass,
            sender_email, smtp_account_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $sender_email = $register_only ? 'N/A (Ledger Only)' : $smtp_account['smtp_username'];
        $smtp_id      = $register_only ? 0 : $smtp_account['id'];
        
        $ins_inv->execute([
            $invoice_number, $invoice_date, $due_date, $client_name, $company_name, $phone, $whatsapp, $emails_str,
            $grand_total, $advance, $pending, $notes,
            $web_link, $source_link, $admin_link, $admin_id, $admin_pass, $email_link, $email_id, $email_pass,
            $sender_email, $smtp_id
        ]);
        
        $invoice_id = $pdo->lastInsertId();
        
        $ins_item = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, qty, price, expiry_date) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $item_desc = trim($item['desc'] ?? '');
            $item_qty = floatval($item['qty'] ?? 1);
            $item_price = floatval($item['price'] ?? 0);
            $item_expiry = trim($item['expiry'] ?? '');
            
            if ($item_desc !== '') {
                $ins_item->execute([$invoice_id, $item_desc, $item_qty, $item_price, $item_expiry]);
            }
        }
        
        // Sync client details in the clients database table (upsert based on client_name)
        if (!empty($client_name)) {
            $upsert_client = $pdo->prepare("INSERT INTO clients (client_name, company_name, phone, whatsapp, emails) 
                VALUES (?, ?, ?, ?, ?)
                ON CONFLICT(client_name) DO UPDATE SET 
                company_name = excluded.company_name, 
                phone = excluded.phone, 
                whatsapp = excluded.whatsapp,
                emails = excluded.emails");
            $upsert_client->execute([$client_name, $company_name, $phone, $whatsapp, $emails_str]);
        }
        
        $pdo->commit();
        $success = true;
    } catch (\Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Could not register invoice in ledger: " . $e->getMessage();
    }
}

if (!empty($success)) {
    if ($register_only) {
        header('Location: invoice.php?registered=1');
    } else {
        header('Location: invoice.php?sent=1');
    }
} else {
    header('Location: invoice.php?error=' . urlencode($error ?? 'Unknown error'));
}
exit;
