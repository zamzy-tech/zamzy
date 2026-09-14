<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.table-checklist tr {
    cursor: pointer;
    transition: background-color 0.2s;
}
.table-checklist tr:hover {
    background-color: #f1f5f9;
}
.table-checklist tr.selected-row {
    background-color: #e2e8f0 !important;
    border-left: 4px solid #3b82f6;
}
.table-checklist tr.drag-over {
    background-color: #dbeafe !important;
    border: 2px dashed #2563eb !important;
}
</style>
<div class="modal fade" id="lead_loan_details_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Lead Details & Document Checklist - <span id="modal_lead_name" class="bold text-primary"></span></h4>
            </div>
            <div class="modal-body">
                <div class="horizontal-scrollable-tabs">
                    <div class="scroller scroller-left"><i class="fa fa-angle-left"></i></div>
                    <div class="scroller scroller-right"><i class="fa fa-angle-right"></i></div>
                    <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab_loan_info" aria-controls="tab_loan_info" role="tab" data-toggle="tab">Applicant Details</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_co_applicant" aria-controls="tab_co_applicant" role="tab" data-toggle="tab">Co-applicant & References</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_checklist" aria-controls="tab_checklist" role="tab" data-toggle="tab">Documents Checklist</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_status_history" aria-controls="tab_status_history" role="tab" data-toggle="tab">Status History</a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content mtop15">
                    <!-- Tab: Applicant & Loan Info -->
                    <div role="tabpanel" class="tab-pane active" id="tab_loan_info">
                        <form id="loan_details_form">
                            <input type="hidden" name="lead_id" id="loan_lead_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="profession_type" class="control-label">Profession Type</label>
                                        <select name="profession_type" id="profession_type" class="form-control selectpicker" data-none-selected-text="Select Profession Type">
                                            <option value="salary">Salary</option>
                                            <option value="business">Business</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('loan_type', 'Load Type', ''); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo render_input('mother_name', 'Mother\'s Name', ''); ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Details</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Co-applicant & References -->
                    <div role="tabpanel" class="tab-pane" id="tab_co_applicant">
                        <form id="co_applicant_details_form">
                            <h4 class="bold text-info tw-mb-4">Co-applicant Details</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo render_input('co_applicant_name', 'Co-applicant Name', ''); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('co_applicant_mother_name', 'Co-applicant Mother Name', ''); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo render_input('co_applicant_mobile', 'Co-applicant Mobile Number', ''); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('co_applicant_email', 'Co-applicant Mail ID', '', 'email'); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php echo render_textarea('co_applicant_address', 'Co-applicant Present Residential Address Proof', ''); ?>
                                </div>
                            </div>
                            <hr />
                            <h4 class="bold text-info tw-mb-4">Two References</h4>
                            <div class="row">
                                <div class="col-md-6" style="border-right: 1px solid #f0f0f0;">
                                    <h5 class="bold">Reference 1</h5>
                                    <?php echo render_input('ref1_name', 'Person Name', ''); ?>
                                    <?php echo render_input('ref1_phone', 'Contact Number', ''); ?>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="bold">Reference 2</h5>
                                    <?php echo render_input('ref2_name', 'Person Name', ''); ?>
                                    <?php echo render_input('ref2_phone', 'Contact Number', ''); ?>
                                </div>
                            </div>
                            <div class="text-right mtop15">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Details</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Documents Checklist -->
                    <div role="tabpanel" class="tab-pane" id="tab_checklist">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; gap: 15px;">
                            <div class="alert alert-info" style="margin-bottom: 0; flex-grow: 1;">
                                Upload documents below. <strong>Tip:</strong> Click a row to select it, then paste (Ctrl+V) or drag & drop files directly onto the row!
                            </div>
                            <form id="upload_zip_form" style="display: inline-block; shrink: 0;">
                                <input type="file" id="zip_file_input" name="file" accept=".zip" style="display: none;">
                                <button type="button" class="btn btn-warning" onclick="$('#zip_file_input').click();">
                                    <i class="fa fa-file-zip-o"></i> Upload ZIP (Auto-Map)
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered checklist-table table-checklist">
                                <thead>
                                    <tr>
                                        <th width="40%">Document Requirement</th>
                                        <th width="20%">Status</th>
                                        <th width="40%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="checklist_tbody">
                                    <!-- Dynamic rows inserted by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Status History -->
                    <div role="tabpanel" class="tab-pane" id="tab_status_history">
                        <div class="alert alert-info">
                            Below is the historical log of all status changes for this lead, including date, time, and staff attribution.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Old Status</th>
                                        <th>New Status</th>
                                        <th>Changed By</th>
                                        <th>Date & Time</th>
                                        <th>Proof File</th>
                                    </tr>
                                </thead>
                                <tbody id="status_history_tbody">
                                    <!-- Populated dynamically via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteUrl = '<?php echo site_url(); ?>';
    // Shared state
    let activeLeadId = null;
    let currentDetails = {};
    let currentDocuments = [];

    // Document checklist templates
    const documentTypes = [
        { key: 'applicant_aadhar', label: 'Applicant: Aadhar Card', category: 'all' },
        { key: 'applicant_pan', label: 'Applicant: PAN Card', category: 'all' },
        { key: 'applicant_address', label: 'Applicant: Present Residential Address Proof', category: 'all' },
        { key: 'bank_statement_1yr', label: 'Applicant: One Year Bank Statement (From 01-08-2025 to date)', category: 'all' },
        { key: 'savings_statement', label: 'Applicant: Savings Account Statement', category: 'all' },
        { key: 'photos_2', label: 'Applicant: Passport Size Photos (2)', category: 'all' },
        { key: 'tax_receipt', label: 'Applicant: Latest Tax Paid Receipt', category: 'all' },
        { key: 'loan_repayment', label: 'Applicant: Present Loan Sanction Letter & Repayment Track', category: 'all' },
        { key: 'property_plan', label: 'Applicant: Property Details (Building Plan & Permission)', category: 'all' },
        { key: 'link_docs_13yrs', label: 'Applicant: 13 Years Link Documents (Sales Deed)', category: 'all' },
        { key: 'itr_3yrs', label: 'Applicant: ITR (3 Years)', category: 'all' },
        { key: 'business_proof', label: 'Business: Proof of Business (3 Years Vintage, GST/Udhyam)', category: 'business' },
        { key: 'gst_returns', label: 'Business: GST Returns (If applicable)', category: 'business' },
        { key: 'co_aadhar', label: 'Co-Applicant: Aadhar Card', category: 'all' },
        { key: 'co_pan', label: 'Co-Applicant: PAN Card', category: 'all' },
        { key: 'co_income', label: 'Co-Applicant: Income Proof', category: 'all' },
        { key: 'co_photos', label: 'Co-Applicant: Passport Size Photos (2)', category: 'all' },
        { key: 'co_savings_1yr', label: 'Co-Applicant: Savings Bank Statement (1 Year)', category: 'all' },
        { key: 'co_itr_3yrs', label: 'Co-Applicant: ITR (3 Years)', category: 'all' },
        { key: 'co_address', label: 'Co-Applicant: Present Address Proof', category: 'all' }
    ];

    // Initialize Lead Details Modal
    window.initLeadLoanDetails = function(leadId) {
        activeLeadId = leadId;
        $('#loan_lead_id').val(leadId);
        
        // Reset forms
        $('#loan_details_form')[0].reset();
        $('#co_applicant_details_form')[0].reset();
        $('#profession_type').selectpicker('val', 'salary');

        // Fetch data
        $.getJSON(admin_url + 'leads/get_loan_details/' + leadId, function(response) {
            $('#modal_lead_name').text(response.lead.name);
            currentDetails = response.details || {};
            currentDocuments = response.documents || [];

            // Prefill Applicant details
            if (currentDetails.profession_type) {
                $('#profession_type').selectpicker('val', currentDetails.profession_type);
            }
            $('#loan_type').val(currentDetails.loan_type || '');
            $('#mother_name').val(currentDetails.mother_name || '');

            // Prefill Co-applicant details
            $('#co_applicant_name').val(currentDetails.co_applicant_name || '');
            $('#co_applicant_mother_name').val(currentDetails.co_applicant_mother_name || '');
            $('#co_applicant_mobile').val(currentDetails.co_applicant_mobile || '');
            $('#co_applicant_email').val(currentDetails.co_applicant_email || '');
            $('#co_applicant_address').val(currentDetails.co_applicant_address || '');

            // Prefill References
            $('#ref1_name').val(currentDetails.ref1_name || '');
            $('#ref1_phone').val(currentDetails.ref1_phone || '');
            $('#ref2_name').val(currentDetails.ref2_name || '');
            $('#ref2_phone').val(currentDetails.ref2_phone || '');

            // Render Checklist
            renderChecklist();

            // Render Status History
            renderStatusHistory(response.status_history || []);

            // Show Modal
            $('#lead_loan_details_modal').modal('show');
        });
    };

    // Render the checklist table dynamically
    function renderChecklist() {
        const profession = $('#profession_type').val() || 'salary';
        const tbody = $('#checklist_tbody');
        tbody.empty();

        documentTypes.forEach(doc => {
            // Skip business documents if profession is salary
            if (doc.category === 'business' && profession !== 'business') {
                return;
            }

            // Check if document is already uploaded
            const uploaded = currentDocuments.find(d => d.document_type === doc.key);

            let statusHtml = '';
            let actionHtml = '';

            if (uploaded) {
                statusHtml = '<span class="badge bg-success" style="background-color: #22c55e; color: #fff; padding: 5px 8px;">Uploaded</span>';
                actionHtml = `
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="${siteUrl + uploaded.file_path}" target="_blank" class="btn btn-default btn-xs" style="margin-right: 5px;"><i class="fa fa-eye"></i> View File</a>
                        <button type="button" class="btn btn-danger btn-xs delete-doc-btn" data-id="${uploaded.id}"><i class="fa fa-remove"></i> Delete</button>
                    </div>
                `;
            } else {
                statusHtml = '<span class="badge bg-warning" style="background-color: #f97316; color: #fff; padding: 5px 8px;">Pending</span>';
                actionHtml = `
                    <form class="upload-doc-form" style="display: flex; gap: 5px; align-items: center;">
                        <input type="hidden" name="document_type" value="${doc.key}">
                        <input type="file" name="file" required class="form-control input-sm" style="max-width: 200px;">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> Upload</button>
                    </form>
                `;
            }

            const row = `
                <tr data-key="${doc.key}">
                    <td class="bold">${doc.label}</td>
                    <td>${statusHtml}</td>
                    <td>${actionHtml}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Update checklist dynamically when Profession Type is changed
    $('#profession_type').on('change', function() {
        renderChecklist();
    });

    // Save Details
    function saveDetails() {
        // Collect form data from both tabs
        const data = {
            lead_id: activeLeadId,
            profession_type: $('#profession_type').val(),
            loan_type: $('#loan_type').val(),
            mother_name: $('#mother_name').val(),
            co_applicant_name: $('#co_applicant_name').val(),
            co_applicant_mother_name: $('#co_applicant_mother_name').val(),
            co_applicant_mobile: $('#co_applicant_mobile').val(),
            co_applicant_email: $('#co_applicant_email').val(),
            co_applicant_address: $('#co_applicant_address').val(),
            ref1_name: $('#ref1_name').val(),
            ref1_phone: $('#ref1_phone').val(),
            ref2_name: $('#ref2_name').val(),
            ref2_phone: $('#ref2_phone').val(),
        };

        $.post(admin_url + 'leads/save_loan_details', data, function(response) {
            const res = JSON.parse(response);
            if (res.success) {
                alert_float('success', res.message);
                if (typeof(list_leads) !== 'undefined') {
                    list_leads(); // Refresh leads table if visible
                }
            } else {
                alert_float('danger', res.message);
            }
        });
    }

    $('#loan_details_form').on('submit', function(e) {
        e.preventDefault();
        saveDetails();
    });

    $('#co_applicant_details_form').on('submit', function(e) {
        e.preventDefault();
        saveDetails();
    });

    // Handle document upload
    $(document).on('submit', '.upload-doc-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        if (typeof(csrfData) !== 'undefined') {
            formData.append(csrfData.token_name, csrfData.hash);
        }
        
        form.find('button[type="submit"]').prop('disabled', true).text('Uploading...');

        $.ajax({
            url: admin_url + 'leads/upload_loan_document/' + activeLeadId,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert_float('success', res.message);
                    currentDocuments.push(res.document);
                    renderChecklist();
                } else {
                    alert_float('danger', res.message);
                    form.find('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-upload"></i> Upload');
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred during file upload.');
                form.find('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-upload"></i> Upload');
            }
        });
    });

    // Handle document deletion
    $(document).on('click', '.delete-doc-btn', function() {
        if (!confirm('Are you sure you want to delete this document?')) {
            return;
        }

        const btn = $(this);
        const docId = btn.data('id');

        btn.prop('disabled', true).text('Deleting...');

        $.post(admin_url + 'leads/delete_loan_document/' + docId, function(response) {
            const res = JSON.parse(response);
            if (res.success) {
                alert_float('success', res.message);
                currentDocuments = currentDocuments.filter(d => d.id != docId);
                renderChecklist();
            } else {
                alert_float('danger', res.message);
                btn.prop('disabled', false).html('<i class="fa fa-remove"></i> Delete');
            }
        });
    });

    // Render Status History
    function renderStatusHistory(history) {
        const tbody = $('#status_history_tbody');
        tbody.empty();
        
        if (!history || history.length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center text-muted">No status history found for this lead.</td></tr>');
            return;
        }
        
        history.forEach(item => {
            let proofHtml = 'N/A';
            if (item.proof_path) {
                proofHtml = `<a href="${siteUrl + item.proof_path}" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> View Proof</a>`;
            }
            
            const row = `
                <tr>
                    <td>${item.old_status || 'N/A'}</td>
                    <td class="bold">${item.new_status}</td>
                    <td>${item.changed_by}</td>
                    <td>${item.changed_at}</td>
                    <td>${proofHtml}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Helper function to upload document file (used by drag-drop and paste)
    function uploadDocumentFile(file, doc_key) {
        const tr = $(`.table-checklist tr[data-key="${doc_key}"]`);
        const actionTd = tr.find('td:last-child');
        const originalHtml = actionTd.html();
        
        actionTd.html('<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Uploading...</span>');
        
        const formData = new FormData();
        formData.append('document_type', doc_key);
        formData.append('file', file);
        if (typeof(csrfData) !== 'undefined') {
            formData.append(csrfData.token_name, csrfData.hash);
        }
        
        $.ajax({
            url: admin_url + 'leads/upload_loan_document/' + activeLeadId,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert_float('success', res.message);
                    currentDocuments = currentDocuments.filter(d => d.document_type !== doc_key);
                    currentDocuments.push(res.document);
                    renderChecklist();
                } else {
                    alert_float('danger', res.message);
                    actionTd.html(originalHtml);
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred during file upload.');
                actionTd.html(originalHtml);
            }
        });
    }

    // Handle ZIP File Upload
    $('#zip_file_input').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        if (typeof(csrfData) !== 'undefined') {
            formData.append(csrfData.token_name, csrfData.hash);
        }

        alert_float('info', 'Processing ZIP archive, please wait...');

        $.ajax({
            url: admin_url + 'leads/upload_loan_document_zip/' + activeLeadId,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert_float('success', res.message);
                    
                    // Reload checklist documents
                    $.getJSON(admin_url + 'leads/get_loan_details/' + activeLeadId, function(data) {
                        currentDocuments = data.documents || [];
                        renderChecklist();
                    });
                } else {
                    alert_float('danger', res.message);
                }
            },
            error: function() {
                alert_float('danger', 'An error occurred during ZIP upload.');
            }
        });

        // Reset
        $(this).val('');
    });

    // Select row on click for copy-paste
    $(document).on('click', '.table-checklist tr[data-key]', function() {
        $('.table-checklist tr').removeClass('selected-row');
        $(this).addClass('selected-row');
    });

    // Handle paste event (Ctrl+V)
    window.addEventListener('paste', function(e) {
        if (!$('#lead_loan_details_modal').hasClass('show') && !$('#lead_loan_details_modal').is(':visible')) {
            return;
        }
        if (!$('#tab_checklist').hasClass('active')) {
            return;
        }

        const selectedRow = $('.table-checklist tr.selected-row');
        if (selectedRow.length === 0) {
            return;
        }

        const doc_key = selectedRow.data('key');
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        
        for (let i = 0; i < items.length; i++) {
            if (items[i].kind === 'file') {
                const file = items[i].getAsFile();
                if (file) {
                    uploadDocumentFile(file, doc_key);
                    break;
                }
            }
        }
    });

    // Handle Drag & Drop events on rows
    $(document).on('dragover', '.table-checklist tr[data-key]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });

    $(document).on('dragleave', '.table-checklist tr[data-key]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });

    $(document).on('drop', '.table-checklist tr[data-key]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const tr = $(this);
        tr.removeClass('drag-over');
        
        const doc_key = tr.data('key');
        const files = (e.originalEvent || e).dataTransfer.files;
        
        if (files && files.length > 0) {
            const file = files[0];
            uploadDocumentFile(file, doc_key);
        }
    });
});
</script>
