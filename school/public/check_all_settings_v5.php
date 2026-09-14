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
    echo "=== SAAS DB SYSTEM_SETTINGS (whatsapp/sms/firebase/mail) ===\n";
    $pdoSaaS = new PDO("mysql:host=$host;dbname=shacartc_eschool_saas;charset=utf8mb4", $user, $pass);
    $pdoSaaS->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdoSaaS->query("SELECT name, data FROM system_settings WHERE name LIKE '%whatsapp%' OR name LIKE '%sms%' OR name LIKE '%firebase%' OR name LIKE '%mail%' OR name IN ('system_name', 'logo')");
    $saasSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($saasSettings as $row) {
        $val = $row['data'];
        if (preg_match('/(password|key|secret)/i', $row['name'])) {
            $val = '********';
        }
        echo "{$row['name']}: $val\n";
    }
    
    echo "\n=== TENANT DB (shacartc_eschool_saas_1_brilliant) SCHOOL_SETTINGS ===\n";
    $pdoTenant = new PDO("mysql:host=$host;dbname=shacartc_eschool_saas_1_brilliant;charset=utf8mb4", $user, $pass);
    $pdoTenant->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdoTenant->query("SELECT name, data FROM school_settings");
    $tenantSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tenantSettings as $row) {
        $val = $row['data'];
        if (preg_match('/(password|key|secret)/i', $row['name'])) {
            $val = '********';
        }
        echo "{$row['name']}: $val\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
