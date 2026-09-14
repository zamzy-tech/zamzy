<?php
require_once "/home/shacartc/dtcampus/vendor/autoload.php";
$app = require_once "/home/shacartc/dtcampus/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $pages = DB::table("pages")->get();
    echo json_encode($pages, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

unlink(__FILE__);
?>