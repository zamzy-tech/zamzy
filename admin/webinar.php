<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';
$msgType = 'success';

// Handle Action Updates (Status changes, Delete, Update Notes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $regId = intval($_POST['reg_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        if ($regId > 0 && in_array($status, ['pending', 'completed', 'verified', 'rejected'])) {
            $stmt = $pdo->prepare("UPDATE `zamzy_webinar_registrations` SET `payment_status` = :status, `admin_notes` = :notes WHERE `id` = :id");
            $stmt->execute([':status' => $status, ':notes' => $adminNotes, ':id' => $regId]);
            $msg = "Registration #{$regId} status updated to " . strtoupper($status) . ".";
        }
    } elseif ($action === 'delete_reg') {
        $regId = intval($_POST['reg_id'] ?? 0);
        if ($regId > 0) {
            $stmt = $pdo->prepare("DELETE FROM `zamzy_webinar_registrations` WHERE `id` = :id");
            $stmt->execute([':id' => $regId]);
            $msg = "Registration #{$regId} deleted successfully.";
            $msgType = 'warning';
        }
    }
}

// Fetch Webinar KPI Metrics
$totalRegs = 0;
$verifiedRegs = 0;
$pendingRegs = 0;
$totalRevenue = 0;

if ($pdo) {
    $totalRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations`")->fetchColumn();
    $verifiedRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations` WHERE `payment_status` IN ('completed', 'verified')")->fetchColumn();
    $pendingRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations` WHERE `payment_status` = 'pending'")->fetchColumn();
    $totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM `zamzy_webinar_registrations` WHERE `payment_status` IN ('completed', 'verified')")->fetchColumn();
}

// Search & Filter Query
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');

$whereClauses = [];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(`reg_code` LIKE :s OR `full_name` LIKE :s OR `email` LIKE :s OR `phone` LIKE :s OR `utr_reference` LIKE :s)";
    $params[':s'] = "%$search%";
}

if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'completed', 'verified', 'rejected'])) {
    $whereClauses[] = "`payment_status` = :st";
    $params[':st'] = $statusFilter;
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
$query = "SELECT * FROM `zamzy_webinar_registrations` {$whereSql} ORDER BY `id` DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$registrations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Admin — Full Stack Webinar Registrations</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        .badge-verified { background: rgba(0,255,204,0.15); color: #00ffcc; border: 1px solid rgba(0,255,204,0.3); }
        .badge-pending { background: rgba(255,190,11,0.15); color: #ffbe0b; border: 1px solid rgba(255,190,11,0.3); }
        .badge-rejected { background: rgba(255,94,87,0.15); color: #ff5e57; border: 1px solid rgba(255,94,87,0.3); }
        .utr-code { font-family: var(--admin-font-mono); font-weight: 700; color: var(--admin-cyan); background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body>

<div class="admin-mobile-header">
    <div class="admin-mobile-brand">
        <span class="admin-mobile-logo">ZAMZY<span>.</span></span>
        <span class="admin-mobile-tag">Webinar Portal</span>
    </div>
    <button class="admin-mobile-toggle" id="adminMobileToggle">☰ Menu</button>
</div>

<button class="admin-floating-fab" id="adminFabToggle">⚡ Menu</button>
<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <div>
            <div class="admin-sidebar__brand">
                <span class="admin-sidebar__logo">ZAMZY<span>.</span></span>
                <span class="admin-sidebar__sub">Executive Console</span>
            </div>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav__item"><span>📊</span> Dashboard</a>
                <a href="webinar.php" class="admin-nav__item active"><span>🎓</span> Full Stack Webinar (<?= $totalRegs ?>)</a>
                <a href="inquiries.php" class="admin-nav__item"><span>📬</span> Inquiries &amp; Leads</a>
                <a href="demos.php" class="admin-nav__item"><span>⚡</span> Demo Requests</a>
                <a href="chats.php" class="admin-nav__item"><span>💬</span> Chat Reports</a>
                <a href="testimonials.php" class="admin-nav__item"><span>★</span> Reviews / Proof</a>
                <a href="careers.php" class="admin-nav__item"><span>👥</span> Careers &amp; Guild</a>
                <a href="settings.php" class="admin-nav__item"><span>⚙️</span> Payment &amp; Gateway Settings</a>
                <a href="../fullstack-webinar" target="_blank" class="admin-nav__item"><span>↗</span> View Webinar Page</a>
            </nav>
        </div>

        <div>
            <div class="admin-user-badge">
                <div class="admin-avatar">A</div>
                <div>
                    <div style="font-weight:600;"><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></div>
                    <div style="font-size:0.75rem; color:var(--admin-dim);">Root Administrator</div>
                </div>
            </div>
            <a href="logout.php" class="btn-admin btn-admin-outline btn-admin-sm" style="width:100%; text-align:center;">Terminate Session</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <div>
                <h1 class="admin-title">Full Stack Webinar Registrations</h1>
                <p class="admin-subtitle">Live Online Workshop (₹96) Student Management &amp; Payment Verification</p>
            </div>
            <div style="display:flex; gap:0.8rem; flex-wrap:wrap;">
                <a href="settings.php" class="btn-admin btn-admin-outline">⚙️ FamPay &amp; Payment Settings</a>
                <a href="../fullstack-webinar" target="_blank" class="btn-admin btn-admin-primary">↗ Open Registration Page</a>
            </div>
        </div>

        <?php if (!empty($msg)): ?>
            <div style="padding:1rem 1.4rem; margin-bottom:1.5rem; border-radius:8px; background:<?= $msgType==='warning'?'rgba(255,94,87,0.15)':'rgba(0,255,204,0.15)' ?>; border:1px solid <?= $msgType==='warning'?'#ff5e57':'#00ffcc' ?>; color:#fff; font-family:var(--admin-font-mono); font-size:0.85rem;">
                ⚡ <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <!-- KPI Grid -->
        <div class="admin-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
            <div class="admin-kpi-card">
                <div class="admin-kpi-label">TOTAL REGISTRATIONS</div>
                <div class="admin-kpi-val"><?= number_format($totalRegs) ?></div>
                <div class="admin-kpi-sub">Students &amp; Developers</div>
            </div>
            <div class="admin-kpi-card" style="border-color: rgba(0,255,204,0.4);">
                <div class="admin-kpi-label" style="color:var(--admin-cyan);">VERIFIED PAYMENTS</div>
                <div class="admin-kpi-val" style="color:var(--admin-cyan);"><?= number_format($verifiedRegs) ?></div>
                <div class="admin-kpi-sub">Confirmed Seat Access</div>
            </div>
            <div class="admin-kpi-card" style="border-color: rgba(255,190,11,0.4);">
                <div class="admin-kpi-label" style="color:#ffbe0b;">PENDING VERIFICATION</div>
                <div class="admin-kpi-val" style="color:#ffbe0b;"><?= number_format($pendingRegs) ?></div>
                <div class="admin-kpi-sub">Awaiting UTR / Admin Check</div>
            </div>
            <div class="admin-kpi-card" style="border-color: rgba(199,125,255,0.4);">
                <div class="admin-kpi-label" style="color:#c77dff;">TOTAL REVENUE COLLECTED</div>
                <div class="admin-kpi-val" style="color:#c77dff;">₹<?= number_format($totalRevenue, 2) ?></div>
                <div class="admin-kpi-sub">Gross Collections</div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="admin-card" style="margin-bottom: 1.8rem;">
            <form method="GET" action="webinar.php" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Reg Code, Name, Phone, Email or UTR..." class="admin-input" style="flex:1; min-width:260px;">
                <select name="status" class="admin-input" style="width:180px;" onchange="this.form.submit()">
                    <option value="all" <?= $statusFilter==='all'?'selected':'' ?>>All Statuses</option>
                    <option value="verified" <?= $statusFilter==='verified'?'selected':'' ?>>Verified / Completed</option>
                    <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>Pending</option>
                    <option value="rejected" <?= $statusFilter==='rejected'?'selected':'' ?>>Rejected</option>
                </select>
                <button type="submit" class="btn-admin btn-admin-primary">Filter Results</button>
                <?php if (!empty($search) || $statusFilter !== 'all'): ?>
                    <a href="webinar.php" class="btn-admin btn-admin-outline">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Registrations Table -->
        <div class="admin-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>REG CODE</th>
                            <th>STUDENT DETAILS</th>
                            <th>COLLEGE / COMPANY</th>
                            <th>AMOUNT</th>
                            <th>PAYMENT UTR / REF</th>
                            <th>STATUS</th>
                            <th>DATE &amp; TIME</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registrations)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:3rem; color:var(--admin-dim);">
                                    No webinar registrations found matching your query.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registrations as $row): ?>
                                <tr>
                                    <td>
                                        <strong style="color:var(--admin-cyan); font-family:var(--admin-font-mono);"><?= htmlspecialchars($row['reg_code']) ?></strong>
                                    </td>
                                    <td>
                                        <div style="font-weight:600; color:#fff;"><?= htmlspecialchars($row['full_name']) ?></div>
                                        <div style="font-size:0.78rem; color:var(--admin-dim);"><?= htmlspecialchars($row['email']) ?></div>
                                        <div style="font-size:0.78rem; color:var(--admin-cyan);"><?= htmlspecialchars($row['phone']) ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size:0.85rem; color:#e2e8f0;"><?= htmlspecialchars($row['college_or_company'] ?: 'N/A') ?></div>
                                        <div style="font-size:0.75rem; color:var(--admin-faint);"><?= htmlspecialchars($row['experience_level']) ?> · <?= htmlspecialchars($row['preferred_language']) ?></div>
                                    </td>
                                    <td>
                                        <strong style="color:#fff;">₹<?= number_format($row['amount'], 2) ?></strong>
                                        <div style="font-size:0.72rem; color:var(--admin-faint);"><?= htmlspecialchars($row['payment_method']) ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['utr_reference'])): ?>
                                            <span class="utr-code"><?= htmlspecialchars($row['utr_reference']) ?></span>
                                        <?php else: ?>
                                            <span style="color:var(--admin-faint); font-size:0.78rem;">No UTR submitted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $st = $row['payment_status'];
                                        $stClass = ($st === 'verified' || $st === 'completed') ? 'badge-verified' : ($st === 'rejected' ? 'badge-rejected' : 'badge-pending');
                                        ?>
                                        <span class="admin-badge <?= $stClass ?>"><?= strtoupper($st) ?></span>
                                    </td>
                                    <td style="font-size:0.78rem; color:var(--admin-dim);">
                                        <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                                            <!-- WhatsApp Chat & Confirm Link -->
                                            <?php
                                            $waText = "Hello " . $row['full_name'] . "! 👋 Your registration for the ZAMZY Full Stack Web Development Live Webinar (Reg Code: " . $row['reg_code'] . ") is VERIFIED & CONFIRMED! 🚀%0A%0AWe are excited to have you join us. Further webinar access links & schedule details will be shared on this WhatsApp chat.";
                                            $waLink = "https://wa.me/" . preg_replace('/[^0-9]/', '', $row['phone']) . "?text=" . urlencode($waText);
                                            ?>
                                            <a href="<?= $waLink ?>" target="_blank" class="btn-admin btn-admin-sm" style="background:#25D366; color:#000; font-weight:700;">💬 WhatsApp</a>

                                            <!-- Status Toggle Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="reg_id" value="<?= $row['id'] ?>">
                                                <?php if ($row['payment_status'] !== 'verified' && $row['payment_status'] !== 'completed'): ?>
                                                    <input type="hidden" name="status" value="verified">
                                                    <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#00ffcc; color:#00ffcc;">✓ Verify</button>
                                                <?php else: ?>
                                                    <input type="hidden" name="status" value="pending">
                                                    <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#ffbe0b; color:#ffbe0b;">⏳ Set Pending</button>
                                                <?php endif; ?>
                                            </form>

                                            <!-- Delete Button -->
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete registration #<?= $row['id'] ?>?');">
                                                <input type="hidden" name="action" value="delete_reg">
                                                <input type="hidden" name="reg_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#ff5e57; color:#ff5e57;">✕</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    document.getElementById('adminMobileToggle')?.addEventListener('click', () => {
        document.getElementById('adminSidebar')?.classList.toggle('open');
        document.getElementById('adminSidebarOverlay')?.classList.toggle('open');
    });
    document.getElementById('adminFabToggle')?.addEventListener('click', () => {
        document.getElementById('adminSidebar')?.classList.toggle('open');
        document.getElementById('adminSidebarOverlay')?.classList.toggle('open');
    });
    document.getElementById('adminSidebarOverlay')?.addEventListener('click', () => {
        document.getElementById('adminSidebar')?.classList.remove('open');
        document.getElementById('adminSidebarOverlay')?.classList.remove('open');
    });
</script>
</body>
</html>
