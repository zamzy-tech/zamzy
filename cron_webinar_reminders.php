<?php
/**
 * ZAMZY Automated Webinar Payment Reminders Cron Script
 * 
 * Scheduled logic:
 * 1. Checks for registrations pending payment > 10 minutes and sends initial reminder link.
 * 2. Checks for pending registrations requiring twice-daily follow-ups.
 * 
 * Can be run via CLI (`php cron_webinar_reminders.php`) or HTTP GET (`curl https://zamzy.in/cron_webinar_reminders.php?key=ZAMZY_CRON_SECRET`).
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

// Set execution limits for background processing
set_time_limit(180);
ini_set('memory_limit', '256M');

// Secure HTTP Access via secret key or CLI mode
$cronSecretKey = getSetting('cron_secret_key', 'ZAMZY_CRON_SECRET_2026');
$requestKey = $_GET['key'] ?? $_POST['key'] ?? null;
$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    header('Content-Type: application/json; charset=utf-8');
    if (!empty($cronSecretKey) && $requestKey !== $cronSecretKey && !isset($_SESSION['admin_user'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized cron invocation. Provide ?key=CRON_SECRET_KEY'
        ]);
        exit;
    }
}

$pdo = getDbConnection();
$result = runWebinarPaymentRemindersCheck($pdo);

if ($isCli) {
    echo "[" . date('Y-m-d H:i:s') . "] Webinar Payment Reminders Run Completed.\n";
    echo "10-Min Reminders Sent: " . ($result['dispatched_10min'] ?? 0) . "\n";
    echo "Daily Reminders Sent: " . ($result['dispatched_daily'] ?? 0) . "\n";
    echo "Total Dispatched: " . ($result['total_dispatched'] ?? 0) . "\n";
    if (!empty($result['errors'])) {
        echo "Errors:\n" . implode("\n", $result['errors']) . "\n";
    }
} else {
    echo json_encode($result, JSON_PRETTY_PRINT);
}
