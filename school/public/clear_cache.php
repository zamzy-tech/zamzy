<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Artisan;

header('Content-Type: text/plain');

echo "Clearing config...\n";
try {
    Artisan::call('config:clear');
    echo Artisan::output();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Clearing route...\n";
try {
    Artisan::call('route:clear');
    echo Artisan::output();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Clearing view...\n";
try {
    Artisan::call('view:clear');
    echo Artisan::output();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Clearing cache...\n";
try {
    Artisan::call('cache:clear');
    echo Artisan::output();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Done!\n";
?>
