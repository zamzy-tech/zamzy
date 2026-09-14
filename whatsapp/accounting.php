<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// Fetch settings for company name in header
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

$success_msg = '';
$error_msg = '';

// Handle expense actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_expense') {
        $description = trim($_POST['description'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $expense_date = trim($_POST['expense_date'] ?? '');
        
        if (empty($description) || $amount <= 0 || empty($expense_date)) {
            $error_msg = 'Please fill in all fields with valid information (Amount must be greater than 0).';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO expenses (description, amount, expense_date) VALUES (?, ?, ?)");
                $stmt->execute([$description, $amount, $expense_date]);
                $success_msg = 'Expense logged successfully!';
            } catch (PDOException $e) {
                $error_msg = 'Failed to log expense: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_expense') {
        $expense_id = intval($_POST['expense_id'] ?? 0);
        if ($expense_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
                $stmt->execute([$expense_id]);
                $success_msg = 'Expense entry deleted successfully!';
            } catch (PDOException $e) {
                $error_msg = 'Failed to delete expense entry: ' . $e->getMessage();
            }
        }
    }
}

// Fetch accounting summary metrics
try {
    // 1. Total Sales = SUM(grand_total) of all active (non-deleted) invoices
    $total_sales = floatval($pdo->query("SELECT SUM(grand_total) FROM invoices WHERE is_deleted = 0 OR is_deleted IS NULL")->fetchColumn() ?: 0);
    
    // 2. Total Expenses = SUM(amount) of all expenses
    $total_expenses = floatval($pdo->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?: 0);
    
    // 3. Remaining balance
    $remaining_balance = $total_sales - $total_expenses;
    
    // Fetch expenses list
    $stmt_expenses = $pdo->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC");
    $expenses = $stmt_expenses->fetchAll();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Financial Ledger &amp; Accounting – <?= htmlspecialchars($settings['company_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="admin_style.css">
<style>
  /* Metrics Grid overrides */
  .metrics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 32px;
  }
  @media(max-width: 768px) {
    .metrics-grid {
      grid-template-columns: 1fr;
      gap: 16px;
    }
  }
  .metric-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
  }
  .metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
  }
  .metric-card.sales::before { background: #34d399; }
  .metric-card.expenses::before { background: #f87171; }
  .metric-card.remaining.positive::before { background: var(--lime); }
  .metric-card.remaining.negative::before { background: #f59e0b; }

  .metric-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 8px;
  }
  .metric-value {
    font-size: 24px;
    font-weight: 800;
    font-family: var(--font-mono);
  }
  .metric-card.sales .metric-value { color: #34d399; }
  .metric-card.expenses .metric-value { color: #f87171; }
  .metric-card.remaining.positive .metric-value { color: var(--lime); }
  .metric-card.remaining.negative .metric-value { color: #f59e0b; }

  /* Layout and tables overrides */
  .layout-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 28px;
  }
  @media(max-width: 768px) {
    .layout-grid {
      grid-template-columns: 1fr;
    }
  }

  .btn-submit {
    width: 100%;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
  .btn-action.delete {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.2);
  }
  .btn-action.delete:hover {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
  }

  .empty-state {
    padding: 48px;
    text-align: center;
    color: var(--text-muted);
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
  <div class="logo-block" style="display: flex; align-items: center; gap: 10px; margin-bottom: 0;">
    <img src="teh_logo.png" alt="THE EXPERT HUB Logo" style="height: 32px; width: auto;">
    <div class="logo" style="font-size: 18px; font-weight: 800; color: #fff; margin: 0; line-height: 1;"><?= htmlspecialchars($settings['company_name']) ?></div>
  </div>
  <nav>
    <a href="invoice.php">Generate Invoice</a>
    <a href="history.php">Billing History</a>
    <a href="clients.php">Clients</a>
    <a href="accounting.php" class="active">Accounting</a>
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
    <h1>Financial Ledger &amp; Accounting</h1>
  </div>

  <?php if (!empty($success_msg)): ?>
    <div class="alert success">✅ <?= htmlspecialchars($success_msg) ?></div>
  <?php elseif (!empty($error_msg)): ?>
    <div class="alert error">❌ <?= htmlspecialchars($error_msg) ?></div>
  <?php endif; ?>

  <!-- Accounting Metrics Cards -->
  <div class="metrics-grid">
    <div class="metric-card sales">
      <div class="metric-label">💰 Total Sales</div>
      <div class="metric-value">Rs.<?= number_format($total_sales, 2) ?></div>
    </div>
    <div class="metric-card expenses">
      <div class="metric-label">💸 Total Expenses</div>
      <div class="metric-value">Rs.<?= number_format($total_expenses, 2) ?></div>
    </div>
    <div class="metric-card remaining <?= $remaining_balance >= 0 ? 'positive' : 'negative' ?>">
      <div class="metric-label">⚖️ Net Remaining Amount</div>
      <div class="metric-value">Rs.<?= number_format($remaining_balance, 2) ?></div>
    </div>
  </div>

  <div class="layout-grid">
    
    <!-- Left: Expense History Ledger -->
    <div class="card" style="padding:0">
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Expense Details</th>
              <th>Expense Date</th>
              <th>Amount (₹)</th>
              <th style="text-align:right">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($expenses)): ?>
              <tr>
                <td colspan="4">
                  <div class="empty-state">
                    <div class="empty-state-icon">💸</div>
                    <div>No expense logs found. Register a new expense on the right!</div>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($expenses as $exp): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($exp['description']) ?></strong></td>
                  <td><?= date('d M Y', strtotime($exp['expense_date'])) ?></td>
                  <td style="font-weight:700; color:#c53030">Rs.<?= number_format($exp['amount'], 2) ?></td>
                  <td style="text-align:right">
                    <form method="POST" action="accounting.php" onsubmit="return confirm('Are you sure you want to delete this expense record?')">
                      <input type="hidden" name="action" value="delete_expense">
                      <input type="hidden" name="expense_id" value="<?= $exp['id'] ?>">
                      <button type="submit" class="btn-action">
                        🗑️ Delete
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

    <!-- Right: Add Expense Card -->
    <div>
      <div class="card">
        <div class="card-title">💸 Log New Expense</div>
        <form method="POST" action="accounting.php">
          <input type="hidden" name="action" value="add_expense">

          <div class="form-group">
            <label>Expense Description *</label>
            <input type="text" name="description" required placeholder="e.g. Server Hosting, Office Supplies">
          </div>

          <div class="form-group">
            <label>Amount (₹) *</label>
            <input type="number" name="amount" step="0.01" min="0.01" required placeholder="e.g. 1500.00">
          </div>

          <div class="form-group">
            <label>Expense Date *</label>
            <input type="date" name="expense_date" required value="<?= date('Y-m-d') ?>">
          </div>

          <button type="submit" class="btn-submit">Record Expense</button>
        </form>
      </div>
    </div>

  </div>

</div>

<?php include_once __DIR__ . '/send_fast_widget.php'; ?>
</body>
</html>
