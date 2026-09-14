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

    $feesID = 2;
    $studentId = 56;

    $student = \App\Models\Student::where("user_id", $studentId)->with(["compulsory_fees" => function($q) use($feesID) {
        $q->whereHas("fees_paid", function($q) use($feesID) {
            $q->where("fees_id", $feesID);
        });
    }])->firstOrFail();

    $fees = \App\Models\Fees::where("id", $feesID)->with("fees_class_type.fees_type", "installments")->firstOrFail();

    $totalFeesAmount = $fees->fees_class_type->where("optional", 0)->sum("amount");
    if ($student->apply_admission_fee) {
        $totalFeesAmount += 2000;
    }

    $totalInstallments = count($fees->installments);
    echo "Total compulsory fees: $totalFeesAmount\n";
    echo "Total installments: $totalInstallments\n";

    // How the system processes it:
    $oneInstallmentPaid = false;
    $paidInstallmentPayments = $student->compulsory_fees->where("type", "Installment Payment")->values();
    $paidCount = $paidInstallmentPayments->count();
    
    echo "Paid installments count: " . $paidCount . "\n";
    foreach ($paidInstallmentPayments as $p) {
        echo "  - Paid installment ID: " . $p->installment_id . ", Amount: " . $p->amount . "\n";
    }

    $index = 0;
    $processed = [];
    foreach ($fees->installments as $installment) {
        // Replicate logic
        $isPaid = $index < $paidCount;
        $installmentPaid = $isPaid ? $paidInstallmentPayments[$index] : null;
        if (!empty($installmentPaid)) {
            --$totalInstallments;
            $oneInstallmentPaid = true;
            $totalFeesAmount -= $installmentPaid->amount;
            $installment["is_paid"] = $installmentPaid;
            if ($totalInstallments) {
                $installment["minimum_amount"] = $totalFeesAmount / $totalInstallments;
            } else {
                $installment["minimum_amount"] = $totalFeesAmount;
            }
            $installment["maximum_amount"] = $totalFeesAmount;
        } else {
            $installment["is_paid"] = null;
            $installment["minimum_amount"] = $totalFeesAmount / $totalInstallments;
            $installment["maximum_amount"] = $totalFeesAmount;
        }
        $index++;
        
        echo "Installment Name: " . $installment->name . "\n";
        echo "  - Due Date: " . $installment->due_date . "\n";
        echo "  - Is Paid: " . ($installment["is_paid"] ? "YES" : "NO") . "\n";
        echo "  - Minimum Amount: " . $installment["minimum_amount"] . "\n";
        echo "  - Maximum Amount: " . $installment["maximum_amount"] . "\n";
        $processed[] = $installment;
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
@unlink(__FILE__);
?>