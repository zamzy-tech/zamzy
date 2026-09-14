<?php
// Activate school for ADMIN@BRILLIANTBCA.COM
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Use the central database
$app->make('db')->setDefaultConnection('mysql');

// Find the user
$user = DB::connection('mysql')->table('users')->where('email', 'ADMIN@BRILLIANTBCA.COM')->first();

if (!$user) {
    // Try case-insensitive search
    $user = DB::connection('mysql')->table('users')->whereRaw('LOWER(email) = ?', [strtolower('ADMIN@BRILLIANTBCA.COM')])->first();
}

if ($user) {
    echo "Found user: " . $user->email . " (ID: " . $user->id . ", School ID: " . ($user->school_id ?? 'NULL') . ")\n";
    
    if ($user->school_id) {
        $school = DB::connection('mysql')->table('schools')->where('id', $user->school_id)->first();
        
        if ($school) {
            echo "School: " . ($school->name ?? 'N/A') . " (ID: " . $school->id . ", Status: " . $school->status . ")\n";
            
            if ($school->status == 0) {
                DB::connection('mysql')->table('schools')->where('id', $school->id)->update(['status' => 1]);
                echo "SUCCESS: School activated! Status changed from 0 to 1.\n";
            } else {
                echo "School is already active (status = " . $school->status . ")\n";
            }
        } else {
            echo "ERROR: School not found with ID: " . $user->school_id . "\n";
        }
    } else {
        echo "ERROR: User has no school_id\n";
    }
} else {
    echo "User not found. Searching all users with 'brilliant' in email...\n";
    $users = DB::connection('mysql')->table('users')->whereRaw("LOWER(email) LIKE '%brilliant%'")->get();
    foreach ($users as $u) {
        echo "  - " . $u->email . " (ID: " . $u->id . ", School: " . ($u->school_id ?? 'NULL') . ")\n";
    }
    
    // Also check in school databases
    $schools = DB::connection('mysql')->table('schools')->get();
    foreach ($schools as $sch) {
        try {
            config(['database.connections.school.database' => $sch->database_name]);
            DB::purge('school');
            $schUser = DB::connection('school')->table('users')->whereRaw("LOWER(email) LIKE '%brilliant%'")->first();
            if ($schUser) {
                echo "Found in school DB '{$sch->database_name}': " . $schUser->email . "\n";
                echo "  School ID: " . $sch->id . ", Status: " . $sch->status . "\n";
                if ($sch->status == 0) {
                    DB::connection('mysql')->table('schools')->where('id', $sch->id)->update(['status' => 1]);
                    echo "  SUCCESS: School activated!\n";
                }
            }
        } catch (\Exception $e) {
            // Skip databases that can't be connected
        }
    }
}

// Also show recent payment transactions
echo "\n--- Recent Payment Transactions ---\n";
$transactions = DB::connection('mysql')->table('payment_transactions')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();
foreach ($transactions as $tx) {
    echo "TX ID: " . $tx->id . " | Status: " . $tx->payment_status . " | Amount: " . $tx->amount . " | School: " . ($tx->school_id ?? 'NULL') . " | Gateway: " . ($tx->payment_gateway ?? 'N/A') . "\n";
}
