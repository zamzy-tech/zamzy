<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content" id="vueApp">
        <div class="row">
            <div class="col-md-12">
                <div
                    class="leads-overview tw-mb-6<?= $isKanBan ? ' hide' : ''; ?>">
                    <h4 class="tw-my-0 tw-font-bold tw-text-xl tw-mb-2">
                        <?= _l('leads'); ?>
                    </h4>
                    <div class="tw-grid tw-gap-2 sm:tw-grid-flow-col sm:tw-auto-cols-max tw-overflow-x-auto">
                        <?php foreach ($summary as $item) { ?>
                        <button type="button"
                            onclick="window.location.href = '<?= admin_url('leads?category=' . $item['key'] . (!empty($selected_batch) ? '&batch_name=' . urlencode($selected_batch) : '')); ?>';"
                            class="tw-bg-transparent tw-border tw-border-solid tw-border-neutral-300 tw-shadow-sm tw-py-1.5 tw-px-3 tw-rounded-lg tw-text-sm hover:tw-bg-neutral-200/60 tw-text-neutral-700 hover:tw-text-neutral-600 focus:tw-text-neutral-600 text-left"
                            style="<?= ($selected_cat === $item['key']) ? 'background-color: #f3f4f6; border-color: #999; font-weight: bold;' : ''; ?>">
                            <span class="tw-font-semibold tw-mr-1 rtl:tw-ml-1">
                                <?= e($item['total']); ?>
                            </span>
                            <span style="color:<?= e($item['color']); ?>; font-weight: 600;">
                                <?= e($item['name']); ?>
                            </span>
                        </button>
                        <?php } ?>
                    </div>
                </div>

                <div class="_buttons tw-mb-2">
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2">
                        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                            <a href="#" onclick="init_lead(); return false;" class="btn btn-primary" id="new-lead-btn">
                                <i class="fa-regular fa-plus"></i>
                                <?= _l('new_lead'); ?>
                            </a>
                            <a href="<?= get_option('excel_sheet_url') ?: 'https://docs.google.com/spreadsheets/d/17hEUmsz8Q8Q32KDKO7qi0uTdhAXIDz7vRvPmkMS7Yv8/edit?usp=sharing'; ?>" 
                               target="_blank" 
                               id="excel-open-sheet-btn" 
                               class="btn btn-warning" 
                               style="<?= ($selected_cat === 'ads_excel_list') ? '' : 'display: none;'; ?> background-color: #ff9f43; border-color: #ff9f43; color: #fff;">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Google Sheet
                            </a>
                            <a href="<?= admin_url('leads/switch_kanban/' . $switch_kanban); ?>"
                                class="btn btn-default hidden-xs !tw-px-3" data-toggle="tooltip" data-placement="top"
                                data-title="<?= $switch_kanban == 1 ? _l('leads_switch_to_kanban') : _l('switch_to_list_view'); ?>">
                                <?php if ($switch_kanban == 1) { ?>
                                <i class="fa-solid fa-grip-vertical"></i>
                                <?php } else { ?>
                                <i class="fa-solid fa-table-list"></i>
                                <?php } ?>
                            </a>
                            <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1') { ?>
                            <a href="<?= admin_url('leads/import'); ?>"
                                class="hidden-xs btn btn-default">
                                <i class="fa-solid fa-upload tw-mr-1"></i>
                                <?= _l('import_leads'); ?>
                            </a>
                            <?php } ?>
                            <?php $selected_batch = $this->input->get('batch_name') ?? ''; ?>
                            <div class="tw-inline-block" style="min-width: 140px; vertical-align: middle;">
                                <select name="view_batch_name" class="selectpicker" data-width="100%" data-none-selected-text="All Sections" data-live-search="true">
                                    <option value="" <?= ($selected_batch === '') ? 'selected' : ''; ?>>All Sections</option>
                                    <?php 
                                    $batches = $this->db->select('DISTINCT(batch_name)')->where('batch_name IS NOT NULL')->where('batch_name !=', '')->order_by('batch_name', 'asc')->get(db_prefix() . 'leads')->result_array();
                                    foreach ($batches as $batch) {
                                        $selected = ($selected_batch === $batch['batch_name']) ? 'selected' : '';
                                        echo '<option value="' . e($batch['batch_name']) . '" ' . $selected . '>' . e($batch['batch_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php $selected_cat = $this->input->get('category') ?? ''; ?>
                            <div class="tw-inline-block" style="min-width: 160px; vertical-align: middle;">
                                <select name="view_lead_category" class="selectpicker" data-width="100%" data-none-selected-text="All Leads" data-live-search="true">
                                    <option value="" <?= ($selected_cat === '') ? 'selected' : ''; ?>>All Leads</option>
                                    <option value="converted" <?= ($selected_cat === 'converted') ? 'selected' : ''; ?>>Converted Leads</option>
                                    <option value="cold_wp" <?= ($selected_cat === 'cold_wp') ? 'selected' : ''; ?>>Cold WP Messages</option>
                                    <option value="ads_wp" <?= ($selected_cat === 'ads_wp') ? 'selected' : ''; ?>>Ads WhatsApp Leads</option>
                                    <option value="ads_excel_list" <?= ($selected_cat === 'ads_excel_list') ? 'selected' : ''; ?>>Ads Excel List</option>
                                </select>
                            </div>
                            <!-- Status Wise Filter -->
                            <?php $selected_status = $this->input->get('status_id') ?: ($this->input->get('status') ?: ''); ?>
                            <div class="tw-inline-block" style="min-width: 160px; vertical-align: middle;">
                                <select name="view_status_id" class="selectpicker" data-width="100%" data-none-selected-text="All Statuses" data-live-search="true">
                                    <option value="" <?= ($selected_status === '') ? 'selected' : ''; ?>>All Statuses</option>
                                    <?php if (!empty($statuses)) {
                                        foreach ($statuses as $st) { 
                                            $selected = ($selected_status == $st['id']) ? 'selected' : '';
                                            echo '<option value="' . $st['id'] . '" ' . $selected . '>' . e($st['name']) . '</option>';
                                        } 
                                    } ?>
                                    <option value="lost" <?= ($selected_status === 'lost') ? 'selected' : ''; ?>>Lost Leads</option>
                                    <option value="junk" <?= ($selected_status === 'junk') ? 'selected' : ''; ?>>Junk Leads</option>
                                </select>
                            </div>
                            <!-- Notes Filter (Has Notes / No Notes) -->
                            <?php $selected_has_notes = $this->input->get('has_notes') ?? ''; ?>
                            <div class="tw-inline-block" style="min-width: 150px; vertical-align: middle;">
                                <select name="view_has_notes" class="selectpicker" data-width="100%" data-none-selected-text="All Notes">
                                    <option value="" <?= ($selected_has_notes === '') ? 'selected' : ''; ?>>All Notes</option>
                                    <option value="has_notes" <?= ($selected_has_notes === 'has_notes') ? 'selected' : ''; ?>>With Notes Only</option>
                                    <option value="no_notes" <?= ($selected_has_notes === 'no_notes') ? 'selected' : ''; ?>>Without Notes</option>
                                </select>
                            </div>
                            <!-- Search in Notes Input -->
                            <?php $selected_notes_kw = $this->input->get('notes_keyword') ?? ''; ?>
                            <div class="tw-inline-block" style="min-width: 170px; vertical-align: middle;">
                                <div class="input-group input-group-sm" style="margin-bottom: 0; display: flex;">
                                    <input type="text" name="view_notes_keyword" class="form-control" placeholder="Search in notes..." value="<?= e($selected_notes_kw); ?>" style="height: 34px; border-radius: 4px 0 0 4px;">
                                    <span class="input-group-btn" style="width: auto;">
                                        <button class="btn btn-default btn-notes-search" type="button" title="Search in Notes" style="height: 34px; border-radius: 0 4px 4px 0;"><i class="fa fa-search"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <?php if ($this->session->userdata('leads_kanban_view') == 'true') { ?>
                            <div class="leads-search">
                                <div data-toggle="tooltip" data-placement="top"
                                    data-title="<?= _l('search_by_tags'); ?>">
                                    <?= render_input('search', '', '', 'search', ['data-name' => 'search', 'onkeyup' => 'leads_kanban();', 'placeholder' => _l('leads_search')], [], 'no-margin') ?>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="tw-inline">
                                <app-filters
                                    id="<?= $table->id(); ?>"
                                    view="<?= $table->viewName(); ?>"
                                    :rules="extra.leadsRules || <?= app\services\utilities\Js::from($this->input->get('status') ? $table->findRule('status')->setValue([$this->input->get('status')]) : []); ?>"
                                    :saved-filters="<?= $table->filtersJs(); ?>"
                                    :available-rules="<?= $table->rulesJs(); ?>">
                                </app-filters>
                            </div>
                            <?php } ?>
                            <?= form_hidden('sort_type'); ?>
                            <?= form_hidden('sort', (get_option('default_leads_kanban_sort') != '' ? get_option('default_leads_kanban_sort_type') : '')); ?>
                        </div>
                    </div>
                </div>
                <div id="normal-leads-container">
                    <div
                        class="<?= $isKanBan ? '' : 'panel_s'; ?>">
                        <div
                            class="<?= $isKanBan ? '' : 'panel-body'; ?>">
                            <div class="tab-content">
                            <?php
                        if ($isKanBan) { ?>
                            <div class="active kan-ban-tab tw-mt-4" id="kan-ban-tab" style="overflow:auto;">
                                <div class="kanban-leads-sort">
                                    <span
                                        class="bold"><?= _l('leads_sort_by'); ?>:
                                    </span>
                                    <a href="#" onclick="leads_kanban_sort('dateadded'); return false"
                                        class="dateadded">
                                        <?php if (get_option('default_leads_kanban_sort') == 'dateadded') {
                                            echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                        } ?><?= _l('leads_sort_by_datecreated'); ?>
                                    </a>
                                    |
                                    <a href="#" onclick="leads_kanban_sort('leadorder');return false;"
                                        class="leadorder">
                                        <?php if (get_option('default_leads_kanban_sort') == 'leadorder') {
                                            echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                        } ?><?= _l('leads_sort_by_kanban_order'); ?>
                                    </a>
                                    |
                                    <a href="#" onclick="leads_kanban_sort('lastcontact');return false;"
                                        class="lastcontact">
                                        <?php if (get_option('default_leads_kanban_sort') == 'lastcontact') {
                                            echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                        } ?><?= _l('leads_sort_by_lastcontact'); ?>
                                    </a>
                                </div>
                                <div class="row">
                                    <div class="container-fluid leads-kan-ban">
                                        <div id="kan-ban"></div>
                                    </div>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="row" id="leads-table">
                                <div class="col-md-12">
                                    <a href="#" data-toggle="modal" data-table=".table-leads"
                                        data-target="#leads_bulk_actions"
                                        class="hide bulk-actions-btn table-btn"><?= _l('bulk_actions'); ?></a>
                                    <a href="#" onclick="openBulkWhatsAppModal(); return false;"
                                        class="hide bulk-actions-btn table-btn btn-success" style="background-color:#25d366; border-color:#25d366; color:#fff; margin-left: 5px;">
                                        <i class="fa-brands fa-whatsapp"></i> Bulk Message
                                    </a>
                                    <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1"
                                        role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close"><span
                                                            aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title">
                                                        <?= _l('bulk_actions'); ?>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php if (is_admin() || staff_can('delete', 'leads')) { ?>
                                                    <div class="checkbox checkbox-danger">
                                                        <input type="checkbox" name="mass_delete" id="mass_delete">
                                                        <label
                                                            for="mass_delete"><?= _l('mass_delete'); ?></label>
                                                    </div>
                                                    <hr class="mass_delete_separator" />
                                                    <?php } ?>
                                                    <div id="bulk_change">
                                                        <div class="form-group">
                                                            <div class="checkbox checkbox-primary checkbox-inline">
                                                                <input type="checkbox" name="leads_bulk_mark_lost"
                                                                    id="leads_bulk_mark_lost" value="1">
                                                                <label for="leads_bulk_mark_lost">
                                                                    <?= _l('lead_mark_as_lost'); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <?= render_select('move_to_status_leads_bulk', $statuses, ['id', 'name'], 'ticket_single_change_status'); ?>
                                                        <?= render_select('move_to_source_leads_bulk', $sources, ['id', 'name'], 'lead_source');
                                echo render_datetime_input('leads_bulk_last_contact', 'leads_dt_last_contact');
                                echo render_select('assign_to_leads_bulk', $staff, ['staffid', ['firstname', 'lastname']], 'leads_dt_assigned');
                                ?>
                                                        <div class="form-group">
                                                            <?= '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                                            <input type="text" class="tagsinput" id="tags_bulk"
                                                                name="tags_bulk" value="" data-role="tagsinput">
                                                        </div>
                                                        <hr />
                                                        <div class="form-group no-mbot">
                                                            <div class="radio radio-primary radio-inline">
                                                                <input type="radio" name="leads_bulk_visibility"
                                                                    id="leads_bulk_public" value="public">
                                                                <label for="leads_bulk_public">
                                                                    <?= _l('lead_public'); ?>
                                                                </label>
                                                            </div>
                                                            <div class="radio radio-primary radio-inline">
                                                                <input type="radio" name="leads_bulk_visibility"
                                                                    id="leads_bulk_private" value="private">
                                                                <label for="leads_bulk_private">
                                                                    <?= _l('private'); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default"
                                                        data-dismiss="modal"><?= _l('close'); ?></button>
                                                    <a href="#" class="btn btn-success" style="background-color: #25d366; border-color: #25d366; color: #fff;"
                                                        onclick="send_cold_wp_bulk_action(); return false;"><i class="fa fa-whatsapp"></i> Send Cold WP Message</a>
                                                    <a href="#" class="btn btn-primary"
                                                        onclick="leads_bulk_action(this); return false;"><?= _l('confirm'); ?></a>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->

                                    <!-- Bulk WhatsApp Modal -->
                                    <div class="modal fade" id="bulk_whatsapp_modal" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><i class="fa-brands fa-whatsapp" style="color:#25d366; margin-right:8px;"></i>Send Bulk WhatsApp Message</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="bulk_wp_template" class="control-label">Select Message Template / Script</label>
                                                        <select id="bulk_wp_template" class="selectpicker" data-width="100%">
                                                            <option value="custom">Custom Message (Write below)</option>
                                                            <option value="questionnaire">Loan Questionnaire Script</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="bulk_wp_message" class="control-label">Message Content</label>
                                                        <textarea id="bulk_wp_message" class="form-control" rows="8" placeholder="Type your message here..."></textarea>
                                                    </div>
                                                    <div id="bulk_wp_selected_count" class="alert alert-info" style="font-weight:600; margin-bottom:0;">
                                                        Selected Leads: <span id="bulk_wp_count_val">0</span>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-success" id="btn_send_bulk_wp"><i class="fa-brands fa-whatsapp"></i> Send Messages</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php

                              $table_data    = [];
                                $_table_data = [
                                    '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="leads"><label></label></div>',
                                    [
                                        'name'     => _l('the_number_sign'),
                                        'th_attrs' => ['class' => 'toggleable', 'id' => 'th-number'],
                                    ],
                                    [
                                        'name'     => _l('leads_dt_name'),
                                        'th_attrs' => ['class' => 'toggleable', 'id' => 'th-name'],
                                    ],
                                ];
                                if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                                    $_table_data[] = [
                                        'name'     => _l('gdpr_consent') . ' (' . _l('gdpr_short') . ')',
                                        'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                                    ];
                                }
                                $_table_data[] = [
                                    'name'     => 'Loan Type',
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-company'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('leads_dt_email'),
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-email'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('leads_dt_phonenumber'),
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-phone'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('leads_dt_lead_value'),
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-lead-value'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('tags'),
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-tags'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('leads_dt_assigned'),
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-assigned'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('leads_dt_status'),
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-status'],
                                ];
                                $_table_data[] = [
                                    'name'     => 'Notes',
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-notes', 'style' => 'min-width: 200px;'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('leads_source'),
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-source'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('leads_dt_last_contact'),
                                    'th_attrs' => ['class' => 'toggleable', 'id' => 'th-last-contact'],
                                ];
                                $_table_data[] = [
                                    'name'     => _l('leads_dt_datecreated'),
                                    'th_attrs' => ['class' => 'date-created toggleable', 'id' => 'th-date-created'],
                                ];

                                if (is_admin()) {
                                    $_table_data[] = [
                                        'name'     => 'Lead clicked',
                                        'th_attrs' => ['class' => 'toggleable', 'id' => 'th-lead-clicked'],
                                    ];
                                }

                                 $_table_data[] = [
                                      'name'     => 'Details',
                                      'th_attrs' => ['class' => 'toggleable', 'id' => 'th-details'],
                                  ];

                                foreach ($_table_data as $_t) {
                                    array_push($table_data, $_t);
                                }
                                $custom_fields = get_custom_fields('leads', ['show_on_table' => 1]);

                                foreach ($custom_fields as $field) {
                                    array_push($table_data, [
                                        'name'     => $field['name'],
                                        'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                                    ]);
                                }
                                $table_data = hooks()->apply_filters('leads_table_columns', $table_data);
                                ?>
                                    <div class="panel-table-full">
                                        <?php
                                  render_datatable(
                                      $table_data,
                                      'leads',
                                      ['customizable-table number-index-2'],
                                      [
                                          'id'                         => 'leads',
                                          'data-last-order-identifier' => 'leads',
                                          'data-default-order'         => get_table_last_order('leads'),
                                      ]
                                  );
                                ?>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="excel-leads-container" style="display: none;">
                <div class="panel_s">
                    <div class="panel-body" id="excel-leads-content">
                        <div class="text-center" style="padding: 40px;">
                            <i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color: #107C41;"></i>
                            <p style="margin-top: 10px; font-weight: 600; color: #666;">Loading Excel Leads...</p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
<script id="hidden-columns-table-leads" type="text/json">
    <?= get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads'); ?>
</script>
<?php include_once APPPATH . 'views/admin/leads/status.php'; ?>
<?php include_once APPPATH . 'views/admin/leads/loan_details_modal.php'; ?>

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

<!-- WhatsApp Script Selection Modal -->
<div class="modal fade" id="whatsapp_script_select_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title bold"><i class="fa fa-whatsapp text-success"></i> Send WhatsApp Message</h4>
            </div>
            <form id="lead_whatsapp_modal_form" enctype="multipart/form-data">
                <div class="modal-body" style="padding: 20px;">
                    <p style="font-size: 14px; margin-bottom: 15px;">Sending message to <strong id="wp_script_lead_name" class="text-primary"></strong> (<span id="wp_script_lead_phone" class="text-muted"></span>)</p>
                    
                    <div class="form-group">
                        <label for="modal_script_select" class="control-label">Load Template Script</label>
                        <select id="modal_script_select" class="form-control" style="width: 100%;">
                            <option value="">-- Choose Script --</option>
                            <?php 
                            $db_templates = $this->db->order_by('title', 'asc')->get(db_prefix() . 'cold_wp_templates')->result_array();
                            foreach ($db_templates as $tmpl) {
                            ?>
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
                        <label for="modal_message_text" class="control-label">Message Content</label>
                        <textarea id="modal_message_text" name="message_text" class="form-control" rows="6" placeholder="Type your message here..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="modal_media_image" class="control-label">Upload Custom Media Image (Optional)</label>
                        <input type="file" id="modal_media_image" name="image" class="form-control" accept="image/*">
                        <input type="hidden" id="modal_image_path" name="image_path" value="">
                    </div>

                    <div id="modal_image_preview_container" style="display: none; text-align: center; border: 1px dashed #ddd; padding: 10px; border-radius: 4px; margin-top: 10px;">
                        <p class="bold text-muted" style="margin-bottom: 5px;"><i class="fa fa-image"></i> Media Preview</p>
                        <img id="modal_image_preview" src="#" style="max-height: 150px; max-width: 100%; object-fit: contain; border-radius: 4px;" />
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
    var openLeadID = '<?= e($leadid); ?>';
    $(function() {
        leads_kanban();

        // Load Excel Leads view dynamically if selected category is ads_excel_list
        if ('<?= $selected_cat; ?>' === 'ads_excel_list') {
            $('#normal-leads-container').hide();
            $('#excel-leads-container').show();
            $('#excel-leads-content').load(admin_url + 'ads_excel_list', function() {
                if (typeof(initExcelTable) === 'function') {
                    initExcelTable();
                }
            });
        }
        $('#leads_bulk_mark_lost').on('change', function() {
            $('#move_to_status_leads_bulk').prop('disabled', $(this).prop('checked') == true);
            $('#move_to_status_leads_bulk').selectpicker('refresh')
        });
        $('#move_to_status_leads_bulk').on('change', function() {
            if ($(this).selectpicker('val') != '') {
                $('#leads_bulk_mark_lost').prop('disabled', true);
                $('#leads_bulk_mark_lost').prop('checked', false);
            } else {
                $('#leads_bulk_mark_lost').prop('disabled', false);
            }
        });

        // Handle phone link clicks
        $(document).on('click', '.lead-phone-click', function(e) {
            e.preventDefault();
            const phone = $(this).data('phone');
            const leadId = $(this).data('id');
            
            let cleanPhone = phone.toString().replace(/[^0-9+]/g, '');
            
            $('#call_opt_phone').text(phone);
            $('#normal_call_link').attr('href', 'tel:' + cleanPhone).data('id', leadId);
            $('#whatsapp_call_link').attr('href', 'https://api.whatsapp.com/send?phone=' + cleanPhone).data('id', leadId);
            
            $('#lead_call_options_modal').modal('show');

            if (leadId) {
                $.post(admin_url + 'leads/track_click/' + leadId + '/1');
            }
        });

        // Handle clicks inside call options modal (track click 2)
        $(document).on('click', '#normal_call_link, #whatsapp_call_link', function() {
            const leadId = $(this).data('id');
            if (leadId) {
                $.post(admin_url + 'leads/track_click/' + leadId + '/2');
            }
        });

        let activeWpButton = null;

        // Handle single WhatsApp send button click - opens script selection modal
        $(document).on('click', '.send-single-wp', function(e) {
            e.preventDefault();
            activeWpButton = $(this);
            const name = activeWpButton.data('name');
            const company = activeWpButton.data('company');
            const phone = activeWpButton.data('phone');
            const cleanName = (name === '/' || name === '' || !name) ? (company ? company : 'there') : name;
            
            // Reset modal fields
            $('#lead_whatsapp_modal_form')[0].reset();
            $('#modal_script_select').val('').trigger('change');
            $('#modal_image_preview_container').hide();
            $('#modal_image_preview').attr('src', '#');
            $('#modal_image_path').val('');
            
            $('#wp_script_lead_name').text(cleanName);
            $('#wp_script_lead_phone').text(phone);
            $('#whatsapp_script_select_modal').modal('show');
        });

        // Handle script select change in the modal
        $(document).on('change', '#modal_script_select', function() {
            const selectedOpt = $(this).find('option:selected');
            if (selectedOpt.val() === '') {
                $('#modal_message_text').val('');
                $('#modal_image_preview_container').hide();
                $('#modal_image_path').val('');
                return;
            }

            const rawMessage = selectedOpt.data('message') || '';
            const imageUrl = selectedOpt.data('image') || '';
            const rawImagePath = selectedOpt.data('raw-image') || '';

            if (activeWpButton) {
                const name = activeWpButton.data('name');
                const company = activeWpButton.data('company');
                const cleanName = (name === '/' || name === '' || !name) ? (company ? company : 'there') : name;
                
                const formattedMessage = rawMessage.replace(/{name}/g, cleanName);
                $('#modal_message_text').val(formattedMessage);
            } else {
                $('#modal_message_text').val(rawMessage);
            }

            if (imageUrl) {
                $('#modal_image_preview').attr('src', imageUrl);
                $('#modal_image_preview_container').show();
                $('#modal_image_path').val(rawImagePath);
            } else {
                $('#modal_image_preview_container').hide();
                $('#modal_image_path').val('');
            }
        });

        // Handle image selection preview inside the modal
        $(document).on('change', '#modal_media_image', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#modal_image_preview').attr('src', e.target.result);
                    $('#modal_image_preview_container').show();
                }
                reader.readAsDataURL(file);
            }
        });

        // Handle modal form submit to send via background API
        $(document).on('submit', '#lead_whatsapp_modal_form', function(e) {
            e.preventDefault();
            if (!activeWpButton) return;

            const btn = activeWpButton;
            const leadId = btn.data('id');
            const phone = btn.data('phone');
            const message = $('#modal_message_text').val().trim();

            if (message === '') {
                alert_float('warning', 'Please enter a message template.');
                return;
            }

            // Hide the modal
            $('#whatsapp_script_select_modal').modal('hide');

            const formData = new FormData(this);
            formData.append('lead_id', leadId);
            formData.append('phone_number', phone);
            
            if (typeof(csrfData) !== 'undefined') {
                formData.append(csrfData.token_name, csrfData.hash);
            }

            // Send AJAX
            $.ajax({
                url: admin_url + 'cold_wp/log_send',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        // Change button appearance to grey/Re-send (clickable)
                        btn.removeClass('btn-success')
                           .addClass('btn-default')
                           .css({
                               'background-color': '#dcdcdc',
                               'border-color': '#dcdcdc',
                               'color': '#777'
                           })
                           .attr('title', 'sended')
                           .html('<i class="fa fa-refresh"></i> Re-send');
                    } else {
                        alert_float('danger', res.message);
                    }
                },
                error: function() {
                    alert_float('danger', 'Failed to log message status on the server.');
                }
            });

            activeWpButton = null;
        });

        // Listen to DataTables pre-XHR event to append custom filter parameters
        $('table.table-leads').on('preXhr.dt', function(e, settings, data) {
            data.batch_name = $('select[name="view_batch_name"]').val();
            data.lead_category = $('select[name="view_lead_category"]').val();
            data.view_status_id = $('select[name="view_status_id"]').val();
            data.view_has_notes = $('select[name="view_has_notes"]').val();
            data.view_notes_keyword = $('input[name="view_notes_keyword"]').val();
        });

        function reloadLeadsTable() {
            if ($.fn.DataTable.isDataTable('.table-leads')) {
                $('.table-leads').DataTable().ajax.reload();
            }
        }

        function updateLeadsFilterUrl() {
            var category = $('select[name="view_lead_category"]').val() || '';
            var batch = $('select[name="view_batch_name"]').val() || '';
            var status = $('select[name="view_status_id"]').val() || '';
            var has_notes = $('select[name="view_has_notes"]').val() || '';
            var notes_kw = $('input[name="view_notes_keyword"]').val() || '';

            var params = new URLSearchParams(window.location.search);
            if (category) { params.set('category', category); } else { params.delete('category'); }
            if (batch) { params.set('batch_name', batch); } else { params.delete('batch_name'); }
            if (status) { params.set('status', status); } else { params.delete('status'); }
            if (has_notes) { params.set('has_notes', has_notes); } else { params.delete('has_notes'); }
            if (notes_kw) { params.set('notes_keyword', notes_kw); } else { params.delete('notes_keyword'); }

            var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', newUrl);
            }
        }

        function handleLeadFiltersChange() {
            var category = $('select[name="view_lead_category"]').val() || '';
            var batch = $('select[name="view_batch_name"]').val() || '';
            var status = $('select[name="view_status_id"]').val() || '';
            var has_notes = $('select[name="view_has_notes"]').val() || '';
            var notes_kw = $('input[name="view_notes_keyword"]').val() || '';

            var params = new URLSearchParams();
            if (category) params.set('category', category);
            if (batch) params.set('batch_name', batch);
            if (status) params.set('status_id', status);
            if (has_notes) params.set('has_notes', has_notes);
            if (notes_kw) params.set('notes_keyword', notes_kw);

            var newUrl = admin_url + 'leads' + (params.toString() ? '?' + params.toString() : '');
            
            if (category === 'ads_excel_list') {
                window.location.href = newUrl;
                return;
            }

            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', newUrl);
            }
            if ($.fn.DataTable.isDataTable('.table-leads')) {
                $('.table-leads').DataTable().ajax.reload();
            } else {
                window.location.href = newUrl;
            }
        }

        $('body').on('change', 'select[name="view_lead_category"], select[name="view_batch_name"], select[name="view_status_id"], select[name="view_has_notes"]', function() {
            handleLeadFiltersChange();
        });

        var notesSearchTimer;
        $('body').on('keyup', 'input[name="view_notes_keyword"]', function() {
            clearTimeout(notesSearchTimer);
            notesSearchTimer = setTimeout(function() {
                handleLeadFiltersChange();
            }, 400);
        });

        $('body').on('click', '.btn-notes-search', function() {
            handleLeadFiltersChange();
        });

        // Listen to change/blur on the lead notes textarea to save automatically via AJAX
        $('body').on('blur', '.lead-notes-textarea', function() {
            var $textarea = $(this);
            var lead_id = $textarea.data('id');
            var description = $textarea.val();
            var $indicator = $textarea.siblings('.save-indicator');

            $.ajax({
                url: admin_url + 'leads/update_lead_description_ajax',
                type: 'POST',
                dataType: 'json',
                data: {
                    lead_id: lead_id,
                    description: description,
                    [csrfData.formattedName]: csrfData.hash
                },
                success: function(response) {
                    if (response && response.success) {
                        $indicator.fadeIn().delay(1500).fadeOut();
                    }
                }
            });
        });
    });

    function send_cold_wp_bulk_action() {
        var ids = [];
        var rows = $('.table-leads').find('tbody tr');
        $.each(rows, function() {
            var checkbox = $(this).find('td:first-child input[type="checkbox"]');
            if (checkbox.prop('checked') === true) {
                ids.push(checkbox.val());
            }
        });
        
        if (ids.length === 0) {
            alert_float('warning', 'Please select at least one lead.');
            return;
        }
        
        window.location.href = admin_url + 'cold_wp?ids=' + ids.join(',');
    }

    var questionnaireText = "Hi 👋 Thanks for contacting us!\n\nTo assist you better, could you please share the following details:\n\n1️⃣ *What type of loan are you looking for?*\n• Personal Loan\n• Business Loan\n• Home/Mortgage Loan\n• Other\n\n2️⃣ *Loan Amount Required:* ₹_____\n\n3️⃣ *Your Location/City:* _______\n\n4️⃣ *Employment/Business:* _______\n\nOnce we have these details, our team will get in touch with you and guide you further. 😊";

    function openBulkWhatsAppModal() {
        var ids = [];
        var rows = $('.table-leads').find('tbody tr');
        $.each(rows, function() {
            var checkbox = $(this).find('td:first-child input[type="checkbox"]');
            if (checkbox.prop('checked') === true) {
                ids.push(checkbox.val());
            }
        });
        
        if (ids.length === 0) {
            alert_float('warning', 'Please select at least one lead.');
            return;
        }

        $('#bulk_wp_count_val').text(ids.length);
        $('#bulk_wp_template').val('custom').selectpicker('refresh');
        $('#bulk_wp_message').val('');
        $('#bulk_whatsapp_modal').modal('show');
    }

    $(document).on('change', '#bulk_wp_template', function() {
        var val = $(this).val();
        if (val === 'questionnaire') {
            $('#bulk_wp_message').val(questionnaireText);
        } else {
            $('#bulk_wp_message').val('');
        }
    });

    $(document).on('click', '#btn_send_bulk_wp', function() {
        var ids = [];
        var rows = $('.table-leads').find('tbody tr');
        $.each(rows, function() {
            var checkbox = $(this).find('td:first-child input[type="checkbox"]');
            if (checkbox.prop('checked') === true) {
                ids.push(checkbox.val());
            }
        });

        var message = $('#bulk_wp_message').val().trim();
        if (message === '') {
            alert_float('warning', 'Please enter message content.');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: admin_url + 'leads/send_bulk_whatsapp_ajax',
            type: 'POST',
            dataType: 'json',
            data: {
                ids: ids,
                message: message,
                [csrfData.formattedName]: csrfData.hash
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fa-brands fa-whatsapp"></i> Send Messages');
                if (response.success) {
                    alert_float('success', response.message);
                    $('#bulk_whatsapp_modal').modal('hide');
                    // Uncheck mass select
                    $('#mass_select_all').prop('checked', false).trigger('change');
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fa-brands fa-whatsapp"></i> Send Messages');
                alert_float('danger', 'Failed to send bulk messages.');
            }
        });
    });
</script>
</body>

</html>