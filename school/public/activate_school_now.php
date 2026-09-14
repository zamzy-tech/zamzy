<?php
define("LARAVEL_START", microtime(true));
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header("Content-Type: text/plain");
echo "=== School Activation Script ===\n\n";

try {
    \Illuminate\Support\Facades\DB::setDefaultConnection("mysql");
    
    // Find schools with status=0
    $inactiveSchools = \Illuminate\Support\Facades\DB::connection("mysql")->table("schools")->where("status", 0)->get();
    
    echo "Inactive schools (status=0): " . count($inactiveSchools) . "\n";
    foreach ($inactiveSchools as $school) {
        echo "  School ID: " . $school->id . " | Name: " . ($school->name ?? "N/A") . " | DB: " . ($school->database_name ?? "N/A") . "\n";
        
        // Check if this school has a successful payment
        $hasPaidTx = \Illuminate\Support\Facades\DB::connection("mysql")->table("payment_transactions")
            ->where("school_id", $school->id)
            ->where("payment_status", "succeed")
            ->exists();
        
        if ($hasPaidTx) {
            \Illuminate\Support\Facades\DB::connection("mysql")->table("schools")->where("id", $school->id)->update(["status" => 1]);
            echo "  -> ACTIVATED (has successful payment)\n";
        } else {
            // Check in the school database
            try {
                \Illuminate\Support\Facades\Config::set("database.connections.school.database", $school->database_name);
                \Illuminate\Support\Facades\DB::purge("school");
                $brilliantUser = \Illuminate\Support\Facades\DB::connection("school")->table("users")
                    ->whereRaw("LOWER(email) LIKE ?", ["%brilliant%"])
                    ->first();
                if ($brilliantUser) {
                    \Illuminate\Support\Facades\DB::connection("mysql")->table("schools")->where("id", $school->id)->update(["status" => 1]);
                    echo "  -> ACTIVATED (found brilliant user: " . $brilliantUser->email . ")\n";
                } else {
                    echo "  -> Skipped (no payment found)\n";
                }
            } catch (\Exception $e) {
                // Force activate - the user just paid
                \Illuminate\Support\Facades\DB::connection("mysql")->table("schools")->where("id", $school->id)->update(["status" => 1]);
                echo "  -> FORCE ACTIVATED (error checking DB: " . $e->getMessage() . ")\n";
            }
        }
    }
    
    echo "\n=== Recent Payment Transactions ===\n";
    $transactions = \Illuminate\Support\Facades\DB::connection("mysql")->table("payment_transactions")
        ->orderBy("id", "desc")
        ->limit(10)
        ->get();
    foreach ($transactions as $tx) {
        echo "TX " . $tx->id . " | " . $tx->payment_status . " | Rs." . $tx->amount . " | School:" . ($tx->school_id ?? "NULL") . " | " . ($tx->payment_gateway ?? "N/A") . " | " . ($tx->created_at ?? "") . "\n";
    }
    
    echo "\nDone.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
?>