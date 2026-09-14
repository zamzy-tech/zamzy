<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 12px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-weight: bold;
            font-size: 16px;
            color: #1e3a8a;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 15px;
        }
        .row {
            display: table-row;
        }
        .col {
            display: table-cell;
            padding: 8px 10px;
            vertical-align: top;
        }
        .col-4 {
            width: 33.33%;
        }
        .col-6 {
            width: 50%;
        }
        .label {
            font-weight: bold;
            color: #555;
            display: block;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .value {
            font-size: 14px;
            word-wrap: break-word;
        }
        table.checklist {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.checklist th, table.checklist td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        table.checklist th {
            background-color: #f3f4f6;
            color: #4b5563;
            font-weight: bold;
        }
        .status-uploaded {
            color: #15803d;
            font-weight: bold;
        }
        .status-pending {
            color: #c2410c;
            font-weight: bold;
        }
        .print-btn-container {
            text-align: right;
            margin-bottom: 20px;
        }
        @media print {
            .print-btn-container {
                display: none;
            }
            body {
                margin: 0;
            }
            .page-break {
                page-break-before: always;
                break-before: page;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <button onclick="window.print();" style="padding: 8px 15px; font-weight: bold; background-color: #3b82f6; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Print Document
        </button>
    </div>

    <div class="header">
        <h1>LOAN APPLICATION SUMMARY</h1>
        <p>Generated on <?php echo date('d-M-Y H:i A'); ?></p>
    </div>

    <!-- Section: Applicant Info -->
    <div class="section">
        <div class="section-title">1. Applicant Personal Details</div>
        <div class="grid">
            <div class="row">
                <div class="col col-4">
                    <span class="label">Full Name</span>
                    <span class="value"><?php echo e($lead->name); ?></span>
                </div>
                <div class="col col-4">
                    <span class="label">Email Address</span>
                    <span class="value"><?php echo e($lead->email ?: 'N/A'); ?></span>
                </div>
                <div class="col col-4">
                    <span class="label">Phone Number</span>
                    <span class="value"><?php echo e($lead->phonenumber ?: 'N/A'); ?></span>
                </div>
            </div>
            <div class="row">
                <div class="col col-4">
                    <span class="label">Profession Type</span>
                    <span class="value"><?php echo e(ucfirst($details->profession_type ?? 'N/A')); ?></span>
                </div>
                <div class="col col-4">
                    <span class="label">Load Type</span>
                    <span class="value"><?php echo e($details->loan_type ?: 'N/A'); ?></span>
                </div>
                <div class="col col-4">
                    <span class="label">Mother's Name</span>
                    <span class="value"><?php echo e($details->mother_name ?: 'N/A'); ?></span>
                </div>
            </div>
            <div class="row">
                <div class="col col-6">
                    <span class="label">Company / Workplace</span>
                    <span class="value"><?php echo e($lead->company ?: 'N/A'); ?></span>
                </div>
                <div class="col col-6">
                    <span class="label">Residential Address</span>
                    <span class="value"><?php echo e($lead->address ?: 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Co-applicant Details -->
    <div class="section">
        <div class="section-title">2. Co-applicant Details</div>
        <div class="grid">
            <div class="row">
                <div class="col col-4">
                    <span class="label">Co-applicant Name</span>
                    <span class="value"><?php echo e($details->co_applicant_name ?: 'N/A'); ?></span>
                </div>
                <div class="col col-4">
                    <span class="label">Mother's Name</span>
                    <span class="value"><?php echo e($details->co_applicant_mother_name ?: 'N/A'); ?></span>
                </div>
                <div class="col col-4">
                    <span class="label">Mobile Number</span>
                    <span class="value"><?php echo e($details->co_applicant_mobile ?: 'N/A'); ?></span>
                </div>
            </div>
            <div class="row">
                <div class="col col-4">
                    <span class="label">Mail ID</span>
                    <span class="value"><?php echo e($details->co_applicant_email ?: 'N/A'); ?></span>
                </div>
                <div class="col col-8">
                    <span class="label">Present Residential Address Proof Details</span>
                    <span class="value"><?php echo e($details->co_applicant_address ?: 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: References -->
    <div class="section">
        <div class="section-title">3. References</div>
        <div class="grid">
            <div class="row">
                <div class="col col-6" style="border-right: 1px dashed #ddd;">
                    <span class="label">Reference 1 - Name</span>
                    <span class="value"><?php echo e($details->ref1_name ?: 'N/A'); ?></span>
                    <span class="label" style="margin-top: 5px;">Reference 1 - Contact Number</span>
                    <span class="value"><?php echo e($details->ref1_phone ?: 'N/A'); ?></span>
                </div>
                <div class="col col-6">
                    <span class="label">Reference 2 - Name</span>
                    <span class="value"><?php echo e($details->ref2_name ?: 'N/A'); ?></span>
                    <span class="label" style="margin-top: 5px;">Reference 2 - Contact Number</span>
                    <span class="value"><?php echo e($details->ref2_phone ?: 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Document Checklist Status -->
    <div class="section">
        <div class="section-title">4. Document Checklist Status</div>
        <table class="checklist">
            <thead>
                <tr>
                    <th>Requirement Description</th>
                    <th>Status</th>
                    <th>Uploaded Filename</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $documentTypes = [
                    ['key' => 'applicant_aadhar', 'label' => 'Applicant: Aadhar Card', 'category' => 'all'],
                    ['key' => 'applicant_pan', 'label' => 'Applicant: PAN Card', 'category' => 'all'],
                    ['key' => 'applicant_address', 'label' => 'Applicant: Present Residential Address Proof', 'category' => 'all'],
                    ['key' => 'bank_statement_1yr', 'label' => 'Applicant: One Year Bank Statement (From 01-08-2025 to date)', 'category' => 'all'],
                    ['key' => 'savings_statement', 'label' => 'Applicant: Savings Account Statement', 'category' => 'all'],
                    ['key' => 'photos_2', 'label' => 'Applicant: Passport Size Photos (2)', 'category' => 'all'],
                    ['key' => 'tax_receipt', 'label' => 'Applicant: Latest Tax Paid Receipt', 'category' => 'all'],
                    ['key' => 'loan_repayment', 'label' => 'Applicant: Present Loan Sanction Letter & Repayment Track', 'category' => 'all'],
                    ['key' => 'property_plan', 'label' => 'Applicant: Property Details (Building Plan & Permission)', 'category' => 'all'],
                    ['key' => 'link_docs_13yrs', 'label' => 'Applicant: 13 Years Link Documents (Sales Deed)', 'category' => 'all'],
                    ['key' => 'itr_3yrs', 'label' => 'Applicant: ITR (3 Years)', 'category' => 'all'],
                    ['key' => 'business_proof', 'label' => 'Business: Proof of Business (3 Years Vintage, GST/Udhyam)', 'category' => 'business'],
                    ['key' => 'gst_returns', 'label' => 'Business: GST Returns (If applicable)', 'category' => 'business'],
                    ['key' => 'co_aadhar', 'label' => 'Co-Applicant: Aadhar Card', 'category' => 'all'],
                    ['key' => 'co_pan', 'label' => 'Co-Applicant: PAN Card', 'category' => 'all'],
                    ['key' => 'co_income', 'label' => 'Co-Applicant: Income Proof', 'category' => 'all'],
                    ['key' => 'co_photos', 'label' => 'Co-Applicant: Passport Size Photos (2)', 'category' => 'all'],
                    ['key' => 'co_savings_1yr', 'label' => 'Co-Applicant: Savings Bank Statement (1 Year)', 'category' => 'all'],
                    ['key' => 'co_itr_3yrs', 'label' => 'Co-Applicant: ITR (3 Years)', 'category' => 'all'],
                    ['key' => 'co_address', 'label' => 'Co-Applicant: Present Address Proof', 'category' => 'all']
                ];

                $profession = $details->profession_type ?? 'salary';

                foreach ($documentTypes as $doc) {
                    if ($doc['category'] === 'business' && $profession !== 'business') {
                        continue;
                    }

                    $uploaded = null;
                    foreach ($documents as $d) {
                        if ($d['document_type'] === $doc['key']) {
                            $uploaded = $d;
                            break;
                        }
                    }

                    echo '<tr>';
                    echo '<td>' . e($doc['label']) . '</td>';
                    if ($uploaded) {
                        echo '<td class="status-uploaded">Uploaded</td>';
                        echo '<td>' . e($uploaded['file_name']) . '</td>';
                    } else {
                        echo '<td class="status-pending">Pending</td>';
                        echo '<td>N/A</td>';
                    }
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Section: Appended Documents -->
    <?php if (!empty($documents)) { ?>
        <?php foreach ($documents as $doc) { ?>
            <div class="page-break" style="text-align: center; padding-top: 20px;">
                <h2 style="color: #1e3a8a; font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; text-align: left;">
                    <?php
                    // Find document label
                    $label = 'Document';
                    foreach ($documentTypes as $dt) {
                        if ($dt['key'] === $doc['document_type']) {
                            $label = $dt['label'];
                            break;
                        }
                    }
                    echo e($label);
                    ?>
                </h2>
                <div style="margin-top: 20px; text-align: center;">
                    <?php 
                    $ext = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                    $file_url = base_url($doc['file_path']);
                    
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    ?>
                        <img src="<?php echo $file_url; ?>" style="max-width: 100%; max-height: 850px; object-fit: contain; border: 1px solid #ddd; padding: 5px; box-shadow: 0 0 5px rgba(0,0,0,0.1);" />
                    <?php } elseif ($ext === 'pdf') { ?>
                        <div style="width: 100%; height: 950px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                            <embed src="<?php echo $file_url; ?>" type="application/pdf" style="width: 100%; height: 100%; border: none;" />
                        </div>
                    <?php } else { ?>
                        <div style="padding: 40px; border: 1px dashed #ccc; background-color: #f9f9f9; border-radius: 4px; display: inline-block;">
                            <p style="font-size: 16px; margin: 0 0 10px 0;"><strong><?php echo e($doc['file_name']); ?></strong></p>
                            <p style="color: #666; margin: 0 0 15px 0;">Non-image/PDF document file type.</p>
                            <a href="<?php echo $file_url; ?>" target="_blank" style="display: inline-block; padding: 8px 15px; background-color: #3b82f6; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">View / Download File</a>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <!-- Blank page printed after each document -->
            <div class="page-break" style="height: 1px; visibility: hidden;">&nbsp;</div>
        <?php } ?>
    <?php } ?>

    <!-- Section: Appended Status Change Proofs -->
    <?php if (!empty($proofs)) { ?>
        <?php foreach ($proofs as $proof) { ?>
            <div class="page-break" style="text-align: center; padding-top: 20px;">
                <h2 style="color: #1e3a8a; font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; text-align: left;">
                    Status Change Proof: Changed to <?php echo e($proof['new_status']); ?> (by <?php echo e($proof['changed_by']); ?> at <?php echo e($proof['changed_at']); ?>)
                </h2>
                <div style="margin-top: 20px; text-align: center;">
                    <?php 
                    $ext = strtolower(pathinfo($proof['proof_path'], PATHINFO_EXTENSION));
                    $file_url = base_url($proof['proof_path']);
                    
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    ?>
                        <img src="<?php echo $file_url; ?>" style="max-width: 100%; max-height: 850px; object-fit: contain; border: 1px solid #ddd; padding: 5px; box-shadow: 0 0 5px rgba(0,0,0,0.1);" />
                    <?php } elseif ($ext === 'pdf') { ?>
                        <div style="width: 100%; height: 950px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                            <embed src="<?php echo $file_url; ?>" type="application/pdf" style="width: 100%; height: 100%; border: none;" />
                        </div>
                    <?php } ?>
                </div>
            </div>
            <!-- Blank page printed after each status change proof -->
            <div class="page-break" style="height: 1px; visibility: hidden;">&nbsp;</div>
        <?php } ?>
    <?php } ?>

    <script>
        window.onload = function() {
            // Automatically open print dialog
            window.print();
        };
    </script>
</body>
</html>
