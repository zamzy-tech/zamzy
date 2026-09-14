<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . '/db.php';
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Verify client session
$api_key = $_SESSION['client_key'] ?? $_GET['api_key'] ?? '';
$client = null;

if (!empty($api_key)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$api_key]);
        $client = $stmt->fetch();
    } catch (Exception $e) {
        $client = null;
    }
}

if (!$client || ($client['status'] ?? '') !== 'active') {
    header("Location: api_link.php");
    exit;
}

// Search & Filter Parameters
$search = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['status'] ?? '');
$view_mode = trim($_GET['view'] ?? 'logs'); // 'logs' or 'contacts'
$limit = isset($_GET['full']) ? 10000 : 3000;

// Calculate Total Log Records
$total_count = 0;
try {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM api_logs WHERE api_key_id = ?");
    $count_stmt->execute([$client['id']]);
    $total_count = $count_stmt->fetchColumn();
} catch (Exception $e) {}

// Calculate Total Unique Contacts/Chats Created
$total_unique_contacts = 0;
try {
    $c_stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT phone) FROM (
            SELECT recipient_phone AS phone FROM api_logs WHERE api_key_id = ?
            UNION
            SELECT client_phone AS phone FROM historical_clients WHERE client_phone IS NOT NULL AND client_phone != ''
        ) AS combined_contacts
    ");
    $c_stmt->execute([$client['id']]);
    $total_unique_contacts = $c_stmt->fetchColumn();
} catch (Exception $e) {
    try {
        $c_stmt = $pdo->prepare("SELECT COUNT(DISTINCT recipient_phone) FROM api_logs WHERE api_key_id = ?");
        $c_stmt->execute([$client['id']]);
        $total_unique_contacts = $c_stmt->fetchColumn();
    } catch (Exception $ex) {
        $total_unique_contacts = 0;
    }
}

// Query Logs or Contact Threads
$logs = [];
$contacts_threads = [];

