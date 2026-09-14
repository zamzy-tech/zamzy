<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_status extends ClientsController
{
    public function index($id = '', $hash = '')
    {
        if (empty($id) || empty($hash)) {
            show_404();
        }

        // Validate hash
        $expected_hash = md5($id . APP_ENC_KEY);
        if ($hash !== $expected_hash) {
            show_error('Access Denied: Invalid secure link.', 403);
        }

        // Load models
        $this->load->model('leads_model');
        $lead = $this->leads_model->get($id);

        if (!$lead) {
            show_404();
        }

        // Check password validation
        $password_error = '';
        $authenticated = $this->session->userdata('lead_status_auth_all') === true || $this->session->userdata('lead_status_auth_' . $id) === true;

        if ($this->input->post()) {
            $entered_password = $this->input->post('password');
            if ($entered_password === 'Credifix@62') {
                $this->session->set_userdata('lead_status_auth_' . $id, true);
                $authenticated = true;
            } else {
                $password_error = 'Incorrect password. Please try again.';
            }
        }

        // Disable standard navigation/menu so it's a clean external page
        $this->disableNavigation();
        $this->disableSubMenu();

        // Prepare view data
        $data['title']          = 'Lead Status Verification';
        $data['lead']           = $lead;
        $data['bodyclass']      = 'lead-status-public-view';
        $data['authenticated']  = $authenticated;
        $data['password_error'] = $password_error;

        if ($authenticated) {
            // Get status name
            $status_name = 'Unknown';
            if ($lead->status) {
                $status_obj = $this->leads_model->get_status($lead->status);
                if ($status_obj) {
                    $status_name = $status_obj->name;
                }
            }

            // Get status history if any
            $this->db->where('lead_id', $id);
            $this->db->order_by('changed_at', 'desc');
            $status_history = $this->db->get(db_prefix() . 'lead_loan_status_history')->result_array();

            // Get loan details if any
            $this->db->where('lead_id', $id);
            $loan_details = $this->db->get(db_prefix() . 'lead_loan_details')->row();

            $data['title']          = 'Lead Status: ' . $lead->name;
            $data['status_name']    = $status_name;
            $data['status_history'] = $status_history;
            $data['loan_details']   = $loan_details;
        }

        // Remove standard customer layout elements
        $this->app_css->remove('reset-css', 'customers-area-default');
        
        $this->data($data);
        $this->view('lead_status_view');
        $this->layout();
    }

    public function all($hash = '')
    {
        if (empty($hash)) {
            show_404();
        }

        // Validate hash
        $expected_hash = md5('all_converted_leads' . APP_ENC_KEY);
        if ($hash !== $expected_hash) {
            show_error('Access Denied: Invalid secure link.', 403);
        }

        // Check password validation
        $password_error = '';
        $authenticated = $this->session->userdata('lead_status_auth_all') === true;

        if ($this->input->post()) {
            $entered_password = $this->input->post('password');
            if ($entered_password === 'Credifix@62') {
                $this->session->set_userdata('lead_status_auth_all', true);
                $authenticated = true;
            } else {
                $password_error = 'Incorrect password. Please try again.';
            }
        }

        // Disable standard navigation/menu so it's a clean external page
        $this->disableNavigation();
        $this->disableSubMenu();

        // Prepare view data
        $data['title']          = 'Converted Leads Tracker';
        $data['bodyclass']      = 'lead-status-public-all-view';
        $data['authenticated']  = $authenticated;
        $data['password_error'] = $password_error;
        $data['hash']           = $hash;

        if ($authenticated) {
            // Load model
            $this->load->model('leads_model');

            // Fetch converted statuses IDs
            $statuses_res = $this->db->select('id, name, color')
                ->where_in('name', [
                    'Contacted / Interested',
                    'Converted / Deal Closed',
                    'Printed',
                    'Hand to Kiran',
                    'Hand to Bank',
                    'Need to Reupload',
                    'Bank Approved'
                ])->get(db_prefix() . 'leads_status')->result_array();

            $status_ids = array_column($statuses_res, 'id');

            // Build query for converted leads
            $this->db->select(db_prefix() . 'leads.id, ' . db_prefix() . 'leads.name, ' . db_prefix() . 'leads.phonenumber, ' . db_prefix() . 'leads_status.name as status_name, ' . db_prefix() . 'leads_status.color as status_color, ' . db_prefix() . 'lead_loan_details.loan_type');
            $this->db->from(db_prefix() . 'leads');
            $this->db->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status', 'left');
            $this->db->join(db_prefix() . 'lead_loan_details', db_prefix() . 'lead_loan_details.lead_id = ' . db_prefix() . 'leads.id', 'left');

            if (!empty($status_ids)) {
                $this->db->group_start();
                $this->db->where(db_prefix() . 'leads.client_id !=', 0);
                $this->db->or_where_in(db_prefix() . 'leads.status', $status_ids);
                $this->db->group_end();
            } else {
                $this->db->where(db_prefix() . 'leads.client_id !=', 0);
            }

            $this->db->order_by(db_prefix() . 'leads.id', 'DESC');
            $leads = $this->db->get()->result_array();

            // Add secure status link to each lead
            foreach ($leads as &$lead) {
                $lead['secure_link'] = site_url('lead/status/' . $lead['id'] . '/' . md5($lead['id'] . APP_ENC_KEY));
            }

            $data['leads'] = $leads;
        }

        // Remove standard customer layout elements
        $this->app_css->remove('reset-css', 'customers-area-default');
        
        $this->data($data);
        $this->view('lead_status_all_view');
        $this->layout();
    }
}
