<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

// Clear Logs Action
$msg = '';
$msgType = 'info';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_old_logs') {
    if ($pdo) {
        try {
            $days = intval($_POST['days'] ?? 30);
            if ($days <= 0) {
                $pdo->exec("TRUNCATE TABLE `zamzy_activity_logs`");
                $msg = "All activity logs successfully cleared!";
            } else {
                $delStmt = $pdo->prepare("DELETE FROM `zamzy_activity_logs` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL :days DAY)");
                $delStmt->execute([':days' => $days]);
                $msg = "Logs older than {$days} days purged successfully.";
            }
            $msgType = 'success';
        } catch (Exception $e) {
            $msg = "Error clearing logs: " . $e->getMessage();
            $msgType = 'danger';
        }
    }
}

// Filters & Pagination
$eventTypeFilter = trim($_GET['event_type'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if (!empty($eventTypeFilter)) {
    $where[] = "`event_type` = :etype";
    $params[':etype'] = $eventTypeFilter;
}

if (!empty($statusFilter)) {
    $where[] = "`status` = :st";
    $params[':st'] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(`ip_address` LIKE :s OR `city` LIKE :s OR `region` LIKE :s OR `country` LIKE :s OR `user_identifier` LIKE :s OR `phone` LIKE :s OR `email` LIKE :s OR `action_name` LIKE :s OR `org_isp` LIKE :s OR `details` LIKE :s)";
    $params[':s'] = "%{$search}%";
}

$whereClause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

$totalLogs = 0;
$todayLogs = 0;
$totalUniqueIps = 0;
$failedEvents = 0;
$logs = [];
$eventTypes = [];

if ($pdo) {
    try {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `zamzy_activity_logs`" . $whereClause);
        $countStmt->execute($params);
        $totalLogs = (int)$countStmt->fetchColumn();

        $todayLogs = (int)$pdo->query("SELECT COUNT(*) FROM `zamzy_activity_logs` WHERE DATE(`created_at`) = CURDATE()")->fetchColumn();
        $totalUniqueIps = (int)$pdo->query("SELECT COUNT(DISTINCT `ip_address`) FROM `zamzy_activity_logs`")->fetchColumn();
        $failedEvents = (int)$pdo->query("SELECT COUNT(*) FROM `zamzy_activity_logs` WHERE `status` = 'failed'")->fetchColumn();

        $fetchStmt = $pdo->prepare("SELECT * FROM `zamzy_activity_logs`" . $whereClause . " ORDER BY `id` DESC LIMIT {$limit} OFFSET {$offset}");
        $fetchStmt->execute($params);
        $logs = $fetchStmt->fetchAll() ?: [];

        $typeStmt = $pdo->query("SELECT DISTINCT `event_type` FROM `zamzy_activity_logs` WHERE `event_type` IS NOT NULL AND `event_type` != '' ORDER BY `event_type` ASC");
        $eventTypes = $typeStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    } catch (Exception $e) {}
}

$totalPages = ceil($totalLogs / $limit);

// Quick counts for sidebar badge
$totalWebinarRegs = 0;
$newInquiries = 0;
$totalDemos = 0;
$totalReviews = 0;
if ($pdo) {
    try { $totalWebinarRegs = $pdo->query("SELECT COUNT(*) FROM `zamzy_webinar_registrations`")->fetchColumn(); } catch (Exception $e) {}
    try { $newInquiries = $pdo->query("SELECT COUNT(*) FROM `zamzy_inquiries` WHERE `status` = 'new'")->fetchColumn(); } catch (Exception $e) {}
    try { $totalDemos = $pdo->query("SELECT COUNT(*) FROM `zamzy_demo_requests`")->fetchColumn(); } catch (Exception $e) {}
    try { $totalReviews = $pdo->query("SELECT COUNT(*) FROM `zamzy_testimonials`")->fetchColumn(); } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ZAMZY Admin — Security, IP Geolocation &amp; Activity Logs</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        .kpi-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        @media(max-width: 1024px) {
            .kpi-grid-4 { grid-template-columns: repeat(2, 1fr); }
        }
        @media(max-width: 600px) {
            .kpi-grid-4 { grid-template-columns: 1fr; }
        }

        .filter-strip {
            background: rgba(13, 13, 22, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1.6rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-item {
            flex: 1;
            min-width: 160px;
        }

        .ip-badge {
            font-family: var(--mono);
            font-size: 0.76rem;
            background: rgba(6, 182, 212, 0.12);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: var(--cyan);
            padding: 3px 7px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .geo-pill {
            font-size: 0.78rem;
            color: #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .geo-flag {
            font-size: 1rem;
        }

        .event-tag {
            font-family: var(--mono);
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
        }

        .event-tag--admin { background: rgba(139, 92, 246, 0.2); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.4); }
        .event-tag--otp { background: rgba(234, 179, 8, 0.2); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.4); }
        .event-tag--inquiry { background: rgba(6, 182, 212, 0.2); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.4); }
        .event-tag--webinar { background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.4); }
        .event-tag--career { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .event-tag--demo { background: rgba(244, 63, 94, 0.2); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.4); }
        .event-tag--other { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.4); }

        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .status-dot.success { background: #10b981; box-shadow: 0 0 8px #10b981; }
        .status-dot.failed { background: #ef4444; box-shadow: 0 0 8px #ef4444; }

        .details-expand {
            max-width: 280px;
            font-family: var(--mono);
            font-size: 0.72rem;
            color: var(--dim);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }
        .details-expand:hover {
            color: #fff;
            text-decoration: underline;
        }

        .log-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .log-modal.open { display: flex; }
        .log-modal-box {
            background: #0d0d16;
            border: 1px solid rgba(6, 182, 212, 0.4);
            border-radius: 14px;
            width: 100%;
            max-width: 620px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), 0 0 30px rgba(6, 182, 212, 0.2);
        }
        .log-modal-header {
            padding: 1.2rem 1.6rem;
            background: linear-gradient(90deg, rgba(6, 182, 212, 0.15), rgba(139, 92, 246, 0.15));
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .log-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            font-family: var(--mono);
            font-size: 0.78rem;
            line-height: 1.7;
        }
        .json-viewer {
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 12px;
            border-radius: 8px;
            color: #a5f3fc;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>
<body>

<!-- Mobile Admin Navigation Header -->
<div class="admin-mobile-header">
    <div class="admin-mobile-brand">
        <span class="admin-mobile-logo">ZAMZY<span>.</span></span>
        <span class="admin-mobile-tag">Security &amp; IP Logs</span>
    </div>
    <button class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Toggle Navigation">
        ☰ Menu
    </button>
</div>

<!-- Floating FAB Mobile Menu Button -->
<button class="admin-floating-fab" id="adminFabToggle" aria-label="Open Navigation Menu">
    ⚡ Menu
</button>

<!-- Mobile Drawer Overlay -->
<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<div class="admin-shell">

    <!-- Sidebar Drawer -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div>
            <div class="admin-sidebar__brand">
                <span class="admin-sidebar__logo">ZAMZY<span>.</span></span>
                <span class="admin-sidebar__sub">Executive Console</span>
            </div>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav__item"><span>📊</span> Dashboard</a>
                <a href="webinar.php" class="admin-nav__item"><span>🎓</span> Full Stack Webinar (<?= $totalWebinarRegs ?>)</a>
                <a href="inquiries.php" class="admin-nav__item"><span>📬</span> Inquiries &amp; Leads (<?= $newInquiries ?>)</a>
                <a href="demos.php" class="admin-nav__item"><span>⚡</span> Demo Requests (<?= $totalDemos ?>)</a>
                <a href="chats.php" class="admin-nav__item"><span>💬</span> Chat Reports</a>
                <a href="testimonials.php" class="admin-nav__item"><span>★</span> Reviews / Proof (<?= $totalReviews ?>)</a>
                <a href="careers.php" class="admin-nav__item"><span>👥</span> Careers &amp; Guild</a>
                <a href="logs.php" class="admin-nav__item active"><span>🛡️</span> Security &amp; IP Logs</a>
                <a href="settings.php" class="admin-nav__item"><span>⚙️</span> Settings &amp; SMTP</a>
                <a href="../" target="_blank" class="admin-nav__item"><span>↗</span> View Live Site</a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <div class="admin-user-pill">
                <span class="dot">●</span> <?= htmlspecialchars($_SESSION['zamzy_admin_name'] ?? 'Administrator') ?>
            </div>
            <a href="logout.php" class="btn-admin btn-admin-outline btn-admin-sm" style="width:100%; text-align:center;">Terminate Session</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        
        <header class="admin-topbar">
            <div>
                <h1 class="admin-page-title">Security &amp; IP Location Logs</h1>
                <p class="admin-page-sub">Real-time Visitor Audit Trail, Geolocation Telemetry, OTP Records &amp; System Events (<?= number_format($totalLogs) ?> Total)</p>
            </div>
            <div class="admin-topbar__actions">
                <button type="button" class="btn-admin btn-admin-outline" onclick="openPurgeModal()">🗑️ Purge Logs</button>
                <a href="logs.php" class="btn-admin btn-admin-primary">🔄 Live Refresh</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box" style="margin-bottom:1.5rem; background:<?= $msgType === 'success' ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)' ?>; border:1px solid <?= $msgType === 'success' ? '#10b981' : '#ef4444' ?>; color:#fff; padding:12px 18px; border-radius:8px;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <!-- KPI Metric Grid -->
        <div class="kpi-grid-4">
            <div class="admin-card">
                <span class="kpi-label">TOTAL LOGGED EVENTS</span>
                <div class="kpi-val" style="color:var(--cyan);"><?= number_format($totalLogs) ?></div>
                <div style="font-size:0.75rem; color:var(--dim); margin-top:0.4rem;">All system &amp; visitor telemetry</div>
            </div>
            <div class="admin-card">
                <span class="kpi-label">EVENTS TODAY</span>
                <div class="kpi-val" style="color:#a855f7;"><?= number_format($todayLogs) ?></div>
                <div style="font-size:0.75rem; color:var(--dim); margin-top:0.4rem;">Captured in the last 24h</div>
            </div>
            <div class="admin-card">
                <span class="kpi-label">UNIQUE CLIENT IPS</span>
                <div class="kpi-val" style="color:#38bdf8;"><?= number_format($totalUniqueIps) ?></div>
                <div style="font-size:0.75rem; color:var(--dim); margin-top:0.4rem;">Distinct visitor geolocations</div>
            </div>
            <div class="admin-card">
                <span class="kpi-label">FAILED / REJECTED ACTIONS</span>
                <div class="kpi-val" style="color:<?= $failedEvents > 0 ? '#ef4444' : '#10b981' ?>;"><?= number_format($failedEvents) ?></div>
                <div style="font-size:0.75rem; color:var(--dim); margin-top:0.4rem;">Auth fails &amp; invalid attempts</div>
            </div>
        </div>

        <!-- Filter & Search Strip -->
        <form method="GET" action="logs.php" class="filter-strip">
            <div class="filter-item" style="flex:2;">
                <input type="text" name="search" class="admin-input" placeholder="Search by IP, City, State, Country, Phone, Email, ISP..." value="<?= htmlspecialchars($search) ?>" style="width:100%;">
            </div>
            <div class="filter-item">
                <select name="event_type" class="admin-input" style="width:100%;">
                    <option value="">All Event Categories</option>
                    <?php foreach ($eventTypes as $et): ?>
                        <option value="<?= htmlspecialchars($et) ?>" <?= $eventTypeFilter === $et ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('_', ' ', $et))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item" style="max-width:160px;">
                <select name="status" class="admin-input" style="width:100%;">
                    <option value="">All Statuses</option>
                    <option value="success" <?= $statusFilter === 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed / Rejected</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-admin btn-admin-primary" style="padding:0.75rem 1.2rem;">Filter</button>
                <?php if (!empty($search) || !empty($eventTypeFilter) || !empty($statusFilter)): ?>
                    <a href="logs.php" class="btn-admin btn-admin-outline" style="padding:0.75rem 1rem;">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Main Logs Data Table -->
        <div class="admin-card" style="padding:0; overflow:hidden;">
            <div style="padding:1.2rem 1.6rem; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-family:var(--mono); font-size:0.85rem; font-weight:700; color:var(--white);">
                    LIVE AUDIT TRAIL RECORDINGS
                </div>
                <div style="font-size:0.78rem; color:var(--dim);">
                    Showing <?= count($logs) ?> of <?= number_format($totalLogs) ?> Records (Page <?= $page ?> of <?= max(1, $totalPages) ?>)
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="admin-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="width:140px;">TIMESTAMP</th>
                            <th style="width:130px;">EVENT TYPE</th>
                            <th>ACTION &amp; USER</th>
                            <th>IP &amp; GEOLOCATION</th>
                            <th>DEVICE &amp; ISP</th>
                            <th>STATUS</th>
                            <th style="width:90px; text-align:center;">DETAILS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:3rem; color:var(--dim);">
                                    No activity logs found matching your filter criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $row): 
                                $etype = $row['event_type'] ?? 'system';
                                $tagClass = 'event-tag--other';
                                if (strpos($etype, 'admin') !== false) $tagClass = 'event-tag--admin';
                                elseif (strpos($etype, 'otp') !== false) $tagClass = 'event-tag--otp';
                                elseif (strpos($etype, 'inquiry') !== false) $tagClass = 'event-tag--inquiry';
                                elseif (strpos($etype, 'webinar') !== false) $tagClass = 'event-tag--webinar';
                                elseif (strpos($etype, 'career') !== false) $tagClass = 'event-tag--career';
                                elseif (strpos($etype, 'demo') !== false) $tagClass = 'event-tag--demo';
                                
                                $status = strtolower($row['status'] ?? 'success');
                                $isSuccess = ($status === 'success');
                                
                                $city = !empty($row['city']) ? $row['city'] : 'Unknown';
                                $region = !empty($row['region']) ? $row['region'] : '';
                                $country = !empty($row['country']) ? $row['country'] : 'India';
                                $locationStr = $city . ($region && $region !== $city ? ', ' . $region : '') . ' (' . $country . ')';
                            ?>
                                <tr>
                                    <!-- Timestamp -->
                                    <td style="font-family:var(--mono); font-size:0.75rem; color:var(--faint); white-space:nowrap;">
                                        <?= date('d M Y', strtotime($row['created_at'])) ?><br>
                                        <strong style="color:var(--white);"><?= date('H:i:s', strtotime($row['created_at'])) ?></strong>
                                    </td>

                                    <!-- Event Type -->
                                    <td>
                                        <span class="event-tag <?= $tagClass ?>"><?= htmlspecialchars(str_replace('_', ' ', $row['event_type'])) ?></span>
                                    </td>

                                    <!-- Action & User Details -->
                                    <td>
                                        <div style="font-weight:600; color:#fff; font-size:0.86rem; margin-bottom:2px;">
                                            <?= htmlspecialchars($row['action_name']) ?>
                                        </div>
                                        <div style="font-size:0.76rem; color:var(--dim); font-family:var(--mono);">
                                            <?php if (!empty($row['user_identifier'])): ?>
                                                👤 <?= htmlspecialchars($row['user_identifier']) ?>
                                            <?php endif; ?>
                                            <?php if (!empty($row['phone'])): ?>
                                                &nbsp;💬 <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['phone']) ?>" target="_blank" style="color:var(--cyan); text-decoration:underline;"><?= htmlspecialchars($row['phone']) ?></a>
                                            <?php endif; ?>
                                            <?php if (!empty($row['email'])): ?>
                                                &nbsp;✉️ <?= htmlspecialchars($row['email']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- IP & Geolocation -->
                                    <td>
                                        <div class="ip-badge">
                                            🌐 <?= htmlspecialchars($row['ip_address']) ?>
                                        </div>
                                        <div class="geo-pill" style="margin-top:4px;">
                                            📍 <?= htmlspecialchars($locationStr) ?>
                                        </div>
                                    </td>

                                    <!-- Device & ISP -->
                                    <td>
                                        <div style="font-size:0.76rem; color:#fff;">
                                            💻 <?= htmlspecialchars($row['device_type'] ?? 'Desktop') ?>
                                        </div>
                                        <?php if (!empty($row['org_isp'])): ?>
                                            <div style="font-size:0.72rem; color:var(--faint); font-family:var(--mono); max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                🏢 <?= htmlspecialchars($row['org_isp']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <span style="font-size:0.76rem; font-weight:700; font-family:var(--mono); color:<?= $isSuccess ? '#10b981' : '#ef4444' ?>;">
                                            <span class="status-dot <?= $isSuccess ? 'success' : 'failed' ?>"></span>
                                            <?= strtoupper($status) ?>
                                        </span>
                                    </td>

                                    <!-- Action / View Modal -->
                                    <td style="text-align:center;">
                                        <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" 
                                                onclick='viewLogDetails(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                title="View Full Telemetry">
                                            🔍 View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($totalPages > 1): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:1.2rem 1.6rem; border-top:1px solid rgba(255,255,255,0.06); flex-wrap:wrap; gap:1rem;">
                    <div style="font-size:0.8rem; color:var(--dim);">
                        Page <?= $page ?> of <?= $totalPages ?> (Total <?= number_format($totalLogs) ?> entries)
                    </div>
                    <div style="display:flex; gap:0.4rem;">
                        <?php if ($page > 1): ?>
                            <a href="logs.php?page=1&search=<?= urlencode($search) ?>&event_type=<?= urlencode($eventTypeFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn-admin btn-admin-outline btn-admin-sm">« First</a>
                            <a href="logs.php?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&event_type=<?= urlencode($eventTypeFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn-admin btn-admin-outline btn-admin-sm">‹ Prev</a>
                        <?php endif; ?>

                        <?php
                        $startP = max(1, $page - 2);
                        $endP = min($totalPages, $page + 2);
                        for ($p = $startP; $p <= $endP; $p++):
                        ?>
                            <a href="logs.php?page=<?= $p ?>&search=<?= urlencode($search) ?>&event_type=<?= urlencode($eventTypeFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn-admin btn-admin-sm <?= $p == $page ? 'btn-admin-primary' : 'btn-admin-outline' ?>"><?= $p ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="logs.php?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&event_type=<?= urlencode($eventTypeFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn-admin btn-admin-outline btn-admin-sm">Next ›</a>
                            <a href="logs.php?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&event_type=<?= urlencode($eventTypeFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="btn-admin btn-admin-outline btn-admin-sm">Last »</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- Modal: Log Details Telemetry Viewer -->
<div class="log-modal" id="logModal" role="dialog" aria-modal="true">
    <div class="log-modal-box">
        <div class="log-modal-header">
            <div>
                <span class="event-tag event-tag--inquiry" id="modalEventTag">EVENT</span>
                <span style="font-weight:700; color:#fff; font-size:1rem; margin-left:8px;" id="modalEventTitle">Activity Inspection</span>
            </div>
            <button type="button" onclick="closeLogModal()" style="background:none; border:none; color:var(--dim); font-size:1.5rem; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <div class="log-modal-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.8rem; margin-bottom:1.2rem;">
                <div>
                    <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">IP Address</span>
                    <div id="modalIp" style="font-weight:700; color:#fff;">--</div>
                </div>
                <div>
                    <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">Geolocation</span>
                    <div id="modalGeo" style="font-weight:700; color:#fff;">--</div>
                </div>
                <div>
                    <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">ISP / Network</span>
                    <div id="modalIsp" style="color:#e2e8f0;">--</div>
                </div>
                <div>
                    <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">Device &amp; OS</span>
                    <div id="modalDevice" style="color:#e2e8f0;">--</div>
                </div>
                <div>
                    <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">User Identifier</span>
                    <div id="modalUser" style="color:#e2e8f0;">--</div>
                </div>
                <div>
                    <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">Timestamp</span>
                    <div id="modalTime" style="color:#e2e8f0;">--</div>
                </div>
            </div>

            <div style="margin-bottom:0.6rem;">
                <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">Request URL</span>
                <div id="modalUrl" style="background:rgba(255,255,255,0.03); padding:6px 10px; border-radius:6px; word-break:break-all; font-size:0.74rem;">--</div>
            </div>

            <div style="margin-bottom:0.6rem;">
                <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">User-Agent String</span>
                <div id="modalUa" style="background:rgba(255,255,255,0.03); padding:6px 10px; border-radius:6px; word-break:break-all; font-size:0.72rem; color:var(--dim);">--</div>
            </div>

            <div>
                <span style="color:var(--cyan); font-size:0.7rem; text-transform:uppercase;">Payload &amp; Action Details (JSON)</span>
                <div class="json-viewer" id="modalDetails">--</div>
            </div>
        </div>
        <div style="padding:1rem 1.6rem; border-top:1px solid rgba(255,255,255,0.06); text-align:right;">
            <button type="button" class="btn-admin btn-admin-outline" onclick="closeLogModal()">Close Telemetry</button>
        </div>
    </div>
</div>

<!-- Modal: Purge Logs -->
<div class="log-modal" id="purgeModal" role="dialog" aria-modal="true">
    <div class="log-modal-box" style="max-width:440px;">
        <div class="log-modal-header">
            <span style="font-weight:700; color:#fff; font-size:1rem;">🗑️ Purge Activity Logs</span>
            <button type="button" onclick="closePurgeModal()" style="background:none; border:none; color:var(--dim); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="logs.php">
            <input type="hidden" name="action" value="clear_old_logs">
            <div class="log-modal-body">
                <p style="font-family:var(--display); font-size:0.86rem; color:var(--dim); margin-bottom:1.2rem;">
                    Select the retention retention window to clean up database records:
                </p>
                <div style="margin-bottom:1.2rem;">
                    <label style="font-family:var(--mono); font-size:0.74rem; color:var(--cyan); display:block; margin-bottom:0.4rem;">PURGE CRITERIA</label>
                    <select name="days" class="admin-input" style="width:100%;">
                        <option value="90">Keep last 90 Days (Purge older)</option>
                        <option value="60">Keep last 60 Days (Purge older)</option>
                        <option value="30" selected>Keep last 30 Days (Purge older)</option>
                        <option value="7">Keep last 7 Days (Purge older)</option>
                        <option value="0">⚠️ Delete ALL Logs (Complete Truncate)</option>
                    </select>
                </div>
            </div>
            <div style="padding:1rem 1.6rem; border-top:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between;">
                <button type="button" class="btn-admin btn-admin-outline" onclick="closePurgeModal()">Cancel</button>
                <button type="submit" class="btn-admin btn-admin-danger">Confirm Purge</button>
            </div>
        </form>
    </div>
</div>

<script>
// Mobile Sidebar Toggle
const toggleBtn = document.getElementById('adminMobileToggle');
const fabBtn = document.getElementById('adminFabToggle');
const sidebar = document.getElementById('adminSidebar');
const overlay = document.getElementById('adminSidebarOverlay');

function toggleSidebar() {
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
}

if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
if (fabBtn) fabBtn.addEventListener('click', toggleSidebar);
if (overlay) overlay.addEventListener('click', toggleSidebar);

// Log Detail Modal Functions
function viewLogDetails(log) {
    document.getElementById('modalEventTag').textContent = (log.event_type || 'system').toUpperCase();
    document.getElementById('modalEventTitle').textContent = log.action_name || 'Event Details';
    document.getElementById('modalIp').textContent = log.ip_address || '127.0.0.1';
    
    let geo = (log.city || 'Unknown') + (log.region ? ', ' + log.region : '') + ' (' + (log.country || 'India') + ')';
    if (log.postal) geo += ' · Zip: ' + log.postal;
    document.getElementById('modalGeo').textContent = geo;
    
    document.getElementById('modalIsp').textContent = log.org_isp || 'N/A';
    document.getElementById('modalDevice').textContent = log.device_type || 'Desktop';
    
    let userStr = (log.user_identifier || 'Anonymous');
    if (log.phone) userStr += ' (' + log.phone + ')';
    if (log.email) userStr += ' <' + log.email + '>';
    document.getElementById('modalUser').textContent = userStr;
    
    document.getElementById('modalTime').textContent = log.created_at || '--';
    document.getElementById('modalUrl').textContent = log.page_url || 'N/A';
    document.getElementById('modalUa').textContent = log.user_agent || 'N/A';

    let detailsFormatted = log.details || 'No additional payload';
    try {
        const parsed = JSON.parse(log.details);
        detailsFormatted = JSON.stringify(parsed, null, 2);
    } catch(e) {}
    document.getElementById('modalDetails').textContent = detailsFormatted;

    document.getElementById('logModal').classList.add('open');
}

function closeLogModal() {
    document.getElementById('logModal').classList.remove('open');
}

function openPurgeModal() {
    document.getElementById('purgeModal').classList.add('open');
}

function closePurgeModal() {
    document.getElementById('purgeModal').classList.remove('open');
}
</script>

</body>
</html>
