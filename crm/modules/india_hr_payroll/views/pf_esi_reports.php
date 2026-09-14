<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-university text-danger"></i> EPFO (PF), ESIC & Professional Tax Statutory Compliance Reports</h4>
                        <hr class="hr-panel-heading" />

                        <!-- Filter -->
                        <form method="get" action="<?php echo admin_url('india_hr_payroll/pf_esi_reports'); ?>">
                            <div class="row mbot20">
                                <div class="col-md-3">
                                    <label>Month:</label>
                                    <select name="month" class="form-control">
                                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                                            <option value="<?php echo $m; ?>" <?php if ($m == $month) echo 'selected'; ?>><?php echo date('F', mktime(0, 0, 0, $m, 10)); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Year:</label>
                                    <select name="year" class="form-control">
                                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++) { ?>
                                            <option value="<?php echo $y; ?>" <?php if ($y == $year) echo 'selected'; ?>><?php echo $y; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mtop25">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter Report</button>
                                </div>
                            </div>
                        </form>

                        <div class="row mbot20">
                            <div class="col-md-12 text-right">
                                <a href="javascript:window.print();" class="btn btn-default"><i class="fa fa-print"></i> Print Compliance Statement</a>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped dt-table">
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>PAN</th>
                                    <th>UAN (EPFO)</th>
                                    <th>ESIC IP</th>
                                    <th>Gross Wage</th>
                                    <th>PF Emp (12%)</th>
                                    <th>PF Employer (12%)</th>
                                    <th>ESI Emp (0.75%)</th>
                                    <th>ESI Employer (3.25%)</th>
                                    <th>Prof. Tax (PT)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $t_gross = $t_pf_emp = $t_pf_empr = $t_esi_emp = $t_esi_empr = $t_pt = 0;
                                foreach ($reports as $r) { 
                                    $t_gross += $r->gross_salary;
                                    $t_pf_emp += $r->pf_employee;
                                    $t_pf_empr += $r->pf_employer;
                                    $t_esi_emp += $r->esi_employee;
                                    $t_esi_empr += $r->esi_employer;
                                    $t_pt += $r->professional_tax;
                                ?>
                                    <tr>
                                        <td><strong><?php echo $r->firstname . ' ' . $r->lastname; ?></strong></td>
                                        <td><?php echo $r->pan_number ? $r->pan_number : 'N/A'; ?></td>
                                        <td><?php echo $r->uan_number ? $r->uan_number : 'N/A'; ?></td>
                                        <td><?php echo $r->esic_number ? $r->esic_number : 'N/A'; ?></td>
                                        <td>₹<?php echo number_format($r->gross_salary, 2); ?></td>
                                        <td>₹<?php echo number_format($r->pf_employee, 2); ?></td>
                                        <td>₹<?php echo number_format($r->pf_employer, 2); ?></td>
                                        <td>₹<?php echo number_format($r->esi_employee, 2); ?></td>
                                        <td>₹<?php echo number_format($r->esi_employer, 2); ?></td>
                                        <td>₹<?php echo number_format($r->professional_tax, 2); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr class="info">
                                    <th colspan="4">Total Statutory Filing Amount:</th>
                                    <th>₹<?php echo number_format($t_gross, 2); ?></th>
                                    <th>₹<?php echo number_format($t_pf_emp, 2); ?></th>
                                    <th>₹<?php echo number_format($t_pf_empr, 2); ?></th>
                                    <th>₹<?php echo number_format($t_esi_emp, 2); ?></th>
                                    <th>₹<?php echo number_format($t_esi_empr, 2); ?></th>
                                    <th>₹<?php echo number_format($t_pt, 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
