<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leaves extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        $db_prefix = db_prefix();
        // Auto database setup
        if (!$this->db->table_exists($db_prefix . 'staff_leaves')) {
            $this->db->query("CREATE TABLE `{$db_prefix}staff_leaves` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `staff_id` INT NOT NULL,
                `leave_type` VARCHAR(50) NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `reason` TEXT,
                `status` VARCHAR(20) DEFAULT 'Pending',
                `created_at` DATETIME NOT NULL,
                `approved_by` INT DEFAULT NULL,
                `approved_at` DATETIME DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        if (!$this->db->field_exists('monthly_salary', $db_prefix . 'staff')) {
            $this->db->query("ALTER TABLE `{$db_prefix}staff` ADD COLUMN `monthly_salary` DECIMAL(15,2) DEFAULT 0.00");
        }
    }

    public function index()
    {
        $db_prefix = db_prefix();
        $is_admin = is_admin();
        $current_staff_id = get_staff_user_id();

        // Handle Admin Dashboard
        if ($is_admin) {
            // Get all leaves
            $this->db->select($db_prefix . 'staff_leaves.*, ' . $db_prefix . 'staff.firstname, ' . $db_prefix . 'staff.lastname');
            $this->db->join($db_prefix . 'staff', $db_prefix . 'staff.staffid = ' . $db_prefix . 'staff_leaves.staff_id', 'left');
            $this->db->order_by($db_prefix . 'staff_leaves.created_at', 'DESC');
            $leaves = $this->db->get($db_prefix . 'staff_leaves')->result_array();

            // Get all staff for salary tracking
            $this->db->select('staffid, firstname, lastname, monthly_salary, email');
            $this->db->where('active', 1);
            $staff_members = $this->db->get($db_prefix . 'staff')->result_array();

            // Calculate unpaid leave summary for each staff for current month
            $current_month_start = date('Y-m-01');
            $current_month_end = date('Y-m-t');

            foreach ($staff_members as &$staff) {
                // Fetch approved unpaid leaves overlap with current month
                $this->db->where('staff_id', $staff['staffid']);
                $this->db->where('status', 'Approved');
                $this->db->where('leave_type', 'Unpaid');
                $this->db->where('start_date <=', $current_month_end);
                $this->db->where('end_date >=', $current_month_start);
                $approved_unpaid = $this->db->get($db_prefix . 'staff_leaves')->result_array();

                $unpaid_days = 0;
                foreach ($approved_unpaid as $l) {
                    $overlap_start = max(strtotime($current_month_start), strtotime($l['start_date']));
                    $overlap_end = min(strtotime($current_month_end), strtotime($l['end_date']));
                    $days = (($overlap_end - $overlap_start) / (60 * 60 * 24)) + 1;
                    if ($days > 0) {
                        $unpaid_days += $days;
                    }
                }
                $staff['unpaid_days_current_month'] = $unpaid_days;

                // Calculate salary deduction (Base salary / 30 * unpaid days)
                $daily_rate = $staff['monthly_salary'] / 30;
                $deduction = $daily_rate * $unpaid_days;
                $staff['salary_deduction'] = round($deduction, 2);
                $staff['net_salary'] = round($staff['monthly_salary'] - $deduction, 2);
            }

            $data['leaves'] = $leaves;
            $data['staff_members'] = $staff_members;
            $data['title'] = 'Leaves & Salary Management (Admin)';

            $this->load->view('admin/leaves/admin_dashboard', $data);
        } else {
            // Normal Staff view
            $this->db->select($db_prefix . 'staff_leaves.*, ' . $db_prefix . 'staff.firstname, ' . $db_prefix . 'staff.lastname');
            $this->db->join($db_prefix . 'staff', $db_prefix . 'staff.staffid = ' . $db_prefix . 'staff_leaves.staff_id', 'left');
            $this->db->where('staff_id', $current_staff_id);
            $this->db->order_by('created_at', 'DESC');
            $leaves = $this->db->get($db_prefix . 'staff_leaves')->result_array();

            // Fetch current staff salary
            $this->db->select('monthly_salary, firstname, lastname');
            $this->db->where('staffid', $current_staff_id);
            $staff = $this->db->get($db_prefix . 'staff')->row_array();

            // Calculate unpaid leave summary for current month
            $current_month_start = date('Y-m-01');
            $current_month_end = date('Y-m-t');

            $this->db->where('staff_id', $current_staff_id);
            $this->db->where('status', 'Approved');
            $this->db->where('leave_type', 'Unpaid');
            $this->db->where('start_date <=', $current_month_end);
            $this->db->where('end_date >=', $current_month_start);
            $approved_unpaid = $this->db->get($db_prefix . 'staff_leaves')->result_array();

            $unpaid_days = 0;
            foreach ($approved_unpaid as $l) {
                $overlap_start = max(strtotime($current_month_start), strtotime($l['start_date']));
                $overlap_end = min(strtotime($current_month_end), strtotime($l['end_date']));
                $days = (($overlap_end - $overlap_start) / (60 * 60 * 24)) + 1;
                if ($days > 0) {
                    $unpaid_days += $days;
                }
            }

            $daily_rate = $staff['monthly_salary'] / 30;
            $deduction = $daily_rate * $unpaid_days;

            $data['leaves'] = $leaves;
            $data['staff'] = $staff;
            $data['unpaid_days'] = $unpaid_days;
            $data['deduction'] = round($deduction, 2);
            $data['net_salary'] = round($staff['monthly_salary'] - $deduction, 2);
            $data['title'] = 'My Leaves & Salary';

            $this->load->view('admin/leaves/staff_dashboard', $data);
        }
    }

    public function apply()
    {
        if ($this->input->post()) {
            $insert_data = [
                'staff_id'   => get_staff_user_id(),
                'leave_type' => $this->input->post('leave_type'),
                'start_date' => to_sql_date($this->input->post('start_date')),
                'end_date'   => to_sql_date($this->input->post('end_date')),
                'reason'     => $this->input->post('reason'),
                'status'     => 'Pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->insert(db_prefix() . 'staff_leaves', $insert_data);
            set_alert('success', 'Leave application submitted successfully.');
        }
        redirect(admin_url('leaves'));
    }

    public function approve($id)
    {
        if (!is_admin()) {
            access_denied('Leaves Approval');
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'staff_leaves', [
            'status'      => 'Approved',
            'approved_by' => get_staff_user_id(),
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        set_alert('success', 'Leave request approved.');
        redirect(admin_url('leaves'));
    }

    public function reject($id)
    {
        if (!is_admin()) {
            access_denied('Leaves Rejection');
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'staff_leaves', [
            'status'      => 'Rejected',
            'approved_by' => get_staff_user_id(),
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        set_alert('success', 'Leave request rejected.');
        redirect(admin_url('leaves'));
    }

    public function delete($id)
    {
        $db_prefix = db_prefix();
        $this->db->where('id', $id);
        $leave = $this->db->get($db_prefix . 'staff_leaves')->row_array();

        if ($leave) {
            if ($leave['staff_id'] == get_staff_user_id() || is_admin()) {
                $this->db->where('id', $id);
                $this->db->delete($db_prefix . 'staff_leaves');
                set_alert('success', 'Leave request deleted.');
            } else {
                access_denied('Delete Leave');
            }
        }

        redirect(admin_url('leaves'));
    }

    public function update_salary()
    {
        if (!is_admin()) {
            access_denied('Update Salary');
        }

        $staff_id = $this->input->post('staff_id');
        $salary = $this->input->post('monthly_salary');

        $this->db->where('staffid', $staff_id);
        $this->db->update(db_prefix() . 'staff', ['monthly_salary' => $salary]);

        echo json_encode(['success' => true, 'message' => 'Monthly salary updated successfully.']);
    }
}
