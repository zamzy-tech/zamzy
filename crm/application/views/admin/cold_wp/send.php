<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <!-- Left Column: Template & Settings -->
            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                            <h4 class="tw-my-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                                <i class="fa fa-whatsapp text-success" style="color: #25d366;"></i> WhatsApp Cold Campaign
                            </h4>
                            <a href="<?= admin_url('cold_wp/logs'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-list"></i> View Message Logs
                            </a>
                        </div>
                        <hr class="hr-panel-separator" />

                        <!-- Form for template and image -->
                        <form id="campaign_form" enctype="multipart/form-data">
                            <input type="hidden" id="template_id" name="template_id" value="">
                            <div class="form-group">
                                <label for="template_title" class="control-label">Template Title</label>
                                <input type="text" id="template_title" name="title" class="form-control" placeholder="e.g. Loan Script 1">
                            </div>

                            <div class="form-group">
                                <label for="message_template" class="control-label">Message Template</label>
                                <textarea id="message_template" name="message_text" class="form-control" rows="8" placeholder="Hello {name}, we have an exciting loan offer for you!"></textarea>
                                <span class="text-muted"><i class="fa fa-info-circle"></i> Use placeholder <code>{name}</code> to personalize the message automatically.</span>
                            </div>

                            <div class="form-group">
                                <label for="media_image" class="control-label">Campaign Media Image (Optional)</label>
                                <input type="file" id="media_image" name="image" class="form-control" accept="image/*">
                                <span class="text-muted"><i class="fa fa-info-circle"></i> Since WhatsApp API does not support direct image pre-attachment via links, uploaded images will be shown below for easy copy-pasting (Ctrl+V) into WhatsApp.</span>
                            </div>

                            <!-- Save / Reset Buttons -->
                            <div class="form-group tw-mt-4 tw-flex tw-space-x-2">
                                <button type="button" class="btn btn-info tw-flex-1" id="save_template_btn">
                                    <i class="fa fa-save"></i> Save Template
                                </button>
                                <button type="button" class="btn btn-default" id="reset_template_btn" style="display: none;">
                                    <i class="fa fa-refresh"></i> Reset
                                </button>
                            </div>

                            <!-- Image Preview Area -->
                            <div id="image_preview_container" style="display: none; margin-top: 15px; border: 1px dashed #ddd; padding: 10px; text-align: center; border-radius: 4px;">
                                <p class="bold text-muted" style="margin-bottom: 5px;"><i class="fa fa-image"></i> Media Preview (Right-click to Copy Image)</p>
                                <img id="image_preview" src="#" style="max-height: 200px; max-width: 100%; object-fit: contain; border-radius: 4px;" />
                            </div>
                        </form>

                        <!-- Saved Templates Table -->
                        <div class="tw-mt-8">
                            <h4 class="tw-my-0 tw-font-bold tw-text-base tw-text-neutral-700 tw-mb-4">
                                <i class="fa fa-list"></i> Saved Templates
                            </h4>
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; border-radius: 4px;">
                                <table class="table table-hover table-bordered" id="templates_table" style="margin-bottom: 0;">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Message</th>
                                            <th>Media</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($templates as $tmpl) { ?>
                                            <tr id="tmpl_row_<?= $tmpl['id']; ?>" 
                                                data-id="<?= $tmpl['id']; ?>" 
                                                data-title="<?= e($tmpl['title']); ?>" 
                                                data-message="<?= e($tmpl['message_text']); ?>" 
                                                data-image="<?= $tmpl['image_path'] ? base_url($tmpl['image_path']) : ''; ?>"
                                                data-raw-image="<?= $tmpl['image_path']; ?>">
                                                <td class="bold tmpl-title"><?= e($tmpl['title']); ?></td>
                                                <td class="tmpl-message" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?= e($tmpl['message_text']); ?>
                                                </td>
                                                <td class="tmpl-image text-center">
                                                    <?php if ($tmpl['image_path']) { ?>
                                                        <img src="<?= base_url($tmpl['image_path']); ?>" style="max-height: 30px; max-width: 50px; object-fit: contain; border-radius: 2px;">
                                                    <?php } else { ?>
                                                        <span class="text-muted">No Media</span>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-center" style="white-space: nowrap;">
                                                    <button type="button" class="btn btn-info btn-xs edit-template-btn" title="Load / Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-xs delete-template-btn" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Queue & Sending -->
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                            <h4 class="tw-my-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                                Recipients Queue (<span id="queue_count"><?= count($leads); ?></span> leads)
                            </h4>
                            <span class="label label-info" id="sent_stats">0 / <?= count($leads); ?> Sent</span>
                        </div>
                        <hr class="hr-panel-separator" />

                        <?php if (count($leads) === 0) { ?>
                            <div class="alert alert-warning text-center" style="margin-top: 20px;">
                                <p class="bold" style="font-size: 16px;"><i class="fa fa-exclamation-triangle"></i> No Leads Selected!</p>
                                <p>Go to the Leads page, select multiple leads using checkboxes, and click <b>"Send Cold WP Message"</b> inside the Bulk Actions modal to load them into this queue.</p>
                                <a href="<?= admin_url('leads'); ?>" class="btn btn-primary" style="margin-top: 10px;">Go to Leads Page</a>
                            </div>
                        <?php } else { ?>
                            <!-- Filter Queue -->
                            <div class="row tw-mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lead_status_filter" class="control-label">Filter by Lead Status</label>
                                        <select id="lead_status_filter" class="form-control selectpicker" data-width="100%" data-none-selected-text="All Statuses">
                                            <option value="">All Statuses</option>
                                            <?php 
                                            $statuses = [];
                                            foreach ($leads as $lead) {
                                                if (!empty($lead['status_name']) && !in_array($lead['status_name'], $statuses)) {
                                                    $statuses[] = $lead['status_name'];
                                                }
                                            }
                                            sort($statuses);
                                            foreach ($statuses as $status) {
                                                echo '<option value="' . e($status) . '">' . e($status) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="batch_name_filter" class="control-label">Filter by Batch Name</label>
                                        <select id="batch_name_filter" class="form-control selectpicker" data-width="100%" data-none-selected-text="All Batches">
                                            <option value="">All Batches</option>
                                            <?php 
                                            $batches = [];
                                            foreach ($leads as $lead) {
                                                if (!empty($lead['batch_name']) && !in_array($lead['batch_name'], $batches)) {
                                                    $batches[] = $lead['batch_name'];
                                                }
                                            }
                                            sort($batches);
                                            foreach ($batches as $batch) {
                                                echo '<option value="' . e($batch) . '">' . e($batch) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Recipient</th>
                                            <th>Phone Number</th>
                                            <th>Lead Status</th>
                                            <th class="text-center">Send Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leads as $lead) { ?>
                                            <tr id="lead_row_<?= $lead['id']; ?>" data-id="<?= $lead['id']; ?>" data-name="<?= e($lead['name']); ?>" data-company="<?= e($lead['company']); ?>" data-lead-status="<?= e($lead['status_name']); ?>" data-batch-name="<?= e($lead['batch_name']); ?>" data-phone="<?= e($lead['phonenumber']); ?>">
                                                <td class="bold"><?= e($lead['name'] == '/' || empty($lead['name']) ? ($lead['company'] ? $lead['company'] : '/') : $lead['name']); ?></td>
                                                <td><?= e($lead['phonenumber']); ?></td>
                                                <td class="lead-status-td"><span class="label label-info"><?= e($lead['status_name']); ?></span></td>
                                                <td class="text-center status-td">
                                                     <span class="label label-default">Pending</span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-success btn-xs send-wp-btn" onclick="sendWhatsApp(<?= $lead['id']; ?>);">
                                                        <i class="fa fa-whatsapp"></i> Send
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Script Selection Modal for Campaign Page -->
<div class="modal fade" id="campaign_script_select_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title bold"><i class="fa fa-whatsapp text-success"></i> Send WhatsApp Message</h4>
            </div>
            <form id="campaign_whatsapp_modal_form" enctype="multipart/form-data">
                <div class="modal-body" style="padding: 20px;">
                    <p style="font-size: 14px; margin-bottom: 15px;">Sending message to <strong id="campaign_wp_script_lead_name" class="text-primary"></strong> (<span id="campaign_wp_script_lead_phone" class="text-muted"></span>)</p>
                    
                    <div class="form-group">
                        <label for="campaign_modal_script_select" class="control-label">Load Template Script</label>
                        <select id="campaign_modal_script_select" class="form-control" style="width: 100%;">
                            <option value="">-- Choose Script --</option>
                            <option value="form_current" data-message="form_current" data-image="form_current">-- Use Current Form Message --</option>
                            <?php foreach ($templates as $tmpl) { ?>
                                <option value="<?= $tmpl['id']; ?>" 
                                        data-message="<?= e($tmpl['message_text']); ?>"
                                        data-image="<?= $tmpl['image_path'] ? base_url($tmpl['image_path']) : ''; ?>"
                                        data-raw-image="<?= $tmpl['image_path']; ?>">
                                    <?= e($tmpl['title']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="campaign_modal_message_text" class="control-label">Message Content</label>
                        <textarea id="campaign_modal_message_text" name="message_text" class="form-control" rows="6" placeholder="Type your message here..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="campaign_modal_media_image" class="control-label">Upload Custom Media Image (Optional)</label>
                        <input type="file" id="campaign_modal_media_image" name="image" class="form-control" accept="image/*">
                        <input type="hidden" id="campaign_modal_image_path" name="image_path" value="">
                    </div>

                    <div id="campaign_modal_image_preview_container" style="display: none; text-align: center; border: 1px dashed #ddd; padding: 10px; border-radius: 4px; margin-top: 10px;">
                        <p class="bold text-muted" style="margin-bottom: 5px;"><i class="fa fa-image"></i> Media Preview</p>
                        <img id="campaign_modal_image_preview" src="#" style="max-height: 150px; max-width: 100%; object-fit: contain; border-radius: 4px;" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" style="background-color:#25d366; border-color:#25d366; color:#fff; font-weight:bold;">
                        <i class="fa fa-paper-plane"></i> Send via API
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    let uploadedImagePath = null;

    // Load / Edit template click handler
    $(document).on('click', '.edit-template-btn', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const title = row.data('title');
        const message = row.data('message');
        const image = row.data('image');
        const rawImage = row.data('raw-image');

        $('#template_id').val(id);
        $('#template_title').val(title);
        $('#message_template').val(message);

        if (image) {
            $('#image_preview').attr('src', image);
            $('#image_preview_container').show();
            uploadedImagePath = rawImage;
        } else {
            $('#image_preview_container').hide();
            uploadedImagePath = null;
        }

        $('#save_template_btn').html('<i class="fa fa-save"></i> Update Template');
        $('#reset_template_btn').show();
    });

    // Reset template form handler
    function resetTemplateForm() {
        $('#template_id').val('');
        $('#template_title').val('');
        $('#message_template').val('');
        $('#media_image').val('');
        $('#image_preview_container').hide();
        $('#image_preview').attr('src', '#');
        uploadedImagePath = null;

        $('#save_template_btn').html('<i class="fa fa-save"></i> Save Template');
        $('#reset_template_btn').hide();
    }

    $('#reset_template_btn').on('click', function() {
        resetTemplateForm();
    });

    // Save/Update template click handler
    $('#save_template_btn').on('click', function() {
        const title = $('#template_title').val().trim();
        const messageText = $('#message_template').val().trim();
        
        if (title === '') {
            alert_float('warning', 'Please enter a template title.');
            return;
        }
        if (messageText === '') {
            alert_float('warning', 'Please enter message template text.');
            return;
        }

        const form = $('#campaign_form')[0];
        const formData = new FormData(form);

        if (typeof(csrfData) !== 'undefined') {
            formData.append(csrfData.token_name, csrfData.hash);
        }

        $.ajax({
            url: admin_url + 'cold_wp/save_template',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert_float('success', res.message);
                    
                    const t = res.template;
                    const mediaHtml = t.image_path ? 
                        '<img src="' + t.image_path + '" style="max-height:30px; max-width:50px; object-fit:contain; border-radius:2px;">' : 
                        '<span class="text-muted">No Media</span>';
                    
                    const existingRow = $('#tmpl_row_' + t.id);
                    if (existingRow.length > 0) {
                        // Update existing row
                        existingRow.attr('data-title', t.title);
                        existingRow.attr('data-message', t.message_text);
                        existingRow.attr('data-image', t.image_path || '');
                        existingRow.attr('data-raw-image', t.raw_image_path || '');
                        
                        existingRow.find('.tmpl-title').text(t.title);
                        existingRow.find('.tmpl-message').text(t.message_text);
                        existingRow.find('.tmpl-image').html(mediaHtml);
                    } else {
                        // Append new row
                        const newRow = $('<tr id="tmpl_row_' + t.id + '"></tr>')
                            .attr('data-id', t.id)
                            .attr('data-title', t.title)
                            .attr('data-message', t.message_text)
                            .attr('data-image', t.image_path || '')
                            .attr('data-raw-image', t.raw_image_path || '');
                        
                        newRow.append('<td class="bold tmpl-title">' + $('<div>').text(t.title).html() + '</td>');
                        newRow.append('<td class="tmpl-message" style="max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' + $('<div>').text(t.message_text).html() + '</td>');
                        newRow.append('<td class="tmpl-image text-center">' + mediaHtml + '</td>');
                        newRow.append('<td class="text-center" style="white-space:nowrap;">' +
                            '<button type="button" class="btn btn-info btn-xs edit-template-btn" title="Load / Edit" style="margin-right:2px;"><i class="fa fa-edit"></i></button>' +
                            '<button type="button" class="btn btn-danger btn-xs delete-template-btn" title="Delete"><i class="fa fa-trash"></i></button>' +
                            '</td>');
                        
                        $('#templates_table tbody').append(newRow);
                    }
                    
                    resetTemplateForm();
                } else {
                    alert_float('danger', res.message);
                }
            },
            error: function() {
                alert_float('danger', 'Failed to save template.');
            }
        });
    });

    // Delete template click handler
    $(document).on('click', '.delete-template-btn', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');

        if (confirm("Are you sure you want to delete this template?")) {
            $.ajax({
                url: admin_url + 'cold_wp/delete_template/' + id,
                type: 'POST',
                data: (typeof(csrfData) !== 'undefined' ? { [csrfData.token_name]: csrfData.hash } : {}),
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        alert_float('success', res.message);
                        row.remove();
                        if ($('#template_id').val() == id) {
                            resetTemplateForm();
                        }
                    } else {
                        alert_float('danger', res.message);
                    }
                },
                error: function() {
                    alert_float('danger', 'Failed to delete template.');
                }
            });
        }
    });

    // Handle Image Preview
    $('#media_image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image_preview').attr('src', e.target.result);
                $('#image_preview_container').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#image_preview_container').hide();
        }
    });

    let activeCampaignLead = null;

    // Send WhatsApp Action - opens campaign script select modal
    window.sendWhatsApp = function(leadId) {
        const row = $('#lead_row_' + leadId);
        const name = row.data('name');
        const company = row.data('company');
        const phone = row.data('phone');

        const cleanName = (name === '/' || name === '' || !name) ? (company ? company : 'there') : name;

        // Reset modal fields
        $('#campaign_whatsapp_modal_form')[0].reset();
        $('#campaign_modal_script_select').val('').trigger('change');
        $('#campaign_modal_image_preview_container').hide();
        $('#campaign_modal_image_preview').attr('src', '#');
        $('#campaign_modal_image_path').val('');

        activeCampaignLead = {
            id: leadId,
            name: cleanName,
            phone: phone,
            row: row
        };

        $('#campaign_wp_script_lead_name').text(cleanName);
        $('#campaign_wp_script_lead_phone').text(phone);
        $('#campaign_script_select_modal').modal('show');
    };

    // Handle template select change in campaign modal
    $(document).on('change', '#campaign_modal_script_select', function() {
        const selectedOpt = $(this).find('option:selected');
        const val = selectedOpt.val();

        if (val === '') {
            $('#campaign_modal_message_text').val('');
            $('#campaign_modal_image_preview_container').hide();
            $('#campaign_modal_image_path').val('');
            return;
        }

        let rawMessage = '';
        let imageUrl = '';
        let rawImagePath = '';

        if (val === 'form_current') {
            rawMessage = $('#message_template').val().trim();
            imageUrl = $('#image_preview').attr('src');
            // If it's a data url or actual url
            if ($('#image_preview_container').is(':visible') && imageUrl && imageUrl !== '#') {
                rawImagePath = uploadedImagePath || '';
            } else {
                imageUrl = '';
            }
        } else {
            rawMessage = selectedOpt.data('message') || '';
            imageUrl = selectedOpt.data('image') || '';
            rawImagePath = selectedOpt.data('raw-image') || '';
        }

        if (activeCampaignLead) {
            const formattedMessage = rawMessage.replace(/{name}/g, activeCampaignLead.name);
            $('#campaign_modal_message_text').val(formattedMessage);
        } else {
            $('#campaign_modal_message_text').val(rawMessage);
        }

        if (imageUrl) {
            $('#campaign_modal_image_preview').attr('src', imageUrl);
            $('#campaign_modal_image_preview_container').show();
            $('#campaign_modal_image_path').val(rawImagePath);
        } else {
            $('#campaign_modal_image_preview_container').hide();
            $('#campaign_modal_image_path').val('');
        }
    });

    // Handle image selection preview inside campaign modal
    $(document).on('change', '#campaign_modal_media_image', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#campaign_modal_image_preview').attr('src', e.target.result);
                $('#campaign_modal_image_preview_container').show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Handle campaign modal form submit to send via background API
    $(document).on('submit', '#campaign_whatsapp_modal_form', function(e) {
        e.preventDefault();
        if (!activeCampaignLead) return;

        const lead = activeCampaignLead;
        const message = $('#campaign_modal_message_text').val().trim();

        if (message === '') {
            alert_float('warning', 'Please enter a message template.');
            return;
        }

        // Hide the modal
        $('#campaign_script_select_modal').modal('hide');

        const formData = new FormData(this);
        formData.append('lead_id', lead.id);
        formData.append('phone_number', lead.phone);
        
        if (typeof(csrfData) !== 'undefined') {
            formData.append(csrfData.token_name, csrfData.hash);
        }

        $.ajax({
            url: admin_url + 'cold_wp/log_send',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    // Update Row status
                    lead.row.find('.status-td').html('<span class="label label-success"><i class="fa fa-check"></i> Sent</span>');
                    lead.row.find('.send-wp-btn').removeClass('btn-success').addClass('btn-default').html('<i class="fa fa-refresh"></i> Re-send');
                    
                    // Update stats
                    updateStats();
                } else {
                    alert_float('danger', res.message);
                }
            },
            error: function() {
                alert_float('danger', 'Failed to log message status on the server.');
            }
        });

        // Reset active lead
        activeCampaignLead = null;
    });

    function filterCampaignQueue() {
        const selectedStatus = $('#lead_status_filter').val();
        const selectedBatch = $('#batch_name_filter').val();

        $('tr[id^="lead_row_"]').each(function() {
            const row = $(this);
            const rowStatus = row.data('lead-status');
            const rowBatch = row.data('batch-name');

            let matchStatus = !selectedStatus || rowStatus === selectedStatus;
            let matchBatch = !selectedBatch || rowBatch === selectedBatch;

            if (matchStatus && matchBatch) {
                row.show();
            } else {
                row.hide();
            }
        });

        updateStats();
    }

    // Handle lead status and batch filters
    $(document).on('change', '#lead_status_filter, #batch_name_filter', function() {
        filterCampaignQueue();
    });

    function updateStats() {
        const visibleRows = $('tr[id^="lead_row_"]:visible');
        const total = visibleRows.length;
        const sent = visibleRows.find('.status-td .label-success').length;
        $('#sent_stats').text(sent + ' / ' + total + ' Sent');
        $('#queue_count').text(total);
    }
});
</script>
</body>
</html>
