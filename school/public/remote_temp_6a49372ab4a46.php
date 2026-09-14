<?php
header('Content-Type: text/plain; charset=UTF-8');

// Bootstrap Laravel
$bootstrapPath = '/home/shacartc/school.tehub.in/bootstrap/app.php';
if (!file_exists($bootstrapPath)) {
    die("Laravel bootstrap not found\n");
}

$app = require_once $bootstrapPath;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "STANDALONE_SCHOOL: " . (env('STANDALONE_SCHOOL') ? 'TRUE' : 'FALSE') . "\n";
echo "DEMO_MODE: " . (env('DEMO_MODE') ? 'TRUE' : 'FALSE') . "\n";


unlink(__FILE__);
?>
