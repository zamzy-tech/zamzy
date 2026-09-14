<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Public-facing controller for digital marketing lead capture forms.
 * No admin login required - accessible to external website visitors.
 */
class Digital_marketing_public extends App_Controller
{
    private $loans_source_name = 'Loans ADS';
    private $it_source_name    = 'IT LDS';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get or create a lead source by name, returning its ID.
     */
    private function _get_or_create_source($name)
    {
        $this->db->where('name', $name);
        $row = $this->db->get(db_prefix() . 'leads_sources')->row();
        if ($row) {
            return $row->id;
        }
        // Create it
        $this->db->insert(db_prefix() . 'leads_sources', ['name' => $name]);
        return $this->db->insert_id();
    }

    /**
     * Public Loans ADS lead capture form
     */
    public function loans()
    {
        if ($this->input->post()) {
            $this->_process_submission('loans');
            return;
        }

        $data['form_type']    = 'loans';
        $data['page_title']   = 'Loan Enquiry Form';
        $data['page_subtitle']= 'Fill in your details and get the best loan offers tailored for you.';
        $data['success']      = $this->session->flashdata('dm_success');
        $data['error']        = $this->session->flashdata('dm_error');
        $data['crm_name']     = get_option('companyname');
        $data['crm_logo']     = get_option('company_logo') ? base_url('uploads/company/' . get_option('company_logo')) : '';
        $data['crm_logo_dark']= get_option('company_logo_dark') ? base_url('uploads/company/' . get_option('company_logo_dark')) : '';
        $data['crm_email']    = get_option('email');
        $data['crm_phone']    = get_option('phonenumber');
        $data['crm_website']  = get_option('website');

        $this->load->view('digital_marketing_public/loans_form', $data);
    }

    /**
     * Public IT LDS lead capture form
     */
    public function it()
    {
        if ($this->input->post()) {
            $this->_process_submission('it');
            return;
        }

        $data['form_type']    = 'it';
        $data['page_title']   = 'IT Solutions Enquiry Form';
        $data['page_subtitle']= 'Tell us about your IT needs and we will craft the perfect solution for your business.';
        $data['crm_name']     = get_option('companyname');
        $data['crm_logo']     = get_option('company_logo') ? base_url('uploads/company/' . get_option('company_logo')) : '';
        $data['crm_logo_dark']= get_option('company_logo_dark') ? base_url('uploads/company/' . get_option('company_logo_dark')) : '';
        $data['crm_email']    = get_option('email');
        $data['crm_phone']    = get_option('phonenumber');
        $data['crm_website']  = get_option('website');
        $data['success']    = $this->session->flashdata('dm_success');
        $data['error']      = $this->session->flashdata('dm_error');

        $this->load->view('digital_marketing_public/it_form', $data);
    }

    /**
     * Process form POST submission and insert lead.
     */
    private function _process_submission($type)
    {
        $name        = trim($this->input->post('name'));
        $phone       = trim($this->input->post('phonenumber'));
        $email       = trim($this->input->post('email'));
        $description = trim($this->input->post('description'));
        $lead_value  = trim($this->input->post('lead_value'));

        if (empty($name) || empty($phone)) {
            $this->session->set_flashdata('dm_error', 'Please provide your name and phone number.');
            redirect(current_url());
            return;
        }

        // Check for duplicate phone number (match last 10 digits)
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) >= 10) {
            $phoneKey = substr($phoneClean, -10);
            $this->db->like('phonenumber', $phoneKey);
            $existing = $this->db->get(db_prefix() . 'leads')->row();
            if ($existing) {
                $this->session->set_flashdata('dm_success', 'Thank you! Your enquiry has been received.');
                redirect(current_url());
                return;
            }
        }

        $source_name = ($type === 'loans') ? $this->loans_source_name : $this->it_source_name;
        $source_id   = $this->_get_or_create_source($source_name);

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
            'description' => !empty($description) ? nl2br($description) : '',
            'lead_value'  => is_numeric($lead_value) ? $lead_value : 0,
            'source'      => $source_id,
            'status'      => $status_id,
            'assigned'    => 0,
            'address'     => '',
            'dateadded'   => date('Y-m-d H:i:s'),
            'addedfrom'   => 0,
            'is_public'   => 0,
            'loan_type'   => $loan_type,
        ];

        $this->db->insert(db_prefix() . 'leads', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if (!empty($data['phonenumber'])) {
                send_automation_whatsapp_reply($insert_id, $data['phonenumber']);
            }
            log_activity('New Public Lead Submitted via Digital Marketing Form [Source: ' . $source_name . ', ID: ' . $insert_id . ']');
            $this->session->set_flashdata('dm_success', 'Thank you! Your enquiry has been submitted. We will contact you shortly.');
        } else {
            $this->session->set_flashdata('dm_error', 'Something went wrong. Please try again.');
        }

        redirect(current_url());
    }
}
