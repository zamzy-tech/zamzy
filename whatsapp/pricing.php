<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// Fetch active settings
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

$success_msg = '';
$error_msg = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_plan') {
        $plan_name = trim($_POST['plan_name'] ?? '');
        $plan_key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($_POST['plan_key'] ?? '')));
        if (empty($plan_key)) {
            $plan_key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $plan_name));
        }
        $description = trim($_POST['description'] ?? '');
        $scanners_included = intval($_POST['scanners_included'] ?? 1);
        $monthly_price = floatval($_POST['monthly_price'] ?? 0);
        $price_3_months = floatval($_POST['price_3_months'] ?? 0);
        $price_6_months = floatval($_POST['price_6_months'] ?? 0);
        $price_12_months = floatval($_POST['price_12_months'] ?? 0);

        if (empty($plan_name)) {
            $error_msg = 'Plan / Service Name is required.';
        } else {
            try {
                $ins = $pdo->prepare("INSERT INTO service_pricing (plan_key, plan_name, description, scanners_included, monthly_price, price_3_months, price_6_months, price_12_months, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
                $ins->execute([$plan_key, $plan_name, $description, $scanners_included, $monthly_price, $price_3_months, $price_6_months, $price_12_months]);
                $success_msg = "Successfully created pricing plan <strong>" . htmlspecialchars($plan_name) . "</strong>!";
            } catch (PDOException $e) {
                $error_msg = "Failed to add plan: " . $e->getMessage();
            }
        }
    } elseif ($action === 'edit_plan') {
        $plan_id = intval($_POST['plan_id'] ?? 0);
        $plan_name = trim($_POST['plan_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $scanners_included = intval($_POST['scanners_included'] ?? 1);
        $monthly_price = floatval($_POST['monthly_price'] ?? 0);
        $price_3_months = floatval($_POST['price_3_months'] ?? 0);
        $price_6_months = floatval($_POST['price_6_months'] ?? 0);
        $price_12_months = floatval($_POST['price_12_months'] ?? 0);

        if ($plan_id > 0 && !empty($plan_name)) {
            try {
                $upd = $pdo->prepare("UPDATE service_pricing SET plan_name = ?, description = ?, scanners_included = ?, monthly_price = ?, price_3_months = ?, price_6_months = ?, price_12_months = ? WHERE id = ?");
                $upd->execute([$plan_name, $description, $scanners_included, $monthly_price, $price_3_months, $price_6_months, $price_12_months, $plan_id]);
                $success_msg = "Updated pricing rates for <strong>" . htmlspecialchars($plan_name) . "</strong> successfully!";
            } catch (PDOException $e) {
                $error_msg = "Failed to update pricing plan: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $plan_id = intval($_POST['plan_id'] ?? 0);
        $current_status = $_POST['current_status'] ?? 'active';
        $new_status = ($current_status === 'active') ? 'suspended' : 'active';

        if ($plan_id > 0) {
            try {
                $upd = $pdo->prepare("UPDATE service_pricing SET status = ? WHERE id = ?");
                $upd->execute([$new_status, $plan_id]);
                $success_msg = "Plan status toggled to <strong>" . $new_status . "</strong>.";
            } catch (PDOException $e) {
                $error_msg = "Failed to toggle status: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_plan') {
        $plan_id = intval($_POST['plan_id'] ?? 0);
        if ($plan_id > 0) {
            try {
                $del = $pdo->prepare("DELETE FROM service_pricing WHERE id = ?");
                $del->execute([$plan_id]);
                $success_msg = "Pricing plan removed successfully.";
            } catch (PDOException $e) {
                $error_msg = "Failed to delete plan: " . $e->getMessage();
            }
        }
    }
}

// Fetch all pricing plans
try {
    $plans_stmt = $pdo->query("SELECT * FROM service_pricing ORDER BY id ASC");
    $pricing_plans = $plans_stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Service Pricing Console – <?= htmlspecialchars($settings['company_name']) ?></title>
<link rel="stylesheet" href="admin_style.css">
<style>
  .card-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }
  .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
  .badge.active { background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.25); color: #34d399; }
  .badge.suspended { background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.25); color: #f87171; }
  
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

  .price-highlight { font-family: var(--font-mono); font-weight: 700; color: var(--lime); }

  /* Overlay Modal */
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
    max-width: 500px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.8);
  }
  .modal-content h3 {
    color: var(--text-primary) !important;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
    margin-bottom: 20px;
  }
  .modal-buttons { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
  .grid-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
</style>
  <link rel="stylesheet" href="2fa_dashboard_theme.css">
</head>
<body>

<header>
  <div class="logo-block">
    <div class="logo">⚡ <?= htmlspecialchars($settings['company_name']) ?></div>
    <div class="tagline">Admin Control Panel</div>
  </div>
  <nav>
    <a href="invoice.php">Generate Invoice</a>
    <a href="history.php">Billing History</a>
    <a href="clients.php">Clients</a>
    <a href="accounting.php">Accounting</a>
    <a href="pricing.php" class="active">Pricing</a>
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
      <a href="settings.php">Settings</a>
      <a href="api_management.php">API Management</a>
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

  <!-- Pricing Header Banner -->
  <div class="card" style="background: linear-gradient(135deg, var(--bg-surface), var(--bg-card)); border-left: 5px solid var(--lime);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
      <div>
        <h2 style="font-family: var(--font-heading); font-size: 20px; color: var(--text-primary); margin-bottom: 4px;">🏷️ Client Portal Service Pricing Console</h2>
        <p style="font-size: 13px; color: var(--text-muted);">Configure active subscription plans, duration renewal rates, and scanner add-on prices displayed live in the client portal calculator.</p>
      </div>
      <a href="api_link.php" target="_blank" class="btn" style="padding: 10px 18px; font-size: 12px;">🌐 Preview Client Portal</a>
    </div>
  </div>

  <!-- Pricing Management Table -->
  <div class="card">
    <div class="card-title">
      <span>Active Service Pricing & Renewals</span>
      <button class="btn" onclick="openAddModal()" style="font-size: 12px; padding: 8px 16px;">➕ Create New Plan</button>
    </div>
    
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Plan Name / Key</th>
            <th>Description & Features</th>
            <th>Scanners</th>
            <th>1 Month Rate</th>
            <th>3 Months Rate</th>
            <th>6 Months Rate</th>
            <th>12 Months Rate</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pricing_plans)): ?>
            <tr>
              <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 24px;">No pricing plans defined yet. Create one above.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($pricing_plans as $plan): ?>
              <tr>
                <td>
                  <strong style="color: var(--text-primary);"><?= htmlspecialchars($plan['plan_name']) ?></strong>
                  <div style="font-size:11px; color: var(--text-muted); font-family: var(--font-mono); margin-top:2px;">
                    key: <?= htmlspecialchars($plan['plan_key']) ?>
                  </div>
                </td>
                <td style="max-width: 220px; font-size: 12px;">
                  <?= htmlspecialchars($plan['description']) ?>
                </td>
                <td style="text-align: center;">
                  <span class="badge" style="background: rgba(212,255,61,0.08); color: var(--lime); border: 1px solid rgba(212,255,61,0.2);"><?= intval($plan['scanners_included']) ?></span>
                </td>
                <td class="price-highlight">Rs <?= number_format($plan['monthly_price']) ?></td>
                <td class="price-highlight">Rs <?= number_format($plan['price_3_months']) ?></td>
                <td class="price-highlight">Rs <?= number_format($plan['price_6_months']) ?></td>
                <td class="price-highlight">Rs <?= number_format($plan['price_12_months']) ?></td>
                <td>
                  <span class="badge <?= htmlspecialchars($plan['status']) ?>">
                    <?= htmlspecialchars($plan['status']) ?>
                  </span>
                </td>
                <td class="actions-cell">
                  <button class="btn-action edit" onclick='openEditModal(<?= json_encode($plan) ?>)'>
                    ✍️ Edit Rates
                  </button>
                  
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Toggle status for this plan?');">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($plan['status']) ?>">
                    <button type="submit" class="btn-action toggle">
                      🚫 Toggle
                    </button>
                  </form>
                  
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this pricing plan?');">
                    <input type="hidden" name="action" value="delete_plan">
                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                    <button type="submit" class="btn-action delete">
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

</div>

<!-- Add Plan Modal -->
<div class="overlay-modal" id="addPlanModal">
  <div class="modal-content">
    <h3>➕ Add New Service Plan</h3>
    <form method="POST">
      <input type="hidden" name="action" value="add_plan">
      
      <div class="form-group" style="margin-bottom: 12px;">
        <label>Plan Name *</label>
        <input type="text" name="plan_name" placeholder="e.g. Enterprise Plan" required>
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Plan Key ID (Unique Identifier)</label>
        <input type="text" name="plan_key" placeholder="e.g. enterprise (auto-generated if empty)">
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Description & Included Features</label>
        <input type="text" name="description" placeholder="e.g. 5 Device Scanners • Unlimited Messages">
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Scanners Included Base</label>
        <input type="number" name="scanners_included" value="1" min="0">
      </div>

      <div class="grid-2col" style="margin-bottom: 12px;">
        <div class="form-group">
          <label>1 Month Rate (Rs) *</label>
          <input type="number" step="0.01" name="monthly_price" placeholder="799" required>
        </div>
        <div class="form-group">
          <label>3 Months Rate (Rs) *</label>
          <input type="number" step="0.01" name="price_3_months" placeholder="2097" required>
        </div>
      </div>

      <div class="grid-2col" style="margin-bottom: 12px;">
        <div class="form-group">
          <label>6 Months Rate (Rs) *</label>
          <input type="number" step="0.01" name="price_6_months" placeholder="3594" required>
        </div>
        <div class="form-group">
          <label>12 Months Rate (Rs) *</label>
          <input type="number" step="0.01" name="price_12_months" placeholder="5988" required>
        </div>
      </div>

      <div class="modal-buttons">
        <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
        <button type="submit" class="btn-primary">Create Plan</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Plan Modal -->
<div class="overlay-modal" id="editPlanModal">
  <div class="modal-content">
    <h3 id="editModalTitle">✍️ Edit Service Pricing</h3>
    <form method="POST">
      <input type="hidden" name="action" value="edit_plan">
      <input type="hidden" name="plan_id" id="edit_plan_id">
      
      <div class="form-group" style="margin-bottom: 12px;">
        <label>Plan Name *</label>
        <input type="text" name="plan_name" id="edit_plan_name" required>
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Description & Features</label>
        <input type="text" name="description" id="edit_description">
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Scanners Included Base</label>
        <input type="number" name="scanners_included" id="edit_scanners_included" min="0">
      </div>

      <div class="grid-2col" style="margin-bottom: 12px;">
        <div class="form-group">
          <label>1 Month Rate (Rs) *</label>
          <input type="number" step="0.01" name="monthly_price" id="edit_monthly_price" required>
        </div>
        <div class="form-group">
          <label>3 Months Rate (Rs) *</label>
          <input type="number" step="0.01" name="price_3_months" id="edit_price_3_months" required>
        </div>
      </div>

      <div class="grid-2col" style="margin-bottom: 12px;">
        <div class="form-group">
          <label>6 Months Rate (Rs) *</label>
          <input type="number" step="0.01" name="price_6_months" id="edit_price_6_months" required>
        </div>
        <div class="form-group">
          <label>12 Months Rate (Rs) *</label>
          <input type="number" step="0.01" name="price_12_months" id="edit_price_12_months" required>
        </div>
      </div>

      <div class="modal-buttons">
        <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('addPlanModal').style.display = 'flex';
}
function closeAddModal() {
  document.getElementById('addPlanModal').style.display = 'none';
}

function openEditModal(plan) {
  document.getElementById('edit_plan_id').value = plan.id;
  document.getElementById('editModalTitle').innerText = "✍️ Edit Rates for " + plan.plan_name;
  document.getElementById('edit_plan_name').value = plan.plan_name;
  document.getElementById('edit_description').value = plan.description;
  document.getElementById('edit_scanners_included').value = plan.scanners_included;
  document.getElementById('edit_monthly_price').value = plan.monthly_price;
  document.getElementById('edit_price_3_months').value = plan.price_3_months;
  document.getElementById('edit_price_6_months').value = plan.price_6_months;
  document.getElementById('edit_price_12_months').value = plan.price_12_months;
  document.getElementById('editPlanModal').style.display = 'flex';
}
function closeEditModal() {
  document.getElementById('editPlanModal').style.display = 'none';
}
</script>
<?php include_once __DIR__ . '/send_fast_widget.php'; ?>
</body>
</html>
