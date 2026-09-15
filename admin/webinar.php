<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';
$msgType = 'success';

// Handle Action Updates (Status changes, Delete, Update Notes, Send Email)
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

            // If marked verified, automatically deliver access materials via SMTP & WhatsApp API
            if (in_array($status, ['verified', 'completed'])) {
                require_once __DIR__ . '/../mailer.php';
                $emailRes = sendWebinarDeliveryEmail($regId);
                if ($emailRes['success']) {
                    $msg .= " ✓ Access materials email dispatched to student.";
                } else {
                    $msg .= " ⚠️ Email delivery notice: " . $emailRes['message'];
                }
                $waRes = sendWebinarDeliveryWhatsApp($regId);
                if ($waRes['success']) {
                    $msg .= " ✓ WhatsApp notification dispatched.";
                } else {
                    $msg .= " ⚠️ WhatsApp API notice: " . ($waRes['message'] ?? 'Not sent');
                }
            }
        }
    } elseif ($action === 'send_access_email') {
        $regId = intval($_POST['reg_id'] ?? 0);
        if ($regId > 0) {
            require_once __DIR__ . '/../mailer.php';
            $emailRes = sendWebinarDeliveryEmail($regId);
            if ($emailRes['success']) {
                $msg = "Webinar access links and PDF resources sent successfully to student email!";
                $msgType = 'success';
            } else {
                $msg = "Failed to dispatch email: " . $emailRes['message'];
                $msgType = 'warning';
            }
        }
    } elseif ($action === 'send_access_whatsapp') {
        $regId = intval($_POST['reg_id'] ?? 0);
        if ($regId > 0) {
            require_once __DIR__ . '/../mailer.php';
            $waRes = sendWebinarDeliveryWhatsApp($regId);
            if ($waRes['success']) {
                $msg = "Webinar access details dispatched successfully via WhatsApp API!";
                $msgType = 'success';
            } else {
                $msg = "WhatsApp dispatch failed: " . ($waRes['message'] ?? 'Check API settings & device connection');
                $msgType = 'warning';
            }
        }
    } elseif ($action === 'force_unlock_seat') {
        // Force unlock: mark as verified and send access materials immediately
        $regId = intval($_POST['reg_id'] ?? 0);
        $txRef = trim($_POST['tx_ref'] ?? 'MANUAL_ADMIN_' . date('YmdHis'));
        if ($regId > 0) {
            // Mark as verified in DB
            $stmt = $pdo->prepare("UPDATE `zamzy_webinar_registrations` SET `payment_status` = 'verified', `utr_reference` = :utr WHERE `id` = :id");
            $stmt->execute([':utr' => $txRef, ':id' => $regId]);
            // Fetch full record
            $fStmt = $pdo->prepare("SELECT * FROM `zamzy_webinar_registrations` WHERE `id` = :id LIMIT 1");
            $fStmt->execute([':id' => $regId]);
            $student = $fStmt->fetch();
            $msg = "✅ Seat force-unlocked for #{$regId}.";
            if ($student) {
                require_once __DIR__ . '/../mailer.php';
                $emailRes = sendWebinarDeliveryEmail($student);
                $waRes = sendWebinarDeliveryWhatsApp($student);
                $msg .= $emailRes['success'] ? " ✓ Access email dispatched." : " ⚠️ Email: " . $emailRes['message'];
                $msg .= ($waRes['success'] ?? false) ? " ✓ WhatsApp sent." : "";
            }
            $msgType = 'success';
        }
    } elseif ($action === 'delete_reg') {
        $regId = intval($_POST['reg_id'] ?? 0);
        if ($regId > 0) {
            $stmt = $pdo->prepare("DELETE FROM `zamzy_webinar_registrations` WHERE `id` = :id");
            $stmt->execute([':id' => $regId]);
            $msg = "Registration #{$regId} deleted successfully.";
            $msgType = 'warning';
        }
    } elseif ($action === 'delete_bulk') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids) && $pdo) {
            $cleanIds = array_map('intval', $ids);
            $inClause = implode(',', $cleanIds);
            $pdo->exec("DELETE FROM `zamzy_webinar_registrations` WHERE `id` IN ($inClause)");
            $msg = count($cleanIds) . " webinar registrations deleted successfully.";
            $msgType = 'warning';
        }
    } elseif ($action === 'verify_bulk') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids) && $pdo) {
            $cleanIds = array_map('intval', $ids);
            $inClause = implode(',', $cleanIds);
            $pdo->exec("UPDATE `zamzy_webinar_registrations` SET `payment_status` = 'verified' WHERE `id` IN ($inClause)");
            $msg = count($cleanIds) . " registrations marked as VERIFIED.";
            $msgType = 'success';
        }
    }
}

