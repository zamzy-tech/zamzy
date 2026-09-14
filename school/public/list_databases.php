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

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SHOW DATABASES LIKE 'shacartc_eschool_saas%'");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Total databases: " . count($dbs) . "\n";
    
    // Sort databases by extracting the number
    usort($dbs, function($a, $b) {
        preg_match('/_(\d+)_/', $a, $mA);
        preg_match('/_(\d+)_/', $b, $mB);
        $numA = isset($mA[1]) ? (int)$mA[1] : 0;
        $numB = isset($mB[1]) ? (int)$mB[1] : 0;
        return $numB - $numA; // descending
    });
    
    echo "Top 10 highest-numbered databases:\n";
    for ($i = 0; $i < min(10, count($dbs)); $i++) {
        echo "  " . $dbs[$i] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
