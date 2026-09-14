<?php
define("LARAVEL_START", microtime(true));
require "/home/shacartc/school.tehub.in/vendor/autoload.php";
$app = require_once "/home/shacartc/school.tehub.in/bootstrap/app.php";

use Illuminate\Contracts\Console\Kernel;
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

header("Content-Type: text/plain");

try {
    $dbName = "shacartc_eschool_saas_7_brilliant";
    \Illuminate\Support\Facades\Config::set("database.connections.mysql.database", $dbName);
    \Illuminate\Support\Facades\DB::purge("mysql");
    \Illuminate\Support\Facades\DB::reconnect("mysql");

    // Let us find the student info for student 10
    // Wait, the student in the receipt (FeesPaid ID 13) has student_id 10 (which is user_id 10). Let us look at student table and user table.
    $student = \App\Models\Student::where("id", 10)->orWhere("user_id", 10)->with("user")->first();
    if ($student) {
        echo "Student ID: " . $student->id . " (User ID: " . $student->user_id . "), Name: " . $student->user->first_name . " " . $student->user->last_name . "\n";
        echo "apply_class_fee: " . $student->apply_class_fee . ", apply_van_fee: " . $student->apply_van_fee . ", apply_admission_fee: " . $student->apply_admission_fee . "\n";
        
        // Find all fees_paids records for this student
        $feesPaids = \App\Models\FeesPaid::where("student_id", $student->user_id)->with("fees")->get();
        echo "Total Fees Paid Records: " . $feesPaids->count() . "\n";
        foreach ($feesPaids as $fp) {
            echo "FeesPaid ID: " . $fp->id . ", Date: " . $fp->date . ", Amount: " . $fp->amount . ", Fees ID: " . $fp->fees_id . "\n";
            
            echo "  Compulsory Fees (child relations):\n";
            $compulsoryFees = \App\Models\CompulsoryFee::where("fees_paid_id", $fp->id)->get();
            foreach ($compulsoryFees as $cf) {
                echo "    - ID: " . $cf->id . ", Amount: " . $cf->amount . ", Date: " . $cf->date . ", Type: " . $cf->type . ", Mode: " . $cf->mode . "\n";
            }
        }
    } else {
        echo "Student not found!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
@unlink(__FILE__);
?>