// Handle Quick UPI Daily Limit Toggle from webinar admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_upi_limit') {
    $currentLimitReached = getSetting('upi_daily_limit_reached', '0');
    $newLimitState = ($currentLimitReached === '1') ? '0' : '1';
    setSetting('upi_daily_limit_reached', $newLimitState);
    $msg = ($newLimitState === '1') ? '⚠️ Daily UPI Limit manually marked as REACHED (Pay Online disabled, Pay Later highlighted).' : '✓ Daily UPI Limit reset to NORMAL (Pay Online active).';
    $msgType = 'success';
}

// Fetch Webinar KPI Metrics
$totalRegs = 0;
$verifiedRegs = 0;
$pendingRegs = 0;
$payLaterRegs = 0;
$totalRevenue = 0;
$todayVerifiedCount = 0;

$upiDailyLimitCount = intval(getSetting('upi_daily_limit_count', '10'));
if ($upiDailyLimitCount <= 0) $upiDailyLimitCount = 10;
$upiManualOverride = getSetting('upi_daily_limit_reached', '0');

if ($pdo) {
    try { $totalRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations`")->fetchColumn(); } catch (Exception $e) {}
    try { $verifiedRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations` WHERE `payment_status` IN ('completed', 'verified')")->fetchColumn(); } catch (Exception $e) {}
    try { $pendingRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations` WHERE `payment_status` = 'pending'")->fetchColumn(); } catch (Exception $e) {}
    try { $payLaterRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations` WHERE `payment_method` LIKE '%Pay Later%' OR `payment_method` LIKE '%pay_later%' OR `utr_reference` = 'PAY_LATER_RESERVED'")->fetchColumn(); } catch (Exception $e) {}
    try { $totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM `zamzy_webinar_registrations` WHERE `payment_status` IN ('completed', 'verified')")->fetchColumn(); } catch (Exception $e) {}
    try { $todayVerifiedCount = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations` WHERE `payment_status` IN ('verified', 'completed') AND `payment_method` NOT LIKE '%Free%' AND DATE(`created_at`) = CURDATE()")->fetchColumn(); } catch (Exception $e) {}
}

$isUpiLimitReached = ($upiManualOverride === '1' || $todayVerifiedCount >= $upiDailyLimitCount);

// Search & Filter Query
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');

$whereClauses = [];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(`reg_code` LIKE :s OR `full_name` LIKE :s OR `email` LIKE :s OR `phone` LIKE :s OR `utr_reference` LIKE :s OR `payment_method` LIKE :s)";
    $params[':s'] = "%$search%";
}

if ($statusFilter === 'pay_later') {
    $whereClauses[] = "(`payment_method` LIKE '%Pay Later%' OR `payment_method` LIKE '%pay_later%' OR `utr_reference` = 'PAY_LATER_RESERVED')";
} elseif ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'completed', 'verified', 'rejected'])) {
    $whereClauses[] = "`payment_status` = :st";
    $params[':st'] = $statusFilter;
}

$registrations = [];
if ($pdo) {
    try {
        $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
        $query = "SELECT * FROM `zamzy_webinar_registrations` {$whereSql} ORDER BY `id` DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $registrations = $stmt->fetchAll();
    } catch (Exception $e) {}
}
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
                <a href="logs.php" class="admin-nav__item"><span>🛡️</span> Security &amp; IP Logs</a>
                <a href="settings.php" class="admin-nav__item"><span>⚙️</span> Settings &amp; SMTP</a>
                <a href="../fullstack-webinar" target="_blank" class="admin-nav__item"><span>↗</span> View Webinar Page</a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
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
        <header class="admin-topbar">
            <div>
                <h1 class="admin-page-title">Full Stack Webinar Registrations</h1>
                <p class="admin-page-sub">Live Online Workshop (₹96) · Student Enrolment, FamPay Orders &amp; Payment Audit</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="settings.php" class="btn-admin btn-admin-outline">⚙️ Deliverables &amp; SMTP Settings</a>
                <a href="../fullstack-webinar" target="_blank" class="btn-admin btn-admin-primary">↗ View Webinar Page</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box" style="<?= $msgType==='warning'?'border-color:#ef4444; background:rgba(239,68,68,0.12); color:#fca5a5;':'' ?>">
                ⚡ <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <!-- KPI Grid -->
        <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Total Registrations</span>
                    <span class="kpi-card__icon">🎓</span>
                </div>
                <div class="kpi-card__val"><?= number_format($totalRegs) ?></div>
                <div class="kpi-card__sub">Enrolled Students &amp; Developers</div>
            </div>

            <div class="kpi-card" style="border-color: rgba(16, 185, 129, 0.4);">
                <div class="kpi-card__top">
                    <span class="kpi-card__label" style="color:#10b981;">Verified Payments</span>
                    <span class="kpi-card__icon">💳</span>
                </div>
                <div class="kpi-card__val" style="color:#10b981; text-shadow:0 0 20px rgba(16,185,129,0.35);"><?= number_format($verifiedRegs) ?></div>
                <div class="kpi-card__sub">Confirmed Seat Access</div>
            </div>

            <div class="kpi-card" style="border-color: rgba(245, 158, 11, 0.4);">
                <div class="kpi-card__top">
                    <span class="kpi-card__label" style="color:#f59e0b;">Pay Later / Reserved</span>
                    <span class="kpi-card__icon">⏳</span>
                </div>
                <div class="kpi-card__val" style="color:#f59e0b; text-shadow:0 0 20px rgba(245,158,11,0.35);"><?= number_format($payLaterRegs) ?></div>
                <div class="kpi-card__sub">Direct Coordinator WhatsApp</div>
            </div>

            <div class="kpi-card" style="border-color: rgba(139, 92, 246, 0.4);">
                <div class="kpi-card__top">
                    <span class="kpi-card__label" style="color:#c4b5fd;">Total Revenue</span>
                    <span class="kpi-card__icon">💰</span>
                </div>
                <div class="kpi-card__val" style="color:#c4b5fd; text-shadow:0 0 20px rgba(139,92,246,0.35);">₹<?= number_format($totalRevenue, 0) ?></div>
                <div class="kpi-card__sub">Gross Collections</div>
            </div>
        </div>

        <!-- UPI Daily Limit & Filter Bar -->
        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); padding: 1.2rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; display:flex; flex-direction:column; gap:1.2rem;">
            
            <!-- Quick UPI Limit Status Strip -->
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; padding-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.06);">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <span style="font-size:1.4rem;"><?= $isUpiLimitReached ? '🚫' : '⚡' ?></span>
                    <div>
                        <div style="font-weight:700; font-size:0.92rem; color:#ffffff;">
                            UPI Daily Limit Status: 
                            <span style="color: <?= $isUpiLimitReached ? '#ef4444' : '#10b981' ?>; font-family:var(--admin-font-mono);">
                                <?= $isUpiLimitReached ? 'REACHED (10/10 LIMIT FULL)' : "ACTIVE ({$todayVerifiedCount}/{$upiDailyLimitCount} Today)" ?>
                            </span>
                        </div>
                        <div style="font-size:0.75rem; color:var(--admin-dim);">
                            <?= $isUpiLimitReached 
                                ? 'Students are guided to "Pay Later". Pay Online button is non-clickable.' 
                                : 'Automated QR / FamPay UPI checkout is active for participants.' ?>
                        </div>
                    </div>
                </div>

                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="toggle_upi_limit">
                    <button type="submit" class="btn-admin btn-admin-sm" style="<?= $isUpiLimitReached ? 'background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#34d399;' : 'background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5;' ?>">
                        <?= $isUpiLimitReached ? '✓ Reset Limit to Normal' : '🚫 Force Set Limit Full (10/10)' ?>
                    </button>
                </form>
            </div>

            <!-- Search & Filter Controls -->
            <form method="GET" action="webinar.php" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center; width:100%;">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Reg Code, Student Name, Phone, Email, College or UTR..." class="search-input" style="flex:1; min-width:280px;">
                <select name="status" class="form-control-admin" style="width:auto; min-width:190px;" onchange="this.form.submit()">
                    <option value="all" <?= $statusFilter==='all'?'selected':'' ?>>All Statuses (<?= $totalRegs ?>)</option>
                    <option value="verified" <?= $statusFilter==='verified'?'selected':'' ?>>✓ Verified (<?= $verifiedRegs ?>)</option>
                    <option value="pay_later" <?= $statusFilter==='pay_later'?'selected':'' ?>>⏳ Pay Later / Reserved (<?= $payLaterRegs ?>)</option>
                    <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>⏳ All Pending (<?= $pendingRegs ?>)</option>
                    <option value="rejected" <?= $statusFilter==='rejected'?'selected':'' ?>>✕ Rejected</option>
                </select>
                <button type="submit" class="btn-admin btn-admin-primary">Filter Results</button>
                <?php if (!empty($search) || $statusFilter !== 'all'): ?>
                    <a href="webinar.php" class="btn-admin btn-admin-outline">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bulk Action Form & Registrations Table -->
        <form id="bulkForm" action="webinar.php" method="POST">
            <input type="hidden" name="action" id="bulkActionInput" value="delete_bulk">
            <div class="bulk-action-bar">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" id="selectAll" class="admin-checkbox">
                    <strong>Select All</strong>
                </label>
                <div style="display:flex; gap:0.6rem; align-items:center;">
                    <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" id="bulkVerifyBtn" style="display:none; border-color:#10b981; color:#10b981;" onclick="document.getElementById('bulkActionInput').value='verify_bulk'; return confirm('Mark all selected registrations as VERIFIED?')">
                        ✓ Verify Selected (<span class="selectedCount">0</span>)
                    </button>
                    <button type="submit" class="btn-danger-admin" id="bulkDeleteBtn" style="display:none;" onclick="document.getElementById('bulkActionInput').value='delete_bulk'; return confirm('Are you sure you want to PERMANENTLY DELETE all selected registrations?')">
                        🗑️ Delete Selected (<span class="selectedCount">0</span>)
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px; text-align:center;"></th>
                            <th>Reg Code</th>
                            <th>Student Details</th>
                            <th>College / Background</th>
                            <th>Fee &amp; Gateway</th>
                            <th>Payment UTR / Ref</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registrations)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:3.5rem 1rem; color:var(--dim);">
                                    <div style="font-size:2rem; margin-bottom:0.8rem; opacity:0.5;">🎓</div>
                                    <div style="font-weight:600; font-size:1.1rem; color:#fff; margin-bottom:0.3rem;">No Registrations Found</div>
                                    <div style="font-size:0.8rem; color:var(--faint);">There are no webinar registrations matching your search criteria.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registrations as $row): ?>
                                <?php 
                                    $isPayLaterRow = (strpos($row['payment_method'], 'Pay Later') !== false || strpos($row['payment_method'], 'pay_later') !== false || $row['utr_reference'] === 'PAY_LATER_RESERVED');
                                ?>
                                <tr>
                                    <td style="text-align:center;">
                                        <input type="checkbox" name="ids[]" value="<?= $row['id'] ?>" class="row-checkbox admin-checkbox">
                                    </td>
                                    <td>
                                        <span class="utr-code" style="font-size:0.75rem; letter-spacing:0.08em;"><?= htmlspecialchars($row['reg_code']) ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight:700; color:#fff; font-size:0.92rem;"><?= htmlspecialchars($row['full_name']) ?></div>
                                        <div style="font-size:0.76rem; color:var(--faint); margin-top:2px;">
                                            <a href="mailto:<?= htmlspecialchars($row['email']) ?>" style="color:inherit; text-decoration:underline;"><?= htmlspecialchars($row['email']) ?></a>
                                        </div>
                                        <div style="font-size:0.76rem; color:var(--cyan); margin-top:2px; font-weight:600;">
                                            📞 <a href="tel:<?= htmlspecialchars($row['phone']) ?>" style="color:var(--cyan);"><?= htmlspecialchars($row['phone']) ?></a>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size:0.85rem; color:#e2e8f0; font-weight:500;"><?= htmlspecialchars($row['college_or_company'] ?: 'Individual Student') ?></div>
                                        <div style="font-size:0.72rem; color:var(--faint); margin-top:3px;">
                                            <span style="background:rgba(255,255,255,0.06); padding:2px 6px; border-radius:4px;"><?= htmlspecialchars($row['experience_level']) ?></span>
                                            &nbsp;·&nbsp; <?= htmlspecialchars($row['preferred_language']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight:700; color:#fff; font-size:0.95rem;">₹<?= number_format($row['amount'], 2) ?></div>
                                        <div style="font-size:0.7rem; color:var(--faint); margin-top:2px;"><?= htmlspecialchars($row['payment_method']) ?></div>
                                        <?php if (!empty($row['coupon_code'])): ?>
                                            <div style="margin-top:4px;">
                                                <span style="font-size:0.68rem; background:rgba(168,85,247,0.15); border:1px solid rgba(168,85,247,0.35); color:#c084fc; padding:2px 6px; border-radius:4px; font-weight:700; font-family:var(--mono);" title="Promo Coupon Code Applied">
                                                    🎟️ <?= htmlspecialchars($row['coupon_code']) ?> (-₹<?= number_format($row['discount_amount'] ?? 0, 0) ?>)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isPayLaterRow && ($row['utr_reference'] === 'PAY_LATER_RESERVED' || empty($row['utr_reference']))): ?>
                                            <span class="utr-code" style="background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.4); color:#ffbe0b;">
                                                ⏳ PAY LATER
                                            </span>
                                        <?php elseif (!empty($row['utr_reference'])): ?>
                                            <span class="utr-code" style="background:rgba(139,92,246,0.15); border-color:rgba(139,92,246,0.3); color:#c4b5fd;">
                                                <?= htmlspecialchars($row['utr_reference']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:var(--faint); font-size:0.75rem; font-style:italic;">No UTR submitted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $st = strtolower($row['payment_status']);
                                        $stClass = ($st === 'verified' || $st === 'completed') ? 'badge-verified' : ($st === 'rejected' ? 'badge-rejected' : 'badge-pending');
                                        ?>
                                        <span class="badge-status <?= $stClass ?>">
                                            <?= ($st === 'verified' || $st === 'completed') ? '✓ ' : ($st === 'pending' ? '⏳ ' : '✕ ') ?><?= strtoupper($st) ?>
                                        </span>
                                        <?php if ($isPayLaterRow && $st === 'pending'): ?>
                                            <div style="margin-top:4px;">
                                                <span style="font-size:0.68rem; background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.35); color:#fbbf24; padding:2px 6px; border-radius:4px; font-weight:700; font-family:var(--mono);">🤝 Reserved</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($row['email_sent'])): ?>
                                            <div style="margin-top:4px;">
                                                <span style="font-size:0.68rem; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:#34d399; padding:2px 6px; border-radius:4px; font-weight:600; font-family:var(--mono);">📧 Email Sent</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($row['whatsapp_sent'])): ?>
                                            <div style="margin-top:3px;">
                                                <span style="font-size:0.68rem; background:rgba(37,211,102,0.15); border:1px solid rgba(37,211,102,0.3); color:#25D366; padding:2px 6px; border-radius:4px; font-weight:600; font-family:var(--mono);">💬 WA Sent</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:0.78rem; color:var(--dim); white-space:nowrap;">
                                        <?= date('d M Y', strtotime($row['created_at'])) ?><br>
                                        <span style="font-size:0.7rem; color:var(--faint);"><?= date('h:i A', strtotime($row['created_at'])) ?></span>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:inline-flex; gap:0.45rem; align-items:center; justify-content:flex-end; flex-wrap:wrap;">
                                            <!-- Send / Resend Email Deliverables -->
                                            <button type="button" onclick="submitSingleAction('send_access_email', <?= $row['id'] ?>)" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:var(--cyan); color:var(--cyan);" title="<?= !empty($row['email_sent']) ? 'Resend meeting link & materials to student email' : 'Send meeting link & materials to student email' ?>">
                                                <?= !empty($row['email_sent']) ? '📧 Resend' : '✉️ Email' ?>
                                            </button>

                                            <!-- Dispatch via WhatsApp Gateway REST API -->
                                            <button type="button" onclick="submitSingleAction('send_access_whatsapp', <?= $row['id'] ?>)" class="btn-admin btn-admin-sm" style="background:rgba(37,211,102,0.15); border:1px solid rgba(37,211,102,0.4); color:#25D366; font-family:var(--mono);" title="<?= !empty($row['whatsapp_sent']) ? 'Resend automated WhatsApp notification' : 'Dispatch automated WhatsApp notification' ?>">
                                                <?= !empty($row['whatsapp_sent']) ? '💬 Resend WA' : '⚡ API WA' ?>
                                            </button>

                                            <!-- Direct WhatsApp Chat Link Fallback -->
                                            <?php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $row['phone']);
                                            if (strlen($cleanPhone) === 10) {
                                                $cleanPhone = '91' . $cleanPhone;
                                            }
                                            $waText = "Hello " . $row['full_name'] . "! 👋 Your registration for the ZAMZY Full Stack Web Development Live Webinar (Reg Code: " . $row['reg_code'] . ") is VERIFIED & CONFIRMED! 🚀\n\nWe are excited to have you join us. Further webinar access links & schedule details will be shared on this WhatsApp chat.";
                                            $waLink = "https://wa.me/" . $cleanPhone . "?text=" . rawurlencode($waText);
                                            ?>
                                            <a href="<?= $waLink ?>" target="_blank" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#25D366; color:#25D366;" title="Open Direct WhatsApp Chat Link">
                                                💬 Chat
                                            </a>

                                            <!-- Status Toggle Form -->
                                            <?php if ($row['payment_status'] !== 'verified' && $row['payment_status'] !== 'completed'): ?>
                                                <button type="button" onclick="submitStatusAction(<?= $row['id'] ?>, 'verified')" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#10b981; color:#10b981;" title="Mark as Verified (Triggers automated access email)">
                                                    ✓ Verify
                                                </button>
                                            <?php else: ?>
                                                <button type="button" onclick="submitStatusAction(<?= $row['id'] ?>, 'pending')" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#f59e0b; color:#f59e0b;" title="Revert to Pending">
                                                    ⏳ Pending
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($row['payment_status'] !== 'verified' && $row['payment_status'] !== 'completed'): ?>
                                            <!-- Force Unlock + Send Notifications -->
                                            <button type="button" onclick="submitForceUnlock(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['full_name'])) ?>', '<?= htmlspecialchars($row['transaction_id'] ?? 'MANUAL') ?>')" class="btn-admin btn-admin-sm" style="background:linear-gradient(135deg,#7c3aed,#2563eb); color:#fff; border:none; font-weight:700;" title="Force unlock: Mark verified + Send Email & WhatsApp instantly">
                                                🔓 Unlock &amp; Notify
                                            </button>
                                            <?php endif; ?>

                                            <?php if (!empty($row['transaction_id']) && (strpos($row['transaction_id'], 'fg_') === 0 || strpos($row['transaction_id'], 'FG') === 0)): ?>
                                            <!-- FamGateway Official PDF Tax Receipt / Invoice -->
                                            <a href="https://famgateway.in/transaction-details.php?id=<?= urlencode($row['transaction_id']) ?>&download=pdf" target="_blank" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#38bdf8; color:#38bdf8;" title="Download Official FamGateway PDF Tax Invoice">
                                                📄 Invoice
                                            </a>
                                            <?php endif; ?>

                                            <!-- Delete Button -->
                                            <button type="button" onclick="submitDeleteReg(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['full_name'])) ?>')" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#ef4444; color:#ef4444; padding:0.45rem 0.6rem;" title="Delete Record">
                                                ✕
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Hidden Single Action Form -->
        <form id="singleActionForm" method="POST" style="display:none;">
            <input type="hidden" name="action" id="singleActionType">
            <input type="hidden" name="reg_id" id="singleActionRegId">
            <input type="hidden" name="status" id="singleActionStatus">
            <input type="hidden" name="tx_ref" id="singleActionTxRef">
        </form>
    </main>
</div>

<script>
    // Multi-Select & Bulk Actions
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkVerifyBtn = document.getElementById('bulkVerifyBtn');
    const selectedCountEls = document.querySelectorAll('.selectedCount');

    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        selectedCountEls.forEach(el => el.textContent = checkedCount);
        if (bulkDeleteBtn) bulkDeleteBtn.style.display = checkedCount > 0 ? 'inline-flex' : 'none';
        if (bulkVerifyBtn) bulkVerifyBtn.style.display = checkedCount > 0 ? 'inline-flex' : 'none';
        if (selectAll) selectAll.checked = (checkedCount > 0 && checkedCount === rowCheckboxes.length);
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });

    // Single Action Triggers
    function submitSingleAction(actionName, regId) {
        document.getElementById('singleActionType').value = actionName;
        document.getElementById('singleActionRegId').value = regId;
        document.getElementById('singleActionForm').submit();
    }

    function submitStatusAction(regId, status) {
        document.getElementById('singleActionType').value = 'update_status';
        document.getElementById('singleActionRegId').value = regId;
        document.getElementById('singleActionStatus').value = status;
        document.getElementById('singleActionForm').submit();
    }

    function submitForceUnlock(regId, name, txId) {
        if (confirm('Force unlock seat for ' + name + '? This will mark as verified and send Email + WhatsApp access materials.')) {
            document.getElementById('singleActionType').value = 'force_unlock_seat';
            document.getElementById('singleActionRegId').value = regId;
            document.getElementById('singleActionTxRef').value = 'FG_' + txId;
            document.getElementById('singleActionForm').submit();
        }
    }

    function submitDeleteReg(regId, name) {
        if (confirm('Are you sure you want to delete registration #' + regId + ' (' + name + ')?')) {
            document.getElementById('singleActionType').value = 'delete_reg';
            document.getElementById('singleActionRegId').value = regId;
            document.getElementById('singleActionForm').submit();
        }
    }

    // Sidebar Mobile Toggle
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
