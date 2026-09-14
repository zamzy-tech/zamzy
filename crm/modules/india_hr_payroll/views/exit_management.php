<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-sign-out text-danger"></i> Exit Management & Relieving / F&F Settlement</h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-danger">
                                    <div class="panel-heading"><strong>Process Employee Exit & Full & Final (F&F) Settlement</strong></div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label>Select Exiting Employee:</label>
                                            <select id="exit_staff" class="selectpicker" data-width="100%">
                                                <?php foreach ($staff_members as $member) { ?>
                                                    <option value="<?php echo $member['staffid']; ?>"><?php echo $member['firstname'] . ' ' . $member['lastname']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Resignation / Relieving Date:</label>
                                            <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Encashment / Notice Pay Adjustment (₹):</label>
                                            <input type="number" step="0.01" class="form-control" value="0.00">
                                        </div>
                                        <button class="btn btn-danger btn-block" onclick="var sid = $('#exit_staff').val(); window.open('<?php echo admin_url('india_hr_payroll/print_relieving_letter/'); ?>' + sid, '_blank');"><i class="fa fa-file-pdf-o"></i> Generate & Open Relieving Letter (PDF)</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
