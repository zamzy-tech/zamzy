<?php
header('Content-Type: text/plain; charset=UTF-8');
echo "Direct config from config file:\n";
$dbConfig = require __DIR__ . '/../config/database.php';
$mysql = $dbConfig['connections']['mysql'];
print_r([
    'driver' => $mysql['driver'] ?? 'mysql',
    'host' => $mysql['host'] ?? '127.0.0.1',
    'database' => $mysql['database'] ?? '',
    'username' => $mysql['username'] ?? '',
    'prefix' => $mysql['prefix'] ?? 'None',
]);
?>
