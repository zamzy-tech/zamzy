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

$env = parseLaravelEnv(__DIR__ . '/../.env');
$host = $env['DB_HOST'] ?? '127.0.0.1';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $pdoSaaS = new PDO("mysql:host=$host;dbname=shacartc_eschool_saas;charset=utf8mb4", $user, $pass);
    $pdoSaaS->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TABLES IN shacartc_eschool_saas ===\n";
    $stmt = $pdoSaaS->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $tName) {
        echo " - $tName\n";
    }
    
    if (in_array('addons', $tables)) {
        echo "\n=== ADDONS ===\n";
        $stmtAddons = $pdoSaaS->query("SELECT * FROM addons");
        print_r($stmtAddons->fetchAll(PDO::FETCH_ASSOC));
    }
    
    if (in_array('addon_subscriptions', $tables)) {
        echo "\n=== ADDON SUBSCRIPTIONS ===\n";
        $stmtAddonSubs = $pdoSaaS->query("SELECT * FROM addon_subscriptions");
        print_r($stmtAddonSubs->fetchAll(PDO::FETCH_ASSOC));
    }
    
    echo "\n=== OTHER SYSTEM SETTINGS CONTROLLER/MODEL SEARCH ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
