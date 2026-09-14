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

        // Disable standard navigation/menu so it's a clean external page
        $this->disableNavigation();
        $this->disableSubMenu();

        // Prepare view data
        $data['title']          = 'Lead Status: ' . $lead->name;
        $data['lead']           = $lead;
        $data['status_name']    = $status_name;
        $data['status_history'] = $status_history;
        $data['loan_details']   = $loan_details;
        $data['bodyclass']      = 'lead-status-public-view';

        // Remove standard customer layout elements
        $this->app_css->remove('reset-css', 'customers-area-default');
        
        $this->data($data);
        $this->view('lead_status_view');
        $this->layout();
    }
}
