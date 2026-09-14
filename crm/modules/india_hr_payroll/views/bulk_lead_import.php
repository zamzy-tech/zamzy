<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-cloud-upload text-primary"></i> Admin Bulk Lead Upload & Telecalling Campaign Assignment</h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-info">
                                    <div class="panel-heading"><strong>1. Upload CSV File & Assign to Telecalling Agent</strong></div>
                                    <div class="panel-body">
                                        <?php echo form_open_multipart(admin_url('india_hr_payroll/bulk_lead_import')); ?>
                                        
                                        <div class="form-group">
                                            <label>Select Target Telecalling Agent:</label>
                                            <select name="assigned_agent" class="selectpicker" data-width="100%" required>
                                                <?php foreach ($staff_members as $member) { ?>
                                                    <option value="<?php echo $member['staffid']; ?>"><?php echo $member['firstname'] . ' ' . $member['lastname'] . ' (' . $member['email'] . ')'; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Initial Lead Status:</label>
                                            <select name="lead_status" class="form-control">
                                                <?php foreach ($statuses as $st) { ?>
                                                    <option value="<?php echo $st->id; ?>" <?php if ($st->name == 'New Lead') echo 'selected'; ?>><?php echo $st->name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Select Leads CSV File (.csv):</label>
                                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                                        </div>

                                        <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-upload"></i> Upload Leads & Assign to Agent</button>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><strong>2. Download Sample CSV Template</strong></div>
                                    <div class="panel-body">
                                        <p>Download our official sample CSV format to ensure your leads file has the exact required columns:</p>
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong>Column 1:</strong> Company Name (e.g. Sri Sai Enterprises)</li>
                                            <li class="list-group-item"><strong>Column 2:</strong> Mobile Number (e.g. 9876543210)</li>
                                            <li class="list-group-item"><strong>Column 3:</strong> Category (e.g. Loans & Website)</li>
                                            <li class="list-group-item"><strong>Column 4:</strong> Main Feature to Explain (e.g. Instant Disbursal at 8.5% ROI)</li>
                                            <li class="list-group-item"><strong>Column 5:</strong> What to Say on the Call (e.g. Hello Sir, calling from Credifix regarding business loan...)</li>
                                            <li class="list-group-item"><strong>Column 6:</strong> Call Status / Notes (e.g. Interested in 10 Lakhs loan)</li>
                                        </ul>
                                        <a href="<?php echo admin_url('india_hr_payroll/download_sample_csv'); ?>" class="btn btn-primary btn-block btn-lg"><i class="fa fa-download"></i> Download Sample CSV File</a>
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
