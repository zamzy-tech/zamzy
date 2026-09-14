<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <!-- Left Side: Salary & Deductions Widgets -->
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold no-margin text-primary"><i class="fa fa-money"></i> Salary summary (<?= date('F Y'); ?>)</h4>
                        <hr class="hr-panel-separator" />
                        
                        <div class="row mtop15">
                            <div class="col-md-12">
                                <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded tw-p-4 tw-mb-4" style="background-color: #fcfcfc;">
                                    <p class="text-muted tw-mb-1 font-medium">Base Monthly Salary</p>
                                    <h3 class="bold no-margin text-dark">₹<?= number_format($staff['monthly_salary'], 2); ?></h3>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded tw-p-4 tw-mb-4" style="background-color: #fff9f0; border-color: #ffe0b2;">
                                    <p class="text-muted tw-mb-1 font-medium">Unpaid Leaves (This Month)</p>
                                    <h3 class="bold no-margin text-warning"><?= $unpaid_days; ?> <?= $unpaid_days > 1 ? 'days' : 'day'; ?></h3>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded tw-p-4 tw-mb-4" style="background-color: #fff5f5; border-color: #ffcdd2;">
                                    <p class="text-muted tw-mb-1 font-medium">Salary Deductions (Unpaid days)</p>
                                    <h3 class="bold no-margin text-danger">₹<?= number_format($deduction, 2); ?></h3>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded tw-p-4" style="background-color: #f6fbf7; border-color: #c8e6c9;">
                                    <p class="text-muted tw-mb-1 font-medium">Estimated Net Salary</p>
                                    <h2 class="bold no-margin text-success">₹<?= number_format($net_salary, 2); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: My Leave Applications -->
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="bold no-margin"><i class="fa fa-calendar-times-o"></i> My Leave Applications</h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#apply_leave_modal">
                                    <i class="fa fa-plus"></i> Apply for Leave
                                </button>
                            </div>
                        </div>
                        <hr class="hr-panel-separator" />
                        
                        <div class="table-responsive mtop15">
                            <table class="table table-hover table-bordered dt-table" data-order-col="3" data-order-type="desc">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Reason</th>
                                        <th>Date Applied</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leaves as $leave) { 
                                        $start = strtotime($leave['start_date']);
                                        $end = strtotime($leave['end_date']);
                                        $days = (($end - $start) / (60 * 60 * 24)) + 1;
                                    ?>
                                        <tr>
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
                                                    <a href="<?= admin_url('leaves/delete/' . $leave['id']); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i> Delete</a>
                                                <?php } else { ?>
                                                    <span class="text-muted">Locked</span>
                                                <?php } ?>
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
</body>
</html>
