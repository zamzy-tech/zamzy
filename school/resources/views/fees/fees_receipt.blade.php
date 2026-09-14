<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fee Receipt || {{ config('app.name') }}</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'DejaVu Sans', sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            font-size: 11px;
            color: #333;
            background-color: #fff;
        }
        .container {
            width: 100%;
            padding: 15px;
        }
        /* Header section */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .header-left {
            width: 60%;
            vertical-align: top;
        }
        .header-right {
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        .logo-img {
            height: 60px;
            width: 60px;
            float: left;
            margin-right: 12px;
        }
        .school-name {
            font-size: 18px;
            color: #0c3e72;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .school-details {
            font-size: 9px;
            color: #555;
            line-height: 1.3;
        }
        .receipt-title-box {
            background-color: #0c3e72;
            color: #fff;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            border-radius: 0 0 0 15px;
            width: 200px;
            margin-bottom: 15px;
        }
        .receipt-info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .receipt-info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .receipt-info-label {
            font-weight: bold;
            color: #0c3e72;
            width: 100px;
        }
        .receipt-info-val {
            color: #333;
        }
        
        /* Cards section (side-by-side using table for Dompdf safety) */
        .cards-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .card-wrapper-left {
            width: 50%;
            padding-right: 8px;
            vertical-align: top;
        }
        .card-wrapper-right {
            width: 50%;
            padding-left: 8px;
            vertical-align: top;
        }
        .card {
            border: 1px solid #ced4da;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
        }
        .card-header {
            background-color: #0c3e72;
            color: #fff;
            padding: 6px 12px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .card-body {
            padding: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 0;
            font-size: 10px;
            vertical-align: top;
        }
        .info-label {
            color: #555;
            width: 90px;
        }
        .info-val {
            font-weight: bold;
            color: #111;
        }
        
        .sub-box {
            background-color: #fffdf5;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            padding: 6px;
            font-size: 9px;
            color: #856404;
            margin-top: 15px;
            font-weight: bold;
        }

        /* Particulars Table */
        .particulars-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .particulars-table th {
            background-color: #0c3e72;
            color: #fff;
            padding: 8px;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #0c3e72;
        }
        .particulars-table td {
            padding: 8px;
            border: 1px solid #ced4da;
            font-size: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .total-row {
            background-color: #eef2f7;
            font-weight: bold;
            font-size: 11px;
        }
        .total-amount-val {
            color: #0c3e72;
            font-size: 14px;
            font-weight: bold;
        }

        .words-box {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px;
            background-color: #fff;
            font-size: 10px;
            margin-bottom: 20px;
        }
        
        /* Footer area */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .footer-left {
            width: 40%;
            vertical-align: top;
        }
        .footer-middle {
            width: 25%;
            vertical-align: top;
            text-align: center;
        }
        .footer-right {
            width: 35%;
            vertical-align: bottom;
            text-align: center;
        }
        .note-box {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 8px;
            background-color: #fafafa;
            font-size: 9px;
            color: #666;
            line-height: 1.4;
        }
        .note-title {
            font-weight: bold;
            margin-bottom: 4px;
            color: #333;
        }
        .note-box ul {
            margin: 0;
            padding-left: 12px;
        }
        .stamp {
            width: 80px;
            height: 80px;
            border: 2px dashed #0c3e72;
            border-radius: 50%;
            text-align: center;
            color: #0c3e72;
            font-size: 9px;
            font-weight: bold;
            padding-top: 12px;
            margin: 0 auto;
        }
        .stamp .inner {
            font-size: 15px;
            border-top: 1px solid #0c3e72;
            border-bottom: 1px solid #0c3e72;
            margin-top: 4px;
            padding: 1px 0;
        }
        .signature-area {
            text-align: center;
            padding-top: 10px;
        }
        .signature-img {
            font-family: 'Brush Script MT', cursive, sans-serif;
            font-size: 24px;
            color: #2e7d32;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .signatory-label {
            border-top: 1px solid #555;
            padding-top: 5px;
            font-size: 10px;
            color: #333;
            width: 150px;
            margin: 0 auto;
            font-weight: bold;
        }
        
        /* Thank you banner */
        .thank-you-banner {
            background-color: #0c3e72;
            color: #fff;
            text-align: center;
            padding: 8px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 30px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

@php
if (!function_exists('numberToWords')) {
    function numberToWords($number) {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
        $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter].$plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise . ' Only';
    }
}

// Data calculations
$classFeeTotal = 0;
$vanFeeTotal = 0;
$admissionFeeTotal = $student->apply_admission_fee ? 2000 : 0;
$totalCompulsoryPaid = 0;
$totalDueSum = 0;

if (isset($feesPaid->fees->fees_class_type)) {
    foreach ($feesPaid->fees->fees_class_type as $fct) {
        if ($fct->optional == 0) {
            $typeName = strtolower($fct->fees_type->name ?? '');
            if (strpos($typeName, 'van') !== false) {
                if (strpos($typeName, '1') !== false && $student->apply_van_fee == 1) {
                    $vanFeeTotal = $fct->amount;
                } elseif (strpos($typeName, '2') !== false && $student->apply_van_fee == 2) {
                    $vanFeeTotal = $fct->amount;
                }
            } elseif (strpos($typeName, 'admission') !== false) {
                $admissionFeeTotal = $fct->amount;
            } else {
                if ($student->apply_class_fee == 1) {
                    $classFeeTotal += $fct->amount;
                }
            }
        }
    }
}

$particulars = [];
$paymentModeText = 'UPI';
$paymentDateText = date('d F Y', strtotime($feesPaid->date));

if (isset($feesPaid->compulsory_fee) && $feesPaid->compulsory_fee->isNotEmpty()) {
    $totalCompulsoryPaid = $feesPaid->compulsory_fee->sum('amount');
    $totalDueSum = $classFeeTotal + $vanFeeTotal + $admissionFeeTotal;
    
    $isPartial = false;
    foreach ($feesPaid->compulsory_fee as $compulsoryFee) {
        if ($compulsoryFee->type == "Full Payment") {
            $isPartial = true;
        }
    }
    
    if ($isPartial && $totalCompulsoryPaid < $totalDueSum) {
        foreach ($feesPaid->compulsory_fee as $compulsoryFee) {
            $paymentModeText = $compulsoryFee->mode;
            $paymentDateText = date('d F Y', strtotime($compulsoryFee->date));
            
            $particulars[] = [
                'name' => __('Compulsory Fees') . ' (' . __('Partial Payment') . ') - ' . __($compulsoryFee->mode) . ' - ' . date('d-m-Y', strtotime($compulsoryFee->date)),
                'due' => $totalDueSum,
                'paid' => $compulsoryFee->amount
            ];
            
            if ($compulsoryFee->due_charges > 0) {
                $particulars[] = [
                    'name' => 'Late Due Charges',
                    'due' => $compulsoryFee->due_charges,
                    'paid' => $compulsoryFee->due_charges
                ];
            }
        }
    } else {
        foreach ($feesPaid->compulsory_fee as $compulsoryFee) {
            $paymentModeText = $compulsoryFee->mode;
            $paymentDateText = date('d F Y', strtotime($compulsoryFee->date));
            
            if ($compulsoryFee->type == "Full Payment") {
                if ($classFeeTotal > 0) {
                    $particulars[] = [
                        'name' => 'Tuition Fee (Class Fee)',
                        'due' => $classFeeTotal,
                        'paid' => $classFeeTotal
                    ];
                }
                if ($vanFeeTotal > 0) {
                    $particulars[] = [
                        'name' => 'Transport Fee (Van Fee)',
                        'due' => $vanFeeTotal,
                        'paid' => $vanFeeTotal
                    ];
                }
                if ($admissionFeeTotal > 0) {
                    $particulars[] = [
                        'name' => 'Admission Fee',
                        'due' => $admissionFeeTotal,
                        'paid' => $admissionFeeTotal
                    ];
                }
            } else {
                $particulars[] = [
                    'name' => 'Fee Installment - ' . ($compulsoryFee->installment_fee->name ?? 'Installment'),
                    'due' => $compulsoryFee->amount,
                    'paid' => $compulsoryFee->amount
                ];
            }
            
            if ($compulsoryFee->due_charges > 0) {
                $particulars[] = [
                    'name' => 'Late Due Charges',
                    'due' => $compulsoryFee->due_charges,
                    'paid' => $compulsoryFee->due_charges
                ];
            }
        }
    }
}

if (isset($feesPaid->optional_fee) && $feesPaid->optional_fee->isNotEmpty()) {
    foreach ($feesPaid->optional_fee as $optFee) {
        if (empty($paymentModeText)) {
            $paymentModeText = $optFee->mode;
            $paymentDateText = date('d F Y', strtotime($optFee->date));
        }
        $particulars[] = [
            'name' => ($optFee->fees_class_type->fees_type->name ?? 'Optional Fee') . ' (Optional)',
            'due' => $optFee->amount,
            'paid' => $optFee->amount
        ];
    }
}

if (empty($particulars)) {
    $particulars[] = [
        'name' => 'Fee Payment',
        'due' => $feesPaid->amount,
        'paid' => $feesPaid->amount
    ];
}

$totalPaid = array_sum(array_column($particulars, 'paid'));
$receiptNo = 'BCA/' . ($feesPaid->fees->session_year->name ?? date('Y')) . '/' . str_pad($feesPaid->id, 6, '0', STR_PAD_LEFT);
$receiptMonth = date('F Y', strtotime($feesPaid->date));
$academicYear = $feesPaid->fees->session_year->name ?? date('Y');

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
    $sortedInstallments = $installments->sortBy('due_date');
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
@endphp


<div class="container">
    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                @if ($school['horizontal_logo'] ?? '')
                    <img class="logo-img" src="{{ public_path('storage/') . $school['horizontal_logo'] }}" alt="Logo">                    
                @else
                    <img class="logo-img" src="{{ public_path('assets/horizontal-logo2.svg') }}" alt="Logo">
                @endif
                <div class="school-name">{{ $school['school_name'] ?? 'BRILLIANT CHILDREN ACADEMY' }}</div>
                <div class="school-details">
                    &#128205; {{ $school['school_address'] ?? 'NO. 9-297, KASMUR, VENKATACHALAM, NELLORE - 524320' }}<br>
                    &#128222; {{ $school['school_phone'] ?? '+91 94919 20892 | +91 74164 10369' }}<br>
                    &#9993; {{ $school['school_email'] ?? 'admin@brilliantbca.com | bca@brilliantbca.com' }}<br>
                    &#127889; {{ $school['school_website'] ?? 'www.brilliantbca.com' }}
                </div>
            </td>
            <td class="header-right">
                <div class="receipt-title-box">FEE RECEIPT</div>
                <table class="receipt-info-table">
                    <tr>
                        <td class="receipt-info-label">Receipt No.</td>
                        <td class="receipt-info-val">: {{ $receiptNo }}</td>
                    </tr>
                    <tr>
                        <td class="receipt-info-label">Receipt Date</td>
                        <td class="receipt-info-val">: {{ date('d F Y', strtotime($feesPaid->date)) }}</td>
                    </tr>
                    <tr>
                        <td class="receipt-info-label">Academic Year</td>
                        <td class="receipt-info-val">: {{ $academicYear }}</td>
                    </tr>
                    <tr>
                        <td class="receipt-info-label">Month</td>
                        <td class="receipt-info-val">: {{ $receiptMonth }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Cards Section -->
    <table class="cards-table">
        <tr>
            <td class="card-wrapper-left">
                <div class="card">
                    <div class="card-header">&#128100; Student Details</div>
                    <div class="card-body">
                        <table class="info-table">
                            <tr>
                                <td class="info-label">Student Name</td>
                                <td class="info-val">: {{ $student->user->first_name . ' ' . $student->user->last_name }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Admission No.</td>
                                <td class="info-val">: {{ $student->admission_no }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Class & Section</td>
                                <td class="info-val">: {{ $student->class_section->full_name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Roll No.</td>
                                <td class="info-val">: {{ $student->roll_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">
                                    {{ ($student->guardian->occupation == 'mother') ? "Mother's Name" : (($student->guardian->occupation == 'father_mother') ? "Father's Name" : "Guardian's Name") }}
                                </td>
                                <td class="info-val">: {{ $student->guardian->first_name . ' ' . $student->guardian->last_name }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Contact No.</td>
                                <td class="info-val">: {{ $student->guardian->mobile }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td class="card-wrapper-right">
                <div class="card">
                    <div class="card-header">&#128179; Payment Details</div>
                    <div class="card-body">
                        <table class="info-table">
                            <tr>
                                <td class="info-label">Payment Mode</td>
                                <td class="info-val">: {{ strtoupper($paymentModeText) }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Payment Date</td>
                                <td class="info-val">: {{ $paymentDateText }}</td>
                            </tr>
                            @if ($upcomingDueDate)
                            <tr>
                                <td class="info-label" style="color: #c62828; font-weight: bold;">Upcoming Due Date</td>
                                <td class="info-val" style="color: #c62828;">: {{ date('d F Y', strtotime($upcomingDueDate)) }}</td>
                            </tr>
                            <tr>
                                <td class="info-label" style="color: #c62828; font-weight: bold;">Upcoming Due Amount</td>
                                <td class="info-val" style="color: #c62828;">: ₹ {{ number_format($upcomingDueAmount, 2) }}</td>
                            </tr>
                            @endif
                        </table>
                        <div class="sub-box">
                            &#9432; Payment Mode: UPI and Cash Only
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Particulars Table -->
    <table class="particulars-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">S.No.</th>
                <th style="width: 52%;" class="text-left">Particulars</th>
                <th style="width: 20%;" class="text-right">Due Amount (₹)</th>
                <th style="width: 20%;" class="text-right">Paid Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($particulars as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $item['name'] }}</td>
                    <td class="text-right">{{ number_format($item['due'], 2) }}</td>
                    <td class="text-right">{{ number_format($item['paid'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL AMOUNT PAID</td>
                <td colspan="2" class="text-right total-amount-val">₹ {{ number_format($totalPaid, 2) }}</td>
            </tr>
            @if ($totalDueSum > 0)
            <tr class="balance-row" style="background-color: #fff6f6; font-weight: bold; font-size: 11px;">
                <td colspan="2" class="text-right text-danger">BALANCE AMOUNT DUE</td>
                <td colspan="2" class="text-right text-danger" style="font-size: 14px;">₹ {{ number_format(max(0, $totalDueSum - $totalCompulsoryPaid), 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Amount in Words -->
    <div class="words-box">
        <strong>Amount in Words:</strong> <em>{{ numberToWords($totalPaid) }}</em>
    </div>

    <!-- Footer Area -->
    <table class="footer-table">
        <tr>
            <td class="footer-left">
                <div class="note-box">
                    <div class="note-title">&#128221; NOTE:</div>
                    <ul>
                        <li>This is a computer generated receipt.</li>
                        <li>No signature is required.</li>
                        <li>Please keep this receipt for future reference.</li>
                    </ul>
                </div>
            </td>
            <td class="footer-middle">
                <div class="stamp">
                    BRILLIANT
                    <div class="inner">BCA</div>
                    CHILDREN
                </div>
            </td>
            <td class="footer-right">
                <div class="signature-area">
                    <span class="signature-img">Ajith</span>
                    <div class="signatory-label">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Thank you banner -->
    <div class="thank-you-banner">
        Thank you for your payment!
    </div>
</div>

</body>
</html>
