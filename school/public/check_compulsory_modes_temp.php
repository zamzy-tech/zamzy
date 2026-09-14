<?php
define('LARAVEL_START', microtime(true));
require '/home/shacartc/school.tehub.in/vendor/autoload.php';
$app = require_once '/home/shacartc/school.tehub.in/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$app->make(Kernel::class)->bootstrap();

try {
    Config::set('database.connections.school.database', 'shacartc_eschool_saas_7_brilliant');
    DB::purge('school');
    DB::connection('school')->reconnect();
    DB::setDefaultConnection('school');

    $records = DB::table('compulsory_fees')->select('id', 'mode', 'amount', 'type')->limit(10)->get();
    foreach ($records as $r) {
        echo "ID: {$r->id}, Mode: " . var_export($r->mode, true) . ", Amount: {$r->amount}, Type: {$r->type}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
