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
    
    $stmt = $pdo->query("SELECT id, name, code, support_email, database_name, status, admin_id, created_at FROM schools ORDER BY id DESC LIMIT 1");
    $school = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$school) {
        echo "No schools found.\n";
        exit;
    }
    
    echo "LATEST SCHOOL (CENTRAL):\n";
    echo "ID: " . $school['id'] . "\n";
    echo "Name: " . $school['name'] . "\n";
    echo "Code: " . $school['code'] . "\n";
    echo "Email: " . $school['support_email'] . "\n";
    echo "DB: " . $school['database_name'] . "\n";
    echo "Status: " . $school['status'] . "\n";
    echo "Admin ID: " . $school['admin_id'] . "\n";
    echo "Created At: " . $school['created_at'] . "\n";
    
    $stmt = $pdo->prepare("SELECT id, email, status, email_verified_at FROM users WHERE id = ?");
    $stmt->execute([$school['admin_id']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        echo "\nADMIN USER (CENTRAL):\n";
        echo "ID: " . $u['id'] . "\n";
        echo "Email: " . $u['email'] . "\n";
        echo "Status: " . $u['status'] . "\n";
        echo "Verified At: " . ($u['email_verified_at'] ?? 'NULL') . "\n";
    }
    
    $tenantDb = $school['database_name'];
    try {
        $tenantPdo = new PDO("mysql:host=$host;dbname=$tenantDb;charset=utf8mb4", $user, $pass);
        $tenantPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $tenantPdo->prepare("SELECT id, email, status, email_verified_at FROM users WHERE id = ?");
        $stmt->execute([$school['admin_id']]);
        $tu = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tu) {
            echo "\nADMIN USER (TENANT DB):\n";
            echo "ID: " . $tu['id'] . "\n";
            echo "Email: " . $tu['email'] . "\n";
            echo "Status: " . $tu['status'] . "\n";
            echo "Verified At: " . ($tu['email_verified_at'] ?? 'NULL') . "\n";
        } else {
            echo "\nADMIN USER NOT FOUND IN TENANT DB (ID: " . $school['admin_id'] . ")\n";
        }
    } catch (Exception $ex) {
        echo "\nFailed to connect to Tenant DB: " . $ex->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
