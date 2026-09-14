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

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_client') {
        $client_name = trim($_POST['client_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $emails = trim($_POST['emails'] ?? '');
        
        if (empty($client_name) || empty($phone) || empty($emails)) {
            $error_msg = 'Please fill in all required fields (Client Name, Phone, and Email).';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO clients (client_name, company_name, phone, whatsapp, emails) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$client_name, $company_name, $phone, $whatsapp, $emails]);
                $success_msg = "Client '{$client_name}' added successfully!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'UNIQUE') !== false) {
                    $error_msg = "A client named '{$client_name}' is already registered.";
                } else {
                    $error_msg = 'Failed to add client: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'edit_client') {
        $client_id = intval($_POST['client_id'] ?? 0);
        $client_name = trim($_POST['client_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $emails = trim($_POST['emails'] ?? '');
        
        if ($client_id <= 0 || empty($client_name) || empty($phone) || empty($emails)) {
            $error_msg = 'Please fill in all required fields (Client Name, Phone, and Email).';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE clients SET client_name = ?, company_name = ?, phone = ?, whatsapp = ?, emails = ? WHERE id = ?");
                $stmt->execute([$client_name, $company_name, $phone, $whatsapp, $emails, $client_id]);
                $success_msg = "Client '{$client_name}' updated successfully!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'UNIQUE') !== false) {
                    $error_msg = "Another client named '{$client_name}' is already registered.";
                } else {
                    $error_msg = 'Failed to update client: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'delete_client') {
        $client_id = intval($_POST['client_id'] ?? 0);
        if ($client_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
                $stmt->execute([$client_id]);
                $success_msg = 'Client deleted successfully!';
            } catch (PDOException $e) {
                $error_msg = 'Failed to delete client: ' . $e->getMessage();
            }
        }
    }
}

// Handle search
$search = trim($_GET['search'] ?? '');
try {
    if ($search !== '') {
        $stmt = $pdo->prepare("SELECT * FROM clients 
            WHERE client_name LIKE ? OR company_name LIKE ? OR emails LIKE ? 
            ORDER BY client_name ASC");
        $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM clients ORDER BY client_name ASC");
    }
    $clients = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client Directory – <?= htmlspecialchars($settings['company_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="admin_style.css">
<style>
  /* Layout Grid overrides */
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

  /* Form and Buttons adjustments */
  .btn-submit {
    width: 100%;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .btn-cancel {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
    margin-top: 8px;
    transition: all 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-family: inherit;
  }
  .btn-cancel:hover {
    color: var(--text-primary);
    background: var(--border-color-soft);
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
  .btn-action.edit {
    background: rgba(212, 255, 61, 0.08);
    color: var(--lime);
    border-color: rgba(212, 255, 61, 0.2);
  }
  .btn-action.edit:hover {
    background: var(--lime);
    color: var(--bg-main);
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
    <a href="clients.php" class="active">Clients</a>
    <a href="accounting.php">Accounting</a>
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
      <a href="settings.php">Settings</a>
      <a href="api_management.php">API Management</a>
    <?php endif; ?>
    <a href="logout.php" class="btn-logout">Logout</a>
  </nav>
</header>

<div class="container">

  <div class="page-header">
    <h1>Client Directory &amp; Database</h1>
    
    <!-- Search Form -->
    <form method="GET" action="clients.php" class="search-container">
      <input type="text" name="search" class="search-input" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, company, or email...">
      <button type="submit" class="btn-search">Search</button>
    </form>
  </div>

  <?php if (!empty($success_msg)): ?>
    <div class="alert success">✅ <?= htmlspecialchars($success_msg) ?></div>
  <?php elseif (!empty($error_msg)): ?>
    <div class="alert error">❌ <?= htmlspecialchars($error_msg) ?></div>
  <?php endif; ?>

  <div class="layout-grid">
    
    <!-- Left: Client Directory Table -->
    <div class="card" style="padding:0">
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Client Name</th>
              <th>Company Name</th>
              <th>Phone</th>
              <th>WhatsApp</th>
              <th>Email Addresses</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($clients)): ?>
              <tr>
                <td colspan="6">
                  <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <div>No registered clients found. Add a new profile on the right!</div>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($clients as $client): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($client['client_name']) ?></strong></td>
                  <td><?= htmlspecialchars($client['company_name'] ?: '—') ?></td>
                  <td style="white-space:nowrap"><?= htmlspecialchars($client['phone']) ?></td>
                  <td style="white-space:nowrap"><?= htmlspecialchars($client['whatsapp'] ?: '—') ?></td>
                  <td style="font-size:12px; color:#4a5568"><?= htmlspecialchars($client['emails']) ?></td>
                  <td style="text-align:right; white-space:nowrap">
                    <button type="button" class="btn-action edit" onclick="startEditClient(<?= htmlspecialchars(json_encode($client)) ?>)">
                      ✏️ Edit
                    </button>
                    <form method="POST" action="clients.php" style="display:inline-block" onsubmit="return confirm('WARNING: Are you sure you want to delete client <?= htmlspecialchars($client['client_name']) ?>? This won\'t delete their existing invoices, but will remove their profile.')">
                      <input type="hidden" name="action" value="delete_client">
                      <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
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

    <!-- Right: Add / Edit Sidebar -->
    <div>
      <div class="card" id="formCard">
        <div class="card-title" id="formCardTitle">➕ Add New Client</div>
        <form method="POST" action="clients.php" id="clientForm">
          <input type="hidden" name="action" id="formAction" value="add_client">
          <input type="hidden" name="client_id" id="formClientId" value="">

          <div class="form-group">
            <label>Client Full Name *</label>
            <input type="text" name="client_name" id="formClientName" required placeholder="e.g. John Doe">
          </div>

          <div class="form-group">
            <label>Company Name</label>
            <input type="text" name="company_name" id="formCompanyName" placeholder="e.g. Acme Corporation">
          </div>

          <div class="form-group">
            <label>Phone Number *</label>
            <input type="tel" name="phone" id="formPhone" required placeholder="e.g. +91 98765 43210">
          </div>

          <div class="form-group">
            <label>WhatsApp Number</label>
            <input type="tel" name="whatsapp" id="formWhatsApp" placeholder="e.g. +91 98765 43210">
          </div>

          <div class="form-group">
            <label>Email Addresses *</label>
            <input type="text" name="emails" id="formEmails" required placeholder="e.g. client@domain.com, support@domain.com">
            <small style="font-size:10px; color:#718096; margin-top:2px">Separate multiple emails with commas</small>
          </div>

          <button type="submit" class="btn-submit" id="formSubmitBtn">Save Client</button>
          <button type="button" class="btn-cancel" id="formCancelBtn" style="display:none" onclick="resetClientForm()">Cancel Edit</button>
        </form>
      </div>
    </div>

  </div>

</div>

<script>
function startEditClient(client) {
  document.getElementById('formCardTitle').textContent = '✏️ Edit Client Profile';
  document.getElementById('formAction').value = 'edit_client';
  document.getElementById('formClientId').value = client.id;
  document.getElementById('formClientName').value = client.client_name;
  document.getElementById('formCompanyName').value = client.company_name;
  document.getElementById('formPhone').value = client.phone;
  document.getElementById('formWhatsApp').value = client.whatsapp || '';
  document.getElementById('formEmails').value = client.emails;
  document.getElementById('formSubmitBtn').textContent = 'Update Client';
  document.getElementById('formCancelBtn').style.display = 'block';
  
  // Scroll form card into view if on mobile
  document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}

function resetClientForm() {
  document.getElementById('formCardTitle').textContent = '➕ Add New Client';
  document.getElementById('formAction').value = 'add_client';
  document.getElementById('formClientId').value = '';
  document.getElementById('clientForm').reset();
  document.getElementById('formSubmitBtn').textContent = 'Save Client';
  document.getElementById('formCancelBtn').style.display = 'none';
}
</script>

<?php include_once __DIR__ . '/send_fast_widget.php'; ?>
</body>
</html>
