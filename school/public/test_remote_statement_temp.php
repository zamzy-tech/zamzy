<?php
define('LARAVEL_START', microtime(true));
require '/home/shacartc/school.tehub.in/vendor/autoload.php';
$app = require_once '/home/shacartc/school.tehub.in/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');
echo "=== Test Remote Statement & PDF ===\n\n";

try {
    Config::set('database.connections.school.database', 'shacartc_eschool_saas_7_brilliant');
    DB::purge('school');
    DB::connection('school')->reconnect();
    DB::setDefaultConnection('school');
    
    $studentId = 50; // Inayah M
    
    $student = \App\Models\Students::with([
        'user:id,first_name,last_name',
        'class_section.class.stream',
        'class_section.section',
        'class_section.medium',
        'guardian:id,first_name,last_name,mobile,occupation'
    ])->where('user_id', $studentId)->firstOrFail();

    echo "Found student: " . $student->user->first_name . " " . $student->user->last_name . "\n";
    echo "Class section: " . ($student->class_section->full_name ?? 'N/A') . "\n";

    $fees = \App\Models\Fee::where([
        'class_id' => $student->class_section->class_id,
        'session_year_id' => $student->session_year_id
    ])->with('fees_class_type.fees_type')->first();

    $compulsoryTotal = 0;
    if ($fees) {
        $fees->filterForStudent($student);
        $compulsoryTotal = $fees->total_compulsory_fees;
        if ($student->apply_admission_fee) {
            $compulsoryTotal += 2000;
        }
        echo "Found Fee Config: " . $fees->name . "\n";
    } else {
        echo "No Fee Config found.\n";
    }

    $paidCompulsory = \App\Models\CompulsoryFee::where('student_id', $student->user_id)
        ->whereHas('fees_paid.fees', function($q) use($student) {
            $q->where('session_year_id', $student->session_year_id);
        })
        ->sum('amount');

    $paidOptional = \App\Models\OptionalFee::where('student_id', $student->user_id)
        ->whereHas('fees_paid.fees', function($q) use($student) {
            $q->where('session_year_id', $student->session_year_id);
        })
        ->sum('amount');

    $totalFees = $compulsoryTotal + $paidOptional;
    $totalPaid = $paidCompulsory + $paidOptional;
    $balanceDue = max(0, $compulsoryTotal - $paidCompulsory);

    if ($balanceDue == 0) {
        $paymentStatus = 'PAID';
    } elseif ($totalPaid > 0) {
        $paymentStatus = 'PARTIALLY PAID';
    } else {
        $paymentStatus = 'UNPAID';
    }

    echo "Totals calculated:\n";
    echo " - Total Fees: $totalFees\n";
    echo " - Total Paid: $totalPaid\n";
    echo " - Balance Due: $balanceDue\n";
    echo " - Payment Status: $paymentStatus\n";

    $feesPaids = \App\Models\FeesPaid::where('student_id', $student->user_id)
        ->whereHas('fees', function($q) use($student) {
            $q->where('session_year_id', $student->session_year_id);
        })
        ->with([
            'fees.session_year',
            'compulsory_fee.payment_transaction',
            'optional_fee.fees_class_type.fees_type'
        ])
        ->orderBy('date', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    echo "Transactions count: " . $feesPaids->count() . "\n";

    $transactions = [];
    foreach ($feesPaids as $fp) {
        $mode = 'Online';
        $details = 'Online Payment';
        
        if ($fp->compulsory_fee->isNotEmpty()) {
            $firstComp = $fp->compulsory_fee->first();
            $mode = $firstComp->mode_name;
            if ($mode === 'Cash') {
                $details = 'Cash Received';
            } elseif ($mode === 'Cheque') {
                $details = 'Cheque No: ' . ($firstComp->cheque_no ?? 'N/A');
            } elseif ($mode === 'Online') {
                $details = 'Transaction ID: ' . ($firstComp->payment_transaction->payment_id ?? $firstComp->payment_transaction_id ?? 'N/A');
                $gateway = strtolower($firstComp->payment_transaction->payment_gateway ?? '');
                if (strpos($gateway, 'upi') !== false || strpos($gateway, 'razorpay') !== false) {
                    $mode = 'UPI';
                }
            }
        } elseif ($fp->optional_fee->isNotEmpty()) {
            $firstOpt = $fp->optional_fee->first();
            $mode = $firstOpt->mode;
            if ($mode === 'Cash' || $firstOpt->mode == 1) {
                $mode = 'Cash';
                $details = 'Cash Received';
            } elseif ($mode === 'Cheque' || $firstOpt->mode == 2) {
                $mode = 'Cheque';
                $details = 'Cheque No: ' . ($firstOpt->cheque_no ?? 'N/A');
            } else {
                $mode = 'Online';
                $details = 'Transaction ID: ' . ($firstOpt->payment_transaction->payment_id ?? $firstOpt->payment_transaction_id ?? 'N/A');
            }
        }
        
        $receiptNo = 'BCA/' . ($fp->fees->session_year->name ?? date('Y')) . '/' . str_pad($fp->id, 6, '0', STR_PAD_LEFT);
        
        $transactions[] = [
            'date' => $fp->date,
            'receipt_no' => $receiptNo,
            'mode' => $mode,
            'details' => $details,
            'amount' => $fp->amount
        ];
    }

    $school = app(\App\Services\CachingService::class)->getSchoolSettings('*', 7);
    $data = explode("storage/", $school['horizontal_logo'] ?? '');
    $school['horizontal_logo'] = end($data);

    if ($school['horizontal_logo'] == null) {
        $systemSettings = app(\App\Services\CachingService::class)->getSystemSettings();
        $data = explode("storage/", $systemSettings['horizontal_logo'] ?? '');
        $school['horizontal_logo'] = end($data);
    }

    echo "Horizontal logo filename: " . $school['horizontal_logo'] . "\n";

    echo "Attempting to render view and generate PDF...\n";
    
    try {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('fees.transaction_statement', compact('school', 'student', 'totalFees', 'totalPaid', 'balanceDue', 'paymentStatus', 'transactions'));
        $output = $pdf->output();
        echo "SUCCESS: Generated PDF, size = " . strlen($output) . " bytes\n";
    } catch (\Throwable $th) {
        echo "PDF Gen Error: " . $th->getMessage() . "\n";
        echo "In File: " . $th->getFile() . " Line: " . $th->getLine() . "\n";
        echo "Stack trace:\n" . $th->getTraceAsString() . "\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
