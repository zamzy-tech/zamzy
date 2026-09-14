<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
/* Modern Calendar Styles Matching Image 2 */
.cal-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.cal-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border-top: 1px solid #e2e8f0;
    border-left: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
}
.cal-day-name {
    background: #f8fafc;
    padding: 12px 5px;
    text-align: center;
    font-weight: bold;
    color: #475569;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    font-size: 13px;
}
.cal-cell {
    min-height: 115px;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px;
    background: #ffffff;
    position: relative;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}
.cal-cell:hover {
    background: #f0f9ff;
    box-shadow: inset 0 0 0 2px #0284c7;
}
.cal-cell.other-month {
    background: #fafafa;
    color: #cbd5e1;
    cursor: not-allowed;
}
.cal-date-number {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 6px;
    display: inline-block;
}
.cal-today .cal-date-number {
    background: #2563eb;
    color: #ffffff !important;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    text-align: center;
    line-height: 24px;
}
.cal-event-tag {
    display: block;
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    margin-top: 4px;
    line-height: 1.3;
    word-break: break-word;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.tag-paid-leave { background: #dbeafe; color: #1e40af; border-left: 3px solid #2563eb; }
.tag-absent { background: #fee2e2; color: #991b1b; border-left: 3px solid #dc2626; }
.tag-half-day { background: #fef3c7; color: #92400e; border-left: 3px solid #d97706; }
.tag-holiday { background: #f3e8ff; color: #6b21a8; border-left: 3px solid #9333ea; }
.tag-present { background: #dcfce7; color: #166534; border-left: 3px solid #16a34a; }
.reason-text { font-size: 10px; opacity: 0.9; margin-top: 2px; font-weight: normal; }
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><i class="fa fa-calendar-check-o text-primary"></i> Employee Attendance & Leave Management</h4>
                        <hr class="hr-panel-heading" />

                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs mbot20" role="tablist">
                            <li role="presentation" class="<?php if ($active_tab == 'calendar') echo 'active'; ?>">
                                <a href="<?php echo admin_url('india_hr_payroll/attendance?tab=calendar&staff_id=' . $selected_staff_id . '&month=' . $month . '&year=' . $year); ?>">
                                    <i class="fa fa-calendar"></i> <strong>1. Interactive Leave & Attendance Calendar</strong>
                                </a>
                            </li>
                            <li role="presentation" class="<?php if ($active_tab == 'monthly') echo 'active'; ?>">
                                <a href="<?php echo admin_url('india_hr_payroll/attendance?tab=monthly&month=' . $month . '&year=' . $year); ?>">
                                    <i class="fa fa-table"></i> <strong>2. Monthly Payroll Attendance Sheet</strong>
                                </a>
                            </li>
                            <li role="presentation" class="<?php if ($active_tab == 'daily') echo 'active'; ?>">
                                <a href="<?php echo admin_url('india_hr_payroll/attendance?tab=daily&date=' . $date); ?>">
                                    <i class="fa fa-clock-o"></i> <strong>3. Daily Punch Log</strong>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            
                            <!-- TAB 1: INTERACTIVE LEAVE & ATTENDANCE CALENDAR (LIKE IMAGE 2) -->
                            <?php if ($active_tab == 'calendar') { 
                                $days_in_month = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
                                $first_day_of_week = (int) date('w', mktime(0, 0, 0, $month, 1, $year)); // 0 = Sun, 1 = Mon ...
                                
                                $prev_month = ($month == 1) ? 12 : $month - 1;
                                $prev_year  = ($month == 1) ? $year - 1 : $year;
                                $next_month = ($month == 12) ? 1 : $month + 1;
                                $next_year  = ($month == 12) ? $year + 1 : $year;

                                $selected_staff_name = 'Employee';
                                foreach ($staff_members as $sm) {
                                    if ($sm['staffid'] == $selected_staff_id) {
                                        $selected_staff_name = $sm['firstname'] . ' ' . $sm['lastname'];
                                        break;
                                    }
                                }
                            ?>
                                <div role="tabpanel" class="tab-pane active">
                                    
                                    <div class="cal-container">
                                        
                                        <!-- Header Bar -->
                                        <div class="cal-header-bar">
                                            
                                            <!-- Navigation Buttons -->
                                            <div class="btn-group">
                                                <a href="<?php echo admin_url('india_hr_payroll/attendance?tab=calendar&staff_id=' . $selected_staff_id . '&month=' . $prev_month . '&year=' . $prev_year); ?>" class="btn btn-default"><i class="fa fa-chevron-left"></i></a>
                                                <a href="<?php echo admin_url('india_hr_payroll/attendance?tab=calendar&staff_id=' . $selected_staff_id . '&month=' . $next_month . '&year=' . $next_year); ?>" class="btn btn-default"><i class="fa fa-chevron-right"></i></a>
                                                <a href="<?php echo admin_url('india_hr_payroll/attendance?tab=calendar&staff_id=' . $selected_staff_id . '&month=' . date('m') . '&year=' . date('Y')); ?>" class="btn btn-default">Today</a>
                                            </div>

                                            <!-- Month Year Title -->
                                            <div>
                                                <h3 style="margin: 0; font-weight: bold; color: #1e293b;">
                                                    <?php echo date('F Y', mktime(0, 0, 0, $month, 10, $year)); ?>
                                                </h3>
                                            </div>

                                            <!-- Staff Member Selector -->
                                            <div style="min-width: 280px;">
                                                <select class="selectpicker" data-width="100%" onchange="location = '<?php echo admin_url('india_hr_payroll/attendance?tab=calendar&month=' . $month . '&year=' . $year . '&staff_id='); ?>' + this.value;">
                                                    <?php foreach ($staff_members as $member) { ?>
                                                        <option value="<?php echo $member['staffid']; ?>" <?php if ($member['staffid'] == $selected_staff_id) echo 'selected'; ?>>
                                                            👤 <?php echo $member['firstname'] . ' ' . $member['lastname'] . ' (' . $member['email'] . ')'; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                        </div>

                                        <!-- Quick Instructions & Color Legend -->
                                        <div class="mbot15" style="display: flex; gap: 15px; flex-wrap: wrap; font-size: 12px; align-items: center; background: #f8fafc; padding: 10px 15px; border-radius: 6px;">
                                            <span><strong>💡 Click on any date box to mark leave & reason:</strong></span>
                                            <span class="cal-event-tag tag-paid-leave">🔵 Paid / Casual Leave</span>
                                            <span class="cal-event-tag tag-absent">🔴 Unpaid Absent</span>
                                            <span class="cal-event-tag tag-half-day">🟡 Half Day</span>
                                            <span class="cal-event-tag tag-holiday">🟣 Holiday / Off</span>
                                            <span class="cal-event-tag tag-present">🟢 Present</span>
                                        </div>

                                        <!-- 7-Column Calendar Grid -->
                                        <div class="cal-grid">
                                            
                                            <!-- Day Names -->
                                            <div class="cal-day-name">Sun</div>
                                            <div class="cal-day-name">Mon</div>
                                            <div class="cal-day-name">Tue</div>
                                            <div class="cal-day-name">Wed</div>
                                            <div class="cal-day-name">Thu</div>
                                            <div class="cal-day-name">Fri</div>
                                            <div class="cal-day-name">Sat</div>

                                            <!-- Leading empty cells -->
                                            <?php 
                                            $prev_month_days = (int) date('t', mktime(0, 0, 0, $prev_month, 1, $prev_year));
                                            for ($p = 0; $p < $first_day_of_week; $p++) { 
                                                $prev_date_num = $prev_month_days - $first_day_of_week + $p + 1;
                                            ?>
                                                <div class="cal-cell other-month">
                                                    <span class="cal-date-number"><?php echo $prev_date_num; ?></span>
                                                </div>
                                            <?php } ?>

                                            <!-- Days of the Current Month -->
                                            <?php 
                                            for ($d = 1; $d <= $days_in_month; $d++) { 
                                                $date_str = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                                $is_today = ($date_str == date('Y-m-d'));
                                                $att_info = isset($calendar_records[$date_str]) ? $calendar_records[$date_str] : null;
                                                $status = $att_info ? $att_info->status : '';
                                                $notes  = $att_info ? $att_info->notes : '';
                                            ?>
                                                <div class="cal-cell <?php if ($is_today) echo 'cal-today'; ?>" 
                                                     onclick="openLeaveModal('<?php echo $date_str; ?>', '<?php echo date('d F Y', strtotime($date_str)); ?>', '<?php echo htmlspecialchars($status); ?>', '<?php echo htmlspecialchars($notes); ?>');">
                                                    
                                                    <span class="cal-date-number"><?php echo $d; ?></span>
                                                    
                                                    <?php if ($status == 'Paid Leave') { ?>
                                                        <div class="cal-event-tag tag-paid-leave">
                                                            <strong>Paid Leave</strong>
                                                            <?php if (!empty($notes)) { ?>
                                                                <div class="reason-text"><?php echo $notes; ?></div>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } elseif ($status == 'Absent') { ?>
                                                        <div class="cal-event-tag tag-absent">
                                                            <strong>Absent / LOP</strong>
                                                            <?php if (!empty($notes)) { ?>
                                                                <div class="reason-text"><?php echo $notes; ?></div>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } elseif ($status == 'Half Day') { ?>
                                                        <div class="cal-event-tag tag-half-day">
                                                            <strong>Half Day</strong>
                                                            <?php if (!empty($notes)) { ?>
                                                                <div class="reason-text"><?php echo $notes; ?></div>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } elseif ($status == 'Holiday') { ?>
                                                        <div class="cal-event-tag tag-holiday">
                                                            <strong>Holiday</strong>
                                                            <?php if (!empty($notes)) { ?>
                                                                <div class="reason-text"><?php echo $notes; ?></div>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } elseif ($status == 'Present') { ?>
                                                        <div class="cal-event-tag tag-present">
                                                            <strong>Present</strong>
                                                        </div>
                                                    <?php } ?>

                                                </div>
                                            <?php } ?>

                                            <!-- Trailing empty cells -->
                                            <?php 
                                            $total_cells = $first_day_of_week + $days_in_month;
                                            $remaining_cells = (7 - ($total_cells % 7)) % 7;
                                            for ($n = 1; $n <= $remaining_cells; $n++) { 
                                            ?>
                                                <div class="cal-cell other-month">
                                                    <span class="cal-date-number"><?php echo $n; ?></span>
                                                </div>
                                            <?php } ?>

                                        </div>

                                    </div>

                                </div>

                                <!-- Modal to Mark Leave / Attendance on a Date -->
                                <div class="modal fade" id="leaveModal" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <?php echo form_open(admin_url('india_hr_payroll/attendance')); ?>
                                            <input type="hidden" name="action" value="save_date_leave">
                                            <input type="hidden" name="staff_id" value="<?php echo $selected_staff_id; ?>">
                                            <input type="hidden" name="date" id="modal_date_input">

                                            <div class="modal-header" style="background: #f8fafc;">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">
                                                    <i class="fa fa-calendar-check-o text-primary"></i> Mark Attendance / Leave for <span id="modal_date_display" style="font-weight: bold; color: #2563eb;"></span>
                                                </h4>
                                            </div>

                                            <div class="modal-body">
                                                <p><strong>Employee:</strong> <?php echo htmlspecialchars($selected_staff_name); ?></p>
                                                
                                                <div class="form-group">
                                                    <label>Select Status / Leave Type:</label>
                                                    <select name="status" id="modal_status_select" class="form-control" required>
                                                        <option value="Paid Leave">🔵 Paid Leave / Casual Leave (Salary Payable)</option>
                                                        <option value="Absent">🔴 Absent / Loss of Pay (Salary Deducted)</option>
                                                        <option value="Half Day">🟡 Half Day (4 Hours Work)</option>
                                                        <option value="Present">🟢 Present (Full Day)</option>
                                                        <option value="Holiday">🟣 Holiday / Office Closed</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label>Reason for Leave / Remarks:</label>
                                                    <textarea name="reason" id="modal_reason_input" class="form-control" rows="3" placeholder="e.g. Medical emergency, Family function, Sick leave, Personal work..."></textarea>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Leave & Sync Payroll</button>
                                            </div>
                                            <?php echo form_close(); ?>
                                        </div>
                                    </div>
                                </div>

                            <?php } ?>

                            <!-- TAB 2: MONTHLY ATTENDANCE SHEET -->
                            <?php if ($active_tab == 'monthly') { ?>
                                <div role="tabpanel" class="tab-pane active">
                                    
                                    <!-- Filter Bar -->
                                    <div class="row mbot20">
                                        <div class="col-md-8">
                                            <form method="get" action="<?php echo admin_url('india_hr_payroll/attendance'); ?>" class="form-inline">
                                                <input type="hidden" name="tab" value="monthly">
                                                <div class="form-group mright10">
                                                    <label>Month:&nbsp;</label>
                                                    <select name="month" class="form-control">
                                                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                                                            <option value="<?php echo $m; ?>" <?php if ($m == $month) echo 'selected'; ?>>
                                                                <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group mright10">
                                                    <label>Year:&nbsp;</label>
                                                    <select name="year" class="form-control">
                                                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++) { ?>
                                                            <option value="<?php echo $y; ?>" <?php if ($y == $year) echo 'selected'; ?>><?php echo $y; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-info"><i class="fa fa-filter"></i> Load Month Sheet</button>
                                            </form>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <button type="button" class="btn btn-default" onclick="markAllFullMonth();">
                                                <i class="fa fa-check-square-o text-success"></i> Mark All 100% Present
                                            </button>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> <strong>Payroll Attendance Sync:</strong> The <strong>Payable Days</strong> calculated below will automatically determine the employee's salary and will be printed on their monthly payslips!
                                    </div>

                                    <?php echo form_open(admin_url('india_hr_payroll/attendance')); ?>
                                    <input type="hidden" name="action" value="save_monthly_attendance">
                                    <input type="hidden" name="month" value="<?php echo $month; ?>">
                                    <input type="hidden" name="year" value="<?php echo $year; ?>">

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr class="active">
                                                    <th>Staff ID</th>
                                                    <th>Employee Name</th>
                                                    <th>Total Days in Month</th>
                                                    <th>Present Days</th>
                                                    <th>Paid Leaves</th>
                                                    <th>Unpaid / Absent Days</th>
                                                    <th class="info text-center"><strong>Payable Days (Salary)</strong></th>
                                                    <th>Notes / Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($monthly_records as $rec) { ?>
                                                    <tr>
                                                        <td>#EMP-<?php echo $rec->staff_id; ?></td>
                                                        <td>
                                                            <strong><?php echo $rec->firstname . ' ' . $rec->lastname; ?></strong><br>
                                                            <span class="text-muted text-small"><?php echo $rec->email; ?></span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-default" style="font-size: 13px;"><?php echo $rec->total_days; ?></span>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.5" min="0" max="<?php echo $rec->total_days; ?>" 
                                                                   name="staff_attendance[<?php echo $rec->staff_id; ?>][present_days]" 
                                                                   id="present_<?php echo $rec->staff_id; ?>" 
                                                                   value="<?php echo $rec->present_days; ?>" 
                                                                   class="form-control text-center present-input" 
                                                                   data-staff="<?php echo $rec->staff_id; ?>" 
                                                                   data-total="<?php echo $rec->total_days; ?>" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.5" min="0" max="<?php echo $rec->total_days; ?>" 
                                                                   name="staff_attendance[<?php echo $rec->staff_id; ?>][paid_leaves]" 
                                                                   id="leaves_<?php echo $rec->staff_id; ?>" 
                                                                   value="<?php echo $rec->paid_leaves; ?>" 
                                                                   class="form-control text-center leave-input" 
                                                                   data-staff="<?php echo $rec->staff_id; ?>">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.5" min="0" max="<?php echo $rec->total_days; ?>" 
                                                                   name="staff_attendance[<?php echo $rec->staff_id; ?>][absent_days]" 
                                                                   id="absent_<?php echo $rec->staff_id; ?>" 
                                                                   value="<?php echo $rec->absent_days; ?>" 
                                                                   class="form-control text-center absent-input" 
                                                                   data-staff="<?php echo $rec->staff_id; ?>" readonly style="background-color: #f8fafc;">
                                                        </td>
                                                        <td class="info">
                                                            <input type="number" step="0.5" min="0" max="<?php echo $rec->total_days; ?>" 
                                                                   name="staff_attendance[<?php echo $rec->staff_id; ?>][payable_days]" 
                                                                   id="payable_<?php echo $rec->staff_id; ?>" 
                                                                   value="<?php echo $rec->payable_days; ?>" 
                                                                   class="form-control text-center payable-input" 
                                                                   style="font-weight: bold; color: #1e3a8a; font-size: 14px;" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="staff_attendance[<?php echo $rec->staff_id; ?>][notes]" value="<?php echo $rec->notes; ?>" class="form-control" placeholder="Optional notes...">
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-right mtop15">
                                        <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save Monthly Attendance for Payroll</button>
                                    </div>
                                    <?php echo form_close(); ?>

                                </div>
                            <?php } ?>

                            <!-- TAB 3: DAILY ATTENDANCE LOGGER -->
                            <?php if ($active_tab == 'daily') { ?>
                                <div role="tabpanel" class="tab-pane active">
                                    
                                    <!-- Date Filter -->
                                    <div class="row mbot20">
                                        <div class="col-md-6">
                                            <form method="get" action="<?php echo admin_url('india_hr_payroll/attendance'); ?>" class="form-inline">
                                                <input type="hidden" name="tab" value="daily">
                                                <div class="form-group mright10">
                                                    <label>Select Date:&nbsp;</label>
                                                    <input type="date" name="date" class="form-control" value="<?php echo $date; ?>">
                                                </div>
                                                <button type="submit" class="btn btn-info"><i class="fa fa-calendar"></i> Load Date Log</button>
                                            </form>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <button type="button" class="btn btn-default" onclick="markAllDailyPresent();">
                                                <i class="fa fa-check text-success"></i> Set All Present (09:30 - 18:30)
                                            </button>
                                        </div>
                                    </div>

                                    <?php echo form_open(admin_url('india_hr_payroll/attendance')); ?>
                                    <input type="hidden" name="action" value="save_daily_attendance">
                                    <input type="hidden" name="date" value="<?php echo $date; ?>">

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr class="active">
                                                    <th>Staff ID</th>
                                                    <th>Employee Name</th>
                                                    <th>Attendance Status</th>
                                                    <th>Check-In Time</th>
                                                    <th>Check-Out Time</th>
                                                    <th>Work Hours</th>
                                                    <th>Remarks / Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($daily_records as $rec) { ?>
                                                    <tr>
                                                        <td>#EMP-<?php echo $rec->staff_id; ?></td>
                                                        <td>
                                                            <strong><?php echo $rec->firstname . ' ' . $rec->lastname; ?></strong><br>
                                                            <span class="text-muted text-small"><?php echo $rec->email; ?></span>
                                                        </td>
                                                        <td>
                                                            <select name="daily_attendance[<?php echo $rec->staff_id; ?>][status]" class="form-control daily-status-select" data-staff="<?php echo $rec->staff_id; ?>">
                                                                <option value="Present" <?php if ($rec->status == 'Present') echo 'selected'; ?>>🟢 Present (Full Day)</option>
                                                                <option value="Half Day" <?php if ($rec->status == 'Half Day') echo 'selected'; ?>>🟡 Half Day</option>
                                                                <option value="Paid Leave" <?php if ($rec->status == 'Paid Leave') echo 'selected'; ?>>🔵 Paid Leave</option>
                                                                <option value="Absent" <?php if ($rec->status == 'Absent') echo 'selected'; ?>>🔴 Absent (Unpaid)</option>
                                                                <option value="Holiday" <?php if ($rec->status == 'Holiday') echo 'selected'; ?>>🟣 Holiday / Off</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="time" name="daily_attendance[<?php echo $rec->staff_id; ?>][check_in]" id="checkin_<?php echo $rec->staff_id; ?>" value="<?php echo $rec->check_in; ?>" class="form-control text-center">
                                                        </td>
                                                        <td>
                                                            <input type="time" name="daily_attendance[<?php echo $rec->staff_id; ?>][check_out]" id="checkout_<?php echo $rec->staff_id; ?>" value="<?php echo $rec->check_out; ?>" class="form-control text-center">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.5" name="daily_attendance[<?php echo $rec->staff_id; ?>][work_hours]" id="hours_<?php echo $rec->staff_id; ?>" value="<?php echo $rec->work_hours; ?>" class="form-control text-center">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="daily_attendance[<?php echo $rec->staff_id; ?>][notes]" value="<?php echo $rec->notes; ?>" class="form-control" placeholder="Optional notes...">
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-right mtop15">
                                        <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save Daily Attendance</button>
                                    </div>
                                    <?php echo form_close(); ?>

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
function openLeaveModal(dateStr, dateFormatted, currentStatus, currentReason) {
    document.getElementById('modal_date_input').value = dateStr;
    document.getElementById('modal_date_display').innerText = dateFormatted;
    
    var sel = document.getElementById('modal_status_select');
    if (sel && currentStatus) {
        sel.value = currentStatus;
    } else if (sel) {
        sel.value = 'Paid Leave';
    }
    
    var reasonBox = document.getElementById('modal_reason_input');
    if (reasonBox) {
        reasonBox.value = currentReason || '';
    }
    
    $('#leaveModal').modal('show');
}

function autoCalculateRow(staffId) {
    var presentInput = document.getElementById('present_' + staffId);
    var leaveInput   = document.getElementById('leaves_' + staffId);
    var absentInput  = document.getElementById('absent_' + staffId);
    var payableInput = document.getElementById('payable_' + staffId);

    if (presentInput && leaveInput && absentInput && payableInput) {
        var totalDays = parseFloat(presentInput.getAttribute('data-total')) || 30;
        var present   = parseFloat(presentInput.value) || 0;
        var leaves    = parseFloat(leaveInput.value) || 0;

        var payable = present + leaves;
        if (payable > totalDays) payable = totalDays;

        var absent = totalDays - payable;
        if (absent < 0) absent = 0;

        absentInput.value  = absent.toFixed(1);
        payableInput.value = payable.toFixed(1);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var presentInputs = document.querySelectorAll('.present-input');
    var leaveInputs   = document.querySelectorAll('.leave-input');

    presentInputs.forEach(function(inp) {
        inp.addEventListener('input', function() {
            autoCalculateRow(inp.getAttribute('data-staff'));
        });
    });

    leaveInputs.forEach(function(inp) {
        inp.addEventListener('input', function() {
            autoCalculateRow(inp.getAttribute('data-staff'));
        });
    });
});

function markAllFullMonth() {
    var presentInputs = document.querySelectorAll('.present-input');
    presentInputs.forEach(function(inp) {
        var staffId = inp.getAttribute('data-staff');
        var totalDays = parseFloat(inp.getAttribute('data-total')) || 30;
        inp.value = totalDays.toFixed(1);
        var leaveInput = document.getElementById('leaves_' + staffId);
        if (leaveInput) leaveInput.value = '0.0';
        autoCalculateRow(staffId);
    });
}

function markAllDailyPresent() {
    var selects = document.querySelectorAll('.daily-status-select');
    selects.forEach(function(sel) {
        sel.value = 'Present';
        var staffId = sel.getAttribute('data-staff');
        var checkin = document.getElementById('checkin_' + staffId);
        var checkout = document.getElementById('checkout_' + staffId);
        var hours = document.getElementById('hours_' + staffId);
        if (checkin) checkin.value = '09:30';
        if (checkout) checkout.value = '18:30';
        if (hours) hours.value = '8.0';
    });
}
</script>
