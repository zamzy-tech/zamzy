<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// Fetch settings for company name in header and WhatsApp template
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

// Handle invoice deletion, restoration, and purging (Trash mechanics)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $invoice_id = intval($_POST['invoice_id'] ?? 0);
    
    try {
        if ($action === 'empty_trash') {
            $pdo->beginTransaction();
            // Permanent Delete (cascades to invoice_items automatically for all is_deleted = 1)
            $stmt = $pdo->prepare("DELETE FROM invoices WHERE is_deleted = 1");
            $stmt->execute();
            $pdo->commit();
            header('Location: history.php?view=trash&success=' . urlencode("Trash bin emptied successfully."));
            exit;
        } elseif ($action === 'trash_all_active') {
            $pdo->beginTransaction();
            // Soft delete all active invoices
            $stmt = $pdo->prepare("UPDATE invoices SET is_deleted = 1 WHERE is_deleted = 0 OR is_deleted IS NULL");
            $stmt->execute();
            $pdo->commit();
            header('Location: history.php?success=' . urlencode("All active invoices moved to Trash successfully."));
            exit;
        } elseif ($invoice_id > 0) {
            $pdo->beginTransaction();
            
            if ($action === 'delete_invoice') {
                // Soft Delete (move to Trash)
                $stmt = $pdo->prepare("UPDATE invoices SET is_deleted = 1 WHERE id = ?");
                $stmt->execute([$invoice_id]);
                $pdo->commit();
                header('Location: history.php?success=' . urlencode("Invoice moved to Trash successfully."));
                exit;
            } elseif ($action === 'restore_invoice') {
                // Restore from Trash
                $stmt = $pdo->prepare("UPDATE invoices SET is_deleted = 0 WHERE id = ?");
                $stmt->execute([$invoice_id]);
                $pdo->commit();
                header('Location: history.php?success=' . urlencode("Invoice restored successfully."));
                exit;
            } elseif ($action === 'purge_invoice') {
                // Permanent Delete (cascades to invoice_items automatically)
                $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
                $stmt->execute([$invoice_id]);
                $pdo->commit();
                header('Location: history.php?view=trash&success=' . urlencode("Invoice permanently deleted."));
                exit;
            }
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Location: history.php?error=' . urlencode("Failed to execute trash operation: " . $e->getMessage()));
        exit;
    }
}

$search = trim($_GET['search'] ?? '');
$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';
$view = $_GET['view'] ?? 'active';

