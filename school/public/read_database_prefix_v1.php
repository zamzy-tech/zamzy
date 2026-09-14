<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

header('Content-Type: text/plain; charset=UTF-8');
echo "Laravel Active Database Config:\n";
$config = config('database.connections.mysql');
print_r([
    'host' => $config['host'],
    'database' => $config['database'],
    'username' => $config['username'],
    'prefix' => $config['prefix'] ?? 'None',
]);
?>
