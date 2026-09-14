<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-play-circle text-danger"></i> Execute Monthly Payroll Run (India Rules)</h4>
                        <hr class="hr-panel-heading" />

                        <?php echo form_open(admin_url('india_hr_payroll/run_payroll')); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Month:</label>
                                    <select name="month" class="form-control">
                                        <?php 
                                        $current_m = date('n');
                                        for ($m = 1; $m <= 12; $m++) { 
                                            $dateObj = DateTime::createFromFormat('!m', $m);
                                            $monthName = $dateObj->format('F');
                                        ?>
                                            <option value="<?php echo $m; ?>" <?php if ($m == $current_m) echo 'selected'; ?>><?php echo $monthName; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Year:</label>
                                    <select name="year" class="form-control">
                                        <?php 
                                        $current_y = date('Y');
                                        for ($y = $current_y - 2; $y <= $current_y + 1; $y++) { 
                                        ?>
                                            <option value="<?php echo $y; ?>" <?php if ($y == $current_y) echo 'selected'; ?>><?php echo $y; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>Automated Statutory Execution:</strong> Executing payroll will automatically calculate Basic, HRA, Allowances, PF (12%), ESI (0.75%), Professional Tax (AP Slabs), and Net Payable Salary for all active employees with a configured salary structure.
                        </div>

                        <button type="submit" class="btn btn-danger btn-lg"><i class="fa fa-cogs"></i> Process & Generate Monthly Payslips</button>
                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