try {
    if ($view === 'trash') {
        if ($search !== '') {
            $stmt = $pdo->prepare("SELECT * FROM invoices 
                WHERE is_deleted = 1 AND (invoice_number LIKE ? OR client_name LIKE ? OR company_name LIKE ?)
                ORDER BY invoice_date DESC, id DESC");
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM invoices WHERE is_deleted = 1 ORDER BY invoice_date DESC, id DESC");
        }
    } else {
        if ($search !== '') {
            $stmt = $pdo->prepare("SELECT * FROM invoices 
                WHERE (is_deleted = 0 OR is_deleted IS NULL) AND (invoice_number LIKE ? OR client_name LIKE ? OR company_name LIKE ?)
                ORDER BY invoice_date DESC, id DESC");
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM invoices WHERE (is_deleted = 0 OR is_deleted IS NULL) ORDER BY invoice_date DESC, id DESC");
        }
    }
    $invoices = $stmt->fetchAll();
    
    // Fetch counts for tabs
    $active_count = $pdo->query("SELECT COUNT(*) FROM invoices WHERE (is_deleted = 0 OR is_deleted IS NULL)")->fetchColumn();
    $trash_count  = $pdo->query("SELECT COUNT(*) FROM invoices WHERE is_deleted = 1")->fetchColumn();
    
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Billing History – The Expert Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script><link rel="stylesheet" href="admin_style.css">
<style>
  /* Search controls overrides */
  .search-card {
    background-color: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
  }
  .search-form {
    display: flex;
    gap: 12px;
    width: 100%;
  }
  .search-input {
    flex: 1;
    background: var(--bg-card);
    border: 1.5px solid var(--border-color);
    border-radius: 8px;
    padding: 10px 14px;
    color: var(--text-primary);
  }
  .search-input:focus {
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
  }
  .btn-search:hover {
    opacity: 0.9;
  }

  /* Main Card Table */
  .card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    overflow: hidden;
    margin-bottom: 24px;
    padding: 24px 32px;
  }
  .table-responsive {
    overflow-x: auto;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th {
    background-color: var(--bg-card) !important;
    color: var(--text-muted) !important;
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border-bottom: 1.5px solid var(--border-color);
    text-align: left;
  }
  td {
    padding: 12px 16px;
    font-size: 13.5px;
    border-bottom: 1px solid var(--border-color-soft);
    color: var(--text-secondary);
    vertical-align: middle;
  }
  tr:hover td {
    background-color: var(--border-color-soft);
  }

  /* Badges */
  .badge {
    display: inline-flex;
    align-items: center;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
  }
  .badge.paid {
    background: #c6f6d5;
    color: #22543d;
  }
  .badge.due {
    background: #fed7d7;
    color: #742a2a;
  }

  /* Action buttons */
  .btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--bg-elev);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    margin-right: 4px;
    font-family: inherit;
  }
  .btn-action:hover {
    background: var(--lime);
    color: var(--bg-main);
    border-color: var(--lime);
  }
  .btn-action.print {
    background: rgba(212, 255, 61, 0.08);
    color: var(--lime);
    border-color: rgba(212, 255, 61, 0.2);
  }
  .btn-action.print:hover {
    background: var(--lime);
    color: var(--bg-main);
  }
  .btn-action.resend {
    background: rgba(52, 211, 153, 0.08);
    color: #34d399;
    border-color: rgba(52, 211, 153, 0.2);
  }
  .btn-action.resend:hover {
    background: #10b981;
    color: var(--bg-main);
    border-color: #10b981;
  }
  .btn-action.whatsapp {
    background: #e6fcf5;
    color: #128c7e;
    border-color: #c6f6d5;
    box-shadow: 0 0 0 rgba(18, 140, 126, 0);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .btn-action.whatsapp:hover {
    background: #128c7e;
    color: #fff;
    border-color: #128c7e;
    box-shadow: 0 4px 10px rgba(18, 140, 126, 0.25);
    transform: translateY(-1px);
  }
  .btn-action.delete {
    background: #fff5f5;
    color: #e53e3e;
    border-color: #feb2b2;
  }
  .btn-action.delete:hover {
    background: #feb2b2;
    color: #742a2a;
  }
  .btn-action.restore {
    background: #f0fff4;
    color: #38a169;
    border-color: #9ae6b4;
  }
  .btn-action.restore:hover {
    background: #9ae6b4;
    color: #22543d;
  }

  /* Tabs styling */
  .ledger-tabs {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
  }
  .ledger-tabs-left {
    display: flex;
    gap: 12px;
  }
  .ledger-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-secondary);
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
  }
  .ledger-tab:hover {
    background: var(--border-color-soft);
    color: var(--text-primary);
  }
  .ledger-tab.active {
    background: rgba(212, 255, 61, 0.1);
    border-color: var(--lime);
    color: var(--lime);
  }
  .ledger-tab .count-badge {
    background: var(--bg-surface);
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    border: 1px solid var(--border-color);
  }
  .ledger-tab.active .count-badge {
    background: var(--lime);
    color: var(--bg-main);
    border-color: var(--lime);
  }

  /* Alerts */
  .alert {
    padding: 14px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
  }
  .alert.success {
    background: #f0fff4;
    border: 1.5px solid #9ae6b4;
    color: #276749;
  }
  .alert.error {
    background: #fff5f5;
    border: 1.5px solid #feb2b2;
    color: #c53030;
  }

  .empty-state {
    padding: 48px;
    text-align: center;
    color: #a0aec0;
  }
  .empty-state-icon {
    font-size: 40px;
    margin-bottom: 12px;
  }
</style>
  <link rel="stylesheet" href="2fa_dashboard_theme.css">
</head>
<body>

<header>
  <div class="logo-block" style="display: flex; align-items: center; gap: 12px; margin-bottom: 0;">
    <img src="zamzy_logo.png" alt="ZAMZY" style="height: 48px; width: auto; max-width: 220px; object-fit: contain;">
  </div>
  <nav>
    <a href="invoice.php">Generate Invoice</a>
    <a href="history.php" class="active">Billing History</a>
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

