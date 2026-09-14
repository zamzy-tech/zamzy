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
            'webinar_email_notes' => trim($_POST['webinar_email_notes'] ?? '')
        ];

        foreach ($settingsToUpdate as $key => $val) {
            setSetting($key, $val);
        }

        $msg = "Configuration saved successfully! All SMTP and Webinar deliverables are updated.";
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
}

// Fetch Current Settings
$apiKey = getSetting('famgateway_api_key', 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3');
$upiId = getSetting('upi_id', '8667702473@fam');
$upiName = getSetting('upi_name', 'Sameer Ahamadh');
$webinarPrice = getSetting('webinar_price', '96');
$webinarTitle = getSetting('webinar_title', 'Full Stack Web Development Live Webinar');

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

            </div>

            <!-- Master Save Button -->
            <div style="margin-top:2.5rem; text-align:center;">
                <button type="submit" class="btn-admin btn-admin-primary" style="font-size:0.95rem; padding:1.2rem 3rem; min-width:320px; box-shadow:0 0 30px rgba(6,182,212,0.35);">
                    💾 Save All Settings &amp; Deliverables
                </button>
            </div>
        </form>

        <!-- Testing Tools Section -->
        <div style="margin-top: 3.5rem;">
            <h2 style="font-family:var(--display); font-size:1.6rem; color:#ffffff; margin-bottom:0.4rem;">
                🛠️ Diagnostic &amp; Test Dispatch Tools
            </h2>
            <p style="font-size:0.85rem; color:var(--dim); margin-bottom:1.5rem;">
                Verify that your SMTP mail server and email delivery pipeline are firing smoothly without waiting for live registrations.
            </p>

            <div class="settings-grid">
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
