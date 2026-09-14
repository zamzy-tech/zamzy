<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-calculator text-success"></i> Indian Salary & CTC Structure Configurator</h4>
                        <hr class="hr-panel-heading" />

                        <!-- Staff Selector -->
                        <div class="row mbot20">
                            <div class="col-md-4">
                                <label for="staff_select"><strong>Select Employee:</strong></label>
                                <select id="staff_select" class="selectpicker" data-width="100%" onchange="location = this.value;">
                                    <?php foreach ($staff_members as $member) { ?>
                                        <option value="<?php echo admin_url('india_hr_payroll/salary_structures/' . $member['staffid']); ?>" <?php if ($member['staffid'] == $selected_staff_id) echo 'selected'; ?>>
                                            <?php echo $member['firstname'] . ' ' . $member['lastname'] . ' (' . $member['email'] . ')'; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <?php echo form_open(admin_url('india_hr_payroll/salary_structures/' . $selected_staff_id)); ?>
                        <div class="row">
                            <!-- Earnings -->
                            <div class="col-md-6">
                                <div class="panel panel-info">
                                    <div class="panel-heading"><strong>Monthly Earnings Breakdown (Gross Salary)</strong></div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label>Basic Salary (₹)</label>
                                            <input type="number" step="0.01" name="basic_salary" class="form-control calc-gross" value="<?php echo $salary_structure->basic_salary; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>House Rent Allowance (HRA) (₹)</label>
                                            <input type="number" step="0.01" name="hra" class="form-control calc-gross" value="<?php echo $salary_structure->hra; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Special Allowance (₹)</label>
                                            <input type="number" step="0.01" name="special_allowance" class="form-control calc-gross" value="<?php echo $salary_structure->special_allowance; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Other Allowances / Bonus (₹)</label>
                                            <input type="number" step="0.01" name="other_allowances" class="form-control calc-gross" value="<?php echo $salary_structure->other_allowances; ?>">
                                        </div>
                                        <hr />
                                        <h4><strong>Total Monthly Gross: ₹<span id="gross_total"><?php echo number_format($salary_structure->gross_monthly, 2); ?></span></strong></h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Statutory Rules -->
                            <div class="col-md-6">
                                <div class="panel panel-warning">
                                    <div class="panel-heading"><strong>Statutory Rules (EPFO, ESIC, PT, TDS)</strong></div>
                                    <div class="panel-body">
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" name="pf_applicable" value="1" id="pf_app" <?php if ($salary_structure->pf_applicable) echo 'checked'; ?>>
                                            <label for="pf_app"><strong>EPFO Provident Fund (PF) Applicable</strong> (12% of Basic Salary)</label>
                                        </div>
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" name="esi_applicable" value="1" id="esi_app" <?php if ($salary_structure->esi_applicable) echo 'checked'; ?>>
                                            <label for="esi_app"><strong>ESIC State Insurance Applicable</strong> (0.75% Employee / 3.25% Employer if Gross ≤ ₹21,000)</label>
                                        </div>
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" name="pt_applicable" value="1" id="pt_app" <?php if ($salary_structure->pt_applicable) echo 'checked'; ?>>
                                            <label for="pt_app"><strong>Professional Tax (PT) Applicable</strong> (Andhra Pradesh State Slabs)</label>
                                        </div>
                                        <div class="form-group mtop15">
                                            <label>Monthly Tax / TDS Deduction (₹)</label>
                                            <input type="number" step="0.01" name="tds_monthly" class="form-control" value="<?php echo $salary_structure->tds_monthly; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right mtop15">
                            <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save Salary Structure</button>
                        </div>
                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
