<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $doc_type; ?> - <?php echo $staff->firstname . ' ' . $staff->lastname; ?></title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; line-height: 1.6; margin: 0; padding: 20px; background-color: #f8fafc; }
        .page { background: #ffffff; padding: 40px; margin: 0 auto 30px auto; max-width: 800px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-radius: 8px; min-height: 980px; box-sizing: border-box; position: relative; }
        .letterhead { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
        .letterhead h1 { margin: 0; color: #1e3a8a; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .letterhead p { margin: 3px 0; color: #64748b; font-size: 11px; }
        .doc-title { text-align: center; margin: 15px 0 20px 0; text-transform: uppercase; letter-spacing: 1.5px; color: #0f172a; text-decoration: underline; font-size: 18px; font-weight: bold; }
        .meta-bar { width: 100%; margin-bottom: 20px; font-size: 13px; }
        .emp-info { margin-bottom: 20px; line-height: 1.6; }
        .emp-info p { margin: 3px 0; }
        .ctc-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; }
        .ctc-table th, .ctc-table td { border: 1px solid #cbd5e1; padding: 9px 12px; text-align: left; }
        .ctc-table th { background-color: #f1f5f9; font-weight: bold; color: #334155; }
        .terms-list { background: #fafafa; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; margin: 15px 0 30px 0; font-size: 12px; line-height: 1.8; white-space: pre-wrap; color: #334155; }
        .signature-container { margin-top: 80px; width: 100%; }
        .sig-box-left { float: left; width: 45%; }
        .sig-box-right { float: right; width: 45%; text-align: right; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .no-print-btn { background: #2563eb; color: #fff; border: none; padding: 10px 24px; font-size: 14px; font-weight: bold; cursor: pointer; border-radius: 6px; margin: 0 auto 20px auto; display: block; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .no-print-btn:hover { background: #1d4ed8; }
        
        @media print { 
            body { background: #ffffff; padding: 0; } 
            .page { box-shadow: none; padding: 30px; margin: 0; min-height: auto; border-radius: 0; } 
            .no-print-btn { display: none; } 
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>

    <?php 
    $company_name = get_option('invoice_company_name') ? get_option('invoice_company_name') : (get_option('companyname') ? get_option('companyname') : 'CREDIFIX');
    $company_address = get_option('invoice_company_address') ? get_option('invoice_company_address') : '304,3rd floor Sri Sai Apartments,near GbR Hospital, Chaitanyapuri, Hyderabad, Telangana 500060';
    $company_phone = get_option('invoice_company_phonenumber') ? get_option('invoice_company_phonenumber') : '9566777266';
    $company_email = get_option('smtp_username') ? get_option('smtp_username') : 'sam@tehub.in';
    ?>

    <button class="no-print-btn" onclick="window.print();"><i class="fa fa-print"></i> Print / Save 2-Page PDF</button>

    <!-- PAGE 1: APPOINTMENT DETAILS & CTC BREAKDOWN -->
    <div class="page">
        <div class="letterhead">
            <h1><?php echo strtoupper($company_name); ?></h1>
            <p><?php echo $company_address; ?></p>
            <p>Phone: <?php echo $company_phone; ?> | Email: <?php echo $company_email; ?></p>
        </div>

        <div class="meta-bar clearfix">
            <div style="float: right;">Date: <strong><?php echo date('d F Y'); ?></strong></div>
            <div>Ref No: <strong>HR/2026/<?php echo str_pad($staff->staffid, 4, '0', STR_PAD_LEFT); ?></strong></div>
        </div>

        <h2 class="doc-title"><?php echo $doc_type; ?></h2>

        <p style="margin-bottom: 5px;">To,</p>
        <div class="emp-info">
            <p><strong><?php echo strtoupper($staff->firstname . ' ' . $staff->lastname); ?></strong></p>
            <p>Email: <?php echo $staff->email; ?></p>
            <p>Mobile: <?php echo !empty($details->mobile_number) ? $details->mobile_number : 'N/A'; ?></p>
            <p>PAN: <?php echo !empty($details->pan_number) ? $details->pan_number : 'N/A'; ?> &nbsp;|&nbsp; Aadhaar: <?php echo !empty($details->aadhaar_number) ? $details->aadhaar_number : 'N/A'; ?></p>
        </div>

        <?php if ($doc_type == 'Offer Letter' || $doc_type == 'Joining Letter') { ?>

            <p>Dear <strong><?php echo strtoupper($staff->firstname); ?></strong>,</p>
            <p>We are pleased to issue this <strong><?php echo $doc_type; ?></strong> for the position of <strong><?php echo !empty($details->designation) ? strtoupper($details->designation) : 'TELE CALLING'; ?></strong> in the <strong><?php echo !empty($details->department) ? strtoupper($details->department) : 'LOANS AND WEBSITE'; ?></strong> department at <strong><?php echo strtoupper($company_name); ?></strong>.</p>
            
            <p><strong>Joining Date:</strong> <?php echo !empty($details->joining_date) ? date('d F Y', strtotime($details->joining_date)) : date('d F Y'); ?></p>
            <p><strong>Probation Period:</strong> <?php echo !empty($details->probation_period) ? $details->probation_period : '3 Months'; ?> &nbsp;|&nbsp; <strong>Notice Period:</strong> <?php echo !empty($details->notice_period) ? $details->notice_period : '15 Days'; ?></p>

            <h3 style="margin-top: 25px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">Compensation & CTC Breakdown</h3>
            <table class="ctc-table">
                <thead>
                    <tr>
                        <th>Salary Component</th>
                        <th>Monthly Amount (₹/month)</th>
                        <th>Annual Amount (₹/year)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Salary</td>
                        <td>₹<?php echo number_format($salary->basic_salary, 2); ?></td>
                        <td>₹<?php echo number_format($salary->basic_salary * 12, 2); ?></td>
                    </tr>
                    <tr>
                        <td>House Rent Allowance (HRA)</td>
                        <td>₹<?php echo number_format($salary->hra, 2); ?></td>
                        <td>₹<?php echo number_format($salary->hra * 12, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Special & Other Allowances</td>
                        <td>₹<?php echo number_format($salary->special_allowance + $salary->other_allowances, 2); ?></td>
                        <td>₹<?php echo number_format(($salary->special_allowance + $salary->other_allowances) * 12, 2); ?></td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f1f5f9;">
                        <td>TOTAL MONTHLY GROSS SALARY</td>
                        <td>₹<?php echo number_format($salary->gross_monthly, 2); ?> / month</td>
                        <td>₹<?php echo number_format($salary->gross_monthly * 12, 2); ?> / year</td>
                    </tr>
                    <?php 
                    $annual_total = !empty($details->annual_ctc) && $details->annual_ctc > 0 ? $details->annual_ctc : ($salary->gross_monthly * 12);
                    ?>
                    <tr style="font-weight: bold; background-color: #dbeafe; color: #1e40af;">
                        <td>TOTAL ANNUAL COST TO COMPANY (CTC)</td>
                        <td>₹<?php echo number_format($annual_total / 12, 2); ?> / month</td>
                        <td>₹<?php echo number_format($annual_total, 2); ?> per annum</td>
                    </tr>
                </tbody>
            </table>
            
            <p style="text-align: right; color: #64748b; font-size: 11px; margin-top: 30px;"><i>Continued on Page 2 (Terms, Conditions & Signatures) ➔</i></p>

        <?php } else { ?>

            <p>To Whomsoever It May Concern,</p>
            <p>This is to certify that <strong><?php echo strtoupper($staff->firstname . ' ' . $staff->lastname); ?></strong> was employed with <strong><?php echo strtoupper($company_name); ?></strong> as <strong><?php echo !empty($details->designation) ? strtoupper($details->designation) : 'Specialist'; ?></strong> in the <strong><?php echo !empty($details->department) ? strtoupper($details->department) : 'Technology'; ?></strong> department.</p>
            <p>During their tenure, we found them to be sincere, dedicated, and hardworking. All full & final (F&F) dues have been settled. We wish them all the best in their future endeavors.</p>

        <?php } ?>
    </div>

    <!-- PAGE BREAK -->
    <div class="page-break"></div>

    <!-- PAGE 2: TERMS, CONDITIONS & SIGNATURES -->
    <?php if ($doc_type == 'Offer Letter' || $doc_type == 'Joining Letter') { ?>
    <div class="page">
        <div class="letterhead">
            <h1><?php echo strtoupper($company_name); ?></h1>
            <p>Ref No: <strong>HR/2026/<?php echo str_pad($staff->staffid, 4, '0', STR_PAD_LEFT); ?></strong> | Employee: <strong><?php echo strtoupper($staff->firstname . ' ' . $staff->lastname); ?></strong></p>
        </div>

        <h3 style="color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; margin-top: 10px;">Employment Terms & Policies</h3>
        <div class="terms-list"><?php echo $details->employment_terms; ?></div>

        <p style="margin-top: 20px;">Please sign below to confirm your acceptance of this offer and agreement to all terms, policies, and regulations specified above.</p>

        <div class="signature-container clearfix">
            <div class="sig-box-left">
                <p>For <strong><?php echo strtoupper($company_name); ?></strong></p>
                <br><br><br>
                <p>___________________________________<br><strong>Authorized Signatory / HR Manager</strong></p>
            </div>
            <div class="sig-box-right">
                <p>Accepted & Agreed</p>
                <br><br><br>
                <p>___________________________________<br><strong>Employee Signature</strong></p>
            </div>
        </div>
    </div>
    <?php } ?>

</body>
</html>
