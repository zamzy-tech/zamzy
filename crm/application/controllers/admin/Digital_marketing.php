<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Digital_marketing extends AdminController
{
    private $loans_source_name = 'Loans ADS';
    private $it_source_name    = 'IT LDS';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');

        // Ensure sources exist in DB
        $this->_ensure_source($this->loans_source_name);
        $this->_ensure_source($this->it_source_name);
    }

    /**
     * Ensure a lead source exists; create if missing.
     */
    private function _ensure_source($name)
    {
        $this->db->where('name', $name);
        $exists = $this->db->get(db_prefix() . 'leads_sources')->row();
        if (!$exists) {
            $this->db->insert(db_prefix() . 'leads_sources', ['name' => $name]);
        }
    }

    /**
     * Get source ID by name.
     */
    private function _get_source_id($name)
    {
        $this->db->where('name', $name);
        $row = $this->db->get(db_prefix() . 'leads_sources')->row();
        return $row ? $row->id : null;
    }

    /**
     * Loans ADS lead page
     */
    public function loans()
    {
        if (!is_admin()) {
            access_denied('Digital Marketing - Loans ADS Lead');
        }

        $source_id = $this->_get_source_id($this->loans_source_name);

        $this->db->select(db_prefix() . 'leads.*, ' . db_prefix() . 'leads_status.name as status_name, ' . db_prefix() . 'leads_sources.name as source_name');
        $this->db->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status', 'left');
        $this->db->join(db_prefix() . 'leads_sources', db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source', 'left');
        $this->db->where(db_prefix() . 'leads.source', $source_id);
        $this->db->order_by(db_prefix() . 'leads.dateadded', 'DESC');
        $leads = $this->db->get(db_prefix() . 'leads')->result_array();

        $statuses = $this->leads_model->get_status();

        $data['leads']       = $leads;
        $data['statuses']    = $statuses;
        $data['source_name'] = $this->loans_source_name;
        $data['source_id']   = $source_id;
        $data['form_type']   = 'loans';
        $data['public_url']  = base_url('digital_marketing_public/loans');
        $data['title']       = 'Loans ADS Lead';

        $this->load->view('admin/digital_marketing/loans', $data);
    }

    /**
     * IT LDS lead page
     */
    public function it()
    {
        if (!is_admin()) {
            access_denied('Digital Marketing - IT LDS Lead');
        }

        $source_id = $this->_get_source_id($this->it_source_name);

        $this->db->select(db_prefix() . 'leads.*, ' . db_prefix() . 'leads_status.name as status_name, ' . db_prefix() . 'leads_sources.name as source_name');
        $this->db->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status', 'left');
        $this->db->join(db_prefix() . 'leads_sources', db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source', 'left');
        $this->db->where(db_prefix() . 'leads.source', $source_id);
        $this->db->order_by(db_prefix() . 'leads.dateadded', 'DESC');
        $leads = $this->db->get(db_prefix() . 'leads')->result_array();

        $statuses = $this->leads_model->get_status();

        $data['leads']       = $leads;
        $data['statuses']    = $statuses;
        $data['source_name'] = $this->it_source_name;
        $data['source_id']   = $source_id;
        $data['form_type']   = 'it';
        $data['public_url']  = base_url('digital_marketing_public/it');
        $data['title']       = 'IT LDS Lead';

        $this->load->view('admin/digital_marketing/it', $data);
    }

    /**
     * Handle manual lead submission from admin panel (AJAX)
     */
    public function add_lead()
    {
        if (!is_admin()) {
            ajax_access_denied();
        }

        $source_id   = (int) $this->input->post('source_id');
        $name        = trim($this->input->post('name'));
        $phone       = trim($this->input->post('phonenumber'));
        $email       = trim($this->input->post('email'));
        $description = trim($this->input->post('description'));
        $lead_value  = trim($this->input->post('lead_value'));

        if (empty($name) || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Name and phone number are required.']);
            return;
        }

        // Check for duplicate phone number (match last 10 digits)
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) >= 10) {
            $phoneKey = substr($phoneClean, -10);
            $this->db->like('phonenumber', $phoneKey);
            $existing = $this->db->get(db_prefix() . 'leads')->row();
            if ($existing) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Duplicate lead: A lead with this phone number already exists in CRM.'
                ]);
                return;
            }
        }

        // Get first lead status
        $first_status = $this->db->order_by('id', 'ASC')->limit(1)->get(db_prefix() . 'leads_status')->row();
        $status_id    = $first_status ? $first_status->id : 1;

        $loan_type = 'Personal';
        if (strpos(strtolower($description), 'business') !== false) {
            $loan_type = 'Business';
        }

        $data = [
            'name'        => $name,
            'phonenumber' => $phone,
            'email'       => $email,
            'description' => $description,
            'lead_value'  => !empty($lead_value) ? $lead_value : 0,
            'source'      => $source_id,
            'status'      => $status_id,
            'assigned'    => 0,
            'address'     => '',
            'dateadded'   => date('Y-m-d H:i:s'),
            'addedfrom'   => get_staff_user_id(),
            'is_public'   => 0,
            'loan_type'   => $loan_type,
        ];

        $this->db->insert(db_prefix() . 'leads', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Lead Added via Digital Marketing Form [ID: ' . $insert_id . ']');
            echo json_encode(['success' => true, 'lead_id' => $insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add lead.']);
        }
    }
}
