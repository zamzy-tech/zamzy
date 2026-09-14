<?php
define('LARAVEL_START', microtime(true));
require '/home/shacartc/school.tehub.in/vendor/autoload.php';
$app = require_once '/home/shacartc/school.tehub.in/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');
echo "=== Test Manual Execution ===\n";

try {
    $controller = app(\App\Http\Controllers\FeesController::class);
    echo "Calling publicTransactionStatementPDF(7, 50)...\n";
    $res = $controller->publicTransactionStatementPDF(7, 50);
    if ($res instanceof \Exception || $res instanceof \Throwable) {
        echo "Returned Exception: " . $res->getMessage() . "\n";
        echo "Trace:\n" . $res->getTraceAsString() . "\n";
    } else {
        echo "Success, returned stream.\n";
    }
} catch (\Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
}
?>
