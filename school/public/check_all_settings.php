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
$db = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== ALL DATABASES ON SERVER ===\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($dbs as $dbName) {
        echo " - $dbName\n";
    }

    echo "\n=== LIST OF BACKUP/SQL FILES IN /home/shacartc/ ===\n";
    $homeDir = '/home/shacartc';
    if (is_dir($homeDir)) {
        $files = scandir($homeDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $path = $homeDir . '/' . $file;
            $isDir = is_dir($path) ? 'DIR' : 'FILE';
            $size = is_dir($path) ? '' : ' (' . round(filesize($path)/1024/1024, 2) . ' MB)';
            echo " - $file [$isDir]$size\n";
            if ($isDir === 'DIR' && (stripos($file, 'school') !== false || stripos($file, 'tehub') !== false)) {
                $subFiles = scandir($path);
                foreach ($subFiles as $sf) {
                    if (preg_match('/\.sql|\.zip|\.gz|\.tar/i', $sf)) {
                        echo "    * $sf\n";
                    }
                }
            }
        }
    } else {
        echo "Cannot read $homeDir\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
