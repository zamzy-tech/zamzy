<?php
header('Content-Type: text/plain; charset=UTF-8');

function parseLaravelEnv($path) {
    if (!file_exists($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $config = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            if (preg_match('/^"([^"]*)"$/', $val, $matches)) $val = $matches[1];
            elseif (preg_match('/^\'([^\']*)\'$/', $val, $matches)) $val = $matches[1];
            $config[$key] = $val;
        }
    }
    return $config;
}

$env = parseLaravelEnv('../.env');
$host = $env['DB_HOST'] ?? '127.0.0.1';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

$databases = [
    'shacartc_eschool_saas_6_brilliant',
    'shacartc_eschool_saas_7_brilliant'
];

foreach ($databases as $db) {
    echo "Database: $db\n";
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT id, user_id, amount, payment_gateway, payment_status, created_at FROM payment_transactions ORDER BY id DESC LIMIT 10");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            echo "  No transactions found.\n";
        } else {
            foreach ($rows as $row) {
                echo "  ID: {$row['id']}, User ID: {$row['user_id']}, Amount: {$row['amount']}, Gateway: {$row['payment_gateway']}, Status: {$row['payment_status']}, Created At: {$row['created_at']}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    echo "----------------------------------------\n";
}
?>
