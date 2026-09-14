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
                        
                        <h2 style="margin-top: 0; margin-bottom: 10px; font-weight: 700; color: #1e3a8a; font-size: 24px;">Secure Status Dashboard</h2>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 30px; line-height: 1.5;">This dashboard is password protected. Please enter the tracking password to view all converted leads status.</p>
                        
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
                                Unlock Dashboard
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
        
        <!-- Authenticated Dashboard View -->
        <div class="row">
            <div class="col-md-12">
                
                <!-- Main Card -->
                <div class="panel_s" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: #ffffff; overflow: hidden;">
                    <!-- Card Header -->
                    <div class="panel-heading" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); padding: 30px 40px; border: none; color: #ffffff;">
                        <span style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.85;">Credifix Client Dashboard</span>
                        <h2 style="margin-top: 5px; margin-bottom: 0; font-weight: 700; color: #ffffff; font-size: 28px; line-height: 1.2;">
                            Converted Leads Tracking Portal
                        </h2>
                    </div>
                    
                    <div class="panel-body" style="padding: 40px;">
                        
                        <!-- Search & Filters -->
                        <div class="row" style="margin-bottom: 30px;">
                            <div class="col-md-6 col-sm-8">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div class="input-group" style="border-radius: 8px; overflow: hidden; border: 1px solid #d1d5db;">
                                        <span class="input-group-addon" style="background: #f9fafb; border: none; color: #9ca3af;">
                                            <i class="fa fa-search"></i>
                                        </span>
                                        <input type="text" id="search-input" class="form-control" placeholder="Search leads by name or phone..." style="border: none; height: 44px; font-size: 15px; box-shadow: none;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-4 text-right" style="margin-top: 10px;">
                                <span style="font-weight: 600; color: #6b7280; font-size: 14px;">
                                    Total Leads: <strong style="color: #1e3a8a;"><?php echo count($leads); ?></strong>
                                </span>
                            </div>
                        </div>

                        <!-- Leads Table -->
                        <div class="table-responsive" style="border: none;">
                            <table class="table" id="leads-tracking-table" style="margin-bottom: 0;">
                                <thead>
                                    <tr style="background-color: #f8fafc; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0;">
                                        <th style="padding: 15px; border-bottom: none; width: 10%;">ID</th>
                                        <th style="padding: 15px; border-bottom: none; width: 30%;">Name</th>
                                        <th style="padding: 15px; border-bottom: none; width: 20%;">Phone</th>
                                        <th style="padding: 15px; border-bottom: none; width: 20%;">Loan Type</th>
                                        <th style="padding: 15px; border-bottom: none; width: 20%;">Status</th>
                                        <th style="padding: 15px; border-bottom: none; width: 10%; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($leads)) { ?>
                                        <tr>
                                            <td colspan="6" style="padding: 30px; text-align: center; color: #94a3b8;">
                                                No converted leads found.
                                            </td>
                                        </tr>
                                    <?php } else { ?>
                                        <?php foreach ($leads as $lead) { 
                                            $status_color = $lead['status_color'] ?: '#777777';
                                        ?>
                                            <tr class="lead-row" style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s;">
                                                <td style="padding: 18px 15px; vertical-align: middle; color: #64748b; font-weight: 600;">
                                                    #<?php echo $lead['id']; ?>
                                                </td>
                                                <td style="padding: 18px 15px; vertical-align: middle; font-weight: 600; color: #1e293b;">
                                                    <a href="<?php echo $lead['secure_link']; ?>" style="color: #1e293b; text-decoration: none;" class="lead-name-link">
                                                        <?php echo html_escape($lead['name']); ?>
                                                    </a>
                                                </td>
                                                <td style="padding: 18px 15px; vertical-align: middle; color: #475569;">
                                                    <?php echo !empty($lead['phonenumber']) ? html_escape($lead['phonenumber']) : '<span style="color:#cbd5e1;">N/A</span>'; ?>
                                                </td>
                                                <td style="padding: 18px 15px; vertical-align: middle; color: #475569; font-weight: 500;">
                                                    <?php echo !empty($lead['loan_type']) ? html_escape($lead['loan_type']) : '<span style="color:#cbd5e1;">N/A</span>'; ?>
                                                </td>
                                                <td style="padding: 18px 15px; vertical-align: middle;">
                                                    <span class="label" style="display: inline-block; color: <?php echo $status_color; ?>; border: 1px solid <?php echo adjust_hex_brightness($status_color, 0.4); ?>; background: <?php echo adjust_hex_brightness($status_color, 0.04); ?>; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 12.5px;">
                                                        <?php echo html_escape($lead['status_name'] ?: 'Converted'); ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 18px 15px; vertical-align: middle; text-align: right;">
                                                    <a href="<?php echo $lead['secure_link']; ?>" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: 600; border-color: #cbd5e1; color: #334155; padding: 6px 14px; transition: all 0.2s;">
                                                        Track <i class="fa fa-arrow-right" style="margin-left: 5px; font-size: 11px;"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Live search filter script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var searchInput = document.getElementById('search-input');
                var rows = document.querySelectorAll('.lead-row');
                
                searchInput.addEventListener('keyup', function(e) {
                    var term = e.target.value.toLowerCase().trim();
                    
                    rows.forEach(function(row) {
                        var name = row.querySelector('.lead-name-link').textContent.toLowerCase();
                        var phone = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        
                        if (name.indexOf(term) > -1 || phone.indexOf(term) > -1) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
                
                // Add hover style effect via JS to match modern aesthetic
                var tableRows = document.querySelectorAll('.lead-row');
                tableRows.forEach(function(row) {
                    row.addEventListener('mouseenter', function() {
                        row.style.backgroundColor = '#f8fafc';
                    });
                    row.addEventListener('mouseleave', function() {
                        row.style.backgroundColor = '';
                    });
                });
            });
        </script>
        
    <?php } ?>
</div>
