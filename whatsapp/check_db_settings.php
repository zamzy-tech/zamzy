<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/db.php';

try {
    echo "=== SETTINGS TABLE ===\n";
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    foreach ($settings as $key => $val) {
        // Obfuscate passwords/tokens for safety, but show the rest
        if (stripos($key, 'password') !== false || stripos($key, 'token') !== false) {
            $val = empty($val) ? 'EMPTY' : 'SET (Length: ' . strlen($val) . ')';
        }
        echo "$key: $val\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
