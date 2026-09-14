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
    
    $stmt = $pdo->query("SELECT id, code, support_email, database_name, status, deleted_at FROM schools WHERE id > 7 ORDER BY id ASC");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total found: " . count($schools) . "\n";
    foreach ($schools as $s) {
        $id = $s['id'];
        $code = preg_replace('/[^\x20-\x7E]/', '', $s['code'] ?? 'NULL');
        $email = preg_replace('/[^\x20-\x7E]/', '', $s['support_email'] ?? 'NULL');
        $dbname = preg_replace('/[^\x20-\x7E]/', '', $s['database_name'] ?? 'NULL');
        $status = $s['status'];
        $d = $s['deleted_at'] ? "D" : "A";
        
        echo "ID:$id | Code:$code | Email:$email | DB:$dbname | S:$status | Lifecycle:$d\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
