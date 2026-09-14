<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons tw-mb-4">
                            <h4 class="tw-my-0 tw-font-bold tw-text-xl tw-mb-4 text-primary">
                                <i class="fa fa-check-circle-o"></i> Converted Leads Workspace
                            </h4>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="panel-table-full">
                            <?php
                            $table_data = [
                                'Status',
                                'Name',
                                'Number',
                                'Load Type',
                                'Actions'
                            ];
                            render_datatable(
                                $table_data,
                                'converted_leads',
                                ['customizable-table'],
                                [
                                    'id'                         => 'converted_leads',
                                    'data-last-order-identifier' => 'converted_leads',
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once APPPATH . 'views/admin/leads/loan_details_modal.php'; ?>
<?php include_once APPPATH . 'views/admin/leads/status.php'; ?>

<!-- Hidden input for Printed proof upload -->
<input type="file" id="printed-proof-input" style="display: none;" accept="image/*,application/pdf">

<!-- Call Options Modal -->
<div class="modal fade" id="lead_call_options_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document" style="margin-top: 15%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center bold"><i class="fa fa-phone"></i> Call Options</h4>
            </div>
            <div class="modal-body text-center" style="padding: 20px;">
                <p style="font-size: 15px; margin-bottom: 20px;">Select how you want to contact <br><strong id="call_opt_phone" class="text-primary"></strong></p>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="#" id="normal_call_link" class="btn btn-primary btn-block btn-lg" style="font-weight: bold; margin-bottom: 5px;">
                        <i class="fa fa-phone-square"></i> Normal Call
                    </a>
                    <a href="#" id="whatsapp_call_link" target="_blank" class="btn btn-success btn-block btn-lg" style="font-weight: bold; background-color: #25d366; border-color: #25d366; color: #fff;">
                        <i class="fa fa-whatsapp"></i> WhatsApp Call
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
// Custom mark status handler for converted leads workspace
function custom_lead_mark_as(status_id, lead_id) {
    if (status_id == 8) { // Printed
        alert_float('warning', 'Please select an image or PDF proof for the Printed status.');
        const fileInput = $('#printed-proof-input');
        
        fileInput.off('change');
        fileInput.on('change', function() {
            const file = this.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('leadid', lead_id);
            formData.append('status', status_id);
            formData.append('proof', file);
            if (typeof(csrfData) !== 'undefined') {
                formData.append(csrfData.token_name, csrfData.hash);
            }
            
            // Post to our custom endpoint
            $.ajax({
                url: admin_url + 'leads/update_converted_lead_status',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        alert_float('success', res.message);
                        $('.table-converted_leads').DataTable().ajax.reload(null, false);
                    } else {
                        alert_float('danger', res.message);
                    }
                },
                error: function() {
                    alert_float('danger', 'Error updating status.');
                }
            });
            
            fileInput.val('');
        });
        
        fileInput.click();
    } else {
        // Other statuses
        const formData = new FormData();
        formData.append('leadid', lead_id);
        formData.append('status', status_id);
        if (typeof(csrfData) !== 'undefined') {
            formData.append(csrfData.token_name, csrfData.hash);
        }
        
        $.ajax({
            url: admin_url + 'leads/update_converted_lead_status',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert_float('success', res.message);
                    $('.table-converted_leads').DataTable().ajax.reload(null, false);
                } else {
                    alert_float('danger', res.message);
                }
            },
            error: function() {
                alert_float('danger', 'Error updating status.');
            }
        });
    }
}

$(function() {
    // Initialize Converted Leads DataTable
    initDataTable('.table-converted_leads', admin_url + 'leads/converted_leads_table', undefined, undefined, 'undefined', [1, 'desc']);

    // Handle copying portal links
    $(document).on('click', '.copy-link-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const link = btn.data('link');

        // Create temporary input element to copy
        const tempInput = document.createElement('input');
        tempInput.value = link;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);

        // Visual feedback
        btn.removeClass('btn-success').addClass('btn-default').html('<i class="fa fa-check"></i> Copied!');
        setTimeout(function() {
            btn.removeClass('btn-default').addClass('btn-success').html('<i class="fa fa-copy"></i> Copy Link');
        }, 2000);
        
        alert_float('success', 'Client Portal link copied to clipboard!');
    });

    // Handle phone link clicks
    $(document).on('click', '.lead-phone-click', function(e) {
        e.preventDefault();
        const phone = $(this).data('phone');
        
        let cleanPhone = phone.toString().replace(/[^0-9+]/g, '');
        
        $('#call_opt_phone').text(phone);
        $('#normal_call_link').attr('href', 'tel:' + cleanPhone);
        $('#whatsapp_call_link').attr('href', 'https://api.whatsapp.com/send?phone=' + cleanPhone);
        
        $('#lead_call_options_modal').modal('show');
    });
});
</script>
</body>
</html>
