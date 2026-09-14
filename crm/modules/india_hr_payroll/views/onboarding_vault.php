<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-address-book text-primary"></i> Comprehensive Employee Profile, Terms & CTC Vault</h4>
                        <hr class="hr-panel-heading" />

                        <!-- Staff Selector -->
                        <div class="row mbot20">
                            <div class="col-md-4">
                                <label for="staff_select"><strong>Select Employee:</strong></label>
                                <select id="staff_select" class="selectpicker" data-width="100%" onchange="location = this.value;">
                                    <?php foreach ($staff_members as $member) { ?>
                                        <option value="<?php echo admin_url('india_hr_payroll/onboarding_vault/' . $member['staffid']); ?>" <?php if ($member['staffid'] == $selected_staff_id) echo 'selected'; ?>>
                                            <?php echo $member['firstname'] . ' ' . $member['lastname'] . ' (' . $member['email'] . ')'; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Employee Full Profile Form -->
                        <?php echo form_open(admin_url('india_hr_payroll/upload_document'), ['id' => 'full_employee_form']); ?>
                        <input type="hidden" name="staff_id" value="<?php echo $selected_staff_id; ?>">
                        
                        <!-- 1. Personal & Family Info -->
                        <div class="panel panel-info">
                            <div class="panel-heading"><strong>1. Personal & Family Information</strong></div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Father's / Mother's / Spouse's Name</label>
                                        <input type="text" name="father_mother_spouse_name" class="form-control" value="<?php echo $employee_details->father_mother_spouse_name; ?>" placeholder="Full Name of Relation">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Relation Type</label>
                                        <select name="relation_type" class="form-control">
                                            <option value="Father" <?php if ($employee_details->relation_type == 'Father') echo 'selected'; ?>>Father</option>
                                            <option value="Mother" <?php if ($employee_details->relation_type == 'Mother') echo 'selected'; ?>>Mother</option>
                                            <option value="Spouse" <?php if ($employee_details->relation_type == 'Spouse') echo 'selected'; ?>>Spouse</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Date of Birth (DOB)</label>
                                        <input type="date" name="dob" class="form-control" value="<?php echo $employee_details->dob; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Gender</label>
                                        <select name="gender" class="form-control">
                                            <option value="Male" <?php if ($employee_details->gender == 'Male') echo 'selected'; ?>>Male</option>
                                            <option value="Female" <?php if ($employee_details->gender == 'Female') echo 'selected'; ?>>Female</option>
                                            <option value="Other" <?php if ($employee_details->gender == 'Other') echo 'selected'; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mtop15">
                                    <div class="col-md-3">
                                        <label>Mobile Number</label>
                                        <input type="text" name="mobile_number" class="form-control" value="<?php echo $employee_details->mobile_number; ?>" placeholder="+91 9876543210">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Current Address</label>
                                        <textarea name="current_address" class="form-control" rows="2" placeholder="Present Residence Address"><?php echo $employee_details->current_address; ?></textarea>
                                    </div>
                                    <div class="col-md-5">
                                        <label>Permanent Address</label>
                                        <textarea name="permanent_address" class="form-control" rows="2" placeholder="Native / Permanent Residence Address"><?php echo $employee_details->permanent_address; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Employment, Monthly Salary & Annual CTC -->
                        <div class="panel panel-warning">
                            <div class="panel-heading"><strong>2. Employment, Monthly Salary & Annual CTC</strong></div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Monthly Salary / Gross (₹/month)</label>
                                        <input type="number" step="0.01" id="monthly_salary_input" name="monthly_salary" class="form-control" value="<?php echo number_format($employee_details->annual_ctc / 12, 2, '.', ''); ?>" placeholder="e.g. 25000">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Annual CTC (₹/year)</label>
                                        <input type="number" step="0.01" id="annual_ctc_input" name="annual_ctc" class="form-control" value="<?php echo $employee_details->annual_ctc; ?>" placeholder="e.g. 300000">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Date of Joining</label>
                                        <input type="date" name="joining_date" class="form-control" value="<?php echo $employee_details->joining_date; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Designation / Role</label>
                                        <input type="text" name="designation" class="form-control" value="<?php echo $employee_details->designation; ?>" placeholder="TELE CALLING / Engineer">
                                    </div>
                                </div>
                                <div class="row mtop15">
                                    <div class="col-md-3">
                                        <label>Department</label>
                                        <input type="text" name="department" class="form-control" value="<?php echo $employee_details->department; ?>" placeholder="LOANS AND WEBSITE">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Probation Period</label>
                                        <input type="text" name="probation_period" class="form-control" value="<?php echo $employee_details->probation_period; ?>" placeholder="3 Months">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Notice Period (Resignation)</label>
                                        <input type="text" name="notice_period" class="form-control" value="<?php echo $employee_details->notice_period; ?>" placeholder="30 Days">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Working Hours & Shift</label>
                                        <input type="text" name="working_hours" class="form-control" value="<?php echo $employee_details->working_hours; ?>" placeholder="9:30 AM - 6:30 PM (Mon - Sat)">
                                    </div>
                                </div>
                                <div class="row mtop15">
                                    <div class="col-md-12">
                                        <label><strong>Employment Terms & Conditions (Appears on Joining & Offer Letters)</strong></label>
                                        <textarea name="employment_terms" class="form-control" rows="5"><?php echo $employee_details->employment_terms; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Statutory Identifiers -->
                        <div class="panel panel-primary">
                            <div class="panel-heading"><strong>3. Statutory Identifiers (EPFO, ESIC, PAN, Bank)</strong></div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>PAN Card Number</label>
                                        <input type="text" name="pan_number" class="form-control" value="<?php echo $employee_details->pan_number; ?>" placeholder="ABCDE1234F">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Aadhaar Number</label>
                                        <input type="text" name="aadhaar_number" class="form-control" value="<?php echo $employee_details->aadhaar_number; ?>" placeholder="1234 5678 9012">
                                    </div>
                                    <div class="col-md-3">
                                        <label>EPFO UAN Number</label>
                                        <input type="text" name="uan_number" class="form-control" value="<?php echo $employee_details->uan_number; ?>" placeholder="100123456789">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Previous PF Member ID</label>
                                        <input type="text" name="previous_pf_number" class="form-control" value="<?php echo $employee_details->previous_pf_number; ?>" placeholder="AP/HYD/0012345/000/0001">
                                    </div>
                                </div>
                                <div class="row mtop15">
                                    <div class="col-md-3">
                                        <label>ESIC IP Number</label>
                                        <input type="text" name="esic_number" class="form-control" value="<?php echo $employee_details->esic_number; ?>" placeholder="31001234560000001">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control" value="<?php echo $employee_details->bank_name; ?>" placeholder="HDFC / SBI / ICICI">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Bank Account Number</label>
                                        <input type="text" name="bank_account_no" class="form-control" value="<?php echo $employee_details->bank_account_no; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>IFSC Code</label>
                                        <input type="text" name="ifsc_code" class="form-control" value="<?php echo $employee_details->ifsc_code; ?>" placeholder="HDFC0001234">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Nominee Details -->
                        <div class="panel panel-success">
                            <div class="panel-heading"><strong>4. Nominee & Family Beneficiary Details</strong></div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Nominee Full Name</label>
                                        <input type="text" name="nominee_name" class="form-control" value="<?php echo $employee_details->nominee_name; ?>" placeholder="Nominee Name">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Nominee Relationship</label>
                                        <input type="text" name="nominee_relation" class="form-control" value="<?php echo $employee_details->nominee_relation; ?>" placeholder="Spouse / Father / Mother">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Nominee DOB</label>
                                        <input type="date" name="nominee_dob" class="form-control" value="<?php echo $employee_details->nominee_dob; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Nominee Aadhaar</label>
                                        <input type="text" name="nominee_aadhaar" class="form-control" value="<?php echo $employee_details->nominee_aadhaar; ?>">
                                    </div>
                                </div>
                                <div class="row mtop15">
                                    <div class="col-md-4">
                                        <label>Emergency Contact Person Name</label>
                                        <input type="text" name="emergency_contact_name" class="form-control" value="<?php echo $employee_details->emergency_contact_name; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Emergency Contact Phone</label>
                                        <input type="text" name="emergency_contact_phone" class="form-control" value="<?php echo $employee_details->emergency_contact_phone; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label>State Jurisdiction</label>
                                        <input type="text" name="state" class="form-control" value="<?php echo $employee_details->state; ?>">
                                    </div>
                                </div>
                                <div class="mtop20 text-right">
                                    <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save Employee Profile & Terms</button>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>

                        <hr />
                        <h3>16 Standardized Document Slots</h3>

                        <!-- Document Upload Grid -->
                        <div class="row">
                            <?php 
                            $docs_by_type = [];
                            foreach ($uploaded_docs as $doc) {
                                $docs_by_type[$doc->doc_type][] = $doc;
                            }

                            foreach ($document_slots as $slot_key => $slot_info) { 
                                $has_docs = isset($docs_by_type[$slot_key]) && count($docs_by_type[$slot_key]) > 0;
                            ?>
                                <div class="col-md-6 mbot20">
                                    <div class="panel panel-<?php echo $has_docs ? 'success' : 'warning'; ?>" style="min-height: 220px;">
                                        <div class="panel-heading">
                                            <strong><?php echo $slot_info['name']; ?></strong>
                                            <span class="label label-default pull-right"><?php echo $slot_info['stage']; ?></span>
                                        </div>
                                        <div class="panel-body">
                                            <?php if ($has_docs) { ?>
                                                <p><i class="fa fa-check-circle text-success"></i> <strong>Uploaded Files:</strong></p>
                                                <ul class="list-group">
                                                    <?php foreach ($docs_by_type[$slot_key] as $udoc) { ?>
                                                        <li class="list-group-item">
                                                            <a href="<?php echo base_url($udoc->file_path); ?>" target="_blank"><i class="fa fa-file-text-o"></i> <?php echo $udoc->file_name; ?></a>
                                                            <span class="text-muted text-small">(<?php echo date('d M Y', strtotime($udoc->uploaded_at)); ?>)</span>
                                                            <a href="<?php echo admin_url('india_hr_payroll/delete_document/' . $udoc->id . '/' . $selected_staff_id); ?>" class="text-danger pull-right" onclick="return confirm('Delete document?');"><i class="fa fa-trash"></i></a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            <?php } else { ?>
                                                <p class="text-muted"><i>No file uploaded yet.</i></p>
                                            <?php } ?>

                                            <!-- Upload Form -->
                                            <?php echo form_open_multipart(admin_url('india_hr_payroll/upload_document')); ?>
                                                <input type="hidden" name="staff_id" value="<?php echo $selected_staff_id; ?>">
                                                <input type="hidden" name="doc_type" value="<?php echo $slot_key; ?>">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <input type="file" name="file" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-upload"></i> Upload</button>
                                                    </div>
                                                </div>
                                            <?php echo form_close(); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var monthlyInput = document.getElementById('monthly_salary_input');
    var annualInput = document.getElementById('annual_ctc_input');

    if (monthlyInput && annualInput) {
        monthlyInput.addEventListener('input', function() {
            var mVal = parseFloat(monthlyInput.value) || 0;
            annualInput.value = (mVal * 12).toFixed(2);
        });

        annualInput.addEventListener('input', function() {
            var aVal = parseFloat(annualInput.value) || 0;
            monthlyInput.value = (aVal / 12).toFixed(2);
        });
    }
});
</script>
