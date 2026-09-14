<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Document_share extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');
        $this->load->library('session');
    }

    public function login($hash = '')
    {
        if (!$hash) {
            show_404();
        }

        // Fetch lead by hash
        $this->db->where('hash', $hash);
        $lead = $this->db->get(db_prefix() . 'leads')->row();

        if (!$lead) {
            show_404();
        }

        // If already logged in, redirect to portal
        if ($this->session->userdata('lead_auth_id') == $lead->id) {
            redirect('document_share/portal/' . $hash);
        }

        $error = '';
        if ($this->input->post()) {
            $email = trim($this->input->post('email'));
            $phone = trim($this->input->post('phone'));

            // Check match
            $email_matches = (strtolower($email) === strtolower($lead->email));
            
            // Clean phone numbers for comparison
            $clean_input_phone = preg_replace('/\D/', '', $phone);
            $clean_lead_phone = preg_replace('/\D/', '', $lead->phonenumber);
            $phone_matches = ($clean_input_phone !== '' && $clean_input_phone === $clean_lead_phone);

            if ($email_matches && $phone_matches) {
                $this->session->set_userdata([
                    'lead_auth_id'   => $lead->id,
                    'lead_auth_hash' => $hash,
                ]);
                redirect('document_share/portal/' . $hash);
            } else {
                $error = 'Invalid email or phone number. Please try again.';
            }
        }

        $data = [
            'lead'  => $lead,
            'error' => $error,
            'hash'  => $hash,
        ];

        $this->load->view('document_share/login', $data);
    }

    public function portal($hash = '')
    {
        if (!$hash) {
            show_404();
        }

        // Fetch lead by hash
        $this->db->where('hash', $hash);
        $lead = $this->db->get(db_prefix() . 'leads')->row();

        if (!$lead) {
            show_404();
        }

        // Authenticate session
        if ($this->session->userdata('lead_auth_id') != $lead->id) {
            redirect('document_share/login/' . $hash);
        }

        // Fetch details
        $this->db->where('lead_id', $lead->id);
        $details = $this->db->get(db_prefix() . 'lead_loan_details')->row();

        // Fetch uploaded documents
        $this->db->where('lead_id', $lead->id);
        $documents = $this->db->get(db_prefix() . 'lead_loan_documents')->result_array();

        $data = [
            'lead'      => $lead,
            'details'   => $details ?: (object)[],
            'documents' => $documents,
            'hash'      => $hash,
        ];

        $this->load->view('document_share/portal', $data);
    }

    public function upload($hash = '')
    {
        if (!$hash || !$this->input->post()) {
            show_404();
        }

        $this->db->where('hash', $hash);
        $lead = $this->db->get(db_prefix() . 'leads')->row();

        if (!$lead || $this->session->userdata('lead_auth_id') != $lead->id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            return;
        }

        $document_type = $this->input->post('document_type');
        if (!$document_type) {
            echo json_encode(['success' => false, 'message' => 'Missing document type.']);
            return;
        }

        $path = FCPATH . 'uploads/lead_loan_documents/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // Validate extension manually to bypass CodeIgniter's strict MIME check bug
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
        if (!in_array($ext, $allowed_exts)) {
            echo json_encode(['success' => false, 'message' => 'The filetype you are attempting to upload is not allowed. (Allowed: ' . implode(', ', $allowed_exts) . ')']);
            return;
        }

        $config['upload_path']   = $path;
        $config['allowed_types'] = '*';
        $config['max_size']      = 20480; // 20MB
        $config['encrypt_name']  = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            echo json_encode([
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ]);
        } else {
            $upload_data = $this->upload->data();
            
            $doc_data = [
                'lead_id'       => $lead->id,
                'document_type' => $document_type,
                'file_name'     => $upload_data['client_name'],
                'file_path'     => 'uploads/lead_loan_documents/' . $upload_data['file_name'],
                'uploaded_at'   => date('Y-m-d H:i:s')
            ];

            // Delete previous document of same type
            $this->db->where('lead_id', $lead->id);
            $this->db->where('document_type', $document_type);
            $prev = $this->db->get(db_prefix() . 'lead_loan_documents')->row();
            if ($prev) {
                if (file_exists(FCPATH . $prev->file_path)) {
                    @unlink(FCPATH . $prev->file_path);
                }
                $this->db->where('id', $prev->id);
                $this->db->delete(db_prefix() . 'lead_loan_documents');
            }

            $success = $this->db->insert(db_prefix() . 'lead_loan_documents', $doc_data);
            
            echo json_encode([
                'success' => $success ? true : false,
                'message' => 'Document uploaded successfully.',
                'document' => $doc_data
            ]);
        }
    }

    public function delete($hash = '', $doc_id = '')
    {
        if (!$hash || !$doc_id) {
            show_404();
        }

        $this->db->where('hash', $hash);
        $lead = $this->db->get(db_prefix() . 'leads')->row();

        if (!$lead || $this->session->userdata('lead_auth_id') != $lead->id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            return;
        }

        $this->db->where('id', $doc_id);
        $doc = $this->db->get(db_prefix() . 'lead_loan_documents')->row();

        if ($doc && $doc->lead_id == $lead->id) {
            if (file_exists(FCPATH . $doc->file_path)) {
                @unlink(FCPATH . $doc->file_path);
            }

            $this->db->where('id', $doc_id);
            $success = $this->db->delete(db_prefix() . 'lead_loan_documents');

            echo json_encode([
                'success' => $success ? true : false,
                'message' => 'Document deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Document not found.'
            ]);
        }
    }

    public function logout($hash = '')
    {
        $this->session->unset_userdata('lead_auth_id');
        $this->session->unset_userdata('lead_auth_hash');
        redirect('document_share/login/' . $hash);
    }
}
