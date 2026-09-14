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

    $feesPaidId = 13;
    $feesPaid = \App\Models\FeesPaid::where("id", $feesPaidId)->with([
        "fees.installments"
    ])->firstOrFail();

    $student = \App\Models\Student::where("user_id", $feesPaid->student_id)->firstOrFail();

    // Replicate our blade calculation
    $classFeeTotal = 11600; // based on our query
    $vanFeeTotal = 400; // wait, let us see
    $admissionFeeTotal = $student->apply_admission_fee ? 2000 : 0;
    
    // In our receipt calculation:
    // classFeeTotal + vanFeeTotal + admissionFeeTotal
    // Let us verify exact values from the DB or receipt
    $totalDueSum = 14000; 
    $totalCompulsoryPaid = 7200; // 5000 + 2000 + 200

    $upcomingDueDate = null;
    $upcomingDueAmount = 0;
    $installments = $feesPaid->fees->installments ?? collect();
    
    if ($installments->isEmpty()) {
        $balance = $totalDueSum - $totalCompulsoryPaid;
        if ($balance > 0) {
            $upcomingDueDate = $feesPaid->fees->due_date ?? null;
            $upcomingDueAmount = $balance;
        }
    } else {
        $sortedInstallments = $installments->sortBy("due_date");
        $remainingPayment = $totalCompulsoryPaid;
        $installmentCount = $sortedInstallments->count();
        
        foreach ($sortedInstallments as $inst) {
            $instAmount = $totalDueSum / $installmentCount;
            if ($remainingPayment >= $instAmount) {
                $remainingPayment -= $instAmount;
            } else {
                $upcomingDueDate = $inst->due_date;
                $upcomingDueAmount = $instAmount - $remainingPayment;
                break;
            }
        }
    }

    echo "Upcoming Due Date: " . $upcomingDueDate . "\n";
    echo "Upcoming Due Amount: " . $upcomingDueAmount . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
@unlink(__FILE__);
?>