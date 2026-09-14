<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM api_keys LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        print_r(array_keys($row));
        echo "\n=== All Users ===\n";
        $stmt2 = $pdo->query("SELECT * FROM api_keys");
        while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            foreach ($r as $k => $v) {
                if (strpos($k, 'pass') === false && strpos($k, 'key') === false) {
                    echo "$k: $v | ";
                }
            }
            echo "\n";
        }
    } else {
        echo "No users found in api_keys table.\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
