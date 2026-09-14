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
    
    // Find user by email
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, mobile, school_id, status, deleted_at FROM users WHERE email = ?");
    $stmt->execute(['auto_dnovtn@tehub.in']);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($u) {
        echo "User found in Central:\n";
        echo "  ID: " . $u['id'] . "\n";
        echo "  Name: " . $u['first_name'] . " " . $u['last_name'] . "\n";
        echo "  Email: " . $u['email'] . "\n";
        echo "  School ID: " . $u['school_id'] . "\n";
        echo "  Status: " . $u['status'] . "\n";
        echo "  Deleted: " . ($u['deleted_at'] ?? 'No') . "\n";
        
        // Find school by ID
        $stmtSchool = $pdo->prepare("SELECT id, name, code, database_name, status, deleted_at FROM schools WHERE id = ?");
        $stmtSchool->execute([$u['school_id']]);
        $s = $stmtSchool->fetch(PDO::FETCH_ASSOC);
        if ($s) {
            echo "\nSchool found in Central:\n";
            echo "  ID: " . $s['id'] . "\n";
            echo "  Name: " . $s['name'] . "\n";
            echo "  Code: " . $s['code'] . "\n";
            echo "  DB: " . $s['database_name'] . "\n";
            echo "  Status: " . $s['status'] . "\n";
            echo "  Deleted: " . ($s['deleted_at'] ?? 'No') . "\n";
        } else {
            echo "\nSchool with ID " . $u['school_id'] . " NOT found in Central.\n";
        }
    } else {
        echo "User auto_dnovtn@tehub.in NOT found in Central.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
