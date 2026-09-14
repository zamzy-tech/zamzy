<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT whatsapp_linked_number, whatsapp_is_connected FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    echo "=== DATABASE SETTINGS ===\n";
    echo "Linked Number: " . ($settings['whatsapp_linked_number'] ?? 'NULL') . "\n";
    echo "Is Connected (DB): " . ($settings['whatsapp_is_connected'] ?? '0') . "\n\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n\n";
}

$url = "https://2fa.tehub.in/whatsapp/status?session=default";
$res = @file_get_contents($url);
if ($res) {
    $data = json_decode($res, true);
    $status = $data['status'] ?? 'UNKNOWN';
    $num = $data['number'] ?? 'NULL';
    echo "=== GATEWAY DEFAULT SESSION ===\n";
    echo "Status: $status\n";
    echo "Linked Number: $num\n";
} else {
    echo "Failed to query gateway.\n";
}
