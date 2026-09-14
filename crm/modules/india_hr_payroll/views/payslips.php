<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons mbot20">
                            <a href="<?php echo admin_url('india_hr_payroll/attendance'); ?>" class="btn btn-info pull-right mleft10"><i class="fa fa-calendar-check-o"></i> Attendance Register</a>
                            <a href="<?php echo admin_url('india_hr_payroll/run_payroll'); ?>" class="btn btn-primary pull-right"><i class="fa fa-calculator"></i> Run New Monthly Payroll</a>
                            <h4 class="no-margin"><i class="fa fa-file-text-o text-primary"></i> Generated Payslip Registry</h4>
                        </div>
                        <hr class="hr-panel-heading" />

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped dt-table">
                                <thead>
                                    <tr>
                                        <th>Payslip #</th>
                                        <th>Employee Name</th>
                                        <th>Month / Year</th>
                                        <th>Payable Days</th>
                                        <th>Gross Salary</th>
                                        <th>PF (Emp)</th>
                                        <th>ESI (Emp)</th>
                                        <th>PT</th>
                                        <th>TDS</th>
                                        <th>Net Salary</th>
                                        <th>Generated Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payslips as $p) { ?>
                                        <tr>
                                            <td><strong><?php echo $p->payslip_number; ?></strong></td>
                                            <td><?php echo $p->firstname . ' ' . $p->lastname; ?></td>
                                            <td><span class="label label-default"><?php echo date('F Y', mktime(0,0,0, $p->month, 10, $p->year)); ?></span></td>
                                            <td><span class="label label-info"><?php echo $p->attendance_days; ?> Days</span></td>
                                            <td>₹<?php echo number_format($p->gross_salary, 2); ?></td>
                                            <td>₹<?php echo number_format($p->pf_employee, 2); ?></td>
                                            <td>₹<?php echo number_format($p->esi_employee, 2); ?></td>
                                            <td>₹<?php echo number_format($p->professional_tax, 2); ?></td>
                                            <td>₹<?php echo number_format($p->tds, 2); ?></td>
                                            <td><strong class="text-success">₹<?php echo number_format($p->net_salary, 2); ?></strong></td>
                                            <td><?php echo date('d M Y', strtotime($p->created_at)); ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('india_hr_payroll/print_payslip/' . $p->id); ?>" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-print"></i> View / Print PDF</a>
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
    </div>
</div>
<?php init_tail(); ?>
