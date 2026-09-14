<?php
define('LARAVEL_START', microtime(true));
require '/home/shacartc/school.tehub.in/vendor/autoload.php';
$app = require_once '/home/shacartc/school.tehub.in/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');
echo "=== Check Shaik Kalesha & Fee ID 3 ===\n\n";

try {
    Config::set('database.connections.school.database', 'shacartc_eschool_saas_7_brilliant');
    DB::purge('school');
    DB::connection('school')->reconnect();
    DB::setDefaultConnection('school');

    $studentId = 31;
    $student = \App\Models\Students::with('user')->where('user_id', $studentId)->firstOrFail();
    echo "Student details:\n";
    echo " - User ID: " . $student->user_id . "\n";
    echo " - Name: " . $student->user->first_name . " " . $student->user->last_name . "\n";
    echo " - Class Section ID: " . $student->class_section_id . "\n";
    echo " - apply_class_fee: " . var_export($student->apply_class_fee, true) . "\n";
    echo " - apply_van_fee: " . var_export($student->apply_van_fee, true) . "\n";
    echo " - apply_admission_fee: " . var_export($student->apply_admission_fee, true) . "\n";
    echo " - payment_package: " . var_export($student->payment_package, true) . "\n";

    echo "\nFee ID 3 details:\n";
    $fees = \App\Models\Fee::where('id', 3)->with('fees_class_type.fees_type')->firstOrFail();
    echo " - Name: " . $fees->name . "\n";
    echo " - Class ID: " . $fees->class_id . "\n";
    echo " - Total Compulsory Fees (unfiltered): " . $fees->total_compulsory_fees . "\n";
    echo " - Compulsory Fees types & amounts:\n";
    foreach ($fees->fees_class_type as $fct) {
        echo "   * Type: " . ($fct->fees_type->name ?? 'N/A') . " (ID: " . $fct->fees_type_id . ") | Optional: " . $fct->optional . " | Amount: " . $fct->amount . "\n";
    }

    echo "\nCompulsory Fees Paid record for student 31:\n";
    $feesPaid = \App\Models\FeesPaid::where(['fees_id' => 3, 'student_id' => 31])->with('compulsory_fee')->first();
    if ($feesPaid) {
        echo " - ID: " . $feesPaid->id . "\n";
        echo " - Amount: " . $feesPaid->amount . "\n";
        echo " - Compulsory fees counts: " . $feesPaid->compulsory_fee->count() . "\n";
    } else {
        echo " - No FeesPaid record found.\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
