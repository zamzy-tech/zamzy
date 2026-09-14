<?php
define("LARAVEL_START", microtime(true));
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

header("Content-Type: text/plain");
echo "Clearing all caches...\n\n";

try {
    $status = $kernel->call("cache:clear");
    echo "cache:clear - Status: $status\n";
    echo \Illuminate\Support\Facades\Artisan::output();
    
    $status = $kernel->call("config:clear");
    echo "config:clear - Status: $status\n";
    echo \Illuminate\Support\Facades\Artisan::output();
    
    $status = $kernel->call("view:clear");
    echo "view:clear - Status: $status\n";
    echo \Illuminate\Support\Facades\Artisan::output();
    
    $status = $kernel->call("route:clear");
    echo "route:clear - Status: $status\n";
    echo \Illuminate\Support\Facades\Artisan::output();
    
    echo "\nAll caches cleared successfully!\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>