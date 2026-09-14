<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require "../vendor/autoload.php";
$app = require_once "../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

try {
    echo "Running Faq query...\n";
    $faqs = App\Models\Faq::where("school_id", null)->get();
    echo "Faqs count: " . count($faqs) . "\n";
} catch (Exception $e) {
    echo "Faq EXCEPTION: " . $e->getMessage() . "\n";
}

try {
    echo "Running Guidance query...\n";
    $guidances = App\Models\Guidance::get();
    echo "Guidances count: " . count($guidances) . "\n";
} catch (Exception $e) {
    echo "Guidance EXCEPTION: " . $e->getMessage() . "\n";
}
?>