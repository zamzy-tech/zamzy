<?php
require_once __DIR__ . '/db.php';
$pdo = getDbConnection();
if ($pdo) {
    echo "--- MYSQL ZAMZY_SETTINGS ---\n";
    $stmt = $pdo->query("SELECT * FROM zamzy_settings");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo $r['setting_key'] . " = " . $r['setting_value'] . "\n";
    }
} else {
    echo "MySQL connection failed\n";
}
