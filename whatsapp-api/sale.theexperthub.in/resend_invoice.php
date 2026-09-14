<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: history.php?error=' . urlencode('Invalid invoice ID.'));
    exit;
}

try {
    // 1. Fetch Invoice
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
    $stmt->execute([$id]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        header('Location: history.php?error=' . urlencode('Invoice not found.'));
        exit;
    }

    // 2. Fetch Invoice Items
    $stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();

    // 3. Fetch Company Branding/Settings
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();

    // 4. Fetch the selected SMTP sender account used for this invoice
    $smtp_account = null;
    if (!empty($invoice['smtp_account_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM smtp_accounts WHERE id = ?");
        $stmt->execute([$invoice['smtp_account_id']]);
        $smtp_account = $stmt->fetch();
    }
    
    // Fall back to default SMTP profile if not found
    if (!$smtp_account) {
        $smtp_account = $pdo->query("SELECT * FROM smtp_accounts WHERE is_default = 1 LIMIT 1")->fetch();
    }
    
    // Fall back to first available SMTP profile if still not found
    if (!$smtp_account) {
        $smtp_account = $pdo->query("SELECT * FROM smtp_accounts ORDER BY id ASC LIMIT 1")->fetch();
    }
    
    if (!$smtp_account) {
        header('Location: history.php?error=' . urlencode('SMTP configurations are completely missing in Settings.'));
        exit;
    }

} catch (PDOException $e) {
    header('Location: history.php?error=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}

// Variables for compiling the email HTML template
$emails_array   = array_filter(array_map('trim', explode(',', $invoice['emails'])));
$client_name    = htmlspecialchars($invoice['client_name']);
$company_name   = htmlspecialchars($invoice['company_name']);
$phone          = htmlspecialchars($invoice['phone']);
$invoice_number = htmlspecialchars($invoice['invoice_number']);
$invoice_date   = date('d M Y', strtotime($invoice['invoice_date']));
$due_date       = $invoice['due_date'] ? date('d M Y', strtotime($invoice['due_date'])) : '';
$advance        = floatval($invoice['advance_amount']);
$pending        = floatval($invoice['pending_amount']);
$grand_total    = floatval($invoice['grand_total']);
$notes          = htmlspecialchars($invoice['notes']);

$web_link    = htmlspecialchars($invoice['web_link']);
$source_link = htmlspecialchars($invoice['source_link']);
$admin_link  = htmlspecialchars($invoice['admin_link']);
$admin_id    = htmlspecialchars($invoice['admin_id']);
$admin_pass  = htmlspecialchars($invoice['admin_pass']);
$email_link  = htmlspecialchars($invoice['email_link']);
$email_id    = htmlspecialchars($invoice['email_id']);
$email_pass  = htmlspecialchars($invoice['email_pass']);

$has_creds = $web_link || $admin_link || $email_link;

// Compile line items HTML
$items_html = '';
foreach ($items as $item) {
    $desc   = htmlspecialchars($item['description']);
    $qty    = floatval($item['qty']);
    $price  = floatval($item['price']);
    $expiry = !empty($item['expiry_date']) ? date('d M Y', strtotime($item['expiry_date'])) : '&mdash;';
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

$notes_html = $notes ? "
  <tr>
    <td style='background:#fffbeb;padding:16px 32px;border-top:2px solid #f6e05e'>
      <p style='margin:0 0 6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#744210'>Notes</p>
      <p style='margin:0;font-size:13px;color:#2d3748;line-height:1.6'>" . nl2br($notes) . "</p>
    </td>
  </tr>" : '';

// Render full HTML email
$html = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
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
            Invoice Date: <strong style='color:#fff'>$invoice_date</strong>
          </td>
          <td align='right' style='font-size:13px;color:#90cdf4'>
            " . ($due_date ? "Due Date: <strong style='color:#fff'>$due_date</strong>" : '') . "
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
            <p style='margin:0;font-size:16px;font-weight:700;color:#1a365d'>$client_name</p>
            " . ($company_name ? "<p style='margin:3px 0 0;font-size:14px;color:#4a5568'>$company_name</p>" : '') . "
            <p style='margin:3px 0 0;font-size:13px;color:#718096'>&#128222; $phone</p>
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
</body>
</html>";

// Dispatch via PHPMailer using chosen SMTP sender account
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

    foreach ($emails_array as $email) {
        $mail->addAddress($email, $client_name);
    }

    $mail->isHTML(true);
    $mail->Subject = "Re: Invoice $invoice_number - " . $settings['company_name'];
    $mail->Body    = $html;
    $mail->AltBody = "Invoice $invoice_number from " . $settings['company_name'] . ".\nAmount Due: Rs." . number_format($pending, 2) . "\nContact: " . $settings['company_email'];

    $mail->send();
    
    header('Location: history.php?success=' . urlencode("Invoice $invoice_number has been successfully resent to " . implode(', ', $emails_array)));
} catch (\Exception $e) {
    header('Location: history.php?error=' . urlencode("Mailer failed: " . $mail->ErrorInfo . " " . $e->getMessage()));
}
exit;
