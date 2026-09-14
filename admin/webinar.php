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
    }
}

// Fetch Webinar KPI Metrics
$totalRegs = 0;
$verifiedRegs = 0;
$pendingRegs = 0;
$totalRevenue = 0;

if ($pdo) {
    try { $totalRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations`")->fetchColumn(); } catch (Exception $e) {}
    try { $verifiedRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations` WHERE `payment_status` IN ('completed', 'verified')")->fetchColumn(); } catch (Exception $e) {}
    try { $pendingRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations` WHERE `payment_status` = 'pending'")->fetchColumn(); } catch (Exception $e) {}
    try { $totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM `zamzy_webinar_registrations` WHERE `payment_status` IN ('completed', 'verified')")->fetchColumn(); } catch (Exception $e) {}
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
        <div class="kpi-grid">
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
                    <span class="kpi-card__label" style="color:#f59e0b;">Pending Verification</span>
                    <span class="kpi-card__icon">⏳</span>
                </div>
                <div class="kpi-card__val" style="color:#f59e0b; text-shadow:0 0 20px rgba(245,158,11,0.35);"><?= number_format($pendingRegs) ?></div>
                <div class="kpi-card__sub">Awaiting UTR / Admin Audit</div>
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

        <!-- Filter & Search Bar -->
        <div class="filter-toolbar" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); padding: 1.2rem 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <form method="GET" action="webinar.php" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center; width:100%;">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Reg Code, Student Name, Phone, Email, College or UTR..." class="search-input" style="flex:1; min-width:280px;">
                <select name="status" class="form-control-admin" style="width:auto; min-width:180px;" onchange="this.form.submit()">
                    <option value="all" <?= $statusFilter==='all'?'selected':'' ?>>All Statuses (<?= $totalRegs ?>)</option>
                    <option value="verified" <?= $statusFilter==='verified'?'selected':'' ?>>✓ Verified (<?= $verifiedRegs ?>)</option>
                    <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>⏳ Pending (<?= $pendingRegs ?>)</option>
                    <option value="rejected" <?= $statusFilter==='rejected'?'selected':'' ?>>✕ Rejected</option>
                </select>
                <button type="submit" class="btn-admin btn-admin-primary">Filter Results</button>
                <?php if (!empty($search) || $statusFilter !== 'all'): ?>
                    <a href="webinar.php" class="btn-admin btn-admin-outline">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Registrations Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
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
                            <td colspan="8" style="text-align:center; padding:3.5rem 1rem; color:var(--dim);">
                                <div style="font-size:2rem; margin-bottom:0.8rem; opacity:0.5;">🎓</div>
                                <div style="font-weight:600; font-size:1.1rem; color:#fff; margin-bottom:0.3rem;">No Registrations Found</div>
                                <div style="font-size:0.8rem; color:var(--faint);">There are no webinar registrations matching your search criteria.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registrations as $row): ?>
                            <tr>
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
                                    <?php if (!empty($row['utr_reference'])): ?>
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
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="send_access_email">
                                            <input type="hidden" name="reg_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:var(--cyan); color:var(--cyan);" title="<?= !empty($row['email_sent']) ? 'Resend meeting link & materials to student email' : 'Send meeting link & materials to student email' ?>">
                                                <?= !empty($row['email_sent']) ? '📧 Resend' : '✉️ Email' ?>
                                            </button>
                                        </form>

                                        <!-- Dispatch via WhatsApp Gateway REST API -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="send_access_whatsapp">
                                            <input type="hidden" name="reg_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn-admin btn-admin-sm" style="background:rgba(37,211,102,0.15); border:1px solid rgba(37,211,102,0.4); color:#25D366; font-family:var(--mono);" title="<?= !empty($row['whatsapp_sent']) ? 'Resend automated WhatsApp notification' : 'Dispatch automated WhatsApp notification' ?>">
                                                <?= !empty($row['whatsapp_sent']) ? '💬 Resend WA' : '⚡ API WA' ?>
                                            </button>
                                        </form>

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
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="reg_id" value="<?= $row['id'] ?>">
                                            <?php if ($row['payment_status'] !== 'verified' && $row['payment_status'] !== 'completed'): ?>
                                                <input type="hidden" name="status" value="verified">
                                                <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#10b981; color:#10b981;" title="Mark as Verified (Triggers automated access email)">
                                                    ✓ Verify
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#f59e0b; color:#f59e0b;" title="Revert to Pending">
                                                    ⏳ Pending
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <?php if ($row['payment_status'] !== 'verified' && $row['payment_status'] !== 'completed'): ?>
                                        <!-- Force Unlock + Send Notifications (For paid but unverified - e.g. FamGateway) -->
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Force unlock seat for <?= htmlspecialchars($row['full_name']) ?>? This will mark as verified and send Email + WhatsApp access materials.');">
                                            <input type="hidden" name="action" value="force_unlock_seat">
                                            <input type="hidden" name="reg_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="tx_ref" value="FG_<?= htmlspecialchars($row['transaction_id'] ?? 'MANUAL') ?>">
                                            <button type="submit" class="btn-admin btn-admin-sm" style="background:linear-gradient(135deg,#7c3aed,#2563eb); color:#fff; border:none; font-weight:700;" title="Force unlock: Mark verified + Send Email & WhatsApp instantly">
                                                🔓 Unlock &amp; Notify
                                            </button>
                                        </form>
                                        <?php endif; ?>

                                        <!-- Delete Button -->
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete registration #<?= $row['id'] ?> (<?= htmlspecialchars($row['full_name']) ?>)?');">
                                            <input type="hidden" name="action" value="delete_reg">
                                            <input type="hidden" name="reg_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="border-color:#ef4444; color:#ef4444; padding:0.45rem 0.6rem;" title="Delete Record">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
