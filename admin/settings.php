<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../mailer.php';

$pdo = getDbConnection();

$msg = '';
$msgType = 'success';
$smtpTestLog = '';

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // 1. Save Settings
    if (isset($_POST['save_settings'])) {
        $settingsToUpdate = [
            // P2P Gateway & UPI
            'famgateway_api_key' => trim($_POST['famgateway_api_key'] ?? 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3'),
            'upi_id' => trim($_POST['upi_id'] ?? '8667702473@fam'),
            'upi_name' => trim($_POST['upi_name'] ?? 'Sameer Ahamadh'),
            'webinar_price' => trim($_POST['webinar_price'] ?? '96'),
            'webinar_title' => trim($_POST['webinar_title'] ?? 'Full Stack Web Development Live Webinar'),
            
            // SMTP Settings
            'smtp_host' => trim($_POST['smtp_host'] ?? 'mail.zamzy.in'),
            'smtp_port' => trim($_POST['smtp_port'] ?? '465'),
            'smtp_secure' => trim($_POST['smtp_secure'] ?? 'ssl'),
            'smtp_username' => trim($_POST['smtp_username'] ?? 'no-reply@zamzy.in'),
            'smtp_password' => trim($_POST['smtp_password'] ?? 'shacartc_zamzy'),
            'smtp_from_email' => trim($_POST['smtp_from_email'] ?? 'no-reply@zamzy.in'),
            'smtp_from_name' => trim($_POST['smtp_from_name'] ?? 'ZAMZY Learning'),
            
            // Webinar Deliverables
            'webinar_schedule' => trim($_POST['webinar_schedule'] ?? 'Live Batch: Weekends 6:00 PM - 8:30 PM IST'),
            'webinar_meeting_link' => trim($_POST['webinar_meeting_link'] ?? ''),
            'webinar_whatsapp_link' => trim($_POST['webinar_whatsapp_link'] ?? ''),
            'webinar_resources' => trim($_POST['webinar_resources'] ?? ''),
            'webinar_email_notes' => trim($_POST['webinar_email_notes'] ?? ''),

            // WhatsApp Message API Settings
            'whatsapp_api_enabled' => isset($_POST['whatsapp_api_enabled']) ? '1' : '0',
            'whatsapp_api_endpoint' => trim($_POST['whatsapp_api_endpoint'] ?? 'https://zamzy.in/api/whatsapp.php'),
            'whatsapp_api_key' => trim($_POST['whatsapp_api_key'] ?? '3c5b81fc69022511c682a14156e1c1fd'),
            'whatsapp_msg_template' => trim($_POST['whatsapp_msg_template'] ?? '')
        ];

        foreach ($settingsToUpdate as $key => $val) {
            setSetting($key, $val);
        }

        $msg = "Configuration saved successfully! All SMTP, WhatsApp Message API, and Webinar deliverables are updated.";
        $msgType = 'success';
    }

    // 2. Test SMTP Connection
    if (isset($_POST['send_test_email'])) {
        $testTo = trim($_POST['test_email_address'] ?? '');
        if (empty($testTo) || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
            $msg = "Please enter a valid destination email address for testing.";
            $msgType = 'danger';
        } else {
            $testSubject = "🧪 ZAMZY SMTP Test Connection — " . date('Y-m-d H:i:s');
            $testHtml = "
            <div style='font-family:sans-serif; background:#0f0f1c; color:#e2e8f0; padding:25px; border-radius:8px;'>
                <h2 style='color:#00ffcc;'>✓ SMTP Authentication &amp; Dispatch Successful!</h2>
                <p>This is an automated verification email sent from your ZAMZY Executive Admin Console.</p>
                <p><strong>Outgoing Server:</strong> " . htmlspecialchars(getSetting('smtp_host', 'mail.zamzy.in')) . ":" . htmlspecialchars(getSetting('smtp_port', '465')) . " (" . htmlspecialchars(getSetting('smtp_secure', 'ssl')) . ")</p>
                <p><strong>Authenticated User:</strong> " . htmlspecialchars(getSetting('smtp_username', 'no-reply@zamzy.in')) . "</p>
                <p><strong>Timestamp:</strong> " . date('r') . "</p>
                <hr style='border:none; border-top:1px solid #334155; margin:20px 0;'>
                <small style='color:#94a3b8;'>ZAMZY Cloud &amp; Autonomous Education Platform</small>
            </div>";

            $debugLog = "";
            $res = sendSmtpEmail($testTo, $testSubject, $testHtml, "Test Recipient", $debugLog);
            $smtpTestLog = $debugLog;

            if ($res['success']) {
                $msg = "✓ Test email sent successfully to {$testTo}!";
                $msgType = 'success';
            } else {
                $msg = "❌ Failed to dispatch email: " . $res['message'];
                $msgType = 'danger';
            }
        }
    }

    // 3. Test Full Webinar Delivery Email
    if (isset($_POST['send_sample_webinar_email'])) {
        $testTo = trim($_POST['test_webinar_email_address'] ?? '');
        if (empty($testTo) || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
            $msg = "Please enter a valid email to preview the webinar delivery email.";
            $msgType = 'danger';
        } else {
            $mockStudent = [
                'id' => 0,
                'full_name' => 'Demo Student',
                'email' => $testTo,
                'reg_code' => 'ZAM-SAMPLE-' . rand(1000, 9999),
                'amount' => getSetting('webinar_price', '96'),
                'utr_reference' => 'DEMO' . rand(100000000000, 999999999999)
            ];

            $res = sendWebinarDeliveryEmail($mockStudent);
            if ($res['success']) {
                $msg = "✓ Webinar Access email dispatched to {$testTo}!";
                $msgType = 'success';
            } else {
                $msg = "❌ Delivery email error: " . $res['message'];
                $msgType = 'danger';
            }
        }
    }

    // 4. Test WhatsApp API Message Dispatch
    if (isset($_POST['send_test_whatsapp'])) {
        $testPhone = trim($_POST['test_whatsapp_phone'] ?? '');
        $testMsg = trim($_POST['test_whatsapp_msg'] ?? "⚡ *ZAMZY WhatsApp Gateway Test Alert*\n\nYour API connection is active and operational!\nTimestamp: " . date('Y-m-d H:i:s'));
        if (empty($testPhone)) {
            $msg = "Please enter a valid destination phone number with country code (e.g. 919876543210).";
            $msgType = 'danger';
        } else {
            $res = sendWhatsAppMessageDirect($testPhone, $testMsg);
            if ($res['success']) {
                $msg = "✓ WhatsApp test message dispatched successfully to {$testPhone}!";
                $msgType = 'success';
            } else {
                $msg = "❌ WhatsApp API dispatch failed: " . ($res['error'] ?? $res['message'] ?? 'Check endpoint & key');
                $msgType = 'danger';
            }
        }
    }

    // 5. Create Promotional Coupon
    if (isset($_POST['create_coupon'])) {
        $cCode = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', trim($_POST['coupon_code'] ?? '')));
        $dType = $_POST['discount_type'] ?? 'free';
        $dVal = floatval($_POST['discount_value'] ?? 0);
        $maxUses = intval($_POST['max_uses'] ?? 0);
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $cNotes = trim($_POST['coupon_notes'] ?? '');

        if (empty($cCode)) {
            $msg = "Please enter a valid coupon code (letters and numbers only).";
            $msgType = 'danger';
        } else {
            try {
                $ins = $pdo->prepare("INSERT INTO `zamzy_coupons` 
                    (`code`, `discount_type`, `discount_value`, `max_uses`, `expiry_date`, `notes`, `status`) 
                    VALUES (:code, :type, :val, :max_uses, :expiry, :notes, 'active')");
                $ins->execute([
                    ':code' => $cCode,
                    ':type' => $dType,
                    ':val' => $dVal,
                    ':max_uses' => $maxUses,
                    ':expiry' => $expiryDate,
                    ':notes' => $cNotes
                ]);
                $msg = "✓ Promotional Coupon '{$cCode}' created successfully!";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Error creating coupon: " . (strpos($e->getMessage(), 'Duplicate') !== false ? "Coupon code '{$cCode}' already exists!" : $e->getMessage());
                $msgType = 'danger';
            }
        }
    }

    // 6. Delete Coupon
    if (isset($_POST['delete_coupon'])) {
        $cId = intval($_POST['coupon_id'] ?? 0);
        if ($cId > 0) {
            try {
                $del = $pdo->prepare("DELETE FROM `zamzy_coupons` WHERE `id` = :id");
                $del->execute([':id' => $cId]);
                $msg = "✓ Coupon deleted successfully.";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Error deleting coupon: " . $e->getMessage();
                $msgType = 'danger';
            }
        }
    }

    // 7. Toggle Coupon Status (Active / Inactive)
    if (isset($_POST['toggle_coupon_status'])) {
        $cId = intval($_POST['coupon_id'] ?? 0);
        $currStatus = $_POST['current_status'] ?? 'active';
        $newStatus = ($currStatus === 'active') ? 'inactive' : 'active';
        if ($cId > 0) {
            try {
                $upd = $pdo->prepare("UPDATE `zamzy_coupons` SET `status` = :status WHERE `id` = :id");
                $upd->execute([':status' => $newStatus, ':id' => $cId]);
                $msg = "✓ Coupon status updated to " . strtoupper($newStatus) . ".";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Error updating coupon status: " . $e->getMessage();
                $msgType = 'danger';
            }
        }
    }
}

// Fetch All Existing Promotional Coupons
$allCoupons = [];
try {
    $cStmt = $pdo->query("SELECT * FROM `zamzy_coupons` ORDER BY `id` DESC");
    if ($cStmt) {
        $allCoupons = $cStmt->fetchAll();
    }
} catch (Exception $e) {}

// Fetch Current Settings
$apiKey = getSetting('famgateway_api_key', 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3');
$upiId = getSetting('upi_id', '8667702473@fam');
$upiName = getSetting('upi_name', 'Sameer Ahamadh');
$webinarPrice = getSetting('webinar_price', '96');
$webinarTitle = getSetting('webinar_title', 'Full Stack Web Development Live Webinar');

$whatsappApiEnabled = getSetting('whatsapp_api_enabled', '1');
$whatsappApiEndpoint = getSetting('whatsapp_api_endpoint', 'https://zamzy.in/api/whatsapp.php');
$whatsappApiKey = getSetting('whatsapp_api_key', '3c5b81fc69022511c682a14156e1c1fd');
$whatsappMsgTemplate = getSetting('whatsapp_msg_template', '');

$smtpHost = getSetting('smtp_host', 'mail.zamzy.in');
$smtpPort = getSetting('smtp_port', '465');
$smtpSecure = getSetting('smtp_secure', 'ssl');
$smtpUsername = getSetting('smtp_username', 'no-reply@zamzy.in');
$smtpPassword = getSetting('smtp_password', 'shacartc_zamzy');
$smtpFromEmail = getSetting('smtp_from_email', 'no-reply@zamzy.in');
$smtpFromName = getSetting('smtp_from_name', 'ZAMZY Learning');

$webinarSchedule = getSetting('webinar_schedule', 'Live Batch: Weekends 6:00 PM - 8:30 PM IST');
$webinarMeetingLink = getSetting('webinar_meeting_link', 'https://meet.google.com/qmv-xyza-web');
$webinarWhatsappLink = getSetting('webinar_whatsapp_link', 'https://chat.whatsapp.com/sample-zamzy-fullstack');
$webinarResources = getSetting('webinar_resources', "• Complete Full Stack Architecture Blueprint & Curriculum (PDF)\n• GitHub Starter Kit: https://github.com/zamzy-tech\n• Interview Cheatsheets & Free Tooling Access");
$webinarEmailNotes = getSetting('webinar_email_notes', 'Please join 5 minutes prior to the scheduled start time. Ensure you have Google Meet / Chrome installed and your laptop ready with VS Code.');

$standardPayload = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($upiName) . "&am=" . urlencode($webinarPrice) . "&cu=INR&tn=Webinar_Registration";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Admin — SMTP, Gateway &amp; Webinar Settings</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        @media(max-width: 1100px) { .settings-grid { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 1.3rem; }
        .form-label { display: block; font-family: var(--mono); font-size: 0.78rem; color: var(--cyan); margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; }
        .form-hint { font-size: 0.75rem; color: var(--dim); margin-top: 0.3rem; }
        .card-header-badge { font-family:var(--mono); font-size:0.68rem; padding:3px 8px; border-radius:4px; text-transform:uppercase; letter-spacing:0.1em; font-weight:700; }
        .code-box { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 12px; font-family: var(--mono); font-size: 0.75rem; color: #a5f3fc; white-space: pre-wrap; word-break: break-all; max-height: 220px; overflow-y: auto; }
        .tab-btn { background: transparent; border: 1px solid rgba(255,255,255,0.1); color: var(--dim); padding: 8px 16px; border-radius: 6px; font-family: var(--mono); font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
        .tab-btn.active { background: rgba(6,182,212,0.15); border-color: var(--cyan); color: var(--cyan); }
    </style>
</head>
<body>

<div class="admin-mobile-header">
    <div class="admin-mobile-brand">
        <span class="admin-mobile-logo">ZAMZY<span>.</span></span>
        <span class="admin-mobile-tag">Settings &amp; SMTP</span>
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
                <a href="webinar.php" class="admin-nav__item"><span>🎓</span> Full Stack Webinar</a>
                <a href="inquiries.php" class="admin-nav__item"><span>📬</span> Inquiries &amp; Leads</a>
                <a href="demos.php" class="admin-nav__item"><span>⚡</span> Demo Requests</a>
                <a href="chats.php" class="admin-nav__item"><span>💬</span> Chat Reports</a>
                <a href="testimonials.php" class="admin-nav__item"><span>★</span> Reviews / Proof</a>
                <a href="careers.php" class="admin-nav__item"><span>👥</span> Careers &amp; Guild</a>
                <a href="settings.php" class="admin-nav__item active"><span>⚙️</span> Settings &amp; SMTP</a>
                <a href="../fullstack-webinar" target="_blank" class="admin-nav__item"><span>↗</span> View Webinar Page</a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <div class="admin-user-badge">
                <div class="admin-avatar">A</div>
                <div>
                    <div style="font-weight:600;"><?= htmlspecialchars($_SESSION['zamzy_admin_name'] ?? $_SESSION['admin_user'] ?? 'Admin') ?></div>
                    <div style="font-size:0.75rem; color:var(--admin-dim);">Root Administrator</div>
                </div>
            </div>
            <a href="logout.php" class="btn-admin btn-admin-outline btn-admin-sm" style="width:100%; text-align:center;">Terminate Session</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1 class="admin-page-title">Settings, SMTP &amp; Webinar Deliverables</h1>
                <p class="admin-page-sub">Configure Mail Server, P2P Automation Layer &amp; Post-Payment Access Materials</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="webinar.php" class="btn-admin btn-admin-outline">🎓 Registrations &amp; Payments</a>
                <a href="../fullstack-webinar" target="_blank" class="btn-admin btn-admin-primary">↗ Live Webinar Page</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box" style="<?= $msgType === 'danger' ? 'background:rgba(239,68,68,0.12); border-color:#ef4444; color:#fca5a5;' : '' ?>">
                <?= $msgType === 'danger' ? '❌' : '⚡' ?> <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($smtpTestLog)): ?>
            <div class="admin-card" style="margin-bottom: 2rem; border: 1px solid rgba(6,182,212,0.3);">
                <h4 style="color:var(--cyan); margin-bottom:0.8rem; font-family:var(--mono);">📡 SMTP Socket Protocol Debug Output</h4>
                <div class="code-box"><?= htmlspecialchars($smtpTestLog) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="save_settings" value="1">
            
            <div class="settings-grid">
                
                <!-- 1. SMTP Mail Server Configuration -->
                <div class="admin-card" style="border: 1px solid rgba(6, 182, 212, 0.35); box-shadow: 0 0 35px rgba(6, 182, 212, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:var(--cyan); font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            📧 SMTP Mail Client Configuration
                        </h3>
                        <span class="card-header-badge" style="background:rgba(6,182,212,0.15); border:1px solid rgba(6,182,212,0.4); color:var(--cyan);">
                            SECURE SSL/TLS
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.6rem; line-height:1.6;">
                        Outgoing server authentication credentials used to deliver registration confirmations, meeting links, and learning materials.
                    </p>

                    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Outgoing SMTP Server</label>
                            <input type="text" name="smtp_host" value="<?= htmlspecialchars($smtpHost) ?>" placeholder="mail.zamzy.in" class="admin-input" required>
                            <div class="form-hint">e.g. mail.zamzy.in</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Port</label>
                            <input type="number" name="smtp_port" value="<?= htmlspecialchars($smtpPort) ?>" placeholder="465" class="admin-input" required>
                            <div class="form-hint">465 (SSL) / 587 (TLS)</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Encryption Protocol</label>
                        <select name="smtp_secure" class="admin-input">
                            <option value="ssl" <?= $smtpSecure === 'ssl' ? 'selected' : '' ?>>SSL (Port 465 - Recommended)</option>
                            <option value="tls" <?= $smtpSecure === 'tls' ? 'selected' : '' ?>>TLS (Port 587)</option>
                            <option value="none" <?= $smtpSecure === 'none' ? 'selected' : '' ?>>None (Plain)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_username" value="<?= htmlspecialchars($smtpUsername) ?>" placeholder="no-reply@zamzy.in" class="admin-input" required>
                        <div class="form-hint">Default: no-reply@zamzy.in</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_password" value="<?= htmlspecialchars($smtpPassword) ?>" placeholder="Password" class="admin-input" required>
                        <div class="form-hint">Email mailbox password (shacartc_zamzy)</div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Sender Display Name</label>
                            <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($smtpFromName) ?>" placeholder="ZAMZY Learning" class="admin-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sender Email Address</label>
                            <input type="email" name="smtp_from_email" value="<?= htmlspecialchars($smtpFromEmail) ?>" placeholder="no-reply@zamzy.in" class="admin-input" required>
                        </div>
                    </div>

                    <div style="margin-top:1.2rem; padding:0.9rem; background:rgba(0,0,0,0.3); border-radius:6px; border:1px solid rgba(255,255,255,0.05); font-size:0.75rem; color:var(--dim); line-height:1.5;">
                        <strong style="color:var(--cyan);">Server Specs:</strong> Incoming IMAP 993 / POP3 995 | Outgoing SMTP 465 (SSL authenticated).
                    </div>
                </div>

                <!-- 2. Webinar Deliverables & Access Materials (Sent on Payment) -->
                <div class="admin-card" style="border: 1px solid rgba(16, 185, 129, 0.35); box-shadow: 0 0 35px rgba(16, 185, 129, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:#34d399; font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            🎓 Post-Payment Deliverables &amp; Links
                        </h3>
                        <span class="card-header-badge" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34d399;">
                            AUTO-DELIVERED
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.6rem; line-height:1.6;">
                        Whatever is updated here will be <strong>automatically emailed to the student</strong> immediately once their payment confirms via gateway or manual verification!
                    </p>

                    <div class="form-group">
                        <label class="form-label">Live Meeting Room Link (Google Meet / Zoom)</label>
                        <input type="url" name="webinar_meeting_link" value="<?= htmlspecialchars($webinarMeetingLink) ?>" placeholder="https://meet.google.com/xxx-yyyy-zzz" class="admin-input" required>
                        <div class="form-hint">Direct access link clickable inside student confirmation email</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Webinar Batch Schedule &amp; Timings</label>
                        <input type="text" name="webinar_schedule" value="<?= htmlspecialchars($webinarSchedule) ?>" placeholder="e.g. Saturday &amp; Sunday, 6:00 PM - 8:30 PM IST" class="admin-input" required>
                        <div class="form-hint">Displayed in the email header and student access badge</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">WhatsApp Community / Discussion Group Link</label>
                        <input type="url" name="webinar_whatsapp_link" value="<?= htmlspecialchars($webinarWhatsappLink) ?>" placeholder="https://chat.whatsapp.com/..." class="admin-input" required>
                        <div class="form-hint">Exclusive cohort group invite for instant updates &amp; doubts</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Documents, PDFs, Starter Kits &amp; Drive Resources</label>
                        <textarea name="webinar_resources" class="admin-input" rows="4" style="resize:vertical;" placeholder="• Resource 1 (link)&#10;• Full Stack Roadmap PDF (link)&#10;• GitHub Starter Kit"><?= htmlspecialchars($webinarResources) ?></textarea>
                        <div class="form-hint">List multiple links, drive folders, or downloadable PDFs (one per line)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Student Prep Notes &amp; Joining Guidelines</label>
                        <textarea name="webinar_email_notes" class="admin-input" rows="2" style="resize:vertical;" placeholder="Requirements or notes to include in email"><?= htmlspecialchars($webinarEmailNotes) ?></textarea>
                        <div class="form-hint">e.g. Please join 5 minutes early with VS Code installed</div>
                    </div>
                </div>

                <!-- 3. FamGateway P2P Non-Custodial Layer -->
                <div class="admin-card" style="border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 0 35px rgba(139, 92, 246, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:#c4b5fd; font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            ⚡ FamGateway P2P Automation
                        </h3>
                        <span class="card-header-badge" style="background:rgba(139,92,246,0.18); border:1px solid rgba(139,92,246,0.4); color:#c4b5fd;">
                            P2P LOOP
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.6rem; line-height:1.6;">
                        Connects your personal FamPay wallet via the FamGateway P2P automation loop without corporate merchant credentials.
                    </p>

                    <div class="form-group">
                        <label class="form-label">FamGateway API Key</label>
                        <input type="text" name="famgateway_api_key" value="<?= htmlspecialchars($apiKey) ?>" placeholder="fam_xxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="admin-input" autocomplete="off" required>
                        <div class="form-hint">Your active authentication key from famgateway.in</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Order Creation Endpoint</label>
                        <input type="text" value="https://famgateway.in/api/create-order.php" class="admin-input" readonly style="opacity:0.7; cursor:not-allowed;">
                    </div>

                    <div style="margin-top:1.2rem; padding:1.2rem; background:rgba(6,182,212,0.06); border:1px dashed rgba(6,182,212,0.3); border-radius:8px;">
                        <div style="font-family:var(--mono); font-size:0.72rem; color:var(--cyan); font-weight:700; text-transform:uppercase; margin-bottom:6px;">
                            Webhook Listener URL (Configure in FamGateway Dashboard)
                        </div>
                        <div style="font-family:var(--mono); font-size:0.82rem; color:#fff; word-break:break-all; font-weight:600;">
                            <?= defined('BASE_URL') ? BASE_URL . '/api.php?action=webhook' : 'https://zamzy.in/api.php?action=webhook' ?>
                        </div>
                        <div style="font-size:0.72rem; color:var(--dim); margin-top:6px;">
                            Auto-captures UTR verification codes and triggers instant seat unlock + automated email delivery.
                        </div>
                    </div>
                </div>

                <!-- 4. Direct UPI Intent & Ticket Price -->
                <div class="admin-card" style="border: 1px solid rgba(245, 158, 11, 0.35); box-shadow: 0 0 35px rgba(245, 158, 11, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:#fbbf24; font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            📲 Direct FamPay VPA &amp; QR
                        </h3>
                        <span class="card-header-badge" style="background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.4); color:#fbbf24;">
                            INTENT
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.6rem; line-height:1.6;">
                        Direct UPI fallback string and ticket pricing applied across the landing page.
                    </p>

                    <div class="form-group">
                        <label class="form-label">FamPay UPI ID (VPA)</label>
                        <input type="text" name="upi_id" value="<?= htmlspecialchars($upiId) ?>" placeholder="e.g. 8667702473@fam" class="admin-input" required>
                        <div class="form-hint">Personal FamPay UPI address used for direct QR generation</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Account Payee Name</label>
                        <input type="text" name="upi_name" value="<?= htmlspecialchars($upiName) ?>" placeholder="e.g. Sameer Ahamadh" class="admin-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Webinar Ticket Price (INR ₹)</label>
                        <input type="number" step="1" name="webinar_price" value="<?= htmlspecialchars($webinarPrice) ?>" placeholder="96" class="admin-input" required>
                        <div class="form-hint">Live workshop fee (Default: ₹96)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Standard UPI Intent String</label>
                        <input type="text" value="<?= htmlspecialchars($standardPayload) ?>" class="admin-input" readonly style="font-size:0.75rem; opacity:0.8;">
                    </div>
                </div>

                <!-- 5. WhatsApp Gateway REST API Configuration -->
                <div class="admin-card" style="grid-column: 1 / -1; border: 1px solid rgba(37, 211, 102, 0.4); box-shadow: 0 0 35px rgba(37, 211, 102, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem; flex-wrap:wrap; gap:0.5rem;">
                        <h3 style="color:#25D366; font-family:var(--display); font-size:1.35rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            💬 WhatsApp Gateway Message API
                        </h3>
                        <span class="card-header-badge" style="background:rgba(37,211,102,0.15); border:1px solid rgba(37,211,102,0.4); color:#25D366;">
                            ⚡ SCANNER SLOT #1 CONNECTED
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.6rem; line-height:1.6;">
                        High-speed WhatsApp REST gateway integration. When enabled, every student who completes registration and payment will <strong>automatically receive their official webinar confirmation, batch schedule, meeting link, and study kit</strong> directly on their WhatsApp!
                    </p>

                    <div style="margin-bottom:1.5rem; padding:1rem; background:rgba(37,211,102,0.06); border:1px solid rgba(37,211,102,0.25); border-radius:8px; display:flex; align-items:center; gap:0.8rem;">
                        <input type="checkbox" id="whatsapp_api_enabled" name="whatsapp_api_enabled" value="1" <?= $whatsappApiEnabled === '1' ? 'checked' : '' ?> style="width:20px; height:20px; accent-color:#25D366; cursor:pointer;">
                        <label for="whatsapp_api_enabled" style="font-weight:600; color:#fff; font-size:0.88rem; cursor:pointer;">
                            Enable Automated WhatsApp Notification Immediately Upon Payment Confirmation
                        </label>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.2rem;">
                        <div class="form-group">
                            <label class="form-label">WhatsApp API Endpoint (Slot #1)</label>
                            <input type="url" name="whatsapp_api_endpoint" value="<?= htmlspecialchars($whatsappApiEndpoint) ?>" placeholder="https://zamzy.in/api/whatsapp.php" class="admin-input" required>
                            <div class="form-hint">
                                Default: <code>https://zamzy.in/api/whatsapp.php</code><br>
                                Alternative slot endpoints: <code>https://zamzy.in/api/whatsapp2.php</code> | <code>https://zamzy.in/api/whatsapp3.php</code>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bearer Authorization Key</label>
                            <input type="text" name="whatsapp_api_key" value="<?= htmlspecialchars($whatsappApiKey) ?>" placeholder="3c5b81fc69022511c682a14156e1c1fd" class="admin-input" required autocomplete="off">
                            <div class="form-hint">Bearer token sent in <code>Authorization: Bearer [KEY]</code> header. Connected Device Slot #1 API Key.</div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:0.8rem;">
                        <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>Custom Automated WhatsApp Message Template</span>
                            <span style="font-weight:normal; font-size:0.7rem; color:var(--dim);">Leave empty to use the standard default layout</span>
                        </label>
                        <textarea name="whatsapp_msg_template" class="admin-input" rows="8" style="resize:vertical; font-family:var(--mono); font-size:0.78rem;" placeholder="Leave empty for standard default template, or write custom template with tokens:&#10;{name}, {reg_code}, {amount}, {utr}, {schedule}, {webinar_title}, {meeting_link}, {whatsapp_link}, {resources}, {notes}"><?= htmlspecialchars($whatsappMsgTemplate) ?></textarea>
                        <div class="form-hint" style="margin-top:0.5rem; line-height:1.6;">
                            <strong>Dynamic Placeholders:</strong> 
                            <code>{name}</code> — Student Name &nbsp;|&nbsp;
                            <code>{reg_code}</code> — Registration ID &nbsp;|&nbsp;
                            <code>{amount}</code> — Ticket Fee &nbsp;|&nbsp;
                            <code>{utr}</code> — UTR / Reference &nbsp;|&nbsp;
                            <code>{schedule}</code> — Date &amp; Timings &nbsp;|&nbsp;
                            <code>{webinar_title}</code> — Course Title &nbsp;|&nbsp;
                            <code>{meeting_link}</code> — Meeting URL &nbsp;|&nbsp;
                            <code>{whatsapp_link}</code> — Community Group &nbsp;|&nbsp;
                            <code>{resources}</code> — PDF &amp; Kits &nbsp;|&nbsp;
                            <code>{notes}</code> — Joining Prep Notes
                        </div>
                    </div>

                    <div style="margin-top:1.2rem; padding:1rem; background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.08); border-radius:6px; font-size:0.75rem; color:var(--dim); line-height:1.6;">
                        <strong style="color:#25D366;">⚡ REST API Protocol Specs:</strong><br>
                        • <strong>Method:</strong> POST <code>https://zamzy.in/api/whatsapp.php</code><br>
                        • <strong>Headers:</strong> <code>Authorization: Bearer 3c5b81fc69022511c682a14156e1c1fd</code> | <code>Content-Type: application/json</code><br>
                        • <strong>Payload:</strong> <code>{"to": "919876543210", "message": "...", "type": "general"}</code>
                    </div>
                </div>

            </div>

            <!-- Master Save Button -->
            <div style="margin-top:2.5rem; text-align:center;">
                <button type="submit" class="btn-admin btn-admin-primary" style="font-size:0.95rem; padding:1.2rem 3rem; min-width:320px; box-shadow:0 0 30px rgba(6,182,212,0.35);">
                    💾 Save All Settings &amp; Deliverables
                </button>
            </div>
        </form>

        <!-- 🎟️ Promotional Coupon Codes Management -->
        <div style="margin-top: 3.5rem;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                <div>
                    <h2 style="font-family:var(--display); font-size:1.6rem; color:#ffffff; margin-bottom:0.4rem; display:flex; align-items:center; gap:0.6rem;">
                        🎟️ Promotional Coupon Codes Management
                    </h2>
                    <p style="font-size:0.85rem; color:var(--dim); margin-bottom:0;">
                        Create instant discount vouchers or 100% free VIP waiver codes for students, campus ambassadors, and partner cohorts.
                    </p>
                </div>
            </div>

            <div class="settings-grid">
                <!-- Create New Coupon Form -->
                <div class="admin-card" style="border: 1px solid rgba(168, 85, 247, 0.35); box-shadow: 0 0 35px rgba(168, 85, 247, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:#c084fc; font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            ➕ Create New Promotional Coupon
                        </h3>
                        <span class="card-header-badge" style="background:rgba(168,85,247,0.18); border:1px solid rgba(168,85,247,0.4); color:#c084fc;">
                            DISCOUNT ENGINE
                        </span>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Coupon Code (e.g. ZAMZY100, FREEPASS, SAVE50)</label>
                            <input type="text" name="coupon_code" placeholder="e.g. VIP2026" class="admin-input" style="text-transform:uppercase; font-family:var(--mono); font-weight:700; letter-spacing:0.08em;" required>
                            <div class="form-hint">Case-insensitive promo code that students type during registration</div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Discount Type</label>
                                <select name="discount_type" class="admin-input" required>
                                    <option value="free">100% Free / Full Fee Waiver (₹0)</option>
                                    <option value="fixed">Fixed Amount Discount (₹)</option>
                                    <option value="percent">Percentage Discount (%)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Discount Value</label>
                                <input type="number" step="0.5" name="discount_value" value="96" class="admin-input" placeholder="e.g. 96" required>
                                <div class="form-hint">For 100% Free, enter ticket price (₹96)</div>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Redemption Limit (Max Uses)</label>
                                <input type="number" name="max_uses" value="100" class="admin-input" placeholder="0 = Unlimited">
                                <div class="form-hint">Enter 0 for unlimited uses</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Expiration Date (Optional)</label>
                                <input type="date" name="expiry_date" class="admin-input">
                                <div class="form-hint">Leave blank for no expiration</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Campaign Notes / Beneficiary</label>
                            <input type="text" name="coupon_notes" placeholder="e.g. College Workshop Partner Batch" class="admin-input">
                        </div>

                        <button type="submit" name="create_coupon" value="1" class="btn-admin btn-admin-primary" style="width:100%; margin-top:0.5rem; background:linear-gradient(135deg, #a855f7 0%, #06b6d4 100%); border:none; box-shadow:0 0 20px rgba(168,85,247,0.35);">
                            🎟️ Create Promotional Coupon
                        </button>
                    </form>
                </div>

                <!-- Existing Coupons Table Card -->
                <div class="admin-card" style="border: 1px solid rgba(255,255,255,0.1);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:#ffffff; font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            📋 Active Promotional Coupons (<?= count($allCoupons) ?>)
                        </h3>
                    </div>

                    <?php if (empty($allCoupons)): ?>
                        <div style="text-align:center; padding:3rem 1rem; color:var(--dim);">
                            <div style="font-size:2rem; margin-bottom:0.6rem;">🎟️</div>
                            <p style="margin-bottom:0;">No coupons created yet. Fill the form to create your first discount voucher!</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table class="data-table" style="font-size:0.8rem;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Benefit</th>
                                        <th>Usage</th>
                                        <th>Expiry</th>
                                        <th>Status</th>
                                        <th style="text-align:right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allCoupons as $cpn): ?>
                                        <tr>
                                            <td>
                                                <span class="utr-code" style="background:rgba(168,85,247,0.15); border-color:rgba(168,85,247,0.35); color:#c084fc; font-weight:700; font-size:0.78rem;">
                                                    <?= htmlspecialchars($cpn['code']) ?>
                                                </span>
                                                <?php if (!empty($cpn['notes'])): ?>
                                                    <div style="font-size:0.68rem; color:var(--faint); margin-top:2px;"><?= htmlspecialchars($cpn['notes']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($cpn['discount_type'] === 'free'): ?>
                                                    <span style="color:#34d399; font-weight:700;">100% FREE</span>
                                                <?php elseif ($cpn['discount_type'] === 'percent'): ?>
                                                    <span style="color:var(--cyan); font-weight:700;"><?= floatval($cpn['discount_value']) ?>% OFF</span>
                                                <?php else: ?>
                                                    <span style="color:#fbbf24; font-weight:700;">₹<?= number_format($cpn['discount_value'], 0) ?> OFF</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span style="font-family:var(--mono); color:#e2e8f0; font-weight:600;"><?= intval($cpn['used_count']) ?></span>
                                                <span style="color:var(--dim); font-size:0.72rem;">/ <?= $cpn['max_uses'] > 0 ? intval($cpn['max_uses']) : '∞' ?></span>
                                            </td>
                                            <td style="font-size:0.75rem; color:var(--dim);">
                                                <?= !empty($cpn['expiry_date']) ? date('d M Y', strtotime($cpn['expiry_date'])) : '<span style="color:var(--faint);">No Expiry</span>' ?>
                                            </td>
                                            <td>
                                                <?php if ($cpn['status'] === 'active'): ?>
                                                    <span class="badge-status badge-verified" style="font-size:0.65rem; padding:2px 6px;">ACTIVE</span>
                                                <?php else: ?>
                                                    <span class="badge-status badge-rejected" style="font-size:0.65rem; padding:2px 6px;">DISABLED</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align:right;">
                                                <div style="display:inline-flex; gap:0.4rem; align-items:center;">
                                                    <!-- Toggle Status -->
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="toggle_coupon_status" value="1">
                                                        <input type="hidden" name="coupon_id" value="<?= $cpn['id'] ?>">
                                                        <input type="hidden" name="current_status" value="<?= $cpn['status'] ?>">
                                                        <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="font-size:0.7rem; padding:3px 8px; border-color:<?= $cpn['status']==='active' ? '#f59e0b' : '#10b981' ?>; color:<?= $cpn['status']==='active' ? '#f59e0b' : '#10b981' ?>;" title="Toggle Active / Inactive">
                                                            <?= $cpn['status']==='active' ? 'Disable' : 'Enable' ?>
                                                        </button>
                                                    </form>

                                                    <!-- Delete Coupon -->
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete coupon \'<?= htmlspecialchars($cpn['code']) ?>\'?');">
                                                        <input type="hidden" name="delete_coupon" value="1">
                                                        <input type="hidden" name="coupon_id" value="<?= $cpn['id'] ?>">
                                                        <button type="submit" class="btn-admin btn-admin-sm btn-admin-outline" style="font-size:0.7rem; padding:3px 6px; border-color:#ef4444; color:#ef4444;" title="Delete Coupon">
                                                            ✕
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Testing Tools Section -->
        <div style="margin-top: 3.5rem;">
            <h2 style="font-family:var(--display); font-size:1.6rem; color:#ffffff; margin-bottom:0.4rem;">
                🛠️ Diagnostic &amp; Test Dispatch Tools
            </h2>
            <p style="font-size:0.85rem; color:var(--dim); margin-bottom:1.5rem;">
                Verify that your SMTP mail server and WhatsApp Gateway REST API are firing smoothly without waiting for live registrations.
            </p>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem;">
                <!-- Test Basic SMTP -->
                <div class="admin-card">
                    <h4 style="color:var(--cyan); margin-bottom:0.8rem; font-family:var(--mono); font-size:0.95rem;">
                        1. Test SMTP Socket Handshake
                    </h4>
                    <p style="font-size:0.8rem; color:var(--dim); margin-bottom:1.2rem;">
                        Sends a test ping email via authenticated SSL socket (`<?= htmlspecialchars($smtpHost) ?>:<?= htmlspecialchars($smtpPort) ?>`) to verify mail server credentials.
                    </p>
                    <form method="POST">
                        <div class="form-group">
                            <input type="email" name="test_email_address" class="admin-input" placeholder="Enter recipient email (e.g. your email)" required value="<?= htmlspecialchars($_SESSION['admin_email'] ?? 'mohamedidris2004@gmail.com') ?>">
                        </div>
                        <button type="submit" name="send_test_email" value="1" class="btn-admin btn-admin-outline" style="width:100%;">
                            📡 Send Test SMTP Email
                        </button>
                    </form>
                </div>

                <!-- Test Webinar Email Delivery -->
                <div class="admin-card">
                    <h4 style="color:#34d399; margin-bottom:0.8rem; font-family:var(--mono); font-size:0.95rem;">
                        2. Send Full Webinar Access Preview Email
                    </h4>
                    <p style="font-size:0.8rem; color:var(--dim); margin-bottom:1.2rem;">
                        Dispatches the exact HTML delivery email containing your meeting link, WhatsApp invite, and PDF resources to your inbox.
                    </p>
                    <form method="POST">
                        <div class="form-group">
                            <input type="email" name="test_webinar_email_address" class="admin-input" placeholder="Enter recipient email (e.g. your email)" required value="<?= htmlspecialchars($_SESSION['admin_email'] ?? 'mohamedidris2004@gmail.com') ?>">
                        </div>
                        <button type="submit" name="send_sample_webinar_email" value="1" class="btn-admin btn-admin-primary" style="width:100%;">
                            🎓 Send Full Webinar Email Preview
                        </button>
                    </form>
                </div>

                <!-- Test WhatsApp API Message Dispatch -->
                <div class="admin-card" style="border: 1px solid rgba(37, 211, 102, 0.4);">
                    <h4 style="color:#25D366; margin-bottom:0.8rem; font-family:var(--mono); font-size:0.95rem; display:flex; align-items:center; gap:0.4rem;">
                        <span>💬</span> 3. Test WhatsApp Gateway API Dispatch
                    </h4>
                    <p style="font-size:0.8rem; color:var(--dim); margin-bottom:1.2rem;">
                        Sends a real-time test notification via <code><?= htmlspecialchars($whatsappApiEndpoint) ?></code> with your Slot Bearer Token to test phone connectivity.
                    </p>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label" style="font-size:0.72rem;">Destination WhatsApp Phone</label>
                            <input type="text" name="test_whatsapp_phone" class="admin-input" placeholder="e.g. 917287060553 or 919876543210" required value="917287060553">
                            <div class="form-hint">Include country code (e.g. 91 for India)</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size:0.72rem;">Test Message Content</label>
                            <textarea name="test_whatsapp_msg" class="admin-input" rows="2" style="resize:vertical; font-size:0.75rem;">⚡ *ZAMZY WhatsApp Gateway Test Alert*&#10;Your API integration is active and operational! 🚀</textarea>
                        </div>
                        <button type="submit" name="send_test_whatsapp" value="1" class="btn-admin btn-admin-sm" style="width:100%; background:#25D366; color:#050505; font-weight:700; border:none; padding:0.8rem; box-shadow:0 0 15px rgba(37,211,102,0.3);">
                            ⚡ Send Test WhatsApp Message
                        </button>
                    </form>
                </div>
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
