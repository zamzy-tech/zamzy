<?php
header('Content-Type: text/plain; charset=UTF-8');

function parseLaravelEnv($path) {
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
$db = $env['DB_DATABASE'] ?? 'shacartc_eschool_saas';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, name, code, support_email, database_name, status, admin_id, deleted_at FROM schools ORDER BY id ASC");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $output = "Total central schools: " . count($schools) . "\n";
    foreach ($schools as $s) {
        $del = $s['deleted_at'] ? "Deleted at {$s['deleted_at']}" : "Active";
        $output .= "ID: {$s['id']} | Name: {$s['name']} | Code: {$s['code']} | Email: {$s['support_email']} | DB: {$s['database_name']} | Status: {$s['status']} | Admin: {$s['admin_id']} | {$del}\n";
    }
    
    echo base64_encode($output);
    
} catch (Exception $e) {
    echo base64_encode("Error: " . $e->getMessage());
}
?>
