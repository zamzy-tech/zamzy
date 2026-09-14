<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin bold"><i class="fa fa-calendar-times-o text-danger"></i> Leaves & Salary Management</h4>
                        <hr class="hr-panel-separator" />
                        
                        <div class="horizontal-scrollable-tabs text-center">
                            <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#leave_requests" aria-controls="leave_requests" role="tab" data-toggle="tab">
                                        <i class="fa fa-clipboard"></i> Leave Requests
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#salary_tracking" aria-controls="salary_tracking" role="tab" data-toggle="tab">
                                        <i class="fa fa-money"></i> Staff Salary Tracker (<?= date('F Y'); ?>)
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="tab-content mtop15">
                            <!-- Tab 1: Leave Requests -->
                            <div role="tabpanel" class="tab-pane active" id="leave_requests">
                                <div class="row mbot15">
                                    <div class="col-md-12 text-right">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#apply_leave_modal">
                                            <i class="fa fa-plus"></i> Apply for Leave
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered dt-table" data-order-col="4" data-order-type="desc">
                                        <thead>
                                            <tr>
                                                <th>Staff Member</th>
                                                <th>Leave Type</th>
                                                <th>Duration</th>
                                                <th>Reason</th>
                                                <th>Date Applied</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($leaves as $leave) { 
                                                $start = strtotime($leave['start_date']);
                                                $end = strtotime($leave['end_date']);
                                                $days = (($end - $start) / (60 * 60 * 24)) + 1;
                                            ?>
                                                <tr>
                                                    <td class="bold"><?= e($leave['firstname'] . ' ' . $leave['lastname']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $badge_class = 'label-info';
                                                        if ($leave['leave_type'] === 'Sick') $badge_class = 'label-danger';
                                                        if ($leave['leave_type'] === 'Unpaid') $badge_class = 'label-warning';
                                                        if ($leave['leave_type'] === 'Casual') $badge_class = 'label-primary';
                                                        ?>
                                                        <span class="label <?= $badge_class; ?>"><?= e($leave['leave_type']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?= e(_d($leave['start_date'])) . ' to ' . e(_d($leave['end_date'])); ?>
                                                        <span class="text-muted">(<?= $days; ?> <?= $days > 1 ? 'days' : 'day'; ?>)</span>
                                                    </td>
                                                    <td><?= e($leave['reason']); ?></td>
                                                    <td data-order="<?= e($leave['created_at']); ?>"><?= e(_dt($leave['created_at'])); ?></td>
                                                    <td>
                                                        <?php 
                                                        $status_class = 'label-default';
                                                        if ($leave['status'] === 'Approved') $status_class = 'label-success';
                                                        if ($leave['status'] === 'Rejected') $status_class = 'label-danger';
                                                        ?>
                                                        <span class="label <?= $status_class; ?>"><?= e($leave['status']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($leave['status'] === 'Pending') { ?>
                                                            <a href="<?= admin_url('leaves/approve/' . $leave['id']); ?>" class="btn btn-success btn-xs" title="Approve"><i class="fa fa-check"></i> Approve</a>
                                                            <a href="<?= admin_url('leaves/reject/' . $leave['id']); ?>" class="btn btn-danger btn-xs" title="Reject"><i class="fa fa-times"></i> Reject</a>
                                                        <?php } ?>
                                                        <a href="<?= admin_url('leaves/delete/' . $leave['id']); ?>" class="btn btn-default btn-xs text-danger _delete" title="Delete"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Tab 2: Salary Tracking -->
                            <div role="tabpanel" class="tab-pane" id="salary_tracking">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Deductions are calculated automatically based on <b>Approved Unpaid Leaves</b> for the current calendar month. Formula: <code>Deduction = (Monthly Salary / 30) * Unpaid Leave Days</code>.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered dt-table">
                                        <thead>
                                            <tr>
                                                <th>Staff Name</th>
                                                <th>Base Monthly Salary (₹)</th>
                                                <th>Unpaid Leave (Days)</th>
                                                <th>Monthly Deduction (₹)</th>
                                                <th>Net Payable Salary (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($staff_members as $staff) { ?>
                                                <tr id="staff_row_<?= $staff['staffid']; ?>">
                                                    <td class="bold"><?= e($staff['firstname'] . ' ' . $staff['lastname']); ?></td>
                                                    <td>
                                                        <div class="input-group" style="max-width: 200px;">
                                                            <span class="input-group-addon">₹</span>
                                                            <input type="number" step="0.01" class="form-control salary-input" 
                                                                   data-staff-id="<?= $staff['staffid']; ?>" 
                                                                   value="<?= e($staff['monthly_salary']); ?>">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-success update-salary-btn" type="button" data-staff-id="<?= $staff['staffid']; ?>">
                                                                    <i class="fa fa-save"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td><span class="label label-warning"><?= e($staff['unpaid_days_current_month']); ?> days</span></td>
                                                    <td class="text-danger bold deduction-val">₹<?= number_format($staff['salary_deduction'], 2); ?></td>
                                                    <td class="text-success bold net-val">₹<?= number_format($staff['net_salary'], 2); ?></td>
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
    </div>
</div>

<!-- Modal: Apply Leave -->
<div class="modal fade" id="apply_leave_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title bold"><i class="fa fa-calendar-plus-o"></i> Apply for Leave</h4>
            </div>
            <form action="<?= admin_url('leaves/apply'); ?>" method="POST" id="apply_leave_form">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="leave_type" class="control-label">Leave Type</label>
                        <select name="leave_type" id="leave_type" class="form-control" required>
                            <option value="Casual">Casual Leave (Paid)</option>
                            <option value="Sick">Sick Leave (Paid)</option>
                            <option value="Unpaid">Unpaid Leave (Deducted from Salary)</option>
                            <option value="Earned">Earned Leave (Paid)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date" class="control-label">Start Date</label>
                                <input type="text" class="form-control datepicker" name="start_date" id="start_date" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date" class="control-label">End Date</label>
                                <input type="text" class="form-control datepicker" name="end_date" id="end_date" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reason" class="control-label">Reason / Remarks</label>
                        <textarea name="reason" id="reason" class="form-control" rows="4" placeholder="Brief explanation of your leave request..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
$(function() {
    // Handle salary update ajax
    $('.update-salary-btn').on('click', function() {
        const staffId = $(this).data('staff-id');
        const row = $('#staff_row_' + staffId);
        const salaryVal = row.find('.salary-input').val();
        
        $.ajax({
            url: admin_url + 'leaves/update_salary',
            type: 'POST',
            data: {
                staff_id: staffId,
                monthly_salary: salaryVal,
                ...(typeof(csrfData) !== 'undefined' ? { [csrfData.token_name]: csrfData.hash } : {})
            },
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert_float('success', res.message);
                    
                    // Recalculate frontend numbers dynamically
                    const unpaidDays = parseFloat(row.find('td:nth-child(3)').text());
                    const dailyRate = salaryVal / 30;
                    const deduction = dailyRate * unpaidDays;
                    const netSalary = salaryVal - deduction;
                    
                    row.find('.deduction-val').text('₹' + deduction.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                    row.find('.net-val').text('₹' + netSalary.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                } else {
                    alert_float('danger', 'Failed to update salary.');
                }
            },
            error: function() {
                alert_float('danger', 'Network error while updating salary.');
            }
        });
    });
});
</script>
</body>
</html>
