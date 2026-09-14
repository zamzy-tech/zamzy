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
    
    // Find school with ID 26 or search by name like 'yaseer'
    $stmt = $pdo->query("SELECT * FROM schools WHERE id = 26 OR name LIKE '%yaseer%' OR database_name LIKE '%_26_%'");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($schools) . " matching schools:\n";
    foreach ($schools as $s) {
        echo "School ID: " . $s['id'] . "\n";
        echo "  Name: " . $s['name'] . "\n";
        echo "  Code: " . $s['code'] . "\n";
        echo "  DB: " . $s['database_name'] . "\n";
        echo "  Status: " . $s['status'] . "\n";
        echo "  Deleted: " . ($s['deleted_at'] ?? 'No') . "\n";
        
        $stmtUser = $pdo->prepare("SELECT id, email, status, email_verified_at FROM users WHERE id = ?");
        $stmtUser->execute([$s['admin_id']]);
        $u = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            echo "  Admin User ID: " . $u['id'] . "\n";
            echo "  Admin Email: " . $u['email'] . "\n";
            echo "  Admin Status: " . $u['status'] . "\n";
        } else {
            echo "  Admin User NOT found (ID: " . $s['admin_id'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
