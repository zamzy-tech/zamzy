<?php
defined('BASEPATH') or exit('No direct script access allowed');

class India_hr_payroll extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('india_hr_payroll_model');
    }

    /**
     * Dashboard / Overview
     */
    public function index()
    {
        $this->agent_leads();
    }

    /**
     * Attendance & Leave Management
     */
    public function attendance()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $tab               = $this->input->get('tab') ? $this->input->get('tab') : 'calendar';
        $month             = $this->input->get('month') ? (int)$this->input->get('month') : (int)date('m');
        $year              = $this->input->get('year') ? (int)$this->input->get('year') : (int)date('Y');
        $date              = $this->input->get('date') ? $this->input->get('date') : date('Y-m-d');
        $staff_members     = $this->staff_model->get('', ['active' => 1]);
        $first_staff_id    = !empty($staff_members) ? $staff_members[0]['staffid'] : get_staff_user_id();
        $selected_staff_id = $this->input->get('staff_id') ? (int)$this->input->get('staff_id') : $first_staff_id;

        // Handle Single Date Leave / Attendance Save (From Calendar Modal)
        if ($this->input->post('action') == 'save_date_leave') {
            $p_staff_id = (int)$this->input->post('staff_id');
            $p_date     = $this->input->post('date');
            $status     = $this->input->post('status');
            $reason     = $this->input->post('reason');
            $work_hours = (float)($this->input->post('work_hours') ?? 8.0);

            $this->india_hr_payroll_model->save_date_leave($p_staff_id, $p_date, $status, $reason, '09:30:00', '18:30:00', $work_hours);
            
            $p_month = (int)date('m', strtotime($p_date));
            $p_year  = (int)date('Y', strtotime($p_date));
            set_alert('success', 'Marked ' . $status . ' for ' . date('d M Y', strtotime($p_date)) . ' successfully!');
            redirect(admin_url('india_hr_payroll/attendance?tab=calendar&staff_id=' . $p_staff_id . '&month=' . $p_month . '&year=' . $p_year));
        }

        // Handle Monthly Attendance Save
        if ($this->input->post('action') == 'save_monthly_attendance') {
            $staff_data = $this->input->post('staff_attendance');
            $p_month    = (int)$this->input->post('month');
            $p_year     = (int)$this->input->post('year');

            if (!empty($staff_data)) {
                $this->india_hr_payroll_model->save_monthly_attendance($p_month, $p_year, $staff_data);
                set_alert('success', 'Monthly attendance for ' . date('F Y', mktime(0, 0, 0, $p_month, 10, $p_year)) . ' saved successfully!');
            }
            redirect(admin_url('india_hr_payroll/attendance?tab=monthly&month=' . $p_month . '&year=' . $p_year));
        }

        // Handle Daily Attendance Save
        if ($this->input->post('action') == 'save_daily_attendance') {
            $daily_data = $this->input->post('daily_attendance');
            $p_date     = $this->input->post('date');

            if (!empty($daily_data)) {
                $this->india_hr_payroll_model->save_daily_attendance($p_date, $daily_data);
                set_alert('success', 'Daily attendance for ' . date('d M Y', strtotime($p_date)) . ' saved successfully!');
            }
            redirect(admin_url('india_hr_payroll/attendance?tab=daily&date=' . $p_date));
        }

        $data['title']             = '📅 Attendance & Leave Management';
        $data['active_tab']        = $tab;
        $data['month']             = $month;
        $data['year']              = $year;
        $data['date']              = $date;
        $data['staff_members']     = $staff_members;
        $data['selected_staff_id'] = $selected_staff_id;
        $data['calendar_records']  = $this->india_hr_payroll_model->get_staff_calendar_attendance($selected_staff_id, $month, $year);
        $data['monthly_records']   = $this->india_hr_payroll_model->get_monthly_attendance($month, $year);
        $data['daily_records']     = $this->india_hr_payroll_model->get_daily_attendance($date);

        $this->load->view('india_hr_payroll/attendance', $data);
    }

    /**
     * Agent Telecalling Workspace (My Assigned Leads)
     */
    public function agent_leads()
    {
        $current_staff_id = get_staff_user_id();
        $selected_agent   = $this->input->get('agent_id') ? $this->input->get('agent_id') : $current_staff_id;

        // Non-admin agents can only view their own leads
        if (!is_admin()) {
            $selected_agent = $current_staff_id;
        }

        if ($this->input->post('action') == 'update_lead') {
            $lead_id    = $this->input->post('lead_id');
            $new_status = $this->input->post('status');
            $call_notes = $this->input->post('call_notes');

            $this->db->where('id', $lead_id);
            if (!is_admin()) {
                $this->db->where('assigned', $current_staff_id);
            }
            $this->db->update(db_prefix() . 'leads', [
                'status'      => $new_status,
                'description' => $call_notes,
                'lastcontact' => date('Y-m-d H:i:s')
            ]);
            set_alert('success', 'Lead status & call notes updated successfully!');
            redirect(admin_url('india_hr_payroll/agent_leads?agent_id=' . $selected_agent));
        }

        $data['title']          = '📞 Agent Telecalling Workspace';
        $data['selected_agent'] = $selected_agent;
        $data['staff_members']  = $this->staff_model->get();
        $data['statuses']       = $this->db->get(db_prefix() . 'leads_status')->result();

        // Fetch assigned leads
        $this->db->where('assigned', $selected_agent);
        $this->db->order_by('id', 'DESC');
        $data['leads'] = $this->db->get(db_prefix() . 'leads')->result();

        $this->load->view('india_hr_payroll/agent_leads', $data);
    }

    /**
     * Bulk Delete Leads (Admin Only)
     */
    public function bulk_delete_leads()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        if ($this->input->post('delete_all_agent')) {
            $agent_id = $this->input->post('agent_id');
            $this->db->where('assigned', $agent_id);
            $this->db->delete(db_prefix() . 'leads');
            set_alert('success', 'All leads for the selected agent have been deleted.');
            redirect(admin_url('india_hr_payroll/agent_leads?agent_id=' . $agent_id));
        }

        $lead_ids = $this->input->post('lead_ids');
        if (!empty($lead_ids) && is_array($lead_ids)) {
            $this->db->where_in('id', $lead_ids);
            $this->db->delete(db_prefix() . 'leads');
            set_alert('success', 'Deleted ' . count($lead_ids) . ' selected leads successfully.');
        } else {
            set_alert('warning', 'No leads were selected for deletion.');
        }
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('india_hr_payroll/agent_leads'));
    }

    /**
     * Single Lead Delete (Admin Only)
     */
    public function delete_lead($id)
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'leads');
        set_alert('success', 'Lead #' . $id . ' deleted successfully.');
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('india_hr_payroll/agent_leads'));
    }

    /**
     * Bulk Lead Upload & Agent Assignment Tool (Admin Only)
     */
    public function bulk_lead_import()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        if ($this->input->post()) {
            $target_agent = $this->input->post('assigned_agent');
            $status_id    = $this->input->post('lead_status') ? $this->input->post('lead_status') : 2; // Default New Lead

            if (!empty($_FILES['csv_file']['tmp_name'])) {
                $file = $_FILES['csv_file']['tmp_name'];
                $handle = fopen($file, "r");
                $row = 0;
                $inserted_count = 0;

                while (($data_row = fgetcsv($handle, 2000, ",")) !== FALSE) {
                    $row++;
                    if ($row == 1) continue; // Skip header

                    $company_name = isset($data_row[0]) ? trim($data_row[0]) : '';
                    $mobile       = isset($data_row[1]) ? trim($data_row[1]) : '';
                    $category     = isset($data_row[2]) ? trim($data_row[2]) : '';
                    $main_feature = isset($data_row[3]) ? trim($data_row[3]) : '';
                    $pitch_script = isset($data_row[4]) ? trim($data_row[4]) : '';
                    $call_notes   = isset($data_row[5]) ? trim($data_row[5]) : '';

                    if (!empty($company_name) || !empty($mobile)) {
                        $this->db->insert(db_prefix() . 'leads', [
                            'name'         => !empty($company_name) ? $company_name : 'Lead ' . $mobile,
                            'company'      => $company_name,
                            'phonenumber'  => $mobile,
                            'category'     => $category,
                            'main_feature' => $main_feature,
                            'pitch_script' => $pitch_script,
                            'description'  => $call_notes,
                            'assigned'     => $target_agent,
                            'status'       => $status_id,
                            'source'       => 4, // Bulk Upload Source
                            'dateadded'    => date('Y-m-d H:i:s')
                        ]);
                        $inserted_count++;
                    }
                }
                fclose($handle);
                set_alert('success', 'Successfully uploaded and assigned ' . $inserted_count . ' leads to the selected agent!');
            } else {
                set_alert('warning', 'Please select a valid CSV file.');
            }
            redirect(admin_url('india_hr_payroll/bulk_lead_import'));
        }

        $data['title']         = '📥 Bulk Upload & Assign Leads';
        $data['staff_members'] = $this->staff_model->get();
        $data['statuses']      = $this->db->get(db_prefix() . 'leads_status')->result();
        $this->load->view('india_hr_payroll/bulk_lead_import', $data);
    }

    /**
     * Download Sample CSV with the 6 exact requested columns
     */
    public function download_sample_csv()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="telecalling_leads_sample.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Company Name',
            'Mobile Number',
            'Category',
            'Main Feature to Explain',
            'What to Say on the Call',
            'Call Status / Notes'
        ]);
        fputcsv($output, [
            'Sri Sai Traders',
            '9876543210',
            'Business Loan',
            'Zero Processing Fee & Disbursal in 24 Hours',
            'Hello Sir, I am calling from Credifix. We have pre-approved your firm for a Business Loan up to 15 Lakhs at 8.5% interest. Would you like to proceed with verification?',
            'Pre-approved loan customer, follow up for KYC'
        ]);
        fputcsv($output, [
            'Tech Solutions Hyderabad',
            '9123456789',
            'Website & CRM',
            'Complete CRM Automation with WhatsApp Integration',
            'Good morning Ma\'am, calling from Credifix regarding complete CRM & Web development services tailored for your business. Can we schedule a quick 10-minute demo today?',
            'Interested in custom CRM software demo'
        ]);
        fclose($output);
        exit;
    }

    /**
     * 16-Slot Employee Document Vault (Admin Only)
     */
    public function onboarding_vault($staff_id = '')
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        if (empty($staff_id)) {
            $staff_id = get_staff_user_id();
        }

        $data['title']            = '16-Slot Employee Document Vault';
        $data['selected_staff_id']= $staff_id;
        $data['staff_members']    = $this->staff_model->get();
        $data['document_slots']   = $this->india_hr_payroll_model->get_document_slots();
        $data['employee_details'] = $this->india_hr_payroll_model->get_employee_details($staff_id);
        $data['uploaded_docs']    = $this->india_hr_payroll_model->get_employee_documents($staff_id);

        $this->load->view('india_hr_payroll/onboarding_vault', $data);
    }

    /**
     * Handle uploading individual documents
     */
    public function upload_document()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        if ($this->input->post()) {
            $staff_id = $this->input->post('staff_id');
            $doc_type = $this->input->post('doc_type');
            $notes    = $this->input->post('notes');

            if ($this->input->post('pan_number')) {
                $this->india_hr_payroll_model->save_employee_details($staff_id, $this->input->post());
            }

            if (!empty($_FILES['file']['name'])) {
                $upload_path = FCPATH . 'uploads/hr_documents/' . $staff_id . '/';
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $filename = time() . '_' . str_replace(' ', '_', $_FILES['file']['name']);
                $target_file = $upload_path . $filename;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                    $this->db->insert(db_prefix() . 'hr_employee_documents', [
                        'staff_id'    => $staff_id,
                        'doc_type'    => $doc_type,
                        'file_name'   => $_FILES['file']['name'],
                        'file_path'   => 'uploads/hr_documents/' . $staff_id . '/' . $filename,
                        'notes'       => $notes,
                        'uploaded_at' => date('Y-m-d H:i:s')
                    ]);
                    set_alert('success', 'Document uploaded successfully!');
                } else {
                    set_alert('warning', 'Failed to save document file.');
                }
            } else {
                set_alert('success', 'Employee details updated successfully!');
            }
        }
        redirect(admin_url('india_hr_payroll/onboarding_vault/' . $staff_id));
    }

    /**
     * Delete Document Slot Entry
     */
    public function delete_document($id, $staff_id)
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $this->db->where('id', $id);
        $doc = $this->db->get(db_prefix() . 'hr_employee_documents')->row();
        if ($doc) {
            if (file_exists(FCPATH . $doc->file_path)) {
                @unlink(FCPATH . $doc->file_path);
            }
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'hr_employee_documents');
            set_alert('success', 'Document deleted.');
        }
        redirect(admin_url('india_hr_payroll/onboarding_vault/' . $staff_id));
    }

    /**
     * Letter Generator Page
     */
    public function document_generator()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $data['title']         = 'India HR Letter Generator';
        $data['staff_members'] = $this->staff_model->get();
        $this->load->view('india_hr_payroll/document_generator', $data);
    }

    /**
     * Download Joining / Appointment Letter
     */
    public function print_joining_letter($staff_id)
    {
        if (!is_admin() && get_staff_user_id() != $staff_id) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $data['staff']    = $this->staff_model->get($staff_id);
        $data['details']  = $this->india_hr_payroll_model->get_employee_details($staff_id);
        $data['salary']   = $this->india_hr_payroll_model->get_salary_structure($staff_id);
        $data['doc_type'] = 'Joining Letter';

        $this->load->view('india_hr_payroll/print_letter', $data);
    }

    /**
     * Download Offer Letter
     */
    public function print_offer_letter($staff_id)
    {
        if (!is_admin() && get_staff_user_id() != $staff_id) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $data['staff']    = $this->staff_model->get($staff_id);
        $data['details']  = $this->india_hr_payroll_model->get_employee_details($staff_id);
        $data['salary']   = $this->india_hr_payroll_model->get_salary_structure($staff_id);
        $data['doc_type'] = 'Offer Letter';

        $this->load->view('india_hr_payroll/print_letter', $data);
    }

    /**
     * Download Relieving Letter
     */
    public function print_relieving_letter($staff_id)
    {
        if (!is_admin() && get_staff_user_id() != $staff_id) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $data['staff']    = $this->staff_model->get($staff_id);
        $data['details']  = $this->india_hr_payroll_model->get_employee_details($staff_id);
        $data['salary']   = $this->india_hr_payroll_model->get_salary_structure($staff_id);
        $data['doc_type'] = 'Relieving Letter';

        $this->load->view('india_hr_payroll/print_letter', $data);
    }

    /**
     * View & Print Individual Payslip
     */
    public function print_payslip($payslip_id)
    {
        $this->db->where('id', $payslip_id);
        $data['payslip'] = $this->db->get(db_prefix() . 'hr_payslips')->row();
        if (!$data['payslip']) {
            show_404();
        }

        if (!is_admin() && get_staff_user_id() != $data['payslip']->staff_id) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $data['staff']   = $this->staff_model->get($data['payslip']->staff_id);
        $data['details'] = $this->india_hr_payroll_model->get_employee_details($data['payslip']->staff_id);

        $this->load->view('india_hr_payroll/print_payslip', $data);
    }

    /**
     * Salary Structures (CTC Builder - Admin Only)
     */
    public function salary_structures($staff_id = '')
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        if (empty($staff_id)) {
            $staff_id = get_staff_user_id();
        }

        if ($this->input->post()) {
            $this->india_hr_payroll_model->save_salary_structure($staff_id, $this->input->post());
            set_alert('success', 'Salary structure saved!');
            redirect(admin_url('india_hr_payroll/salary_structures/' . $staff_id));
        }

        $data['title']            = 'Indian CTC & Salary Structures';
        $data['selected_staff_id']= $staff_id;
        $data['staff_members']    = $this->staff_model->get();
        $data['salary_structure'] = $this->india_hr_payroll_model->get_salary_structure($staff_id);

        $this->load->view('india_hr_payroll/salary_structures', $data);
    }

    /**
     * Run Monthly Payroll (Admin Only)
     */
    public function run_payroll()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year  = $this->input->post('year');
            $run_id = $this->india_hr_payroll_model->execute_payroll_run($month, $year, get_staff_user_id());
            set_alert('success', 'Payroll run executed successfully! Generated payslips with attendance sync for ' . $month . '/' . $year);
            redirect(admin_url('india_hr_payroll/payslips'));
        }

        $data['title'] = 'Run Monthly Payroll (India Rules)';
        $this->load->view('india_hr_payroll/run_payroll', $data);
    }

    /**
     * Payslip Registry (Admin Only)
     */
    public function payslips()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $data['title']    = 'Payslip Registry';
        $data['payslips'] = $this->db->select('p.*, s.firstname, s.lastname, s.email')
                                     ->from(db_prefix() . 'hr_payslips p')
                                     ->join(db_prefix() . 'staff s', 's.staffid = p.staff_id')
                                     ->order_by('p.id', 'DESC')
                                     ->get()->result();

        $this->load->view('india_hr_payroll/payslips', $data);
    }

    /**
     * PF / ESI / Professional Tax Compliance Reports (Admin Only)
     */
    public function pf_esi_reports()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $data['title'] = 'EPFO (PF), ESIC & PT Statutory Reports';
        $data['month'] = $this->input->get('month') ? $this->input->get('month') : date('m');
        $data['year']  = $this->input->get('year') ? $this->input->get('year') : date('Y');

        $data['reports'] = $this->db->select('p.*, s.firstname, s.lastname, d.pan_number, d.aadhaar_number, d.uan_number, d.esic_number, d.bank_account_no, d.ifsc_code')
                                    ->from(db_prefix() . 'hr_payslips p')
                                    ->join(db_prefix() . 'staff s', 's.staffid = p.staff_id')
                                    ->join(db_prefix() . 'hr_employee_details d', 'd.staff_id = p.staff_id', 'left')
                                    ->where('p.month', $data['month'])
                                    ->where('p.year', $data['year'])
                                    ->get()->result();

        $this->load->view('india_hr_payroll/pf_esi_reports', $data);
    }

    /**
     * Exit & Relieving Management (Admin Only)
     */
    public function exit_management()
    {
        if (!is_admin()) {
            redirect(admin_url('india_hr_payroll/agent_leads'));
        }

        $data['title']         = 'Exit Management & Relieving Letters';
        $data['staff_members'] = $this->staff_model->get();
        $this->load->view('india_hr_payroll/exit_management', $data);
    }
}
