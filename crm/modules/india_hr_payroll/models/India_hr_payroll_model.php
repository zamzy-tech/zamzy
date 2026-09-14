<?php
defined('BASEPATH') or exit('No direct script access allowed');

class India_hr_payroll_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Helper to get total days in a month without requiring php-calendar extension
     */
    public function get_days_in_month($month, $year)
    {
        return (int) date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
    }

    /**
     * Default Indian Company Terms and Conditions Template
     */
    public function get_default_employment_terms()
    {
        return "1. PROBATION & CONFIRMATION: You will be on probation for a period of 3 Months from your Date of Joining. Upon satisfactory performance, your employment will be confirmed in writing.\n" .
               "2. WORKING HOURS & LEAVE POLICY: Regular office hours are 9:30 AM to 6:30 PM (Monday through Saturday). Leaves will be credited as per company leave policy.\n" .
               "3. NOTICE PERIOD & RESIGNATION: During probation, either party may terminate employment with 15 days notice. Post confirmation, a mandatory notice period of 30 Days is required prior to relieving.\n" .
               "4. CONFIDENTIALITY & INTELLECTUAL PROPERTY: All technical work, software code, databases, client information, and trade secrets remain the exclusive intellectual property of The Expert Hub (TEHUB).\n" .
               "5. CODE OF CONDUCT: You agree to adhere to all corporate governance, anti-harassment (POSH), and professional conduct guidelines of the company.";
    }

    /**
     * Get 16 Document Slot Metadata
     */
    public function get_document_slots()
    {
        return [
            'aadhaar'              => ['name' => '1. Aadhaar Card / National ID', 'stage' => 'Pre-Joining'],
            'pan'                  => ['name' => '2. PAN Card', 'stage' => 'Pre-Joining'],
            'address_proof'        => ['name' => '3. Address Proof (Voter ID/Passport/Utility)', 'stage' => 'Pre-Joining'],
            'bank_passbook'        => ['name' => '4. Bank Account Passbook / Cancelled Cheque', 'stage' => 'Pre-Joining'],
            'photo'                => ['name' => '5. Passport Size Photograph', 'stage' => 'Pre-Joining'],
            'education'            => ['name' => '6. Educational Certificates (Degree/Diploma)', 'stage' => 'Pre-Joining'],
            'experience_prev'      => ['name' => '7. Previous Employment / Experience Certificates', 'stage' => 'Pre-Joining'],
            'uan_doc'              => ['name' => '8. EPFO UAN Details / Declaration', 'stage' => 'Pre-Joining'],
            'esic_doc'             => ['name' => '9. ESIC Details / Form 1', 'stage' => 'Pre-Joining'],
            'emergency_decl'       => ['name' => '10. Emergency Contact & Nomination Declaration', 'stage' => 'Pre-Joining'],
            'offer_letter'         => ['name' => '11. Signed Offer Letter', 'stage' => 'Joining'],
            'joining_letter'       => ['name' => '12. Appointment / Joining Letter', 'stage' => 'Joining'],
            'agreement'            => ['name' => '13. Employment & Confidentiality/IP Agreement', 'stage' => 'Joining'],
            'leave_policy'         => ['name' => '14. Leave Policy & Code of Conduct Acknowledgment', 'stage' => 'Joining'],
            'payslips_archived'    => ['name' => '15. Monthly Payslips & Tax Declarations', 'stage' => 'Payroll'],
            'exit_docs'            => ['name' => '16. Resignation, F&F & Relieving Certificate', 'stage' => 'Exit']
        ];
    }

    /**
     * Get complete employee details with Terms & CTC
     */
    public function get_employee_details($staff_id)
    {
        $this->db->where('staff_id', $staff_id);
        $details = $this->db->get(db_prefix() . 'hr_employee_details')->row();
        if (!$details) {
            return (object) [
                'staff_id'                  => $staff_id,
                'father_mother_spouse_name' => '',
                'relation_type'             => 'Father',
                'dob'                       => '',
                'gender'                    => 'Male',
                'mobile_number'             => '',
                'current_address'           => '',
                'permanent_address'         => '',
                'designation'               => '',
                'department'                => '',
                'annual_ctc'                => 0.00,
                'probation_period'          => '3 Months',
                'notice_period'             => '30 Days',
                'working_hours'             => '9:30 AM - 6:30 PM (Mon - Sat)',
                'employment_terms'          => $this->get_default_employment_terms(),
                'pan_number'                => '',
                'aadhaar_number'            => '',
                'pf_eligible'               => 1,
                'uan_number'                => '',
                'previous_pf_number'        => '',
                'previous_member_id'        => '',
                'esic_number'               => '',
                'bank_name'                 => '',
                'bank_account_no'           => '',
                'ifsc_code'                 => '',
                'nominee_name'              => '',
                'nominee_relation'          => '',
                'nominee_dob'               => '',
                'nominee_aadhaar'           => '',
                'emergency_contact_name'    => '',
                'emergency_contact_phone'   => '',
                'state'                     => 'Andhra Pradesh',
                'joining_date'              => ''
            ];
        }

        if (empty($details->employment_terms)) {
            $details->employment_terms = $this->get_default_employment_terms();
        }
        return $details;
    }

    /**
     * Save employee details & sync Monthly Salary & Annual CTC
     */
    public function save_employee_details($staff_id, $data)
    {
        $monthly_salary = floatval($data['monthly_salary'] ?? 0);
        $annual_ctc     = floatval($data['annual_ctc'] ?? 0);

        if ($annual_ctc <= 0 && $monthly_salary > 0) {
            $annual_ctc = round($monthly_salary * 12, 2);
        }
        if ($monthly_salary <= 0 && $annual_ctc > 0) {
            $monthly_salary = round($annual_ctc / 12, 2);
        }

        $update_data = [
            'father_mother_spouse_name' => $data['father_mother_spouse_name'] ?? '',
            'relation_type'             => $data['relation_type'] ?? 'Father',
            'dob'                       => !empty($data['dob']) ? $data['dob'] : null,
            'gender'                    => $data['gender'] ?? 'Male',
            'mobile_number'             => $data['mobile_number'] ?? '',
            'current_address'           => $data['current_address'] ?? '',
            'permanent_address'         => $data['permanent_address'] ?? '',
            'designation'               => $data['designation'] ?? '',
            'department'                => $data['department'] ?? '',
            'annual_ctc'                => $annual_ctc,
            'probation_period'          => $data['probation_period'] ?? '3 Months',
            'notice_period'             => $data['notice_period'] ?? '30 Days',
            'working_hours'             => $data['working_hours'] ?? '9:30 AM - 6:30 PM (Mon - Sat)',
            'employment_terms'          => $data['employment_terms'] ?? $this->get_default_employment_terms(),
            'pan_number'                => $data['pan_number'] ?? '',
            'aadhaar_number'            => $data['aadhaar_number'] ?? '',
            'pf_eligible'               => isset($data['pf_eligible']) ? 1 : 0,
            'uan_number'                => $data['uan_number'] ?? '',
            'previous_pf_number'        => $data['previous_pf_number'] ?? '',
            'previous_member_id'        => $data['previous_member_id'] ?? '',
            'esic_number'               => $data['esic_number'] ?? '',
            'bank_name'                 => $data['bank_name'] ?? '',
            'bank_account_no'           => $data['bank_account_no'] ?? '',
            'ifsc_code'                 => $data['ifsc_code'] ?? '',
            'nominee_name'              => $data['nominee_name'] ?? '',
            'nominee_relation'          => $data['nominee_relation'] ?? '',
            'nominee_dob'               => !empty($data['nominee_dob']) ? $data['nominee_dob'] : null,
            'nominee_aadhaar'           => $data['nominee_aadhaar'] ?? '',
            'emergency_contact_name'    => $data['emergency_contact_name'] ?? '',
            'emergency_contact_phone'   => $data['emergency_contact_phone'] ?? '',
            'state'                     => $data['state'] ?? 'Andhra Pradesh',
            'joining_date'              => !empty($data['joining_date']) ? $data['joining_date'] : null
        ];

        $this->db->where('staff_id', $staff_id);
        if ($this->db->get(db_prefix() . 'hr_employee_details')->row()) {
            $this->db->where('staff_id', $staff_id);
            $this->db->update(db_prefix() . 'hr_employee_details', $update_data);
        } else {
            $update_data['staff_id'] = $staff_id;
            $this->db->insert(db_prefix() . 'hr_employee_details', $update_data);
        }

        // Auto calculate & update monthly salary structure
        if ($monthly_salary > 0) {
            $basic   = round($monthly_salary * 0.50, 2);
            $hra     = round($basic * 0.50, 2);
            $special = round($monthly_salary - ($basic + $hra), 2);

            $this->save_salary_structure($staff_id, [
                'basic_salary'      => $basic,
                'hra'               => $hra,
                'special_allowance' => $special,
                'other_allowances'  => 0,
                'pf_applicable'     => 1,
                'esi_applicable'    => $monthly_salary <= 21000 ? 1 : 0,
                'pt_applicable'     => 1,
                'tds_monthly'       => 0
            ]);
        }

        return true;
    }

    /**
     * Get documents uploaded for a staff member
     */
    public function get_employee_documents($staff_id)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->order_by('uploaded_at', 'DESC');
        return $this->db->get(db_prefix() . 'hr_employee_documents')->result();
    }

    /**
     * Get Calendar Attendance for a specific staff member across a month
     */
    public function get_staff_calendar_attendance($staff_id, $month, $year)
    {
        $days_in_month = $this->get_days_in_month($month, $year);
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date   = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);

        $this->db->where('staff_id', $staff_id);
        $this->db->where('date >=', $start_date);
        $this->db->where('date <=', $end_date);
        $results = $this->db->get(db_prefix() . 'hr_attendance')->result();

        $map = [];
        foreach ($results as $row) {
            $map[$row->date] = $row;
        }
        return $map;
    }

    /**
     * Save/Mark Date Leave or Attendance & Auto-Recalculate Monthly Roll-up
     */
    public function save_date_leave($staff_id, $date, $status, $reason = '', $check_in = '09:30:00', $check_out = '18:30:00', $work_hours = 8.0)
    {
        if ($status == 'Absent' || $status == 'Paid Leave') {
            $work_hours = 0.0;
        } elseif ($status == 'Half Day') {
            $work_hours = 4.0;
        }

        $data = [
            'staff_id'   => $staff_id,
            'date'       => $date,
            'check_in'   => $check_in,
            'check_out'  => $check_out,
            'status'     => $status,
            'work_hours' => $work_hours,
            'notes'      => $reason
        ];

        $this->db->where('staff_id', $staff_id);
        $this->db->where('date', $date);
        $exists = $this->db->get(db_prefix() . 'hr_attendance')->row();

        if ($exists) {
            $this->db->where('staff_id', $staff_id);
            $this->db->where('date', $date);
            $this->db->update(db_prefix() . 'hr_attendance', $data);
        } else {
            $this->db->insert(db_prefix() . 'hr_attendance', $data);
        }

        // Auto-recalculate monthly attendance for this employee
        $month = (int) date('m', strtotime($date));
        $year  = (int) date('Y', strtotime($date));
        $this->recalculate_monthly_attendance($staff_id, $month, $year);

        return true;
    }

    /**
     * Recalculate monthly totals for staff from daily logs
     */
    public function recalculate_monthly_attendance($staff_id, $month, $year)
    {
        $days_in_month = $this->get_days_in_month($month, $year);
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date   = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);

        $this->db->where('staff_id', $staff_id);
        $this->db->where('date >=', $start_date);
        $this->db->where('date <=', $end_date);
        $logs = $this->db->get(db_prefix() . 'hr_attendance')->result();

        $marked_dates = [];
        $paid_leaves  = 0.0;
        $absent_days  = 0.0;
        $half_days    = 0.0;

        foreach ($logs as $log) {
            $marked_dates[$log->date] = true;
            if ($log->status == 'Paid Leave') {
                $paid_leaves += 1.0;
            } elseif ($log->status == 'Absent') {
                $absent_days += 1.0;
            } elseif ($log->status == 'Half Day') {
                $half_days += 1.0;
                $absent_days += 0.5; // Half day unpaid
            }
        }

        // Days not explicitly marked absent/leave are default present
        $present_days = (float)$days_in_month - ($paid_leaves + $absent_days);
        if ($present_days < 0) $present_days = 0.0;

        $payable_days = $present_days + $paid_leaves;
        if ($payable_days > $days_in_month) $payable_days = (float)$days_in_month;

        $save_data = [
            'staff_id'     => $staff_id,
            'month'        => $month,
            'year'         => $year,
            'total_days'   => $days_in_month,
            'present_days' => $present_days,
            'paid_leaves'  => $paid_leaves,
            'absent_days'  => $absent_days,
            'payable_days' => $payable_days,
            'notes'        => 'Auto-synced from Calendar Leaves'
        ];

        $this->db->where('staff_id', $staff_id);
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $exists = $this->db->get(db_prefix() . 'hr_monthly_attendance')->row();

        if ($exists) {
            $this->db->where('staff_id', $staff_id);
            $this->db->where('month', $month);
            $this->db->where('year', $year);
            $this->db->update(db_prefix() . 'hr_monthly_attendance', $save_data);
        } else {
            $this->db->insert(db_prefix() . 'hr_monthly_attendance', $save_data);
        }
        return true;
    }

    /**
     * Get Monthly Attendance Records for all active staff
     */
    public function get_monthly_attendance($month, $year)
    {
        $days_in_month = $this->get_days_in_month($month, $year);
        $staff_list = $this->db->get_where(db_prefix() . 'staff', ['active' => 1])->result();
        $records = [];

        foreach ($staff_list as $staff) {
            $this->db->where('staff_id', $staff->staffid);
            $this->db->where('month', $month);
            $this->db->where('year', $year);
            $att = $this->db->get(db_prefix() . 'hr_monthly_attendance')->row();

            if (!$att) {
                // Return default values
                $records[] = (object) [
                    'staff_id'      => $staff->staffid,
                    'firstname'     => $staff->firstname,
                    'lastname'      => $staff->lastname,
                    'email'         => $staff->email,
                    'total_days'    => $days_in_month,
                    'present_days'  => (float)$days_in_month,
                    'paid_leaves'   => 0.0,
                    'absent_days'   => 0.0,
                    'payable_days'  => (float)$days_in_month,
                    'notes'         => ''
                ];
            } else {
                $records[] = (object) [
                    'staff_id'      => $staff->staffid,
                    'firstname'     => $staff->firstname,
                    'lastname'      => $staff->lastname,
                    'email'         => $staff->email,
                    'total_days'    => $att->total_days,
                    'present_days'  => (float)$att->present_days,
                    'paid_leaves'   => (float)$att->paid_leaves,
                    'absent_days'   => (float)$att->absent_days,
                    'payable_days'  => (float)$att->payable_days,
                    'notes'         => $att->notes
                ];
            }
        }
        return $records;
    }

    /**
     * Save Monthly Attendance Grid
     */
    public function save_monthly_attendance($month, $year, $staff_data)
    {
        $days_in_month = $this->get_days_in_month($month, $year);

        foreach ($staff_data as $staff_id => $data) {
            $present = floatval($data['present_days'] ?? $days_in_month);
            $leaves  = floatval($data['paid_leaves'] ?? 0);
            $absent  = floatval($data['absent_days'] ?? 0);
            $payable = floatval($data['payable_days'] ?? ($present + $leaves));
            $notes   = $data['notes'] ?? '';

            $save_data = [
                'staff_id'     => $staff_id,
                'month'        => $month,
                'year'         => $year,
                'total_days'   => $days_in_month,
                'present_days' => $present,
                'paid_leaves'  => $leaves,
                'absent_days'  => $absent,
                'payable_days' => $payable,
                'notes'        => $notes
            ];

            $this->db->where('staff_id', $staff_id);
            $this->db->where('month', $month);
            $this->db->where('year', $year);
            $exists = $this->db->get(db_prefix() . 'hr_monthly_attendance')->row();

            if ($exists) {
                $this->db->where('staff_id', $staff_id);
                $this->db->where('month', $month);
                $this->db->where('year', $year);
                $this->db->update(db_prefix() . 'hr_monthly_attendance', $save_data);
            } else {
                $this->db->insert(db_prefix() . 'hr_monthly_attendance', $save_data);
            }
        }
        return true;
    }

    /**
     * Get Daily Attendance for specific date
     */
    public function get_daily_attendance($date)
    {
        $staff_list = $this->db->get_where(db_prefix() . 'staff', ['active' => 1])->result();
        $records = [];

        foreach ($staff_list as $staff) {
            $this->db->where('staff_id', $staff->staffid);
            $this->db->where('date', $date);
            $att = $this->db->get(db_prefix() . 'hr_attendance')->row();

            $records[] = (object) [
                'staff_id'   => $staff->staffid,
                'firstname'  => $staff->firstname,
                'lastname'   => $staff->lastname,
                'email'      => $staff->email,
                'date'       => $date,
                'check_in'   => $att ? $att->check_in : '09:30:00',
                'check_out'  => $att ? $att->check_out : '18:30:00',
                'status'     => $att ? $att->status : 'Present',
                'work_hours' => $att ? $att->work_hours : 8.00,
                'notes'      => $att ? $att->notes : ''
            ];
        }
        return $records;
    }

    /**
     * Save Daily Attendance Records
     */
    public function save_daily_attendance($date, $staff_data)
    {
        foreach ($staff_data as $staff_id => $data) {
            $status    = $data['status'] ?? 'Present';
            $check_in  = $data['check_in'] ?? '09:30:00';
            $check_out = $data['check_out'] ?? '18:30:00';
            $hours     = floatval($data['work_hours'] ?? ($status == 'Half Day' ? 4.0 : ($status == 'Present' ? 8.0 : 0.0)));
            $notes     = $data['notes'] ?? '';

            $save_data = [
                'staff_id'   => $staff_id,
                'date'       => $date,
                'check_in'   => $check_in,
                'check_out'  => $check_out,
                'status'     => $status,
                'work_hours' => $hours,
                'notes'      => $notes
            ];

            $this->db->where('staff_id', $staff_id);
            $this->db->where('date', $date);
            $exists = $this->db->get(db_prefix() . 'hr_attendance')->row();

            if ($exists) {
                $this->db->where('staff_id', $staff_id);
                $this->db->where('date', $date);
                $this->db->update(db_prefix() . 'hr_attendance', $save_data);
            } else {
                $this->db->insert(db_prefix() . 'hr_attendance', $save_data);
            }
        }
        return true;
    }

    /**
     * Calculate Indian Statutory Deductions
     */
    public function calculate_statutory($basic, $gross, $pf_app = 1, $esi_app = 1, $pt_app = 1, $state = 'Andhra Pradesh')
    {
        $pf_employee = 0.00;
        $pf_employer = 0.00;
        if ($pf_app) {
            $pf_employee = round($basic * 0.12, 2);
            $pf_employer = round($basic * 0.12, 2);
        }

        $esi_employee = 0.00;
        $esi_employer = 0.00;
        if ($esi_app && $gross <= 21000) {
            $esi_employee = round($gross * 0.0075, 2);
            $esi_employer = round($gross * 0.0325, 2);
        }

        $professional_tax = 0.00;
        if ($pt_app) {
            if ($gross > 20000) {
                $professional_tax = 200.00;
            } elseif ($gross > 15000) {
                $professional_tax = 150.00;
            } else {
                $professional_tax = 0.00;
            }
        }

        return [
            'pf_employee'      => $pf_employee,
            'pf_employer'      => $pf_employer,
            'esi_employee'     => $esi_employee,
            'esi_employer'     => $esi_employer,
            'professional_tax' => $professional_tax
        ];
    }

    /**
     * Get or Save Salary Structure
     */
    public function get_salary_structure($staff_id)
    {
        $this->db->where('staff_id', $staff_id);
        $struct = $this->db->get(db_prefix() . 'hr_salary_structures')->row();
        if (!$struct) {
            return (object) [
                'staff_id'          => $staff_id,
                'basic_salary'      => 0.00,
                'hra'               => 0.00,
                'special_allowance' => 0.00,
                'other_allowances'  => 0.00,
                'gross_monthly'     => 0.00,
                'pf_applicable'     => 1,
                'esi_applicable'    => 1,
                'pt_applicable'     => 1,
                'tds_monthly'       => 0.00
            ];
        }
        return $struct;
    }

    public function save_salary_structure($staff_id, $data)
    {
        $basic = floatval($data['basic_salary'] ?? 0);
        $hra = floatval($data['hra'] ?? 0);
        $special = floatval($data['special_allowance'] ?? 0);
        $other = floatval($data['other_allowances'] ?? 0);
        $gross = $basic + $hra + $special + $other;

        $save_data = [
            'basic_salary'      => $basic,
            'hra'               => $hra,
            'special_allowance' => $special,
            'other_allowances'  => $other,
            'gross_monthly'     => $gross,
            'pf_applicable'     => isset($data['pf_applicable']) ? 1 : 0,
            'esi_applicable'    => isset($data['esi_applicable']) ? 1 : 0,
            'pt_applicable'     => isset($data['pt_applicable']) ? 1 : 0,
            'tds_monthly'       => floatval($data['tds_monthly'] ?? 0)
        ];

        $this->db->where('staff_id', $staff_id);
        if ($this->db->get(db_prefix() . 'hr_salary_structures')->row()) {
            $this->db->where('staff_id', $staff_id);
            $this->db->update(db_prefix() . 'hr_salary_structures', $save_data);
        } else {
            $save_data['staff_id'] = $staff_id;
            $this->db->insert(db_prefix() . 'hr_salary_structures', $save_data);
        }
        return true;
    }

    /**
     * Run Monthly Payroll with Attendance Integration
     */
    public function execute_payroll_run($month, $year, $processed_by)
    {
        $days_in_month = $this->get_days_in_month($month, $year);

        $run_data = [
            'month'        => $month,
            'year'         => $year,
            'status'       => 'processed',
            'processed_by' => $processed_by,
            'created_at'   => date('Y-m-d H:i:s')
        ];
        $this->db->insert(db_prefix() . 'hr_payroll_runs', $run_data);
        $run_id = $this->db->insert_id();

        $staff_list = $this->db->get_where(db_prefix() . 'staff', ['active' => 1])->result();

        foreach ($staff_list as $staff) {
            $struct = $this->get_salary_structure($staff->staffid);
            if ($struct->gross_monthly <= 0) continue;

            // Fetch actual attendance
            $this->db->where('staff_id', $staff->staffid);
            $this->db->where('month', $month);
            $this->db->where('year', $year);
            $att = $this->db->get(db_prefix() . 'hr_monthly_attendance')->row();

            $payable_days = $att ? (float)$att->payable_days : (float)$days_in_month;
            $leave_days   = $att ? (float)$att->absent_days : 0.0;
            $total_days   = $att ? (float)$att->total_days : (float)$days_in_month;

            // Pro-rate salary if attendance is less than total days
            $attendance_factor = ($total_days > 0) ? ($payable_days / $total_days) : 1.0;
            if ($attendance_factor > 1.0) $attendance_factor = 1.0;

            $earned_basic   = round($struct->basic_salary * $attendance_factor, 2);
            $earned_hra     = round($struct->hra * $attendance_factor, 2);
            $earned_special = round(($struct->special_allowance + $struct->other_allowances) * $attendance_factor, 2);
            $earned_gross   = round($struct->gross_monthly * $attendance_factor, 2);

            $stat = $this->calculate_statutory(
                $earned_basic,
                $earned_gross,
                $struct->pf_applicable,
                $struct->esi_applicable,
                $struct->pt_applicable
            );

            $tds = floatval($struct->tds_monthly);
            $total_deductions = $stat['pf_employee'] + $stat['esi_employee'] + $stat['professional_tax'] + $tds;
            $net_salary = round($earned_gross - $total_deductions, 2);

            $payslip_no = 'PAY/' . $year . '/' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($staff->staffid, 4, '0', STR_PAD_LEFT);

            $payslip = [
                'payroll_run_id'   => $run_id,
                'staff_id'         => $staff->staffid,
                'month'            => $month,
                'year'             => $year,
                'payslip_number'   => $payslip_no,
                'basic'            => $earned_basic,
                'hra'              => $earned_hra,
                'special_allowance'=> $earned_special,
                'gross_salary'     => $earned_gross,
                'pf_employee'      => $stat['pf_employee'],
                'pf_employer'      => $stat['pf_employer'],
                'esi_employee'     => $stat['esi_employee'],
                'esi_employer'     => $stat['esi_employer'],
                'professional_tax' => $stat['professional_tax'],
                'tds'              => $tds,
                'other_deductions' => 0.00,
                'net_salary'       => $net_salary,
                'attendance_days'  => $payable_days,
                'leave_days'       => $leave_days,
                'created_at'       => date('Y-m-d H:i:s')
            ];

            $this->db->insert(db_prefix() . 'hr_payslips', $payslip);
        }

        return $run_id;
    }
}
