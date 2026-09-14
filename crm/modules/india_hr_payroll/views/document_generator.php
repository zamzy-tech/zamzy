<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-file-pdf-o text-warning"></i> HR Document & Letter Generator</h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <!-- Joining Letter -->
                            <div class="col-md-6">
                                <div class="panel panel-info">
                                    <div class="panel-heading"><strong>Generate Appointment / Joining Letter</strong></div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label>Select Employee:</label>
                                            <select id="gen_staff_1" class="selectpicker" data-width="100%">
                                                <?php foreach ($staff_members as $member) { ?>
                                                    <option value="<?php echo $member['staffid']; ?>"><?php echo $member['firstname'] . ' ' . $member['lastname']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <button class="btn btn-info btn-block" onclick="var sid = $('#gen_staff_1').val(); window.open('<?php echo admin_url('india_hr_payroll/print_joining_letter/'); ?>' + sid, '_blank');"><i class="fa fa-file-pdf-o"></i> Generate & Open Joining Letter (PDF)</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Offer Letter -->
                            <div class="col-md-6">
                                <div class="panel panel-primary">
                                    <div class="panel-heading"><strong>Generate Official Offer Letter & CTC Schedule</strong></div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label>Select Employee / Candidate:</label>
                                            <select id="gen_staff_2" class="selectpicker" data-width="100%">
                                                <?php foreach ($staff_members as $member) { ?>
                                                    <option value="<?php echo $member['staffid']; ?>"><?php echo $member['firstname'] . ' ' . $member['lastname']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <button class="btn btn-primary btn-block" onclick="var sid = $('#gen_staff_2').val(); window.open('<?php echo admin_url('india_hr_payroll/print_offer_letter/'); ?>' + sid, '_blank');"><i class="fa fa-file-pdf-o"></i> Generate & Open Offer Letter (PDF)</button>
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
