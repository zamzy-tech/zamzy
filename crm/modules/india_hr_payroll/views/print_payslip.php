<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - <?php echo $payslip->payslip_number; ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #0284c7; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #0369a1; text-transform: uppercase; font-size: 22px; }
        .header p { margin: 2px 0; color: #64748b; font-size: 12px; }
        .payslip-title { text-align: center; font-size: 16px; font-weight: bold; background: #f0f9ff; border: 1px solid #bae6fd; padding: 8px; margin-bottom: 20px; color: #0369a1; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 6px 10px; border: 1px solid #e2e8f0; }
        .calc-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .calc-table th, .calc-table td { border: 1px solid #cbd5e1; padding: 8px 12px; }
        .calc-table th { background: #f1f5f9; text-align: left; }
        .text-right { text-align: right; }
        .no-print-btn { background: #0284c7; color: #fff; border: none; padding: 10px 20px; font-size: 14px; cursor: pointer; border-radius: 5px; margin-bottom: 20px; }
        @media print { .no-print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <?php 
    $company_name = get_option('invoice_company_name') ? get_option('invoice_company_name') : (get_option('companyname') ? get_option('companyname') : 'CREDIFIX');
    $company_address = get_option('invoice_company_address') ? get_option('invoice_company_address') : 'Software & Business Technology Solutions | Hyderabad, India';
    ?>

    <button class="no-print-btn" onclick="window.print();"><i class="fa fa-print"></i> Print / Save Payslip PDF</button>

    <div class="header">
        <h2><?php echo strtoupper($company_name); ?></h2>
        <?php if (!empty($company_address)) { ?>
            <p><?php echo $company_address; ?></p>
        <?php } ?>
    </div>

    <div class="payslip-title">PAYSLIP FOR THE MONTH OF <?php echo strtoupper(date('F Y', mktime(0,0,0, $payslip->month, 10, $payslip->year))); ?></div>

    <table class="info-table">
        <tr>
            <td style="width: 25%;"><strong>Employee Name:</strong></td>
            <td style="width: 25%;"><?php echo $staff->firstname . ' ' . $staff->lastname; ?></td>
            <td style="width: 25%;"><strong>Payslip No:</strong></td>
            <td style="width: 25%;"><?php echo $payslip->payslip_number; ?></td>
        </tr>
        <tr>
            <td><strong>Designation / Role:</strong></td>
            <td><?php echo !empty($details->designation) ? $details->designation : 'Executive'; ?></td>
            <td><strong>Department:</strong></td>
            <td><?php echo !empty($details->department) ? $details->department : 'Operations'; ?></td>
        </tr>
        <tr>
            <td><strong>Joining Date:</strong></td>
            <td><?php echo !empty($details->joining_date) ? $details->joining_date : 'N/A'; ?></td>
            <td><strong>PAN Number:</strong></td>
            <td><?php echo !empty($details->pan_number) ? $details->pan_number : 'N/A'; ?></td>
        </tr>
        <tr>
            <td><strong>Aadhaar Number:</strong></td>
            <td><?php echo !empty($details->aadhaar_number) ? $details->aadhaar_number : 'N/A'; ?></td>
            <td><strong>EPFO UAN:</strong></td>
            <td><?php echo !empty($details->uan_number) ? $details->uan_number : 'N/A'; ?></td>
        </tr>
        <tr>
            <td><strong>Bank Account:</strong></td>
            <td><?php echo !empty($details->bank_account_no) ? $details->bank_account_no : 'N/A'; ?></td>
            <td><strong>Bank IFSC:</strong></td>
            <td><?php echo !empty($details->ifsc_code) ? $details->ifsc_code : 'N/A'; ?></td>
        </tr>
        <tr style="background-color: #f8fafc; font-weight: bold;">
            <td style="color: #0369a1;"><strong>📅 Payable Days:</strong></td>
            <td style="color: #0369a1;"><?php echo $payslip->attendance_days; ?> Days</td>
            <td style="color: #dc2626;"><strong>🏖️ Leaves / Loss of Pay:</strong></td>
            <td style="color: #dc2626;"><?php echo $payslip->leave_days; ?> Days</td>
        </tr>
    </table>

    <table class="calc-table">
        <thead>
            <tr>
                <th style="width: 50%;">Earnings (Monthly)</th>
                <th class="text-right">Amount (₹)</th>
                <th style="width: 50%;">Deductions</th>
                <th class="text-right">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td class="text-right">₹<?php echo number_format($payslip->basic, 2); ?></td>
                <td>EPFO PF (Employee Contribution 12%)</td>
                <td class="text-right">₹<?php echo number_format($payslip->pf_employee, 2); ?></td>
            </tr>
            <tr>
                <td>House Rent Allowance (HRA)</td>
                <td class="text-right">₹<?php echo number_format($payslip->hra, 2); ?></td>
                <td>ESIC State Insurance (0.75%)</td>
                <td class="text-right">₹<?php echo number_format($payslip->esi_employee, 2); ?></td>
            </tr>
            <tr>
                <td>Special & Other Allowances</td>
                <td class="text-right">₹<?php echo number_format($payslip->special_allowance, 2); ?></td>
                <td>Professional Tax (PT)</td>
                <td class="text-right">₹<?php echo number_format($payslip->professional_tax, 2); ?></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>TDS / Income Tax</td>
                <td class="text-right">₹<?php echo number_format($payslip->tds, 2); ?></td>
            </tr>
            <tr style="font-weight: bold; background: #f8fafc;">
                <td>Total Gross Earnings</td>
                <td class="text-right">₹<?php echo number_format($payslip->gross_salary, 2); ?></td>
                <td>Total Deductions</td>
                <td class="text-right">₹<?php echo number_format($payslip->pf_employee + $payslip->esi_employee + $payslip->professional_tax + $payslip->tds, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <div style="background: #e0f2fe; border: 1px solid #7dd3fc; padding: 12px; font-size: 16px; margin-bottom: 30px;">
        <strong>NET SALARY PAYABLE: ₹<?php echo number_format($payslip->net_salary, 2); ?></strong>
    </div>

    <table style="width: 100%; margin-top: 40px;">
        <tr>
            <td style="width: 50%;">
                <p>Employer Signature</p><br><br>
                <p>__________________________<br><strong><?php echo $company_name; ?></strong></p>
            </td>
            <td style="width: 50%; text-align: right;">
                <p>Employee Signature</p><br><br>
                <p>__________________________<br><strong><?php echo $staff->firstname . ' ' . $staff->lastname; ?></strong></p>
            </td>
        </tr>
    </table>

</body>
</html>
