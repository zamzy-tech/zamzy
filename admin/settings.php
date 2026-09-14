<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';
$msgType = 'success';

// Save Settings Form Submission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['save_settings'])) {
    $settingsToUpdate = [
        'famgateway_api_key' => trim($_POST['famgateway_api_key'] ?? 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3'),
        'upi_id' => trim($_POST['upi_id'] ?? '8667702473@fam'),
        'upi_name' => trim($_POST['upi_name'] ?? 'Sameer Ahamadh'),
        'webinar_price' => trim($_POST['webinar_price'] ?? '96'),
        'webinar_title' => trim($_POST['webinar_title'] ?? 'Full Stack Web Development Live Webinar')
    ];

    foreach ($settingsToUpdate as $key => $val) {
        setSetting($key, $val);
    }

    $msg = "P2P Gateway & UPI Settings updated successfully!";
}

// Fetch Current Settings
$apiKey = getSetting('famgateway_api_key', 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3');
$upiId = getSetting('upi_id', '8667702473@fam');
$upiName = getSetting('upi_name', 'Sameer Ahamadh');
$webinarPrice = getSetting('webinar_price', '96');
$webinarTitle = getSetting('webinar_title', 'Full Stack Web Development Live Webinar');
$standardPayload = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($upiName) . "&am=" . urlencode($webinarPrice) . "&cu=INR&tn=Webinar_Registration";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Admin — P2P Gateway &amp; FamPay Settings</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        @media(max-width: 992px) { .settings-grid { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 1.4rem; }
        .form-label { display: block; font-family: var(--admin-font-mono); font-size: 0.78rem; color: var(--admin-cyan); margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; }
        .form-hint { font-size: 0.75rem; color: var(--admin-faint); margin-top: 0.3rem; }
    </style>
</head>
<body>

<div class="admin-mobile-header">
    <div class="admin-mobile-brand">
        <span class="admin-mobile-logo">ZAMZY<span>.</span></span>
        <span class="admin-mobile-tag">Gateway Settings</span>
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
                <a href="settings.php" class="admin-nav__item active"><span>⚙️</span> Payment &amp; Gateway Settings</a>
                <a href="../fullstack-webinar" target="_blank" class="admin-nav__item"><span>↗</span> View Webinar Page</a>
            </nav>
        </div>

        <div>
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
                <h1 class="admin-page-title">Payment &amp; Gateway Settings</h1>
                <p class="admin-page-sub">P2P Automation Layer, FamGateway Integration &amp; Dynamic UPI Verification</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="webinar.php" class="btn-admin btn-admin-outline">🎓 View Webinar Registrations</a>
                <a href="../fullstack-webinar" target="_blank" class="btn-admin btn-admin-primary">↗ View Webinar Page</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box">
                ⚡ <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="save_settings" value="1">
            <div class="settings-grid">
                
                <!-- FamGateway P2P Non-Custodial Layer -->
                <div class="admin-card" style="border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 0 35px rgba(139, 92, 246, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:#c4b5fd; font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            ⚡ FamGateway P2P Automation
                        </h3>
                        <span style="font-family:var(--mono); font-size:0.68rem; background:rgba(139,92,246,0.18); border:1px solid rgba(139,92,246,0.4); color:#c4b5fd; padding:3px 8px; border-radius:4px; text-transform:uppercase; letter-spacing:0.1em; font-weight:700;">
                            NON-CUSTODIAL
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.8rem; line-height:1.6;">
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
                        <div class="form-hint">Used by api.php to request dynamic UPI checkout orders</div>
                    </div>

                    <div style="margin-top:1.5rem; padding:1.2rem; background:rgba(6,182,212,0.06); border:1px dashed rgba(6,182,212,0.3); border-radius:8px;">
                        <div style="font-family:var(--mono); font-size:0.72rem; color:var(--cyan); font-weight:700; text-transform:uppercase; margin-bottom:6px;">
                            Webhook Listener URL (Enter this in FamGateway Dashboard)
                        </div>
                        <div style="font-family:var(--mono); font-size:0.82rem; color:#fff; word-break:break-all; font-weight:600;">
                            <?= defined('BASE_URL') ? BASE_URL . '/api.php?action=webhook' : 'https://zamzy.in/api.php?action=webhook' ?>
                        </div>
                        <div style="font-size:0.72rem; color:var(--faint); margin-top:6px;">
                            Captures incoming structural UTR codes &amp; automatically unlocks student seats.
                        </div>
                    </div>
                </div>

                <!-- UPI & Webinar Pricing Settings Box -->
                <div class="admin-card" style="border: 1px solid rgba(6, 182, 212, 0.35); box-shadow: 0 0 35px rgba(6, 182, 212, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:var(--cyan); font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            📲 Direct FamPay VPA &amp; QR
                        </h3>
                        <span style="font-family:var(--mono); font-size:0.68rem; background:rgba(6,182,212,0.18); border:1px solid rgba(6,182,212,0.4); color:var(--cyan); padding:3px 8px; border-radius:4px; text-transform:uppercase; letter-spacing:0.1em; font-weight:700;">
                            DYNAMIC PAYLOAD
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.8rem; line-height:1.6;">
                        Configures the standard P2P UPI Intent payload and registration fee for the live workshop.
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
                        <div class="form-hint">Displayed across landing page &amp; UPI intent (Default: ₹96)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Standard UPI Intent String</label>
                        <input type="text" value="<?= htmlspecialchars($standardPayload) ?>" class="admin-input" readonly style="font-size:0.75rem; opacity:0.8;">
                    </div>

                    <div style="margin-top:2.2rem;">
                        <button type="submit" class="btn-admin btn-admin-primary" style="width:100%; font-size:0.88rem; padding:1.1rem;">
                            💾 Save P2P Gateway Settings
                        </button>
                    </div>
                </div>
            </div>
        </form>
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