<div class="container">

  <div class="page-header">
    <h1>Invoice Log &amp; Billing History</h1>
    
    <!-- Search Form -->
    <form method="GET" action="history.php" class="search-container">
      <?php if ($view === 'trash'): ?>
        <input type="hidden" name="view" value="trash">
      <?php endif; ?>
      <input type="text" name="search" class="search-input" value="<?= htmlspecialchars($search) ?>" placeholder="Search by invoice#, client, or company...">
      <button type="submit" class="btn-search">Search</button>
    </form>
  </div>

  <!-- Ledger View Tabs -->
  <div class="ledger-tabs">
    <div class="ledger-tabs-left">
      <a href="history.php?view=active<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>" class="ledger-tab <?= $view !== 'trash' ? 'active' : '' ?>">
        📂 Active Invoices <span class="count-badge"><?= $active_count ?></span>
      </a>
      <a href="history.php?view=trash<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>" class="ledger-tab <?= $view === 'trash' ? 'active' : '' ?>">
        🗑️ Trash Bin <span class="count-badge"><?= $trash_count ?></span>
      </a>
    </div>
    <?php if ($view !== 'trash' && $active_count > 0): ?>
      <form method="POST" action="history.php" onsubmit="return confirm('WARNING: Are you sure you want to move ALL active invoices to the Trash Bin?')">
        <input type="hidden" name="action" value="trash_all_active">
        <button type="submit" class="btn-action delete" style="padding: 10px 18px; font-size: 14px; margin-right: 0;" title="Move All Active Invoices to Trash">
          🗑️ Move All to Trash
        </button>
      </form>
    <?php elseif ($view === 'trash' && $trash_count > 0): ?>
      <form method="POST" action="history.php" onsubmit="return confirm('WARNING: Are you sure you want to PERMANENTLY delete ALL invoices in the Trash Bin? This will completely erase all these records and cannot be undone.')">
        <input type="hidden" name="action" value="empty_trash">
        <button type="submit" class="btn-action delete" style="padding: 10px 18px; font-size: 14px; margin-right: 0;" title="Permanently Erase All Invoices in Trash">
          💥 Empty Trash Bin
        </button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (!empty($success_msg)): ?>
    <div class="alert success">✅ <?= htmlspecialchars($success_msg) ?></div>
  <?php elseif (!empty($error_msg)): ?>
    <div class="alert error">❌ <?= htmlspecialchars($error_msg) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Client Info</th>
            <th>Grand Total</th>
            <th>Advance</th>
            <th>Balance Due</th>
            <th>Status</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($invoices)): ?>
            <tr>
              <td colspan="8">
                <div class="empty-state">
                  <div class="empty-state-icon"><?= $view === 'trash' ? '🗑️' : '📂' ?></div>
                  <div><?= $view === 'trash' ? 'Your Trash Bin is currently empty.' : 'No active invoices matching the criteria were found.' ?></div>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($invoices as $inv): ?>
              <?php 
                $pending = floatval($inv['pending_amount']);
                $is_paid = $pending <= 0;
                $emails = implode(', ', array_filter(explode(',', $inv['emails'])));
              ?>
              <tr>
                <td><strong><?= htmlspecialchars($inv['invoice_number']) ?></strong></td>
                <td style="white-space:nowrap"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                <td>
                  <div style="font-weight: 600; color: #1a365d;"><?= htmlspecialchars($inv['client_name']) ?></div>
                  <?php if ($inv['company_name']): ?>
                    <div style="font-size:12px; color:#718096"><?= htmlspecialchars($inv['company_name']) ?></div>
                  <?php endif; ?>
                  <div style="font-size:11px; color:#a0aec0"><?= htmlspecialchars($emails) ?></div>
                  <?php if (!empty($inv['sender_email'])): ?>
                    <div style="font-size:11px; color:#2b6cb0; margin-top: 4px; display: flex; align-items: center; gap: 4px;" title="Sender Profile">
                      <span>✉️</span> <span><?= htmlspecialchars($inv['sender_email']) ?></span>
                    </div>
                  <?php endif; ?>
                </td>
                <td>Rs.<?= number_format($inv['grand_total'], 2) ?></td>
                <td style="color:#276749">Rs.<?= number_format($inv['advance_amount'], 2) ?></td>
                <td style="font-weight:700; color:<?= $is_paid ? '#276749' : '#c53030' ?>">
                  Rs.<?= number_format($pending, 2) ?>
                </td>
                <td>
                  <span class="badge <?= $is_paid ? 'paid' : 'due' ?>">
                    <?= $is_paid ? 'Paid' : 'Balance Due' ?>
                  </span>
                </td>
                <td style="text-align:right; white-space:nowrap">
                  <?php if ($view === 'trash'): ?>
                    <form method="POST" action="history.php" style="display:inline-block" onsubmit="return confirm('Are you sure you want to restore invoice <?= htmlspecialchars($inv['invoice_number']) ?> back to Active?')">
                      <input type="hidden" name="action" value="restore_invoice">
                      <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">
                      <button type="submit" class="btn-action restore" title="Restore Invoice to Active Ledger">
                        ↩️ Restore
                      </button>
                    </form>
                    <form method="POST" action="history.php" style="display:inline-block" onsubmit="return confirm('WARNING: Are you sure you want to PERMANENTLY delete invoice <?= htmlspecialchars($inv['invoice_number']) ?>? This will completely erase the record and cannot be undone.')">
                      <input type="hidden" name="action" value="purge_invoice">
                      <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">
                      <button type="submit" class="btn-action delete" title="Permanently Erase Invoice">
                        ❌ Permanent Delete
                      </button>
                    </form>
                  <?php else: ?>
                    <a href="view_invoice.php?id=<?= $inv['id'] ?>" target="_blank" class="btn-action print" title="View & Print Invoice">
                      🖨️ Print
                    </a>
                    <a href="resend_invoice.php?id=<?= $inv['id'] ?>" class="btn-action resend" title="Resend Invoice Email" onclick="return confirm('Are you sure you want to resend invoice <?= htmlspecialchars($inv['invoice_number']) ?> to the client?')">
                      ✉️ Resend
                    </a>
                    <?php
                      $target_phone = !empty($inv['whatsapp']) ? $inv['whatsapp'] : $inv['phone'];
                      $clean_phone = preg_replace('/[^0-9]/', '', $target_phone);
                      
                      $client_name = $inv['client_name'];
                      $inv_num = $inv['invoice_number'];
                      $inv_date = date('d M Y', strtotime($inv['invoice_date']));
                      $due_date = !empty($inv['due_date']) ? date('d M Y', strtotime($inv['due_date'])) : '';
                      $grand_total = number_format($inv['grand_total'], 2);
                      $advance_amount = number_format($inv['advance_amount'], 2);
                      $pending_amount = number_format($pending, 2);
                      $company_name = $settings['company_name'] ?? 'The Expert Hub';
                      
                      if (!function_exists('format_history_template')) {
                          function format_history_template($template, $vars) {
                              $res = $template;
                              foreach ($vars as $key => $val) {
                                  $res = str_replace('{' . $key . '}', $val, $res);
                              }
                              return $res;
                          }
                      }
                      
                      if ($pending > 0) {
                          if (!empty($settings['template_invoice_create'])) {
                              $wa_msg = format_history_template($settings['template_invoice_create'], [
                                  'client_name' => $client_name,
                                  'company_name' => $company_name,
                                  'invoice_number' => $inv_num,
                                  'invoice_date' => $inv_date,
                                  'due_date' => $due_date,
                                  'grand_total' => $grand_total,
                                  'advance_amount' => $advance_amount,
                                  'pending_amount' => $pending_amount,
                                  'web_link' => $inv['web_link'] ?? ''
                              ]);
                          } else {
                              $wa_msg = "*Dear {$client_name},*\n\n"
                                      . "🔔 *Payment Reminder from {$company_name}* 🔔\n\n"
                                      . "This is a friendly reminder that there is an outstanding balance of *Rs.{$pending_amount}* on invoice *{$inv_num}*.\n\n"
                                      . "*Invoice Details:*\n"
                                      . "• *Invoice Number:* {$inv_num}\n"
                                      . "• *Invoice Date:* {$inv_date}\n"
                                      . "• *Grand Total:* Rs.{$grand_total}\n"
                                      . "• *Advance Paid:* Rs.{$advance_amount}\n"
                                      . "• *Outstanding Balance:* *Rs.{$pending_amount}*\n\n"
                                      . "We kindly request you to clear the pending balance at your earliest convenience. If you have already made the payment, please ignore this message.\n\n"
                                      . "Thank you for your continued partnership!\n"
                                      . "Best regards,\n"
                                      . "*{$company_name}*";
                          }
                      } else {
                          if (!empty($settings['template_payment_receive'])) {
                              $wa_msg = format_history_template($settings['template_payment_receive'], [
                                  'client_name' => $client_name,
                                  'company_name' => $company_name,
                                  'invoice_number' => $inv_num,
                                  'invoice_date' => $inv_date,
                                  'due_date' => $due_date,
                                  'grand_total' => $grand_total,
                                  'advance_amount' => $advance_amount,
                                  'pending_amount' => $pending_amount,
                                  'amount_paid' => $grand_total
                              ]);
                          } else {
                              $wa_msg = "*Dear {$client_name},*\n\n"
                                      . "✅ *Payment Received - Thank You!* ✅\n\n"
                                      . "We have received the payment in full for invoice *{$inv_num}*.\n\n"
                                      . "*Invoice Details:*\n"
                                      . "• *Invoice Number:* {$inv_num}\n"
                                      . "• *Invoice Date:* {$inv_date}\n"
                                      . "• *Grand Total:* Rs.{$grand_total}\n"
                                      . "• *Advance Paid:* Rs.{$advance_amount}\n"
                                      . "• *Outstanding Balance:* *Rs.{$pending_amount}* (Fully Paid)\n\n"
                                      . "Thank you for your business! We look forward to working with you again.\n\n"
                                      . "Best regards,\n"
                                      . "*{$company_name}*";
                          }
                      }
                      
                      $wa_url = "https://api.whatsapp.com/send?phone=" . urlencode($clean_phone) . "&text=" . urlencode($wa_msg);
                    ?>
                    <a href="<?= $wa_url ?>" target="_blank" class="btn-action whatsapp" data-phone="<?= htmlspecialchars($clean_phone) ?>" data-message="<?= htmlspecialchars($wa_msg) ?>" data-id="<?= $inv['id'] ?>" data-invoice-number="<?= htmlspecialchars($inv['invoice_number']) ?>" title="Send WhatsApp <?= $pending > 0 ? 'Reminder' : 'Confirmation' ?>" onclick="sendHistoryWhatsApp(event, this)">
                      💬 WhatsApp
                    </a>
                    <form method="POST" action="history.php" style="display:inline-block" onsubmit="return confirm('Are you sure you want to move invoice <?= htmlspecialchars($inv['invoice_number']) ?> to the Trash?')">
                      <input type="hidden" name="action" value="delete_invoice">
                      <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">
                      <button type="submit" class="btn-action delete" title="Move Invoice to Trash">
                        🗑️ Trash
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
const WHATSAPP_GATEWAY_TYPE = <?= json_encode($settings['whatsapp_gateway_type'] ?? 'browser') ?>;

