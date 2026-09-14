<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\School;

header('Content-Type: text/plain');

$schools = School::on('mysql')->where('name', 'LIKE', '%Auto%')->withTrashed()->get();
echo "Found " . count($schools) . " schools:\n";
foreach ($schools as $s) {
    echo "ID: {$s->id}, Code: {$s->code}, Name: {$s->name}, Email: {$s->support_email}, Status: {$s->status}, Deleted: " . ($s->deleted_at ?? 'No') . "\n";
}
?>
