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
        'fampay_api_key' => trim($_POST['fampay_api_key'] ?? ''),
        'fampay_secret_key' => trim($_POST['fampay_secret_key'] ?? ''),
        'fampay_merchant_id' => trim($_POST['fampay_merchant_id'] ?? ''),
        'fampay_env' => trim($_POST['fampay_env'] ?? 'production'),
        'upi_id' => trim($_POST['upi_id'] ?? '7287060553@ybl'),
        'upi_name' => trim($_POST['upi_name'] ?? 'ZAMZY Digital Solutions'),
        'webinar_price' => trim($_POST['webinar_price'] ?? '96'),
        'webinar_title' => trim($_POST['webinar_title'] ?? 'Full Stack Web Development Live Webinar')
    ];

    foreach ($settingsToUpdate as $key => $val) {
        setSetting($key, $val);
    }

    $msg = "Payment Gateway & System Settings updated successfully!";
}

// Fetch Current Settings
$fampayApiKey = getSetting('fampay_api_key', '');
$fampaySecretKey = getSetting('fampay_secret_key', '');
$fampayMerchantId = getSetting('fampay_merchant_id', '');
$fampayEnv = getSetting('fampay_env', 'production');
$upiId = getSetting('upi_id', '7287060553@ybl');
$upiName = getSetting('upi_name', 'ZAMZY Digital Solutions');
$webinarPrice = getSetting('webinar_price', '96');
$webinarTitle = getSetting('webinar_title', 'Full Stack Web Development Live Webinar');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Admin — Payment &amp; FamPay Gateway Settings</title>
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
                <p class="admin-page-sub">Configure FamPay Developer API Keys, Direct UPI VPA &amp; Webinar Pricing Engine</p>
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
                
                <!-- FamPay API Credentials Box -->
                <div class="admin-card" style="border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 0 35px rgba(139, 92, 246, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:#c4b5fd; font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            💳 FamPay Payment Gateway
                        </h3>
                        <span style="font-family:var(--mono); font-size:0.68rem; background:rgba(139,92,246,0.18); border:1px solid rgba(139,92,246,0.4); color:#c4b5fd; padding:3px 8px; border-radius:4px; text-transform:uppercase; letter-spacing:0.1em; font-weight:700;">
                            API INTEGRATION
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.8rem; line-height:1.6;">
                        Enter your FamPay merchant credentials below. Instant FamPay Checkout API orders will be created automatically for webinar registrations.
                    </p>

                    <div class="form-group">
                        <label class="form-label">FamPay API Key</label>
                        <input type="text" name="fampay_api_key" value="<?= htmlspecialchars($fampayApiKey) ?>" placeholder="e.g. fp_live_sk_89234xxxx" class="admin-input" autocomplete="off">
                        <div class="form-hint">Obtained from FamPay Merchant Dashboard &rarr; Developer API Keys</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">FamPay Secret Key</label>
                        <input type="password" name="fampay_secret_key" value="<?= htmlspecialchars($fampaySecretKey) ?>" placeholder="••••••••••••••••••••••••" class="admin-input" autocomplete="off">
                        <div class="form-hint">Used for HMAC SHA256 webhook payload signing &amp; verification</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">FamPay Merchant ID</label>
                        <input type="text" name="fampay_merchant_id" value="<?= htmlspecialchars($fampayMerchantId) ?>" placeholder="e.g. MERCH_ZAMZY_01" class="admin-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gateway Environment Mode</label>
                        <select name="fampay_env" class="admin-input">
                            <option value="production" <?= $fampayEnv==='production'?'selected':'' ?>>Production Mode (Live Payments)</option>
                            <option value="sandbox" <?= $fampayEnv==='sandbox'?'selected':'' ?>>Sandbox Mode (Test &amp; Debug)</option>
                        </select>
                    </div>

                    <div style="margin-top:1.5rem; padding:1rem; background:rgba(6,182,212,0.06); border:1px dashed rgba(6,182,212,0.3); border-radius:8px;">
                        <div style="font-family:var(--mono); font-size:0.7rem; color:var(--cyan); font-weight:700; text-transform:uppercase; margin-bottom:4px;">
                            Webhook Callback URL
                        </div>
                        <div style="font-family:var(--mono); font-size:0.75rem; color:#fff; word-break:break-all;">
                            <?= defined('BASE_URL') ? BASE_URL . '/api.php?action=fampay_webhook' : 'https://zamzy.in/api.php?action=fampay_webhook' ?>
                        </div>
                    </div>
                </div>

                <!-- UPI & Webinar Pricing Settings Box -->
                <div class="admin-card" style="border: 1px solid rgba(6, 182, 212, 0.35); box-shadow: 0 0 35px rgba(6, 182, 212, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                        <h3 style="color:var(--cyan); font-family:var(--display); font-size:1.3rem; display:flex; align-items:center; gap:0.6rem; margin-bottom:0;">
                            📲 Direct UPI &amp; Pricing
                        </h3>
                        <span style="font-family:var(--mono); font-size:0.68rem; background:rgba(6,182,212,0.18); border:1px solid rgba(6,182,212,0.4); color:var(--cyan); padding:3px 8px; border-radius:4px; text-transform:uppercase; letter-spacing:0.1em; font-weight:700;">
                            DYNAMIC QR CONTROLS
                        </span>
                    </div>

                    <p style="font-size:0.82rem; color:var(--dim); margin-bottom:1.8rem; line-height:1.6;">
                        Direct UPI Virtual Payment Address (VPA) for instant QR codes and student ticket fee configuration.
                    </p>

                    <div class="form-group">
                        <label class="form-label">Primary UPI ID (VPA)</label>
                        <input type="text" name="upi_id" value="<?= htmlspecialchars($upiId) ?>" placeholder="e.g. 7287060553@ybl" class="admin-input" required>
                        <div class="form-hint">Displayed on checkout QR codes when instant UTR verification is used</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">UPI Merchant Account Name</label>
                        <input type="text" name="upi_name" value="<?= htmlspecialchars($upiName) ?>" placeholder="e.g. ZAMZY Digital Solutions" class="admin-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Webinar Ticket Price (INR ₹)</label>
                        <input type="number" step="1" name="webinar_price" value="<?= htmlspecialchars($webinarPrice) ?>" placeholder="96" class="admin-input" required>
                        <div class="form-hint">Displayed across webinar landing page &amp; checkout (Default: ₹96)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Webinar Display Title</label>
                        <input type="text" name="webinar_title" value="<?= htmlspecialchars($webinarTitle) ?>" class="admin-input">
                    </div>

                    <div style="margin-top:2.2rem;">
                        <button type="submit" class="btn-admin btn-admin-primary" style="width:100%; font-size:0.88rem; padding:1.1rem;">
                            💾 Save Payment &amp; Gateway Settings
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
