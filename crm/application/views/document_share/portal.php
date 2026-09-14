<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Document Share Portal</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery for AJAX uploads -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #0b0f19;
            font-family: 'Inter', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col">
    <!-- Navbar -->
    <nav class="border-b border-white/5 bg-slate-950/40 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-shield-halved text-blue-500 text-2xl"></i>
                    <span class="font-extrabold text-lg text-white tracking-wider">DOCSHARE</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="hidden md:inline-block text-sm text-neutral-400">Signed in as <b class="text-white"><?php echo e($lead->name); ?></b></span>
                    <a href="<?php echo site_url('document_share/logout/' . $hash); ?>" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 px-4 py-2 rounded-xl text-sm font-semibold transition-all">
                        Log Out
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="flex-grow max-w-6xl w-full mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-white">Upload Your Documents</h1>
            <p class="text-neutral-400 mt-2 text-sm">Please upload the files requested below to complete your loan application. You can view or delete files after uploading.</p>
        </div>

        <div id="alert_container" class="hidden mb-6 p-4 rounded-xl text-sm border"></div>

        <!-- Checklist Grid -->
        <div class="glass-card rounded-2xl p-6 shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 text-neutral-400 text-sm font-semibold">
                            <th class="py-4 px-4 w-1/2">Required Document</th>
                            <th class="py-4 px-4 w-1/6 text-center">Status</th>
                            <th class="py-4 px-4 w-1/3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
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

                            // Check if document exists
                            $uploaded = null;
                            foreach ($documents as $d) {
                                if ($d['document_type'] === $doc['key']) {
                                    $uploaded = $d;
                                    break;
                                }
                            }
                        ?>
                            <tr class="hover:bg-white/[0.01] transition-colors duration-150" data-key="<?php echo $doc['key']; ?>">
                                <td class="py-4 px-4 align-middle">
                                    <span class="font-semibold text-sm text-white"><?php echo e($doc['label']); ?></span>
                                </td>
                                <td class="py-4 px-4 align-middle text-center" id="status-col-<?php echo $doc['key']; ?>">
                                    <?php if ($uploaded) { ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-400 border border-green-500/20">
                                            <i class="fa-solid fa-check-circle mr-1"></i> Uploaded
                                        </span>
                                    <?php } else { ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                            <i class="fa-solid fa-clock mr-1"></i> Pending
                                        </span>
                                    <?php } ?>
                                </td>
                                <td class="py-4 px-4 align-middle" id="action-col-<?php echo $doc['key']; ?>">
                                    <?php if ($uploaded) { ?>
                                        <div class="flex items-center space-x-3">
                                            <a href="<?php echo site_url($uploaded['file_path']); ?>" target="_blank" class="text-xs bg-white/5 hover:bg-white/10 text-white font-semibold py-1.5 px-3 rounded-lg border border-white/10 transition-all">
                                                <i class="fa-solid fa-eye mr-1"></i> View File
                                            </a>
                                            <button type="button" class="delete-btn text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 font-semibold py-1.5 px-3 rounded-lg border border-red-500/20 transition-all" data-id="<?php echo $uploaded['id']; ?>" data-key="<?php echo $doc['key']; ?>">
                                                <i class="fa-solid fa-trash mr-1"></i> Delete
                                            </button>
                                        </div>
                                    <?php } else { ?>
                                        <form class="upload-form flex items-center space-x-2">
                                            <input type="hidden" name="document_type" value="<?php echo $doc['key']; ?>">
                                            <input type="file" name="file" required class="block w-full text-xs text-neutral-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer transition-all">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-2 px-4 rounded-lg transition-all flex items-center">
                                                <i class="fa-solid fa-upload mr-1"></i> Upload
                                            </button>
                                        </form>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-6 bg-slate-950/40 text-center">
        <p class="text-xs text-neutral-500">&copy; <?php echo date('Y'); ?> Secure Document Sharing Portal. All rights reserved.</p>
    </footer>

    <!-- Custom AJAX handling script -->
    <script>
        const hash = '<?php echo $hash; ?>';
        const csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        const csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

        function showAlert(type, message) {
            const container = $('#alert_container');
            container.removeClass('hidden bg-red-500/10 border-red-500/20 text-red-400 bg-green-500/10 border-green-500/20 text-green-400');
            if (type === 'success') {
                container.addClass('bg-green-500/10 border-green-500/20 text-green-400');
            } else {
                container.addClass('bg-red-500/10 border-red-500/20 text-red-400');
            }
            container.html(message).fadeIn().delay(5000).fadeOut();
        }

        // Handle file uploads
        $(document).on('submit', '.upload-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const key = form.find('input[name="document_type"]').val();
            const formData = new FormData(this);
            formData.append(csrfName, csrfHash);

            form.find('button').prop('disabled', true).text('Uploading...');

            $.ajax({
                url: '<?php echo site_url("document_share/upload/"); ?>' + hash,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        showAlert('success', 'Document uploaded successfully!');
                        
                        // Update status column
                        $(`#status-col-${key}`).html(`
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-400 border border-green-500/20">
                                <i class="fa-solid fa-check-circle mr-1"></i> Uploaded
                            </span>
                        `);

                        // Update action column
                        $(`#action-col-${key}`).html(`
                            <div class="flex items-center space-x-3">
                                <a href="<?php echo site_url(); ?>${res.document.file_path}" target="_blank" class="text-xs bg-white/5 hover:bg-white/10 text-white font-semibold py-1.5 px-3 rounded-lg border border-white/10 transition-all">
                                    <i class="fa-solid fa-eye mr-1"></i> View File
                                </a>
                                <button type="button" class="delete-btn text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 font-semibold py-1.5 px-3 rounded-lg border border-red-500/20 transition-all" data-id="${res.document.id || ''}" data-key="${key}">
                                    <i class="fa-solid fa-trash mr-1"></i> Delete
                                </button>
                            </div>
                        `);
                    } else {
                        showAlert('error', res.message || 'File upload failed.');
                        form.find('button').prop('disabled', false).html('<i class="fa-solid fa-upload mr-1"></i> Upload');
                    }
                },
                error: function() {
                    showAlert('error', 'An error occurred during upload.');
                    form.find('button').prop('disabled', false).html('<i class="fa-solid fa-upload mr-1"></i> Upload');
                }
            });
        });

        // Handle file deletions
        $(document).on('click', '.delete-btn', function() {
            if (!confirm('Are you sure you want to delete this document?')) {
                return;
            }

            const btn = $(this);
            const docId = btn.data('id');
            const key = btn.data('key');

            const postData = {};
            postData[csrfName] = csrfHash;

            $.post('<?php echo site_url("document_share/delete/"); ?>' + hash + '/' + docId, postData, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    showAlert('success', 'Document deleted successfully.');

                    // Update status column
                    $(`#status-col-${key}`).html(`
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                            <i class="fa-solid fa-clock mr-1"></i> Pending
                        </span>
                    `);

                    // Update action column
                    $(`#action-col-${key}`).html(`
                        <form class="upload-form flex items-center space-x-2">
                            <input type="hidden" name="document_type" value="${key}">
                            <input type="file" name="file" required class="block w-full text-xs text-neutral-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer transition-all">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-2 px-4 rounded-lg transition-all flex items-center">
                                <i class="fa-solid fa-upload mr-1"></i> Upload
                            </button>
                        </form>
                    `);
                } else {
                    showAlert('error', res.message || 'File deletion failed.');
                    btn.prop('disabled', false).html('<i class="fa-solid fa-trash mr-1"></i> Delete');
                }
            });
        });
    </script>
</body>
</html>