function sendHistoryWhatsApp(event, el) {
  if (typeof WHATSAPP_GATEWAY_TYPE !== 'undefined' && WHATSAPP_GATEWAY_TYPE === 'gateway') {
    // Prevent standard tab redirection
    event.preventDefault();
    
    const phone = el.getAttribute('data-phone');
    const msg = el.getAttribute('data-message');
    const invoiceId = el.getAttribute('data-id');
    const invoiceNum = el.getAttribute('data-invoice-number') || 'INVOICE';
    
    // Add dynamic animation CSS rules if not present
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
    
    // Create glassmorphic overlay loader
    let overlay = document.getElementById('waOverlay');
    if (overlay) overlay.remove();
    
    overlay = document.createElement('div');
    overlay.id = 'waOverlay';
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
      <div id="waOverlaySpinner" style="border: 4px solid #e2e8f0; border-top: 4px solid #128c7e; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
      <h3 id="waOverlayTitle" style="color: #1a365d; font-size: 18px; font-weight: 700; margin-bottom: 8px;">Preparing WhatsApp PDF</h3>
      <p id="waOverlayText" style="font-size: 13.5px; color: #4a5568;">Fetching historic invoice layout...</p>
      <button id="waOverlayCloseBtn" style="display: none; background: #e53e3e; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; font-family: inherit; margin-top: 15px;">Close</button>
    `;
    overlay.appendChild(box);
    document.body.appendChild(overlay);
    
    // Fetch historic print layout
    fetch('view_invoice.php?id=' + invoiceId)
    .then(res => {
      if (!res.ok) throw new Error("Failed to fetch invoice HTML structure");
      return res.text();
    })
    .then(htmlText => {
      document.getElementById('waOverlayText').textContent = "Compiling PDF invoice in-memory...";
      
      const parser = new DOMParser();
      const doc = parser.parseFromString(htmlText, 'text/html');
      
      // Remove any script elements
      const scripts = doc.querySelectorAll('script');
      scripts.forEach(s => s.remove());
      
      // Create offscreen container
      const container = document.createElement('div');
      container.style.position = 'absolute';
      container.style.left = '-9999px';
      container.style.top = '-9999px';
      container.innerHTML = doc.body.innerHTML;
      document.body.appendChild(container);
      
      const opt = {
        margin:       10,
        filename:     'Invoice-' + invoiceNum + '.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };
      
      return html2pdf().set(opt).from(container).outputPdf('datauristring').then(pdfBase64 => {
        document.body.removeChild(container);
        return pdfBase64;
      });
    })
    .then(pdfBase64 => {
      document.getElementById('waOverlayText').innerHTML = "Transmitting PDF invoice and summary to <strong>" + phone + "</strong>...";
      
      const formData = new FormData();
      formData.append('phone', phone);
      formData.append('message', msg);
      formData.append('pdf', pdfBase64);
      formData.append('filename', 'Invoice-' + invoiceNum + '.pdf');
      
      return fetch('send_whatsapp_background.php', {
        method: 'POST',
        body: formData
      });
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const spinner = document.getElementById('waOverlaySpinner');
        if (spinner) {
          spinner.outerHTML = `
            <div style="width: 50px; height: 50px; background: #c6f6d5; color: #22543d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 20px; font-weight: bold;">
              ✓
            </div>
          `;
        }
        document.getElementById('waOverlayTitle').textContent = 'Dispatched!';
        document.getElementById('waOverlayTitle').style.color = '#22543d';
        document.getElementById('waOverlayText').innerHTML = 'Historic PDF and balance reminder sent to WhatsApp.';
        
        setTimeout(() => {
          overlay.style.opacity = '0';
          setTimeout(() => overlay.remove(), 300);
        }, 2200);
      } else {
        throw new Error(data.error || 'Unknown gateway response');
      }
    })
    .catch(err => {
      const spinner = document.getElementById('waOverlaySpinner');
      if (spinner) {
        spinner.outerHTML = `
          <div style="width: 50px; height: 50px; background: #fed7d7; color: #742a2a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 20px;">
            ✕
          </div>
        `;
      }
      document.getElementById('waOverlayTitle').textContent = 'Dispatch Failed';
      document.getElementById('waOverlayTitle').style.color = '#c53030';
      document.getElementById('waOverlayText').innerHTML = `<span style="display:block; margin-bottom: 12px; font-weight:600; color:#e53e3e; font-size:12px;">${err.message}</span>
        Background send failed. Would you like to fallback to a manual browser redirect?`;
        
      const closeBtn = document.getElementById('waOverlayCloseBtn');
      if (closeBtn) {
        closeBtn.style.display = 'inline-block';
        closeBtn.textContent = 'Cancel';
        closeBtn.onclick = () => {
          overlay.style.opacity = '0';
          setTimeout(() => overlay.remove(), 300);
        };
      }
      
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
        overlay.remove();
        window.open(el.getAttribute('href'), '_blank');
      };
      box.appendChild(fallbackBtn);
    });
  }
}
</script>
<?php include_once __DIR__ . '/send_fast_widget.php'; ?>
</body>
</html>
