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

echo "=== TABLES IN DB shacartc_school ===\n";
try {
    $pdo = new PDO("mysql:host=$host;dbname=shacartc_school;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $tName) {
        echo " - $tName\n";
    }
    
    // Check if system_settings table exists
    if (in_array('system_settings', $tables)) {
        echo "\n=== SYSTEM SETTINGS IN shacartc_school ===\n";
        $stmtSettings = $pdo->query("SELECT name, data FROM system_settings WHERE name IN ('system_name', 'logo', 'horizontal_logo', 'vertical_logo', 'favicon', 'sms_provider', 'whatsapp_provider', 'whatsapp_status') OR name LIKE '%whatsapp%' OR name LIKE '%sms%'");
        $settings = $stmtSettings->fetchAll(PDO::FETCH_ASSOC);
        foreach ($settings as $row) {
            echo "{$row['name']}: {$row['data']}\n";
        }
    } else {
        echo "\nNo system_settings table in shacartc_school.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
