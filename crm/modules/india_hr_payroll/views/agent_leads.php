<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-phone text-success"></i> Agent Telecalling Lead Management Workspace</h4>
                        <hr class="hr-panel-heading" />

                        <!-- Agent Selector & Admin Actions -->
                        <div class="row mbot20">
                            <?php if (is_admin()) { ?>
                                <div class="col-md-4">
                                    <label><strong>Viewing Leads for Agent:</strong></label>
                                    <select class="selectpicker" data-width="100%" onchange="location = '<?php echo admin_url('india_hr_payroll/agent_leads?agent_id='); ?>' + this.value;">
                                        <?php 
                                        $current_agent_name = 'Agent';
                                        foreach ($staff_members as $member) { 
                                            if ($member['staffid'] == $selected_agent) {
                                                $current_agent_name = $member['firstname'] . ' ' . $member['lastname'];
                                            }
                                        ?>
                                            <option value="<?php echo $member['staffid']; ?>" <?php if ($member['staffid'] == $selected_agent) echo 'selected'; ?>>
                                                <?php echo $member['firstname'] . ' ' . $member['lastname'] . ' (' . $member['email'] . ')'; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-8 text-right" style="margin-top: 25px;">
                                    <!-- Delete All For Agent Form -->
                                    <?php echo form_open(admin_url('india_hr_payroll/bulk_delete_leads'), ['style' => 'display:inline-block;']); ?>
                                        <input type="hidden" name="delete_all_agent" value="1">
                                        <input type="hidden" name="agent_id" value="<?php echo $selected_agent; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('WARNING: Are you sure you want to delete ALL leads assigned to <?php echo htmlspecialchars($current_agent_name); ?>? This cannot be undone.');">
                                            <i class="fa fa-trash"></i> Delete ALL Leads for <?php echo htmlspecialchars($current_agent_name); ?>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Bulk Delete Selected Form -->
                        <?php if (is_admin()) { ?>
                            <?php echo form_open(admin_url('india_hr_payroll/bulk_delete_leads'), ['id' => 'bulk_delete_form']); ?>
                            <input type="hidden" name="agent_id" value="<?php echo $selected_agent; ?>">
                            <div class="mbot15" id="bulk_actions_bar" style="display: none; background: #fee2e2; border: 1px solid #fca5a5; padding: 10px 15px; border-radius: 4px;">
                                <strong><span id="selected_count">0</span> Leads Selected</strong> &nbsp;&nbsp;
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected leads?');">
                                    <i class="fa fa-trash"></i> Delete Selected Leads
                                </button>
                            </div>
                        <?php } ?>

                        <!-- Telecalling Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped dt-table">
                                <thead>
                                    <tr>
                                        <?php if (is_admin()) { ?>
                                            <th style="width: 35px; text-align: center;"><input type="checkbox" id="select_all_leads"></th>
                                        <?php } ?>
                                        <th>Company Name</th>
                                        <th>Mobile Number</th>
                                        <th>Category</th>
                                        <th>Main Feature to Explain</th>
                                        <th>What to Say on the Call (Pitch)</th>
                                        <th>Call Status / Notes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leads as $lead) { ?>
                                        <tr>
                                            <?php if (is_admin()) { ?>
                                                <td style="text-align: center;">
                                                    <input type="checkbox" name="lead_ids[]" value="<?php echo $lead->id; ?>" class="lead-checkbox">
                                                </td>
                                            <?php } ?>
                                            <td><strong><?php echo !empty($lead->company) ? $lead->company : $lead->name; ?></strong></td>
                                            <td>
                                                <a href="tel:<?php echo $lead->phonenumber; ?>" class="btn btn-info btn-xs" style="font-weight: bold;"><i class="fa fa-phone"></i> <?php echo $lead->phonenumber; ?></a>
                                            </td>
                                            <td>
                                                <span class="label label-primary" style="font-size: 11px;"><?php echo !empty($lead->category) ? $lead->category : 'General'; ?></span>
                                            </td>
                                            <td style="max-width: 200px;">
                                                <div style="background: #f0fdf4; border-left: 3px solid #16a34a; padding: 6px 8px; font-size: 12px; color: #166534;">
                                                    <?php echo !empty($lead->main_feature) ? $lead->main_feature : '<i>Standard Service</i>'; ?>
                                                </div>
                                            </td>
                                            <td style="max-width: 250px;">
                                                <div style="background: #eff6ff; border-left: 3px solid #2563eb; padding: 6px 8px; font-size: 11px; color: #1e40af; line-height: 1.4;">
                                                    <?php echo !empty($lead->pitch_script) ? $lead->pitch_script : '<i>Pitch standard company offerings</i>'; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_name = 'New Lead';
                                                $status_color = '#0284c7';
                                                foreach ($statuses as $st) {
                                                    if ($st->id == $lead->status) {
                                                        $status_name = $st->name;
                                                        $status_color = $st->color;
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <span class="label" style="background-color: <?php echo $status_color; ?>; color: #fff; font-size: 11px; padding: 4px 6px;"><?php echo $status_name; ?></span>
                                                <div class="text-muted" style="font-size: 11px; margin-top: 4px;">
                                                    <?php echo !empty($lead->description) ? $lead->description : '<i>No call notes yet</i>'; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#update_lead_<?php echo $lead->id; ?>"><i class="fa fa-phone"></i> Update Call</button>

                                                <?php if (is_admin()) { ?>
                                                    <a href="<?php echo admin_url('india_hr_payroll/delete_lead/' . $lead->id); ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete Lead #<?php echo $lead->id; ?>?');" title="Delete Single Lead"><i class="fa fa-trash"></i></a>
                                                <?php } ?>

                                                <!-- Modal for Lead Update -->
                                                <div class="modal fade" id="update_lead_<?php echo $lead->id; ?>" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <?php echo form_open(admin_url('india_hr_payroll/agent_leads?agent_id=' . $selected_agent)); ?>
                                                            <input type="hidden" name="action" value="update_lead">
                                                            <input type="hidden" name="lead_id" value="<?php echo $lead->id; ?>">
                                                            
                                                            <div class="modal-header" style="background-color: #f8fafc;">
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                <h4 class="modal-title">Telecalling: <?php echo !empty($lead->company) ? $lead->company : $lead->name; ?> (<?php echo $lead->phonenumber; ?>)</h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                
                                                                <!-- Script & Feature Reference Box -->
                                                                <?php if (!empty($lead->main_feature) || !empty($lead->pitch_script)) { ?>
                                                                    <div class="alert alert-info" style="font-size: 12px;">
                                                                        <?php if (!empty($lead->main_feature)) { ?>
                                                                            <p><strong>🎯 Main Feature to Explain:</strong> <?php echo $lead->main_feature; ?></p>
                                                                        <?php } ?>
                                                                        <?php if (!empty($lead->pitch_script)) { ?>
                                                                            <p><strong>🗣️ What to Say on the Call:</strong> <?php echo $lead->pitch_script; ?></p>
                                                                        <?php } ?>
                                                                    </div>
                                                                <?php } ?>

                                                                <div class="form-group">
                                                                    <label>Call Status Result:</label>
                                                                    <select name="status" class="form-control" required>
                                                                        <?php foreach ($statuses as $st) { ?>
                                                                            <option value="<?php echo $st->id; ?>" <?php if ($st->id == $lead->status) echo 'selected'; ?>><?php echo $st->name; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Call Status / Discussion Notes:</label>
                                                                    <textarea name="call_notes" class="form-control" rows="4" placeholder="Enter conversation details, client requirements, or follow-up notes..."><?php echo $lead->description; ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Call Log</button>
                                                            </div>
                                                            <?php echo form_close(); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (is_admin()) { ?>
                            <?php echo form_close(); ?>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('select_all_leads');
    var checkboxes = document.querySelectorAll('.lead-checkbox');
    var bulkBar = document.getElementById('bulk_actions_bar');
    var countSpan = document.getElementById('selected_count');

    function updateCount() {
        var checked = document.querySelectorAll('.lead-checkbox:checked').length;
        if (countSpan) countSpan.textContent = checked;
        if (bulkBar) {
            bulkBar.style.display = checked > 0 ? 'block' : 'none';
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) {
                cb.checked = selectAll.checked;
            });
            updateCount();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateCount);
    });
});
</script>
