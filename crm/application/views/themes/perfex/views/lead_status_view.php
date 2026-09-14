<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container public-lead-status-container" style="margin-top: 60px; margin-bottom: 60px;">
    
    <?php if (!$authenticated) { ?>
        <!-- Password Verification Card -->
        <div class="row">
            <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                <div class="panel_s" style="border: none; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); background: #ffffff; overflow: hidden; margin-top: 20px;">
                    <!-- Top accent bar -->
                    <div style="height: 6px; background: linear-gradient(90deg, #1e3a8a, #3b82f6);"></div>
                    
                    <div class="panel-body" style="padding: 40px 35px; text-align: center;">
                        <!-- Icon representation -->
                        <div style="width: 70px; height: 70px; border-radius: 50%; background: #eff6ff; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 25px; border: 1px solid #dbeafe;">
                            <i class="fa fa-lock" style="font-size: 32px; color: #3b82f6;"></i>
                        </div>
                        
                        <h2 style="margin-top: 0; margin-bottom: 10px; font-weight: 700; color: #1e3a8a; font-size: 24px;">Secure Status Access</h2>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 30px; line-height: 1.5;">This lead status page is password protected. Please enter the tracking password to view the current status and updates.</p>
                        
                        <?php if (!empty($password_error)) { ?>
                            <div class="alert alert-danger" style="border-radius: 8px; border: none; background-color: #fef2f2; color: #b91c1c; padding: 12px 15px; margin-bottom: 20px; font-size: 13.5px; text-align: left; font-weight: 500;">
                                <i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo html_escape($password_error); ?>
                            </div>
                        <?php } ?>
                        
                        <?php echo form_open($this->uri->uri_string()); ?>
                            <div class="form-group" style="text-align: left; margin-bottom: 25px;">
                                <label for="password" style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Enter Access Password</label>
                                <div class="input-group" style="border-radius: 8px; overflow: hidden; border: 1px solid #d1d5db; transition: border-color 0.2s;">
                                    <span class="input-group-addon" style="background: #f9fafb; border: none; color: #9ca3af; padding-left: 15px; padding-right: 10px;">
                                        <i class="fa fa-key"></i>
                                    </span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required style="border: none; height: 46px; font-size: 15px; padding-left: 10px; box-shadow: none;">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block" style="height: 48px; border-radius: 8px; font-size: 16px; font-weight: 600; background: linear-gradient(135deg, #1e3a8a, #3b82f6); border: none; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25); transition: all 0.2s;">
                                Unlock Status Page
                            </button>
                        <?php echo form_close(); ?>
                        
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 25px;">
                    <p style="font-size: 12px; color: #9ca3af; margin-bottom: 0;">Powered by Credifix Lead Tracking Portal</p>
                </div>
            </div>
        </div>
        
    <?php } else { ?>
        
        <!-- Authenticated Status View -->
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                
                <!-- Main Card -->
                <div class="panel_s" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: #ffffff; overflow: hidden;">
                    <!-- Card Header -->
                    <div class="panel-heading" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); padding: 30px 40px; border: none; color: #ffffff;">
                        <div class="row">
                            <div class="col-md-8">
                                <span style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.85;">Lead Tracking System</span>
                                <h2 style="margin-top: 5px; margin-bottom: 0; font-weight: 700; color: #ffffff; font-size: 28px; line-height: 1.2;">
                                    <?php echo html_escape($lead->name); ?>
                                </h2>
                            </div>
                            <div class="col-md-4 text-right" style="margin-top: 10px;">
                                <span style="display: inline-block; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 30px; font-weight: 600; font-size: 14px; border: 1px solid rgba(255,255,255,0.3); text-shadow: 0 1px 2px rgba(0,0,0,0.15);">
                                    Current Status: <?php echo html_escape($status_name); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel-body" style="padding: 40px;">
                        <!-- Overview Grid -->
                        <div class="row" style="margin-bottom: 40px;">
                            <div class="col-sm-4" style="border-right: 1px solid #f0f0f0; margin-bottom: 20px;">
                                <h5 style="color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px;">Email Address</h5>
                                <p style="font-size: 15px; font-weight: 500; color: #1f2937; margin-bottom: 0;">
                                    <?php echo !empty($lead->email) ? html_escape($lead->email) : '<span style="color:#9ca3af;">N/A</span>'; ?>
                                </p>
                            </div>
                            <div class="col-sm-4" style="border-right: 1px solid #f0f0f0; margin-bottom: 20px;">
                                <h5 style="color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px;">Phone Number</h5>
                                <p style="font-size: 15px; font-weight: 500; color: #1f2937; margin-bottom: 0;">
                                    <?php echo !empty($lead->phonenumber) ? html_escape($lead->phonenumber) : '<span style="color:#9ca3af;">N/A</span>'; ?>
                                </p>
                            </div>
                            <div class="col-sm-4" style="margin-bottom: 20px;">
                                <h5 style="color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px;">Date Assigned</h5>
                                <p style="font-size: 15px; font-weight: 500; color: #1f2937; margin-bottom: 0;">
                                    <?php echo _d($lead->dateadded); ?>
                                </p>
                            </div>
                        </div>

                        <hr style="border-top: 1px solid #f0f0f0; margin-bottom: 40px;">

                        <div class="row">
                            <!-- Left Column: Status Timeline -->
                            <div class="col-md-7" style="margin-bottom: 30px;">
                                <h3 style="font-weight: 700; color: #1e3a8a; font-size: 18px; margin-top: 0; margin-bottom: 25px;">
                                    <i class="fa fa-history" style="margin-right: 8px;"></i> Status Updates History
                                </h3>
                                
                                <?php if (empty($status_history)) { ?>
                                    <div class="alert alert-info" style="border-radius: 8px; border: none; background-color: #eff6ff; color: #1e40af; padding: 15px 20px;">
                                        No status changes have been logged yet. The current status is: <strong><?php echo html_escape($status_name); ?></strong>.
                                    </div>
                                <?php } else { ?>
                                    <div class="timeline-wrapper" style="border-left: 2px solid #e5e7eb; padding-left: 25px; margin-left: 10px;">
                                        <?php foreach ($status_history as $index => $history) { ?>
                                            <div class="timeline-item" style="position: relative; margin-bottom: 30px;">
                                                <!-- Marker Dot -->
                                                <div style="position: absolute; left: -32px; top: 2px; width: 14px; height: 14px; border-radius: 50%; background: <?php echo ($index === 0) ? '#3b82f6' : '#9ca3af'; ?>; border: 3px solid #ffffff; box-shadow: 0 0 0 2px <?php echo ($index === 0) ? 'rgba(59,130,246,0.3)' : 'rgba(156,163,175,0.2)'; ?>;"></div>
                                                
                                                <div style="font-size: 12px; color: #6b7280; font-weight: 600; margin-bottom: 4px;">
                                                    <?php echo _dt($history['changed_at']); ?>
                                                </div>
                                                <div style="font-size: 15px; font-weight: 600; color: #111827; margin-bottom: 4px;">
                                                    Status updated to <span style="color: #3b82f6; font-weight: 700;"><?php echo html_escape($history['new_status']); ?></span>
                                                </div>
                                                <div style="font-size: 13px; color: #4b5563;">
                                                    Previous status was <em><?php echo html_escape($history['old_status']); ?></em>
                                                </div>
                                                <?php if (!empty($history['proof_path'])) { ?>
                                                    <div style="margin-top: 10px;">
                                                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 600; color: #6b7280; display: block; margin-bottom: 4px;">Verification Proof:</span>
                                                        <a href="<?php echo base_url($history['proof_path']); ?>" target="_blank" class="btn btn-default btn-xs" style="border-radius: 4px; padding: 4px 10px; font-weight: 600; border-color: #d1d5db; color: #374151;">
                                                            <i class="fa fa-file-pdf-o" style="margin-right: 5px; color: #dc2626;"></i> View Attached Proof
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <!-- Right Column: Loan Details / Information -->
                            <div class="col-md-5">
                                <h3 style="font-weight: 700; color: #1e3a8a; font-size: 18px; margin-top: 0; margin-bottom: 25px;">
                                    <i class="fa fa-info-circle" style="margin-right: 8px;"></i> Loan details
                                </h3>
                                
                                <?php if (!$loan_details) { ?>
                                    <div class="alert alert-warning" style="border-radius: 8px; border: none; background-color: #fffbef; color: #92400e; padding: 15px 20px;">
                                        No loan details are currently registered for this lead.
                                    </div>
                                <?php } else { ?>
                                    <div style="background-color: #f9fafb; border: 1px solid #f3f4f6; border-radius: 8px; padding: 20px;">
                                        <table class="table" style="margin-bottom: 0;">
                                            <tbody>
                                                <?php if (!empty($loan_details->loan_type)) { ?>
                                                    <tr>
                                                        <td style="border-top: none; font-weight: 600; color: #4b5563; padding-left: 0; width: 45%;">Loan Type:</td>
                                                        <td style="border-top: none; color: #111827; font-weight: 500;"><?php echo html_escape($loan_details->loan_type); ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if (!empty($loan_details->profession_type)) { ?>
                                                    <tr>
                                                        <td style="font-weight: 600; color: #4b5563; padding-left: 0;">Profession Type:</td>
                                                        <td style="color: #111827; font-weight: 500;"><?php echo html_escape($loan_details->profession_type); ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if (!empty($loan_details->co_applicant_name)) { ?>
                                                    <tr>
                                                        <td style="font-weight: 600; color: #4b5563; padding-left: 0;">Co-Applicant:</td>
                                                        <td style="color: #111827; font-weight: 500;"><?php echo html_escape($loan_details->co_applicant_name); ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if (!empty($loan_details->co_applicant_mobile)) { ?>
                                                    <tr>
                                                        <td style="font-weight: 600; color: #4b5563; padding-left: 0;">Co-App Mobile:</td>
                                                        <td style="color: #111827; font-weight: 500;"><?php echo html_escape($loan_details->co_applicant_mobile); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                
                                <!-- Support Section -->
                                <div style="margin-top: 30px; padding: 20px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                    <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin-top: 0; margin-bottom: 8px;">Need Assistance?</h4>
                                    <p style="font-size: 12px; color: #64748b; margin-bottom: 0; line-height: 1.5;">
                                        If you have any questions regarding your application status, please reach out to your designated representative or contact us directly.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
    <?php } ?>
</div>
