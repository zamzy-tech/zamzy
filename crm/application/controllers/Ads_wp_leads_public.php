<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Public receiver for CREDIFIX website WhatsApp OTP-verified lead submissions.
 * No admin login required — called via HTTP GET from credifix/send_otp.php
 */
class Ads_wp_leads_public extends App_Controller
{
    private $source_name = 'Ads WhatsApp';

    // Shared secret — must match what credifix/send_otp.php sends
    private $secret_key  = 'credifix_wp_2026';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Receive a lead submission from the credifix website.
     *
     * GET params:
     *   secret   - shared secret key (security check)
     *   name     - customer full name
     *   phone    - WhatsApp mobile number (10 digits)
     *   service  - service/loan type selected
     *   amount   - loan/service amount requested
     *   batch    - optional batch/section label
     */
    public function receive()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        // Security check
        $secret = $this->input->get('secret');
        if ($secret !== $this->secret_key) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $name    = trim($this->input->get('name')    ?? '');
        $phone   = trim($this->input->get('phone')   ?? '');
        $service = trim($this->input->get('service') ?? '');
        $amount  = trim($this->input->get('amount')  ?? '');
        $batch   = trim($this->input->get('batch')   ?? '');

        if (empty($name) && empty($phone)) {
            echo json_encode(['success' => false, 'error' => 'Name and phone are required.']);
            exit;
        }

        // Check for duplicate phone number (match last 10 digits)
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) >= 10) {
            $phoneKey = substr($phoneClean, -10);
            $this->db->like('phonenumber', $phoneKey);
            $existing = $this->db->get(db_prefix() . 'leads')->row();
            if ($existing) {
                echo json_encode([
                    'success' => true,
                    'lead_id' => $existing->id,
                    'message' => 'Lead already exists in CRM (skipped duplicate).'
                ]);
                exit;
            }
        }

        // Get or create lead source
        $source_id = $this->_get_or_create_source($this->source_name);

        // Get first lead status
        $first_status = $this->db->order_by('id', 'ASC')->limit(1)
            ->get(db_prefix() . 'leads_status')->row();
        $status_id = $first_status ? $first_status->id : 1;

        $description = '';

        $loan_type = 'Personal';
        if (strpos(strtolower($service), 'business') !== false) {
            $loan_type = 'Business';
        }

        $data = [
            'name'        => $name ?: 'Unknown',
            'phonenumber' => $phone,
            'company'     => $service,      // service stored in company field
            'lead_value'  => is_numeric($amount) ? (float)$amount : 0,
            'description' => $description,
            'source'      => $source_id,
            'status'      => $status_id,
            'assigned'    => 0,
            'address'     => '',
            'dateadded'   => date('Y-m-d H:i:s'),
            'addedfrom'   => 0,
            'is_public'   => 0,
            'batch_name'  => $batch ?: null,
            'loan_type'   => $loan_type,
        ];

        $this->db->insert(db_prefix() . 'leads', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if (!empty($data['phonenumber'])) {
                send_automation_whatsapp_reply($insert_id, $data['phonenumber']);
            }
            log_activity('New Ads WhatsApp Lead received from CREDIFIX website [ID: ' . $insert_id . ', Name: ' . $name . ', Phone: ' . $phone . ']');
            echo json_encode([
                'success'  => true,
                'lead_id'  => $insert_id,
                'message'  => 'Lead saved to CRM successfully.',
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save lead to database.']);
        }
        exit;
    }

    /**
     * Get source ID by name, create it if it doesn't exist.
     */
    private function _get_or_create_source($name)
    {
        $this->db->where('name', $name);
        $row = $this->db->get(db_prefix() . 'leads_sources')->row();
        if ($row) {
            return $row->id;
        }
        $this->db->insert(db_prefix() . 'leads_sources', ['name' => $name]);
        return $this->db->insert_id();
    }
}
