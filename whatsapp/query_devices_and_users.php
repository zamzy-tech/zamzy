<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/db.php';

try {
    echo "=== CLIENT DEVICES ===\n";
    $stmt = $pdo->query("SELECT * FROM client_devices");
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']} | Client ID: {$row['client_id']} | Slot: {$row['slot_number']} | Phone: {$row['phone_number']} | Key: {$row['api_key']} | Conn: {$row['whatsapp_is_connected']}\n";
    }
    
    echo "\n=== ADMIN USERS / OTHER TABLES ===\n";
    // Check if tables exist first
    $tables = ['users', 'youtubers', 'staff_users'];
    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() > 0) {
            echo "--- Table: $table ---\n";
            $stmt = $pdo->query("SELECT * FROM `$table` LIMIT 10");
            while ($row = $stmt->fetch()) {
                // Print key/value
                $out = [];
                foreach ($row as $k => $v) {
                    if (stripos($k, 'pass') !== false) $v = 'REDACTED';
                    $out[] = "$k: $v";
                }
                echo implode(" | ", $out) . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