if ($view_mode === 'contacts') {
    // Unique Contacts Chat Thread Summary
    $sql = "
        SELECT recipient_phone, COUNT(*) AS message_count, MAX(created_at) AS last_chat_at, MAX(status) AS last_status
        FROM api_logs 
        WHERE api_key_id = ?
    ";
    $params = [$client['id']];
    if (!empty($search)) {
        $sql .= " AND (recipient_phone LIKE ? OR response_message LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $sql .= " GROUP BY recipient_phone ORDER BY last_chat_at DESC LIMIT " . intval($limit);
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $contacts_threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
} else {
    // Detailed Message Logs
    $sql = "SELECT * FROM api_logs WHERE api_key_id = ?";
    $params = [$client['id']];

    if (!empty($search)) {
        $sql .= " AND (recipient_phone LIKE ? OR response_message LIKE ? OR message_type LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($filter_status)) {
        $sql .= " AND status = ?";
        $params[] = $filter_status;
    }

    $sql .= " ORDER BY id DESC LIMIT " . intval($limit);

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $logs = [];
    }
}

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=whatsapp_full_history_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    if ($view_mode === 'contacts') {
        fputcsv($output, ['Recipient Phone', 'Total Messages Sent', 'Last Chat Timestamp', 'Last Status']);
        foreach ($contacts_threads as $row) {
            fputcsv($output, ['+' . $row['recipient_phone'], $row['message_count'], $row['last_chat_at'], $row['last_status']]);
        }
    } else {
        fputcsv($output, ['ID', 'Date & Time', 'Recipient Phone', 'Message Type', 'Content / Response', 'Status']);
        foreach ($logs as $row) {
            fputcsv($output, [$row['id'], $row['created_at'], '+' . $row['recipient_phone'], $row['message_type'], $row['response_message'], $row['status']]);
        }
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TOTAL FULL WHATSAPP CHAT & CONTACT HISTORY - THE EXPERT HUB</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg-main: #07090e;
      --bg-surface: #0e131f;
      --bg-card: #141b2d;
      --bg-elev: #1a243b;
      --border-color: rgba(255, 255, 255, 0.08);
      --border-color-soft: rgba(255, 255, 255, 0.04);
      --lime: #d4ff3d;
      --lime-deep: #b8e62e;
      --lime-glow: rgba(212, 255, 61, 0.15);
      --text-primary: #f8fafc;
      --text-secondary: #94a3b8;
      --text-muted: #64748b;
      --font-body: 'Plus Jakarta Sans', sans-serif;
      --font-mono: 'Geist Mono', monospace;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background-color: var(--bg-main);
      color: var(--text-primary);
      font-family: var(--font-body);
      min-height: 100vh;
      padding: 30px 20px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border-color);
    }
    .header-bar h1 {
      font-size: 22px;
      font-weight: 800;
      color: var(--lime);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .header-bar .btn-back {
      padding: 8px 16px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      border-radius: 8px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.2s;
    }
    .header-bar .btn-back:hover {
      border-color: var(--lime);
      color: var(--lime);
    }

    .stats-row {
      display: flex;
      gap: 16px;
      margin-bottom: 20px;
    }
    .stat-badge {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 14px 20px;
      flex: 1;
    }
    .stat-badge .label { font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; }
    .stat-badge .val { font-size: 22px; font-weight: 800; color: var(--lime); font-family: var(--font-mono); margin-top: 4px; }

    .card {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }

    .tab-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      border-bottom: 1px solid var(--border-color);
      padding-bottom: 12px;
    }
    .tab-btn {
      padding: 8px 16px;
      border-radius: 8px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      color: var(--text-secondary);
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }
    .tab-btn.active {
      background: var(--lime);
      color: var(--bg-main);
      border-color: var(--lime);
      font-weight: 800;
    }

    .filter-bar {
      display: flex;
      gap: 12px;
      margin-bottom: 20px;
      flex-wrap: wrap;
      align-items: center;
    }

    input[type="text"], select {
      background: var(--bg-card);
      border: 1.5px solid var(--border-color);
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 13.5px;
      color: var(--text-primary);
      outline: none;
      font-family: inherit;
    }
    input[type="text"]:focus, select:focus {
      border-color: var(--lime);
      box-shadow: 0 0 0 3px var(--lime-glow);
    }

    .btn-search {
      padding: 10px 20px;
      background: var(--lime);
      color: var(--bg-main);
      border: none;
      border-radius: 10px;
      font-weight: 700;
      font-size: 13.5px;
      cursor: pointer;
    }
    .btn-search:hover { background: var(--lime-deep); }

    .btn-export {
      padding: 10px 18px;
      background: #10b981;
      color: #fff;
      border: none;
      border-radius: 10px;
      font-weight: 700;
      font-size: 13.5px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-export:hover { background: #059669; }

    .table-responsive {
      overflow-x: auto;
    }
    .history-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .history-table th, .history-table td {
      padding: 12px 14px;
      text-align: left;
      border-bottom: 1px solid var(--border-color-soft);
    }
    .history-table th {
      background: var(--bg-card);
      color: var(--text-muted);
      text-transform: uppercase;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .badge-status {
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
    }
    .badge-status.success { background: rgba(52,211,153,0.15); color: #34d399; border: 1px solid rgba(52,211,153,0.3); }
    .badge-status.failed { background: rgba(248,113,113,0.15); color: #f87171; border: 1px solid rgba(248,113,113,0.3); }

    .msg-preview {
      max-width: 450px;
      word-break: break-word;
      color: var(--text-secondary);
      font-size: 12.5px;
      line-height: 1.4;
    }

    .phone-tag {
      font-family: var(--font-mono);
      font-weight: 600;
      color: #38bdf8;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="header-bar">
    <h1>💬 WHATSAPP CHATS & CONTACT HISTORY</h1>
    <a href="api_link.php" class="btn-back">← Back to Dashboard</a>
  </div>

  <!-- Summary Stats -->
  <div class="stats-row">
    <div class="stat-badge">
      <div class="label">📇 Total Unique Contacts / Chats Created</div>
      <div class="val"><?= number_format($total_unique_contacts) ?> Unique Contacts</div>
    </div>
    <div class="stat-badge">
      <div class="label">💬 Total API Messages Dispatched</div>
      <div class="val"><?= number_format($total_count) ?> Messages</div>
    </div>
  </div>

  <div class="card">
    <!-- Tab Switcher -->
    <div class="tab-bar">
      <a href="chat_history.php?view=logs" class="tab-btn <?= $view_mode === 'logs' ? 'active' : '' ?>">📜 Detailed Message Logs</a>
      <a href="chat_history.php?view=contacts" class="tab-btn <?= $view_mode === 'contacts' ? 'active' : '' ?>">📇 Unique Contacts & Chat Threads</a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="chat_history.php" class="filter-bar">
      <input type="hidden" name="view" value="<?= htmlspecialchars($view_mode) ?>">
      <input type="text" name="search" placeholder="Search phone number or message content..." value="<?= htmlspecialchars($search) ?>" style="flex:1; min-width:250px;">
      
      <?php if ($view_mode === 'logs'): ?>
        <select name="status">
          <option value="">All Delivery Statuses</option>
          <option value="success" <?= $filter_status === 'success' ? 'selected' : '' ?>>Success / Delivered</option>
          <option value="failed" <?= $filter_status === 'failed' ? 'selected' : '' ?>>Failed</option>
        </select>
      <?php endif; ?>
      
      <button type="submit" class="btn-search">🔍 Search History</button>

      <?php 
        $export_url = "chat_history.php?action=export_csv&view=" . urlencode($view_mode) . (!empty($search) ? "&search=" . urlencode($search) : "") . (!empty($filter_status) ? "&status=" . urlencode($filter_status) : "");
      ?>
      <a href="<?= $export_url ?>" class="btn-export">📥 Export CSV</a>
    </form>

    <?php if ($view_mode === 'contacts'): ?>
      <!-- Contacts & Chat Threads Table -->
      <div class="table-responsive">
        <table class="history-table">
          <thead>
            <tr>
              <th>Contact Phone Number</th>
              <th>Total Messages Sent</th>
              <th>Last Chat Timestamp</th>
              <th>Last Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($contacts_threads)): ?>
              <?php foreach ($contacts_threads as $ct): ?>
                <tr>
                  <td class="phone-tag">+<?= htmlspecialchars($ct['recipient_phone']) ?></td>
                  <td style="font-family:var(--font-mono); font-weight:700; color:var(--lime);"><?= number_format($ct['message_count']) ?> Messages</td>
                  <td style="font-family:var(--font-mono); color:var(--text-muted); font-size:12px;">
                    <?= date('d-M-Y H:i:s', strtotime($ct['last_chat_at'])) ?>
                  </td>
                  <td>
                    <span class="badge-status success">✅ <?= htmlspecialchars($ct['last_status']) ?></span>
                  </td>
                  <td>
                    <a href="chat_history.php?view=logs&search=<?= urlencode($ct['recipient_phone']) ?>" class="btn-search" style="padding:4px 10px; font-size:11px; text-decoration:none; display:inline-block;">View Messages</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" style="text-align:center; padding:35px; color:var(--text-muted);">
                  No contact chat threads found.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    <?php else: ?>
      <!-- Message Logs Table -->
      <div class="table-responsive">
        <table class="history-table">
          <thead>
            <tr>
              <th>#ID</th>
              <th>Date & Time</th>
              <th>Recipient Number</th>
              <th>Message Type</th>
              <th>Message / Response Content</th>
              <th>Delivery Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($logs)): ?>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td style="font-family:var(--font-mono); color:var(--text-muted); font-size:11px;">#<?= $log['id'] ?></td>
                  <td style="font-family:var(--font-mono); color:var(--text-muted); font-size:12px; white-space:nowrap;">
                    <?= date('d-M-Y H:i:s', strtotime($log['created_at'])) ?>
                  </td>
                  <td class="phone-tag">+<?= htmlspecialchars($log['recipient_phone']) ?></td>
                  <td><span style="background:var(--bg-card); padding:3px 8px; border-radius:4px; font-size:11px; font-weight:600; text-transform:uppercase; border:1px solid var(--border-color);"><?= htmlspecialchars($log['message_type'] ?? 'WhatsApp') ?></span></td>
                  <td class="msg-preview"><?= htmlspecialchars($log['response_message'] ?? 'N/A') ?></td>
                  <td>
                    <?php 
                      $st = strtolower($log['status']);
                      $isSuccess = (strpos($st, 'success') !== false || strpos($st, 'sent') !== false || strpos($st, 'ok') !== false);
                    ?>
                    <span class="badge-status <?= $isSuccess ? 'success' : 'failed' ?>">
                      <?= $isSuccess ? '✅ Sent' : '❌ ' . htmlspecialchars($log['status']) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align:center; padding:35px; color:var(--text-muted);">
                  No detailed message logs found.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

</body>
</html>
