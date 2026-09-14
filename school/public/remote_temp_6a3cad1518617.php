<?php
header('Content-Type: text/plain; charset=UTF-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting bootstrap test...\n";

$bmsDir = '/home/shacartc/bms.tehub.in';

echo "1. Checking autoload.php...\n";
if (!file_exists("$bmsDir/vendor/autoload.php")) {
    die("Error: vendor/autoload.php does not exist.\n");
}
require "$bmsDir/vendor/autoload.php";
echo "Autoload loaded successfully.\n";

echo "2. Checking app.php...\n";
if (!file_exists("$bmsDir/bootstrap/app.php")) {
    die("Error: bootstrap/app.php does not exist.\n");
}
$app = require_once "$bmsDir/bootstrap/app.php";
echo "App instance retrieved.\n";

echo "3. Bootstrapping kernel...\n";
try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "Kernel bootstrapped successfully!\n";
} catch (\Throwable $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

unlink(__FILE__);
?>
