<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT id, name, login_id, status FROM api_keys");
    while ($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Phone (Login ID): " . $row['login_id'] . " | Status: " . $row['status'] . "\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
