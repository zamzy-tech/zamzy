<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fee Transaction Statement || {{ config('app.name') }}</title>
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
            margin-bottom: 20px;
            border-bottom: 2px solid #0c3e72;
            padding-bottom: 10px;
        }
        .header-left {
            width: 55%;
            vertical-align: top;
        }
        .header-right {
            width: 45%;
            vertical-align: top;
            text-align: right;
        }
        .logo-img {
            height: 65px;
            width: 65px;
            float: left;
            margin-right: 15px;
        }
        .school-name {
            font-size: 18px;
            color: #0c3e72;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .school-tagline {
            font-size: 9px;
            color: #e65100;
            font-style: italic;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .school-details {
            font-size: 9px;
            color: #555;
            line-height: 1.4;
            text-align: right;
        }
        .statement-title-box {
            background-color: #0c3e72;
            color: #fff;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            border-radius: 4px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        /* Cards section (side-by-side using table for Dompdf safety) */
        .cards-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .card-wrapper-left {
            width: 42%;
            vertical-align: top;
        }
        .card-wrapper-middle {
            width: 16%;
            vertical-align: middle;
            text-align: center;
        }
        .card-wrapper-right {
            width: 42%;
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
            padding: 5px 10px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .card-body {
            padding: 8px 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 0;
            font-size: 9px;
            vertical-align: top;
        }
        .info-label {
            color: #555;
            width: 95px;
        }
        .info-val {
            font-weight: bold;
            color: #111;
        }
        
        /* Badges */
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #2e7d32;
        }
        .badge-unpaid {
            background-color: #c62828;
        }
        .badge-partial {
            background-color: #ef6c00;
        }

        /* Transaction History Table */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .history-table th {
            background-color: #0c3e72;
            color: #fff;
            padding: 8px;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #0c3e72;
            text-transform: uppercase;
        }
        .history-table td {
            padding: 8px;
            border: 1px solid #ced4da;
            font-size: 9px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .total-row {
            background-color: #eef2f7;
            font-weight: bold;
            font-size: 10px;
        }
        .total-amount-val {
            color: #2e7d32;
            font-size: 12px;
            font-weight: bold;
        }
        
        .mode-badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            background-color: #f1f3f4;
            border: 1px solid #dadce0;
            color: #3c4043;
            display: inline-block;
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
            font-size: 11px;
            font-weight: bold;
            margin-top: 25px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

@php
$academicYear = $student->session_year->name ?? date('Y');
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
                <div class="school-name">{{ $school['school_name'] ?? 'BRILLIANT CHILDRENS ACADEMY' }}</div>
                <div class="school-tagline">Quality Education with Moral Values</div>
            </td>
            <td class="header-right">
                <div class="statement-title-box">FEE TRANSACTION STATEMENT</div>
                <div class="school-details">
                    &#128205; {{ $school['school_address'] ?? 'NO. 9-297, KASMUR, VENKATACHALAM, NELLORE - 524320' }}<br>
                    &#128222; {{ $school['school_phone'] ?? '+91 94919 20892 | +91 74164 10369' }}<br>
                    &#9993; {{ $school['school_email'] ?? 'admin@brilliantbca.com | bca@brilliantbca.com' }}<br>
                    &#127889; {{ $school['school_website'] ?? 'www.brilliantbca.com' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Cards Section -->
    <table class="cards-table">
        <tr>
            <!-- Student Details -->
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
            
            <!-- Calendar / Academic Year -->
            <td class="card-wrapper-middle">
                <div style="text-align: center;">
                    <div style="font-size: 24px; line-height: 1; margin-bottom: 5px;">📅</div>
                    <div style="font-size: 8px; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 0.5px;">Academic Year</div>
                    <div style="font-size: 11px; font-weight: bold; color: #0c3e72; margin-top: 2px;">{{ $academicYear }}</div>
                </div>
            </td>
            
            <!-- Statement Summary -->
            <td class="card-wrapper-right">
                <div class="card">
                    <div class="card-header">&#128221; Statement Summary</div>
                    <div class="card-body">
                        <table class="info-table">
                            <tr>
                                <td class="info-label">Total Fees (Academic Year)</td>
                                <td class="info-val">: ₹ {{ number_format($totalFees, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Total Paid Amount</td>
                                <td class="info-val" style="color: #2e7d32;">: ₹ {{ number_format($totalPaid, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Balance Due</td>
                                <td class="info-val" style="color: #c62828;">: ₹ {{ number_format($balanceDue, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Payment Status</td>
                                <td class="info-val">: 
                                    @if ($paymentStatus === 'PAID')
                                        <span class="badge badge-paid">PAID</span>
                                    @elseif ($paymentStatus === 'PARTIALLY PAID')
                                        <span class="badge badge-partial">PARTIALLY PAID</span>
                                    @else
                                        <span class="badge badge-unpaid">UNPAID</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Transaction History Table -->
    <div style="font-size: 10px; font-weight: bold; color: #0c3e72; margin-bottom: 6px; text-transform: uppercase;">
        &#128187; Transaction History
    </div>
    <table class="history-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">S.No.</th>
                <th style="width: 15%;" class="text-center">Date</th>
                <th style="width: 22%;" class="text-left">Receipt No.</th>
                <th style="width: 15%;" class="text-center">Payment Mode</th>
                <th style="width: 25%;" class="text-left">Transaction ID / Details</th>
                <th style="width: 15%;" class="text-right">Paid Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ date('d M Y', strtotime($item['date'])) }}</td>
                    <td class="text-left">{{ $item['receipt_no'] }}</td>
                    <td class="text-center">
                        <span class="mode-badge">{{ $item['mode'] }}</span>
                    </td>
                    <td class="text-left" style="font-size: 8px;">{{ $item['details'] }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($item['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #888;">No transactions found.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5" class="text-right" style="text-transform: uppercase;">TOTAL PAID AMOUNT</td>
                <td class="text-right total-amount-val">₹ {{ number_format($totalPaid, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Area -->
    <table class="footer-table">
        <tr>
            <td class="footer-left">
                <div class="note-box">
                    <div class="note-title">&#128221; NOTE:</div>
                    <ul>
                        <li>This is a computer generated statement.</li>
                        <li>No signature is required.</li>
                        <li>Please keep this statement for future reference.</li>
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
                    <span class="signature-img">Anupama</span>
                    <div class="signatory-label">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Thank you banner -->
    <div class="thank-you-banner">
        Thank you for being a part of our academy!
    </div>
</div>

</body>
</html>
