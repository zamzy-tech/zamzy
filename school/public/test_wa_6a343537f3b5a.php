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
    $studentId = 56;
    $amount = 200; // testing 200 rs payment

    $feesPaid = \App\Models\FeesPaid::where("id", $feesPaidId)->firstOrFail();
    $student = \App\Models\Student::where("user_id", $studentId)->with("user", "guardian")->firstOrFail();
    $fees = \App\Models\Fees::findOrFail($feesPaid->fees_id);

    $currency_symbol = "₹";
    $template = app(\App\Services\CachingService::class)->getSchoolSettings("whatsapp-template-fee-payment", $student->school_id);
    
    if (!empty($template)) {
        $template = htmlspecialchars_decode($template);
        $totalPaid = $amount + (!empty($feesPaid) ? $feesPaid->amount : 0);
        $balance_amount = max(0, $fees->total_compulsory_fees - $totalPaid);
        $body = str_replace([
            "{parent_name}",
            "{student_name}",
            "{fee_name}",
            "{amount}",
            "{currency_symbol}",
            "{school_name}",
            "{balance_amount}",
            "{due_date}",
            "{url}"
        ], [
            $student->guardian->full_name ?? "",
            $student->user->first_name . " " . $student->user->last_name,
            $fees->name,
            $amount,
            $currency_symbol,
            app(\App\Services\CachingService::class)->getSchoolSettings("school_name", $student->school_id) ?? "",
            $balance_amount,
            $fees->due_date,
            url("/")
        ], $template);
    } else {
        $body = "Dear Parent/Student, fee payment of " . $currency_symbol . " " . $amount . " has been received successfully.";
    }

    $whatsappMsg = $body;
    $schoolId = $student->school_id;

    // Simulate link appending from helper
    $latestPayment = \App\Models\FeesPaid::where("student_id", $student->user_id)
        ->orderBy("id", "desc")
        ->first();
    if ($latestPayment) {
        $receiptUrl = \Illuminate\Support\Facades\URL::signedRoute("public.receipt.pdf", [
            "school_id" => $schoolId,
            "id" => $latestPayment->id
        ]);
        $whatsappMsg .= "\n\n📄 *Download Receipt:* " . $receiptUrl;
    }

    $title = "Fees Payment Received";
    if (!empty($title) && $title !== $whatsappMsg) {
        $whatsappMsg = "*" . $title . "*\n\n" . $whatsappMsg;
    }

    echo "=== Final WhatsApp Message Render ===\n";
    echo $whatsappMsg . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
@unlink(__FILE__);
?>