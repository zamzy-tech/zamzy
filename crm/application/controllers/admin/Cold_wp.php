<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cold_wp extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');

        // Automatic DB Migration checks
        $db_prefix = db_prefix();
        if (!$this->db->table_exists($db_prefix . 'cold_wp_messages')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$db_prefix}cold_wp_messages` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `lead_id` INT NOT NULL,
                `phone_number` VARCHAR(30) NOT NULL,
                `message_text` TEXT NOT NULL,
                `image_path` VARCHAR(255) DEFAULT NULL,
                `sent_by` VARCHAR(100) NOT NULL,
                `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `status` VARCHAR(20) DEFAULT 'Sent'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        if (!$this->db->table_exists($db_prefix . 'cold_wp_templates')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$db_prefix}cold_wp_templates` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `message_text` TEXT NOT NULL,
                `image_path` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        if (!$this->db->field_exists('batch_name', $db_prefix . 'leads')) {
            $this->db->query("ALTER TABLE `{$db_prefix}leads` ADD COLUMN `batch_name` VARCHAR(191) DEFAULT NULL");
        }
    }

    public function index()
    {
        if (!staff_can('view', 'cold_wp_messages')) {
            access_denied('Cold WP Messages');
        }

        $lead_ids_str = $this->input->get('ids');
        $leads = [];

        $db_prefix = db_prefix();
        if (!empty($lead_ids_str)) {
            $ids = explode(',', $lead_ids_str);
            $ids = array_filter(array_map('intval', $ids));
            
            if (count($ids) > 0) {
                $this->db->select($db_prefix . 'leads.*, ' . $db_prefix . 'leads_status.name as status_name');
                $this->db->join($db_prefix . 'leads_status', $db_prefix . 'leads_status.id = ' . $db_prefix . 'leads.status', 'left');
                $this->db->where_in($db_prefix . 'leads.id', $ids);
                $leads = $this->db->get($db_prefix . 'leads')->result_array();
            }
        } else {
            // Load all active leads (not lost, not junk) by default
            $this->db->select($db_prefix . 'leads.*, ' . $db_prefix . 'leads_status.name as status_name');
            $this->db->join($db_prefix . 'leads_status', $db_prefix . 'leads_status.id = ' . $db_prefix . 'leads.status', 'left');
            $this->db->where('lost', 0);
            $this->db->where('junk', 0);
            $this->db->order_by('name', 'asc');
            $leads = $this->db->get($db_prefix . 'leads')->result_array();
        }

        $templates = $this->db->order_by('title', 'asc')->get(db_prefix() . 'cold_wp_templates')->result_array();

        $data = [
            'leads' => $leads,
            'templates' => $templates,
            'title' => 'Send Cold WhatsApp Messages'
        ];

        $this->load->view('admin/cold_wp/send', $data);
    }

    public function log_send()
    {
        if (!staff_can('create', 'cold_wp_messages')) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        $lead_id = $this->input->post('lead_id');
        $phone_number = $this->input->post('phone_number');
        $message_text = $this->input->post('message_text');

        $image_path = null;

        if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $path = FCPATH . 'uploads/cold_wp_images/';
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $new_filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $path . $new_filename)) {
                    $image_path = 'uploads/cold_wp_images/' . $new_filename;
                }
            }
        } else {
            $image_path = $this->input->post('image_path');
        }

        // Clean phone number (remove non-digits). Prepends 91 if it is 10 digits
        $clean_phone = preg_replace('/[^0-9]/', '', $phone_number);
        if (strlen($clean_phone) === 10) {
            $clean_phone = '91' . $clean_phone;
        }

        // Call the WhatsApp API to dispatch the message programmatically
        $url = 'https://2fa.tehub.in/api/whatsapp.php';
        $api_key = get_option('whatsapp_api_key') ?: 'b0b306dc4bf090c19f85c584906a967c';

        $payload = [
            'to' => $clean_phone,
            'message' => $message_text,
            'type' => 'general'
        ];

        if (!empty($image_path) && file_exists(FCPATH . $image_path)) {
            $image_data = file_get_contents(FCPATH . $image_path);
            $base64_image = base64_encode($image_data);
            
            $payload['image'] = $base64_image;
            $payload['media'] = $base64_image;
            $payload['file'] = $base64_image;
            $payload['pdf'] = $base64_image;
            $payload['filename'] = pathinfo($image_path, PATHINFO_BASENAME);
            
            $mime = mime_content_type(FCPATH . $image_path);
            $data_uri = 'data:' . $mime . ';base64,' . $base64_image;
            $payload['image_uri'] = $data_uri;
            $payload['media_uri'] = $data_uri;
            
            $payload['image_url'] = base_url($image_path);
            $payload['media_url'] = base_url($image_path);
            $payload['file_url'] = base_url($image_path);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $api_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res_decoded = json_decode($api_response, true);
        
        $api_success = false;
        $error_message = 'Failed to connect to WhatsApp API gateway.';

        if ($http_code === 200 && isset($res_decoded['success']) && $res_decoded['success'] === true) {
            $api_success = true;
        } else {
            if (isset($res_decoded['error'])) {
                $error_message = $res_decoded['error'];
            } elseif (isset($res_decoded['message'])) {
                $error_message = $res_decoded['message'];
            }
        }

        if (!$api_success) {
            echo json_encode([
                'success' => false,
                'message' => 'WhatsApp API Error: ' . $error_message
            ]);
            return;
        }

        $changed_by = get_staff_full_name(get_staff_user_id());

        $insert_data = [
            'lead_id' => $lead_id,
            'phone_number' => $phone_number,
            'message_text' => $message_text,
            'image_path' => $image_path,
            'sent_by' => $changed_by,
            'status' => 'Sent'
        ];

        $this->db->insert(db_prefix() . 'cold_wp_messages', $insert_data);

        echo json_encode([
            'success' => true,
            'message' => 'Message send logged successfully.',
            'image_path' => $image_path
        ]);
    }

    public function logs()
    {
        try {
            if (!staff_can('view', 'cold_wp_messages')) {
                access_denied('Cold WP Messages');
            }

            $this->db->select(db_prefix() . 'cold_wp_messages.*, ' . db_prefix() . 'leads.name as lead_name');
            $this->db->from(db_prefix() . 'cold_wp_messages');
            $this->db->join(db_prefix() . 'leads', db_prefix() . 'leads.id = ' . db_prefix() . 'cold_wp_messages.lead_id', 'left');
            $this->db->order_by(db_prefix() . 'cold_wp_messages.sent_at', 'desc');
            $logs = $this->db->get()->result_array();

            $data = [
                'logs' => $logs,
                'title' => 'WhatsApp Cold Message Logs'
            ];

            $this->load->view('admin/cold_wp/logs', $data);
        } catch (Throwable $e) {
            show_error('Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        }
    }

    public function delete_log($id)
    {
        if (!staff_can('delete', 'cold_wp_messages')) {
            access_denied('Cold WP Messages');
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'cold_wp_messages');

        set_alert('success', 'Log record deleted successfully.');
        redirect(admin_url('cold_wp/logs'));
    }

    public function save_template()
    {
        if (!staff_can('create', 'cold_wp_messages')) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        $template_id = $this->input->post('template_id');
        $title = $this->input->post('title');
        $message_text = $this->input->post('message_text');
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Template Title is required.']);
            return;
        }

        $image_path = null;
        $existing_template = null;

        if (!empty($template_id)) {
            $existing_template = $this->db->where('id', (int)$template_id)->get(db_prefix() . 'cold_wp_templates')->row_array();
            if ($existing_template) {
                $image_path = $existing_template['image_path'];
            }
        }

        // Handle file upload if any new file is selected
        if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $path = FCPATH . 'uploads/cold_wp_images/';
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $new_filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $path . $new_filename)) {
                    $image_path = 'uploads/cold_wp_images/' . $new_filename;
                }
            }
        }

        $save_data = [
            'title' => $title,
            'message_text' => $message_text,
            'image_path' => $image_path
        ];

        if ($existing_template) {
            $this->db->where('id', $existing_template['id']);
            $this->db->update(db_prefix() . 'cold_wp_templates', $save_data);
            $saved_id = $existing_template['id'];
            $msg = 'Template updated successfully.';
        } else {
            $this->db->insert(db_prefix() . 'cold_wp_templates', $save_data);
            $saved_id = $this->db->insert_id();
            $msg = 'Template saved successfully.';
        }

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'template' => [
                'id' => $saved_id,
                'title' => $title,
                'message_text' => $message_text,
                'image_path' => $image_path ? base_url($image_path) : null,
                'raw_image_path' => $image_path
            ]
        ]);
    }

    public function delete_template($id)
    {
        if (!staff_can('delete', 'cold_wp_messages')) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        $this->db->where('id', (int)$id);
        $this->db->delete(db_prefix() . 'cold_wp_templates');

        echo json_encode(['success' => true, 'message' => 'Template deleted successfully.']);
    }
}
