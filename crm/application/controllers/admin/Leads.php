<?php

use app\services\imap\Imap;
use app\services\LeadProfileBadges;
use app\services\leads\LeadsKanban;
use app\services\imap\ConnectionErrorException;
use Ddeboer\Imap\Exception\MailboxDoesNotExistException;

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Leads extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');

        // Auto-check/create batch_name column in tblleads
        $db_prefix = db_prefix();
        if (!$this->db->field_exists('batch_name', $db_prefix . 'leads')) {
            $this->db->query("ALTER TABLE `{$db_prefix}leads` ADD COLUMN `batch_name` VARCHAR(191) DEFAULT NULL");
        }
        if (!$this->db->field_exists('click_1', $db_prefix . 'leads')) {
            $this->db->query("ALTER TABLE `{$db_prefix}leads` ADD COLUMN `click_1` INT DEFAULT 0");
        }
        if (!$this->db->field_exists('click_2', $db_prefix . 'leads')) {
            $this->db->query("ALTER TABLE `{$db_prefix}leads` ADD COLUMN `click_2` INT DEFAULT 0");
        }
        if (!$this->db->field_exists('click_1_time', $db_prefix . 'leads')) {
            $this->db->query("ALTER TABLE `{$db_prefix}leads` ADD COLUMN `click_1_time` DATETIME DEFAULT NULL");
        }
        if (!$this->db->field_exists('click_2_time', $db_prefix . 'leads')) {
            $this->db->query("ALTER TABLE `{$db_prefix}leads` ADD COLUMN `click_2_time` DATETIME DEFAULT NULL");
        }
        if (!$this->db->field_exists('loan_type', $db_prefix . 'leads')) {
            $this->db->query("ALTER TABLE `{$db_prefix}leads` ADD COLUMN `loan_type` VARCHAR(191) DEFAULT NULL");
        }
    }

    /* List all leads */
    public function index($id = '')
    {
        close_setup_menu();

        if (!is_staff_member()) {
            access_denied('Leads');
        }

        // Automatically sync Excel leads from Google Sheets to database if auto import setting is enabled
        if (get_option('auto_import_to_lead') == '1') {
            $this->sync_excel_leads();
        }

        // Clean up any existing notes that contain auto-generated metadata
        $this->db->query("UPDATE " . db_prefix() . "leads SET description = '' WHERE description LIKE '%Id: l:%' OR description LIKE '%Ad Id:%' OR description LIKE '%Campaign Name:%' OR description LIKE '%Form Name:%' OR description LIKE '%Lead Status:%' OR description LIKE '%Platform:%' OR description LIKE 'Created Time%' OR description LIKE 'Service:%'");

        $data['switch_kanban'] = true;

        if ($this->session->userdata('leads_kanban_view') == 'true') {
            $data['switch_kanban'] = false;
            $data['bodyclass']     = 'kan-ban-body';
        }

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }
        $batch_name = $this->input->get('batch_name') ?? '';
        
        $summary = [];
        
        // 1. All Leads
        $this->db->from(db_prefix() . 'leads');
        if (staff_cant('view', 'leads')) {
            $this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
        }
        if (!empty($batch_name)) {
            $this->db->where('batch_name', $batch_name);
        }
        $summary[] = [
            'key' => '',
            'name' => 'All Leads',
            'total' => $this->db->count_all_results(),
            'color' => '#1d3557'
        ];

        // 2. Converted Leads
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('id IN (SELECT leadid FROM ' . db_prefix() . 'clients)');
        if (staff_cant('view', 'leads')) {
            $this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
        }
        if (!empty($batch_name)) {
            $this->db->where('batch_name', $batch_name);
        }
        $summary[] = [
            'key' => 'converted',
            'name' => 'Converted Leads',
            'total' => $this->db->count_all_results(),
            'color' => '#2e7d32'
        ];

        // 3. Cold WP Messages
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('lost', 0);
        $this->db->where('junk', 0);
        if (staff_cant('view', 'leads')) {
            $this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
        }
        if (!empty($batch_name)) {
            $this->db->where('batch_name', $batch_name);
        }
        $summary[] = [
            'key' => 'cold_wp',
            'name' => 'Cold WP Messages',
            'total' => $this->db->count_all_results(),
            'color' => '#f57c00'
        ];

        // 4. Ads WhatsApp Leads
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('source = (SELECT id FROM ' . db_prefix() . 'leads_sources WHERE name = "Ads WhatsApp" LIMIT 1)');
        if (staff_cant('view', 'leads')) {
            $this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
        }
        if (!empty($batch_name)) {
            $this->db->where('batch_name', $batch_name);
        }
        $summary[] = [
            'key' => 'ads_wp',
            'name' => 'Ads WhatsApp Leads',
            'total' => $this->db->count_all_results(),
            'color' => '#128c7e'
        ];

        // 5. Ads Excel List
        $this->db->from(db_prefix() . 'leads');
        $this->db->where('source = (SELECT id FROM ' . db_prefix() . 'leads_sources WHERE name = "Ads Excel List" LIMIT 1)');
        if (staff_cant('view', 'leads')) {
            $this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
        }
        if (!empty($batch_name)) {
            $this->db->where('batch_name', $batch_name);
        }
        $summary[] = [
            'key' => 'ads_excel_list',
            'name' => 'Ads Excel List',
            'total' => $this->db->count_all_results(),
            'color' => '#107c41'
        ];

        $data['summary']  = $summary;
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['title']    = _l('leads');
        $data['table'] = App_table::find('leads');
        // in case accesed the url leads/index/ directly with id - used in search
        $data['leadid']   = $id;
        $data['isKanBan'] = $this->session->has_userdata('leads_kanban_view') &&
            $this->session->userdata('leads_kanban_view') == 'true';

        $this->load->view('admin/leads/manage_leads', $data);
    }

    private function sync_excel_leads()
    {
        $this->db->where('name', 'Ads Excel List');
        $source = $this->db->get(db_prefix() . 'leads_sources')->row();
        
        $total_existing = 0;
        if ($source) {
            $total_existing = total_rows(db_prefix() . 'leads', ['source' => $source->id]);
        }

        $last_sync = get_option('last_excel_sync_time');
        $excel_lead_count_setting = get_option('excel_lead_count');
        $sync_interval = ($excel_lead_count_setting == '-1') ? 5 : 300;
        // Force synchronization if we have 0 leads in the DB under this source
        if ($total_existing > 0 && $last_sync && (time() - (int)$last_sync) < $sync_interval) {
            return;
        }

        $sheet_url = get_option('excel_sheet_url') ?: 'https://docs.google.com/spreadsheets/d/17hEUmsz8Q8Q32KDKO7qi0uTdhAXIDz7vRvPmkMS7Yv8/edit?usp=sharing';
        
        // Convert sharing link to export CSV link
        $csv_url = $sheet_url;
        if (strpos($sheet_url, '/edit') !== false) {
            $csv_url = preg_replace('/\/edit.*/', '/export?format=csv', $sheet_url);
        } elseif (strpos($sheet_url, '/pubhtml') !== false) {
            $csv_url = str_replace('/pubhtml', '/pub?output=csv', $sheet_url);
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'follow_location' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $csvContent = @file_get_contents($csv_url, false, $context);
        if (empty($csvContent)) {
            return;
        }

        if (strpos($csvContent, '<!DOCTYPE html>') !== false || strpos($csvContent, '<html') !== false) {
            return;
        }

        // Successfully fetched valid CSV content, so cache/update the sync time
        update_option('last_excel_sync_time', time());

        $lines = explode("\n", str_replace("\r", "", $csvContent));
        if (count($lines) < 2) {
            return;
        }

        $headerLineIndex = -1;
        foreach ($lines as $idx => $line) {
            $rowValues = str_getcsv($line);
            if (in_array('created_time', $rowValues) || in_array('form_name', $rowValues)) {
                $headerLineIndex = $idx;
                break;
            }
        }

        if ($headerLineIndex === -1) {
            $headerLineIndex = 0;
        }

        $rawHeaders = str_getcsv($lines[$headerLineIndex]);
        
        $phoneIdx = -1;
        $nameIdx = -1;
        $emailIdx = -1;
        $valueIdx = -1;

        foreach ($rawHeaders as $idx => $h) {
            $hLower = strtolower(trim($h));
            if (strpos($hLower, 'phone') !== false) {
                $phoneIdx = $idx;
            } elseif (strpos($hLower, 'name') !== false || $hLower === 'full_name') {
                $nameIdx = $idx;
            } elseif (strpos($hLower, 'email') !== false) {
                $emailIdx = $idx;
            } elseif (strpos($hLower, 'value') !== false || strpos($hLower, 'funding') !== false || strpos($hLower, 'amount') !== false) {
                $valueIdx = $idx;
            }
        }

        $this->db->where('name', 'Ads Excel List');
        $source = $this->db->get(db_prefix() . 'leads_sources')->row();
        if (!$source) {
            $this->db->insert(db_prefix() . 'leads_sources', ['name' => 'Ads Excel List']);
            $source_id = $this->db->insert_id();
        } else {
            $source_id = $source->id;
        }

        $db_leads = $this->db->select('phonenumber')->get(db_prefix() . 'leads')->result_array();
        $existing_phones = [];
        foreach ($db_leads as $dl) {
            $clean = preg_replace('/[^0-9]/', '', $dl['phonenumber']);
            if (strlen($clean) >= 10) {
                $existing_phones[substr($clean, -10)] = true;
            }
        }

        $lead_count = get_option('excel_lead_count') ? (int)get_option('excel_lead_count') : 40;
        if ($lead_count == -1) {
            $lead_count = 999999;
        }
        $processed_leads = 0;

        for ($i = 0; $i < count($lines); $i++) {
            if ($i === $headerLineIndex) {
                continue;
            }
            if (trim($lines[$i]) === '') {
                continue;
            }
            $rowValues = str_getcsv($lines[$i]);
            if (empty(array_filter($rowValues))) {
                continue;
            }

            if (isset($rowValues[0]) && (trim($rowValues[0]) === 'id' || trim($rowValues[0]) === 'created_time')) {
                continue;
            }
            if (isset($rowValues[1]) && trim($rowValues[1]) === 'created_time') {
                continue;
            }

            $processed_leads++;
            if ($processed_leads > $lead_count) {
                break;
            }

            $phone = ($phoneIdx !== -1 && isset($rowValues[$phoneIdx])) ? trim($rowValues[$phoneIdx]) : '';
            if (empty($phone)) {
                continue;
            }

            $pClean = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($pClean) < 10) {
                continue;
            }

            $pKey = substr($pClean, -10);
            if (isset($existing_phones[$pKey])) {
                continue;
            }

            $name = ($nameIdx !== -1 && isset($rowValues[$nameIdx])) ? trim($rowValues[$nameIdx]) : 'Excel Lead';
            $email = ($emailIdx !== -1 && isset($rowValues[$emailIdx])) ? trim($rowValues[$emailIdx]) : '';
            
            $lead_value = 0;
            if ($valueIdx !== -1 && isset($rowValues[$valueIdx])) {
                $valStr = trim($rowValues[$valueIdx]);
                if (stripos($valStr, 'lakh') !== false) {
                    preg_match_all('!\d+!', $valStr, $matches);
                    if (!empty($matches[0])) {
                        $lead_value = (float)$matches[0][0] * 100000;
                    }
                } else {
                    $lead_value = (float)preg_replace('/[^0-9.]/', '', $valStr);
                }
            }

            $description = '';

            // Determine Loan Type
            $loan_type = 'Personal';
            foreach ($rawHeaders as $idx => $h) {
                $hLower = strtolower($h);
                if (strpos($hLower, 'funding') !== false || strpos($hLower, 'describes') !== false) {
                    if (isset($rowValues[$idx])) {
                        $valLower = strtolower($rowValues[$idx]);
                        if (strpos($valLower, 'business') !== false || strpos($valLower, 'owner') !== false) {
                            $loan_type = 'Business';
                            break;
                        }
                    }
                }
            }

            $lead_data = [
                'name'        => $name,
                'phonenumber' => $pClean,
                'email'       => $email,
                'lead_value'  => $lead_value,
                'description' => trim($description),
                'source'      => $source_id,
                'status'      => 1,
                'assigned'    => 1,
                'dateadded'   => date('Y-m-d H:i:s'),
                'loan_type'   => $loan_type
            ];

            $this->db->insert(db_prefix() . 'leads', $lead_data);
            $insert_id = $this->db->insert_id();
            if ($insert_id && !empty($lead_data['phonenumber'])) {
                send_automation_whatsapp_reply($insert_id, $lead_data['phonenumber']);
            }
            $existing_phones[$pKey] = true;
        }
    }

    public function update_lead_description_ajax()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $lead_id = $this->input->post('lead_id');
        $description = $this->input->post('description');

        if ($lead_id) {
            $this->db->where('id', $lead_id);
            $this->db->update(db_prefix() . 'leads', ['description' => $description]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    public function send_bulk_whatsapp_ajax()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $ids     = $this->input->post('ids');
        $message = $this->input->post('message');

        if (empty($ids) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Missing leads or message content.']);
            exit;
        }

        $success_count = 0;
        foreach ($ids as $id) {
            $lead = $this->leads_model->get($id);
            if ($lead && !empty($lead->phonenumber)) {
                send_automation_whatsapp_reply($lead->id, $lead->phonenumber, $message, 'System Bulk Automation');
                $success_count++;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Successfully queued ' . $success_count . ' WhatsApp messages.'
        ]);
        exit;
    }

    public function table()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        App_table::find('leads')->output();
    }

    public function kanban()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $data['statuses']      = $this->leads_model->get_status();
        $data['base_currency'] = get_base_currency();
        $data['summary']       = get_leads_summary();

        echo $this->load->view('admin/leads/kan-ban', $data, true);
    }

    /* Add or update lead */
    public function lead($id = '')
    {
        if (!is_staff_member() || ($id != '' && !$this->leads_model->staff_can_access_lead($id))) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            if ($id == '') {
                $id      = $this->leads_model->add($this->input->post());
                $message = $id ? _l('added_successfully', _l('lead')) : '';

                echo json_encode([
                    'success'  => $id ? true : false,
                    'id'       => $id,
                    'message'  => $message,
                    'leadView' => $id ? $this->_get_lead_data($id) : [],
                ]);
            } else {
                $emailOriginal   = $this->db->select('email')->where('id', $id)->get(db_prefix() . 'leads')->row()->email;
                $proposalWarning = false;
                $message         = '';
                $success         = $this->leads_model->update($this->input->post(), $id);

                if ($success) {
                    $emailNow = $this->db->select('email')->where('id', $id)->get(db_prefix() . 'leads')->row()->email;

                    $proposalWarning = (total_rows(db_prefix() . 'proposals', [
                        'rel_type' => 'lead',
                        'rel_id'   => $id, ]) > 0 && ($emailOriginal != $emailNow) && $emailNow != '') ? true : false;

                    $message = _l('updated_successfully', _l('lead'));
                }
                echo json_encode([
                    'success'          => $success,
                    'message'          => $message,
                    'id'               => $id,
                    'proposal_warning' => $proposalWarning,
                    'leadView'         => $this->_get_lead_data($id),
                ]);
            }
            die;
        }

        echo json_encode([
            'leadView' => $this->_get_lead_data($id),
        ]);
    }

    private function _get_lead_data($id = '')
    {
        $reminder_data         = '';
        $data['lead_locked']   = false;
        $data['openEdit']      = $this->input->get('edit') ? true : false;
        $data['members']       = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['status_id']     = $this->input->get('status_id') ? $this->input->get('status_id') : get_option('leads_default_status');
        $data['base_currency'] = get_base_currency();

        if (is_numeric($id)) {
            $leadWhere = (staff_can('view',  'leads') ? [] : '(assigned = ' . get_staff_user_id() . ' OR addedfrom=' . get_staff_user_id() . ' OR is_public=1)');

            $lead = $this->leads_model->get($id, $leadWhere);

            if (!$lead) {
                header('HTTP/1.0 404 Not Found');
                echo _l('lead_not_found');
                die;
            }

            if (total_rows(db_prefix() . 'clients', ['leadid' => $id ]) > 0) {
                $data['lead_locked'] = ((!is_admin() && get_option('lead_lock_after_convert_to_customer') == 1) ? true : false);
            }

            $reminder_data = $this->load->view('admin/includes/modals/reminder', [
                    'id'             => $lead->id,
                    'name'           => 'lead',
                    'members'        => $data['members'],
                    'reminder_title' => _l('lead_set_reminder_title'),
                ], true);

            $data['lead']          = $lead;
            $data['mail_activity'] = $this->leads_model->get_mail_activity($id);
            $data['notes']         = $this->misc_model->get_notes($id, 'lead');
            $data['activity_log']  = $this->leads_model->get_lead_activity_log($id);

            if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                $this->load->model('gdpr_model');
                $data['purposes'] = $this->gdpr_model->get_consent_purposes($lead->id, 'lead');
                $data['consents'] = $this->gdpr_model->get_consents(['lead_id' => $lead->id]);
            }

            $leadProfileBadges         = new LeadProfileBadges($id);
            $data['total_reminders']   = $leadProfileBadges->getCount('reminders');
            $data['total_notes']       = $leadProfileBadges->getCount('notes');
            $data['total_attachments'] = $leadProfileBadges->getCount('attachments');
            $data['total_tasks']       = $leadProfileBadges->getCount('tasks');
            $data['total_proposals']   = $leadProfileBadges->getCount('proposals');
        }


        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();

        $data = hooks()->apply_filters('lead_view_data', $data);

        return [
            'data'          => $this->load->view('admin/leads/lead', $data, true),
            'reminder_data' => $reminder_data,
        ];
    }

    public function leads_kanban_load_more()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $this->db->where('id', $status);
        $status = $this->db->get(db_prefix() . 'leads_status')->row_array();

        $leads = (new LeadsKanban($status['id']))
        ->search($this->input->get('search'))
        ->sortBy(
            $this->input->get('sort_by'),
            $this->input->get('sort')
        )
        ->page($page)->get();

        foreach ($leads as $lead) {
            $this->load->view('admin/leads/_kan_ban_card', [
                'lead'   => $lead,
                'status' => $status,
            ]);
        }
    }

    public function switch_kanban($set = 0)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'leads_kanban_view' => $set,
        ]);
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function export($id)
    {
        if (is_admin()) {
            $this->load->library('gdpr/gdpr_lead');
            $this->gdpr_lead->export($id);
        }
    }

    /* Delete lead from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('leads'));
        }

        if (staff_cant('delete', 'leads')) {
            access_denied('Delete Lead');
        }

        $response = $this->leads_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_lowercase')));
        } elseif ($response === true) {
            set_alert('success', _l('deleted', _l('lead')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_lowercase')));
        }

        $ref = $_SERVER['HTTP_REFERER'];

        // if user access leads/inded/ID to prevent redirecting on the same url because will throw 404
        if (!$ref || strpos($ref, 'index/' . $id) !== false) {
            redirect(admin_url('leads'));
        }

        redirect($ref);
    }

    public function mark_as_lost($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->mark_as_lost($id);
        if ($success) {
            $message = _l('lead_marked_as_lost');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'leadView' => $this->_get_lead_data($id),
            'id'       => $id,
        ]);
    }

    public function unmark_as_lost($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->unmark_as_lost($id);
        if ($success) {
            $message = _l('lead_unmarked_as_lost');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'leadView' => $this->_get_lead_data($id),
            'id'       => $id,
        ]);
    }

    public function mark_as_junk($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->mark_as_junk($id);
        if ($success) {
            $message = _l('lead_marked_as_junk');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'leadView' => $this->_get_lead_data($id),
            'id'       => $id,
        ]);
    }

    public function unmark_as_junk($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->unmark_as_junk($id);
        if ($success) {
            $message = _l('lead_unmarked_as_junk');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'leadView' => $this->_get_lead_data($id),
            'id'       => $id,
        ]);
    }

    public function add_activity()
    {
        $leadid = $this->input->post('leadid');
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($leadid)) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $message = $this->input->post('activity');
            $aId     = $this->leads_model->log_lead_activity($leadid, $message);
            if ($aId) {
                $this->db->where('id', $aId);
                $this->db->update(db_prefix() . 'lead_activity_log', ['custom_activity' => 1]);
            }
            echo json_encode(['leadView' => $this->_get_lead_data($leadid), 'id' => $leadid]);
        }
    }

    public function get_convert_data($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['purposes'] = $this->gdpr_model->get_consent_purposes($id, 'lead');
        }
        $data['lead'] = $this->leads_model->get($id);
        $this->load->view('admin/leads/convert_to_customer', $data);
    }

    /**
     * Convert lead to client
     * @since  version 1.0.1
     * @return mixed
     */
    public function convert_to_customer()
    {
        if (!is_staff_member()) {
            access_denied('Lead Convert to Customer');
        }

        if ($this->input->post()) {
            $default_country  = get_option('customer_default_country');
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            $original_lead_email = $data['original_lead_email'];
            unset($data['original_lead_email']);

            if (isset($data['transfer_notes'])) {
                $notes = $this->misc_model->get_notes($data['leadid'], 'lead');
                unset($data['transfer_notes']);
            }

            if (isset($data['transfer_consent'])) {
                $this->load->model('gdpr_model');
                $consents = $this->gdpr_model->get_consents(['lead_id' => $data['leadid']]);
                unset($data['transfer_consent']);
            }

            if (isset($data['merge_db_fields'])) {
                $merge_db_fields = $data['merge_db_fields'];
                unset($data['merge_db_fields']);
            }

            if (isset($data['merge_db_contact_fields'])) {
                $merge_db_contact_fields = $data['merge_db_contact_fields'];
                unset($data['merge_db_contact_fields']);
            }

            if (isset($data['include_leads_custom_fields'])) {
                $include_leads_custom_fields = $data['include_leads_custom_fields'];
                unset($data['include_leads_custom_fields']);
            }

            if ($data['country'] == '' && $default_country != '') {
                $data['country'] = $default_country;
            }

            $data['billing_street']  = $data['address'];
            $data['billing_city']    = $data['city'];
            $data['billing_state']   = $data['state'];
            $data['billing_zip']     = $data['zip'];
            $data['billing_country'] = $data['country'];

            $data['is_primary'] = 1;
            $id                 = $this->clients_model->add($data, true);
            if ($id) {
                $primary_contact_id = get_primary_contact_user_id($id);

                if (isset($notes)) {
                    foreach ($notes as $note) {
                        $this->db->insert(db_prefix() . 'notes', [
                            'rel_id'         => $id,
                            'rel_type'       => 'customer',
                            'dateadded'      => $note['dateadded'],
                            'addedfrom'      => $note['addedfrom'],
                            'description'    => $note['description'],
                            'date_contacted' => $note['date_contacted'],
                            ]);
                    }
                }
                if (isset($consents)) {
                    foreach ($consents as $consent) {
                        unset($consent['id']);
                        unset($consent['purpose_name']);
                        $consent['lead_id']    = 0;
                        $consent['contact_id'] = $primary_contact_id;
                        $this->gdpr_model->add_consent($consent);
                    }
                }
                if (staff_cant('view', 'customers') && get_option('auto_assign_customer_admin_after_lead_convert') == 1) {
                    $this->db->insert(db_prefix() . 'customer_admins', [
                        'date_assigned' => date('Y-m-d H:i:s'),
                        'customer_id'   => $id,
                        'staff_id'      => get_staff_user_id(),
                    ]);
                }
                $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted', false, serialize([
                    get_staff_full_name(),
                ]));
                $default_status = $this->leads_model->get_status('', [
                    'isdefault' => 1,
                ]);
                $this->db->where('id', $data['leadid']);
                $this->db->update(db_prefix() . 'leads', [
                    'date_converted' => date('Y-m-d H:i:s'),
                    'status'         => $default_status[0]['id'],
                    'junk'           => 0,
                    'lost'           => 0,
                ]);
                // Check if lead email is different then client email
                $contact = $this->clients_model->get_contact(get_primary_contact_user_id($id));
                if ($contact->email != $original_lead_email) {
                    if ($original_lead_email != '') {
                        $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted_email', false, serialize([
                            $original_lead_email,
                            $contact->email,
                        ]));
                    }
                }
                if (isset($include_leads_custom_fields)) {
                    foreach ($include_leads_custom_fields as $fieldid => $value) {
                        // checked don't merge
                        if ($value == 5) {
                            continue;
                        }
                        // get the value of this leads custom fiel
                        $this->db->where('relid', $data['leadid']);
                        $this->db->where('fieldto', 'leads');
                        $this->db->where('fieldid', $fieldid);
                        $lead_custom_field_value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                        // Is custom field for contact ot customer
                        if ($value == 1 || $value == 4) {
                            if ($value == 4) {
                                $field_to = 'contacts';
                            } else {
                                $field_to = 'customers';
                            }
                            $this->db->where('id', $fieldid);
                            $field = $this->db->get(db_prefix() . 'customfields')->row();
                            // check if this field exists for custom fields
                            $this->db->where('fieldto', $field_to);
                            $this->db->where('name', $field->name);
                            $exists               = $this->db->get(db_prefix() . 'customfields')->row();
                            $copy_custom_field_id = null;
                            if ($exists) {
                                $copy_custom_field_id = $exists->id;
                            } else {
                                // there is no name with the same custom field for leads at the custom side create the custom field now
                                $this->db->insert(db_prefix() . 'customfields', [
                                    'fieldto'        => $field_to,
                                    'name'           => $field->name,
                                    'required'       => $field->required,
                                    'type'           => $field->type,
                                    'options'        => $field->options,
                                    'display_inline' => $field->display_inline,
                                    'field_order'    => $field->field_order,
                                    'slug'           => slug_it($field_to . '_' . $field->name, [
                                        'separator' => '_',
                                    ]),
                                    'active'        => $field->active,
                                    'only_admin'    => $field->only_admin,
                                    'show_on_table' => $field->show_on_table,
                                    'bs_column'     => $field->bs_column,
                                ]);
                                $new_customer_field_id = $this->db->insert_id();
                                if ($new_customer_field_id) {
                                    $copy_custom_field_id = $new_customer_field_id;
                                }
                            }
                            if ($copy_custom_field_id != null) {
                                $insert_to_custom_field_id = $id;
                                if ($value == 4) {
                                    $insert_to_custom_field_id = get_primary_contact_user_id($id);
                                }
                                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                    'relid'   => $insert_to_custom_field_id,
                                    'fieldid' => $copy_custom_field_id,
                                    'fieldto' => $field_to,
                                    'value'   => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 2) {
                            if (isset($merge_db_fields)) {
                                $db_field = $merge_db_fields[$fieldid];
                                // in case user don't select anything from the db fields
                                if ($db_field == '') {
                                    continue;
                                }
                                if ($db_field == 'country' || $db_field == 'shipping_country' || $db_field == 'billing_country') {
                                    $this->db->where('iso2', $lead_custom_field_value);
                                    $this->db->or_where('short_name', $lead_custom_field_value);
                                    $this->db->or_like('long_name', $lead_custom_field_value);
                                    $country = $this->db->get(db_prefix() . 'countries')->row();
                                    if ($country) {
                                        $lead_custom_field_value = $country->country_id;
                                    } else {
                                        $lead_custom_field_value = 0;
                                    }
                                }
                                $this->db->where('userid', $id);
                                $this->db->update(db_prefix() . 'clients', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 3) {
                            if (isset($merge_db_contact_fields)) {
                                $db_field = $merge_db_contact_fields[$fieldid];
                                if ($db_field == '') {
                                    continue;
                                }
                                $this->db->where('id', $primary_contact_id);
                                $this->db->update(db_prefix() . 'contacts', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        }
                    }
                }
                // set the lead to status client in case is not status client
                $this->db->where('isdefault', 1);
                $status_client_id = $this->db->get(db_prefix() . 'leads_status')->row()->id;
                $this->db->where('id', $data['leadid']);
                $this->db->update(db_prefix() . 'leads', [
                    'status' => $status_client_id,
                ]);

                set_alert('success', _l('lead_to_client_base_converted_success'));

                if (is_gdpr() && get_option('gdpr_after_lead_converted_delete') == '1') {
                    // When lead is deleted
                    // move all proposals to the actual customer record
                    $this->db->where('rel_id', $data['leadid']);
                    $this->db->where('rel_type', 'lead');
                    $this->db->update('proposals', [
                        'rel_id'   => $id,
                        'rel_type' => 'customer',
                    ]);

                    $this->leads_model->delete($data['leadid']);

                    $this->db->where('userid', $id);
                    $this->db->update(db_prefix() . 'clients', ['leadid' => null]);
                }

                log_activity('Created Lead Client Profile [LeadID: ' . $data['leadid'] . ', ClientID: ' . $id . ']');
                hooks()->do_action('lead_converted_to_customer', ['lead_id' => $data['leadid'], 'customer_id' => $id]);
                redirect(admin_url('clients/client/' . $id));
            }
        }
    }

    /* Used in kanban when dragging and mark as */
    public function update_lead_status()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->leads_model->update_lead_status($this->input->post());
        }
    }

    public function update_status_order()
    {
        if ($post_data = $this->input->post()) {
            $this->leads_model->update_status_order($post_data);
        }
    }

    public function add_lead_attachment()
    {
        $id       = $this->input->post('id');
        $lastFile = $this->input->post('last_file');

        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }

        handle_lead_attachments($id);
        echo json_encode(['leadView' => $lastFile ? $this->_get_lead_data($id) : [], 'id' => $id]);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->leads_model->add_attachment_to_database(
                $this->input->post('lead_id'),
                $this->input->post('files'),
                $this->input->post('external')
            );
        }
    }

    public function delete_attachment($id, $lead_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }
        echo json_encode([
            'success'  => $this->leads_model->delete_lead_attachment($id),
            'leadView' => $this->_get_lead_data($lead_id),
            'id'       => $lead_id,
        ]);
    }

    public function delete_note($id, $lead_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }
        echo json_encode([
            'success'  => $this->misc_model->delete_note($id),
            'leadView' => $this->_get_lead_data($lead_id),
            'id'       => $lead_id,
        ]);
    }

    public function update_all_proposal_emails_linked_to_lead($id)
    {
        $success = false;
        $email   = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email');
            $this->db->where('id', $id);
            $email = $this->db->get(db_prefix() . 'leads')->row()->email;

            $proposals = $this->proposals_model->get('', [
                'rel_type' => 'lead',
                'rel_id'   => $id,
            ]);
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update(db_prefix() . 'proposals', [
                    'email' => $email,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => _l('proposals_emails_updated', [
                _l('lead_lowercase'),
                $email,
            ]),
        ]);
    }

    public function save_form_data()
    {
        $data = $this->input->post();

        // form data should be always sent to the request and never should be empty
        // this code is added to prevent losing the old form in case any errors
        if (!isset($data['formData']) || isset($data['formData']) && !$data['formData']) {
            echo json_encode([
                'success' => false,
            ]);
            die;
        }

        // If user paste with styling eq from some editor word and the Codeigniter XSS feature remove and apply xss=remove, may break the json.
        $data['formData'] = preg_replace('/=\\\\/m', "=''", $data['formData']);

        $this->db->where('id', $data['id']);
        $this->db->update(db_prefix() . 'web_to_lead', [
            'form_data' => $data['formData'],
        ]);
        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'success' => true,
                'message' => _l('updated_successfully', _l('web_to_lead_form')),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function form($id = '')
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }
        if ($this->input->post()) {
            if ($id == '') {
                $data = $this->input->post();
                $id   = $this->leads_model->add_form($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('web_to_lead_form')));
                    redirect(admin_url('leads/form/' . $id));
                }
            } else {
                $success = $this->leads_model->update_form($id, $this->input->post());
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('web_to_lead_form')));
                }
                redirect(admin_url('leads/form/' . $id));
            }
        }

        $data['formData'] = [];
        $custom_fields    = get_custom_fields('leads', 'type != "link"');

        $cfields       = format_external_form_custom_fields($custom_fields);
        $data['title'] = _l('web_to_lead');

        if ($id != '') {
            $data['form'] = $this->leads_model->get_form([
                'id' => $id,
            ]);
            $data['title']    = $data['form']->name . ' - ' . _l('web_to_lead_form');
            $data['formData'] = $data['form']->form_data;
        }

        $this->load->model('roles_model');
        $data['roles']    = $this->roles_model->get();
        $data['sources']  = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();

        $data['members'] = $this->staff_model->get('', [
            'active'       => 1,
            'is_not_staff' => 0,
        ]);

        $data['languages'] = $this->app->get_available_languages();
        $data['cfields']   = $cfields;

        $db_fields = [];
        $fields    = [
            'name',
            'title',
            'email',
            'phonenumber',
            'lead_value',
            'company',
            'address',
            'city',
            'state',
            'country',
            'zip',
            'description',
            'website',
        ];

        $fields = hooks()->apply_filters('lead_form_available_database_fields', $fields);

        $className = 'form-control';

        foreach ($fields as $f) {
            $_field_object = new stdClass();
            $type          = 'text';
            $subtype       = '';
            if ($f == 'email') {
                $subtype = 'email';
            } elseif ($f == 'description' || $f == 'address') {
                $type = 'textarea';
            } elseif ($f == 'country') {
                $type = 'select';
            }

            if ($f == 'name') {
                $label = _l('lead_add_edit_name');
            } elseif ($f == 'email') {
                $label = _l('lead_add_edit_email');
            } elseif ($f == 'phonenumber') {
                $label = _l('lead_add_edit_phonenumber');
            } elseif ($f == 'lead_value') {
                $label = _l('lead_add_edit_lead_value');
                $type  = 'number';
            } else {
                $label = _l('lead_' . $f);
            }

            $field_array = [
                'subtype'   => $subtype,
                'type'      => $type,
                'label'     => $label,
                'className' => $className,
                'name'      => $f,
            ];

            if ($f == 'country') {
                $field_array['values'] = [];

                $field_array['values'][] = [
                    'label'    => '',
                    'value'    => '',
                    'selected' => false,
                ];

                $countries = get_all_countries();
                foreach ($countries as $country) {
                    $selected = false;
                    if (get_option('customer_default_country') == $country['country_id']) {
                        $selected = true;
                    }
                    array_push($field_array['values'], [
                        'label'    => $country['short_name'],
                        'value'    => (int) $country['country_id'],
                        'selected' => $selected,
                    ]);
                }
            }

            if ($f == 'name') {
                $field_array['required'] = true;
            }

            $_field_object->label    = $label;
            $_field_object->name     = $f;
            $_field_object->fields   = [];
            $_field_object->fields[] = $field_array;
            $db_fields[]             = $_field_object;
        }
        $data['bodyclass'] = 'web-to-lead-form';
        $data['db_fields'] = $db_fields;
        $this->load->view('admin/leads/formbuilder', $data);
    }

    public function forms($id = '')
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('web_to_lead');
        }

        $data['title'] = _l('web_to_lead');
        $this->load->view('admin/leads/forms', $data);
    }

    public function delete_form($id)
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }

        $success = $this->leads_model->delete_form($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('web_to_lead_form')));
        }

        redirect(admin_url('leads/forms'));
    }

    // Sources
    /* Manage leads sources */
    public function sources()
    {
        if (!is_admin()) {
            access_denied('Leads Sources');
        }
        $data['sources'] = $this->leads_model->get_source();
        $data['title']   = 'Leads sources';
        $this->load->view('admin/leads/manage_sources', $data);
    }

    /* Add or update leads sources */
    public function source()
    {
        if (!is_admin() && get_option('staff_members_create_inline_lead_source') == '0') {
            access_denied('Leads Sources');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }

                $id = $this->leads_model->add_source($data);

                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_source')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : false, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->leads_model->update_source($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('lead_source')));
                }
            }
        }
    }

    /* Delete leads source */
    public function delete_source($id)
    {
        if (!is_admin()) {
            access_denied('Delete Lead Source');
        }
        if (!$id) {
            redirect(admin_url('leads/sources'));
        }
        $response = $this->leads_model->delete_source($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_source_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_source')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_source_lowercase')));
        }
        redirect(admin_url('leads/sources'));
    }

    // Statuses
    /* View leads statuses */
    public function statuses()
    {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        $data['statuses'] = $this->leads_model->get_status();
        $data['title']    = 'Leads statuses';
        $this->load->view('admin/leads/manage_statuses', $data);
    }

    /* Add or update leads status */
    public function status()
    {
        if (!is_admin() && get_option('staff_members_create_inline_lead_status') == '0') {
            access_denied('Leads Statuses');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }
                $id = $this->leads_model->add_status($data);
                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_status')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : false, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->leads_model->update_status($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('lead_status')));
                }
            }
        }
    }

    /* Delete leads status from databae */
    public function delete_status($id)
    {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        if (!$id) {
            redirect(admin_url('leads/statuses'));
        }
        $response = $this->leads_model->delete_status($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_status_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_status')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_status_lowercase')));
        }
        redirect(admin_url('leads/statuses'));
    }

    /* Add new lead note */
    public function add_note($rel_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($rel_id)) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            if ($data['contacted_indicator'] == 'yes') {
                $contacted_date         = to_sql_date($data['custom_contact_date'], true);
                $data['date_contacted'] = $contacted_date;
            }

            unset($data['contacted_indicator']);
            unset($data['custom_contact_date']);

            // Causing issues with duplicate ID or if my prefixed file for lead.php is used
            $data['description'] = isset($data['lead_note_description']) ? $data['lead_note_description'] : $data['description'];

            if (isset($data['lead_note_description'])) {
                unset($data['lead_note_description']);
            }

            $note_id = $this->misc_model->add_note($data, 'lead', $rel_id);

            if ($note_id) {
                if (isset($contacted_date)) {
                    $this->db->where('id', $rel_id);
                    $this->db->update(db_prefix() . 'leads', [
                        'lastcontact' => $contacted_date,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $this->leads_model->log_lead_activity($rel_id, 'not_lead_activity_contacted', false, serialize([
                            get_staff_full_name(get_staff_user_id()),
                            _dt($contacted_date),
                        ]));
                    }
                }
            }
        }
        echo json_encode(['leadView' => $this->_get_lead_data($rel_id), 'id' => $rel_id]);
    }

    public function email_integration_folders()
    {
        if (!is_admin()) {
            ajax_access_denied('Leads Test Email Integration');
        }

        app_check_imap_open_function();

        $imap = new Imap(
            $this->input->post('email'),
            $this->input->post('password', false),
            $this->input->post('imap_server'),
            $this->input->post('encryption')
        );

        try {
            echo json_encode($imap->getSelectableFolders());
        } catch (ConnectionErrorException $e) {
            echo json_encode([
                'alert_type' => 'warning',
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function test_email_integration()
    {
        if (!is_admin()) {
            access_denied('Leads Test Email Integration');
        }

        app_check_imap_open_function(admin_url('leads/email_integration'));

        $mail     = $this->leads_model->get_email_integration();
        $password = $mail->password;

        if (false == $this->encryption->decrypt($password)) {
            set_alert('danger', _l('failed_to_decrypt_password'));
            redirect(admin_url('leads/email_integration'));
        }

        $imap = new Imap(
            $mail->email,
            $this->encryption->decrypt($password),
            $mail->imap_server,
            $mail->encryption
        );

        try {
            $connection = $imap->testConnection();

            try {
                $connection->getMailbox($mail->folder);
                set_alert('success', _l('lead_email_connection_ok'));
            } catch (MailboxDoesNotExistException $e) {
                set_alert('danger', str_replace(["\n", 'Mailbox'], ['<br />', 'Folder'], addslashes($e->getMessage())));
            }
        } catch (ConnectionErrorException $e) {
            $error = str_replace("\n", '<br />', addslashes($e->getMessage()));
            set_alert('danger', _l('lead_email_connection_not_ok') . '<br /><br /><b>' . $error . '</b>');
        }

        redirect(admin_url('leads/email_integration'));
    }

    public function email_integration()
    {
        if (!is_admin()) {
            access_denied('Leads Email Intregration');
        }
        if ($this->input->post()) {
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $success = $this->leads_model->update_email_integration($data);
            if ($success) {
                set_alert('success', _l('leads_email_integration_updated'));
            }
            redirect(admin_url('leads/email_integration'));
        }
        $data['roles']    = $this->roles_model->get();
        $data['sources']  = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();

        $data['members'] = $this->staff_model->get('', [
            'active'       => 1,
            'is_not_staff' => 0,
        ]);

        $data['title'] = _l('leads_email_integration');
        $data['mail']  = $this->leads_model->get_email_integration();

        $data['bodyclass'] = 'leads-email-integration';
        $this->load->view('admin/leads/email_integration', $data);
    }

    public function change_status_color()
    {
        if ($this->input->post() && is_admin()) {
            $this->leads_model->change_status_color($this->input->post());
        }
    }

    public function import()
    {
        if (!is_admin() && get_option('allow_non_admin_members_to_import_leads') != '1') {
            access_denied('Leads Import');
        }

        $dbFields = $this->db->list_fields(db_prefix() . 'leads');
        array_push($dbFields, 'tags');

        $this->load->library('import/import_leads', [], 'import');
        $this->import->setDatabaseFields($dbFields)
        ->setCustomFields(get_custom_fields('leads'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if ($this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
            $this->import->setSimulation($this->input->post('simulate'))
                          ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                          ->setFilename($_FILES['file_csv']['name'])
                          ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['members']  = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['title'] = _l('import');
        $this->load->view('admin/leads/import', $data);
    }

    public function track_click($lead_id, $type)
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $lead_id = (int)$lead_id;
        $type = (int)$type;

        if ($type === 1) {
            $this->db->where('id', $lead_id);
            $this->db->update(db_prefix() . 'leads', ['click_1' => 1, 'click_1_time' => date('Y-m-d H:i:s')]);
        } elseif ($type === 2) {
            $this->db->where('id', $lead_id);
            $this->db->update(db_prefix() . 'leads', ['click_2' => 1, 'click_2_time' => date('Y-m-d H:i:s')]);
        }

        echo json_encode(['success' => true]);
    }

    public function validate_unique_field()
    {
        if ($this->input->post()) {

            // First we need to check if the field is the same
            $lead_id = $this->input->post('lead_id');
            $field   = $this->input->post('field');
            $value   = $this->input->post($field);

            if ($lead_id != '') {
                $this->db->select($field);
                $this->db->where('id', $lead_id);
                $row = $this->db->get(db_prefix() . 'leads')->row();
                if ($row->{$field} == $value) {
                    echo json_encode(true);
                    die();
                }
            }

            echo total_rows(db_prefix() . 'leads', [ $field => $value ]) > 0 ? 'false' : 'true';
        }
    }

    public function bulk_action()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        hooks()->do_action('before_do_bulk_action_for_leads');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids                   = $this->input->post('ids');
            $status                = $this->input->post('status');
            $source                = $this->input->post('source');
            $assigned              = $this->input->post('assigned');
            $visibility            = $this->input->post('visibility');
            $tags                  = $this->input->post('tags');
            $last_contact          = $this->input->post('last_contact');
            $lost                  = $this->input->post('lost');
            $has_permission_delete = is_admin() || staff_can('delete',  'leads');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($has_permission_delete) {
                            if ($this->leads_model->delete($id)) {
                                $total_deleted++;
                            }
                        }
                    } else {
                        if ($status || $source || $assigned || $last_contact || $visibility) {
                            $update = [];
                            if ($status) {
                                // We will use the same function to update the status
                                $this->leads_model->update_lead_status([
                                    'status' => $status,
                                    'leadid' => $id,
                                ]);
                            }
                            if ($source) {
                                $update['source'] = $source;
                            }
                             if ($assigned) {
                                 $update['assigned'] = $assigned;
                                 $current_lead = $this->leads_model->get($id);
                                 if ($current_lead && $current_lead->assigned != $assigned) {
                                     $this->leads_model->lead_assigned_member_notification($id, $assigned);
                                 }
                             }
                            if ($last_contact) {
                                $last_contact          = to_sql_date($last_contact, true);
                                $update['lastcontact'] = $last_contact;
                            }

                            if ($visibility) {
                                if ($visibility == 'public') {
                                    $update['is_public'] = 1;
                                } else {
                                    $update['is_public'] = 0;
                                }
                            }

                            if (count($update) > 0) {
                                $this->db->where('id', $id);
                                $this->db->update(db_prefix() . 'leads', $update);
                            }
                        }
                        if ($tags) {
                            handle_tags_save($tags, $id, 'lead');
                        }
                        if ($lost == 'true') {
                            $this->leads_model->mark_as_lost($id);
                        }
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_leads_deleted', $total_deleted));
        }
    }

    public function download_files($lead_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }

        $files = $this->leads_model->get_lead_attachments($lead_id);

        if (count($files) == 0) {
            redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
        }

        $path = get_upload_path_by_type('lead') . $lead_id;

        $this->load->library('zip');

        foreach ($files as $file) {
            $this->zip->read_file($path . '/' . $file['file_name']);
        }

        $this->zip->download('files.zip');
        $this->zip->clear_data();
    }

    public function get_loan_details($lead_id)
    {
        if (!staff_can('view', 'converted_leads') || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }

        $this->db->where('lead_id', $lead_id);
        $details = $this->db->get(db_prefix() . 'lead_loan_details')->row();

        $this->db->where('lead_id', $lead_id);
        $this->db->order_by('changed_at', 'desc');
        $history = $this->db->get(db_prefix() . 'lead_loan_status_history')->result_array();

        $lead = $this->leads_model->get($lead_id);

        echo json_encode([
            'details' => $details ?: (object)[],
            'documents' => $documents,
            'lead' => $lead,
            'status_history' => $history
        ]);
    }

    public function save_loan_details()
    {
        if (!staff_can('edit', 'converted_leads') && !staff_can('create', 'converted_leads')) {
            ajax_access_denied();
        }

        $lead_id = $this->input->post('lead_id');
        if (!$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }

        $data = [
            'profession_type' => $this->input->post('profession_type'),
            'loan_type' => $this->input->post('loan_type'),
            'mother_name' => $this->input->post('mother_name'),
            'co_applicant_name' => $this->input->post('co_applicant_name'),
            'co_applicant_mother_name' => $this->input->post('co_applicant_mother_name'),
            'co_applicant_mobile' => $this->input->post('co_applicant_mobile'),
            'co_applicant_email' => $this->input->post('co_applicant_email'),
            'co_applicant_address' => $this->input->post('co_applicant_address'),
            'ref1_name' => $this->input->post('ref1_name'),
            'ref1_phone' => $this->input->post('ref1_phone'),
            'ref2_name' => $this->input->post('ref2_name'),
            'ref2_phone' => $this->input->post('ref2_phone'),
        ];

        $this->db->where('lead_id', $lead_id);
        $exists = $this->db->get(db_prefix() . 'lead_loan_details')->row();

        if ($exists) {
            $this->db->where('lead_id', $lead_id);
            $success = $this->db->update(db_prefix() . 'lead_loan_details', $data);
        } else {
            $data['lead_id'] = $lead_id;
            $success = $this->db->insert(db_prefix() . 'lead_loan_details', $data);
        }

        echo json_encode([
            'success' => $success ? true : false,
            'message' => $success ? 'Details saved successfully.' : 'Failed to save details.'
        ]);
    }

    public function upload_loan_document($lead_id)
    {
        if ((!staff_can('edit', 'converted_leads') && !staff_can('create', 'converted_leads')) || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
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
        $config['max_size']      = 20480; 
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
                'lead_id' => $lead_id,
                'document_type' => $document_type,
                'file_name' => $upload_data['client_name'],
                'file_path' => 'uploads/lead_loan_documents/' . $upload_data['file_name'],
                'uploaded_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('lead_id', $lead_id);
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
                'message' => $success ? 'Document uploaded successfully.' : 'Failed to save database record.',
                'document' => $doc_data
            ]);
        }
    }

    public function upload_loan_document_zip($lead_id)
    {
        if ((!staff_can('edit', 'converted_leads') && !staff_can('create', 'converted_leads')) || !$this->leads_model->staff_can_access_lead($lead_id)) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        if (empty($_FILES['file']['name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            return;
        }

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            echo json_encode(['success' => false, 'message' => 'Only ZIP files are allowed here.']);
            return;
        }

        $path = FCPATH . 'uploads/lead_loan_documents/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // Upload zip file temporarily
        $temp_zip = $path . uniqid() . '.zip';
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $temp_zip)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save uploaded ZIP file.']);
            return;
        }

        // Unzip
        $zip = new ZipArchive;
        if ($zip->open($temp_zip) === TRUE) {
            $extracted_files = [];
            $unmapped_files = [];

            // Define keyword mappings to document types
            $mappings = [
                'co_aadhar' => ['co_aadhar', 'co-aadhar', 'co applicant aadhar', 'co-applicant aadhar'],
                'co_pan' => ['co_pan', 'co-pan', 'co applicant pan', 'co-applicant pan'],
                'co_income' => ['co_income', 'co-income', 'co applicant income', 'co-applicant income'],
                'co_photos' => ['co_photo', 'co-photo', 'co applicant photo', 'co-applicant photo'],
                'co_savings_1yr' => ['co_savings', 'co-savings', 'co applicant savings', 'co-applicant savings'],
                'co_itr_3yrs' => ['co_itr', 'co-itr', 'co applicant itr', 'co-applicant itr'],
                'co_address' => ['co_address', 'co-address', 'co applicant address', 'co-applicant address'],
                
                'applicant_aadhar' => ['applicant_aadhar', 'applicant-aadhar', 'aadhar', 'adhar'],
                'applicant_pan' => ['applicant_pan', 'applicant-pan', 'pan'],
                'applicant_address' => ['applicant_address', 'applicant-address', 'address proof', 'residence proof'],
                'bank_statement_1yr' => ['bank_statement', 'bank-statement', 'bank statement', '1yr bank', 'statement 1yr'],
                'savings_statement' => ['savings_statement', 'savings-statement', 'savings account', 'savings bank'],
                'photos_2' => ['photo', 'passport photo', 'photos'],
                'tax_receipt' => ['tax_receipt', 'tax-receipt', 'tax paid', 'tax receipt'],
                'loan_repayment' => ['repayment', 'sanction letter', 'loan repayment'],
                'property_plan' => ['property_plan', 'property-plan', 'building plan', 'building permission', 'property details'],
                'link_docs_13yrs' => ['link_docs', 'link-docs', 'link document', '13 years link', 'sales deed'],
                'itr_3yrs' => ['itr', 'tax return', 'income tax'],
                'business_proof' => ['business_proof', 'business-proof', 'proof of business', 'gst registration', 'udhyam'],
                'gst_returns' => ['gst_returns', 'gst-returns', 'gst return']
            ];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                
                // Skip directories and MAC OS metadata
                if (substr($filename, -1) === '/' || strpos($filename, '__MACOSX') !== false || strpos($filename, '.DS_Store') !== false) {
                    continue;
                }

                $file_info = pathinfo($filename);
                $file_ext = isset($file_info['extension']) ? strtolower($file_info['extension']) : '';
                
                // Only allow images, PDFs, and office docs
                if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'])) {
                    continue;
                }

                // Match filename to document type
                $matched_type = null;
                $lower_name = strtolower($file_info['filename']);

                foreach ($mappings as $doc_key => $keywords) {
                    foreach ($keywords as $kw) {
                        if (strpos($lower_name, $kw) !== false) {
                            $matched_type = $doc_key;
                            break 2; // Found a match, break outer loop
                        }
                    }
                }

                if ($matched_type) {
                    // Extract file content
                    $content = $zip->getFromIndex($i);
                    $new_filename = uniqid() . '.' . $file_ext;
                    $dest_path = $path . $new_filename;

                    if (file_put_contents($dest_path, $content) !== FALSE) {
                        // Check if previous document exists and delete it
                        $this->db->where('lead_id', $lead_id);
                        $this->db->where('document_type', $matched_type);
                        $prev = $this->db->get(db_prefix() . 'lead_loan_documents')->row();
                        if ($prev) {
                            if (file_exists(FCPATH . $prev->file_path)) {
                                @unlink(FCPATH . $prev->file_path);
                            }
                            $this->db->where('id', $prev->id);
                            $this->db->delete(db_prefix() . 'lead_loan_documents');
                        }

                        // Insert new document record
                        $doc_data = [
                            'lead_id' => $lead_id,
                            'document_type' => $matched_type,
                            'file_name' => $file_info['basename'],
                            'file_path' => 'uploads/lead_loan_documents/' . $new_filename,
                            'uploaded_at' => date('Y-m-d H:i:s')
                        ];
                        $this->db->insert(db_prefix() . 'lead_loan_documents', $doc_data);
                        $extracted_files[] = $file_info['basename'] . ' mapped to ' . $matched_type;
                    }
                } else {
                    $unmapped_files[] = $file_info['basename'];
                }
            }
            $zip->close();
            @unlink($temp_zip);

            $msg = 'ZIP file processed. ';
            if (count($extracted_files) > 0) {
                $msg .= count($extracted_files) . ' files automatically mapped successfully. ';
            }
            if (count($unmapped_files) > 0) {
                $msg .= count($unmapped_files) . ' files could not be mapped (check file names). ';
            }

            echo json_encode([
                'success' => true,
                'message' => $msg,
                'mapped' => $extracted_files,
                'unmapped' => $unmapped_files
            ]);
        } else {
            @unlink($temp_zip);
            echo json_encode(['success' => false, 'message' => 'Failed to open ZIP archive.']);
        }
    }

    public function delete_loan_document($doc_id)
    {
        if (!staff_can('delete', 'converted_leads')) {
            ajax_access_denied();
        }

        $this->db->where('id', $doc_id);
        $doc = $this->db->get(db_prefix() . 'lead_loan_documents')->row();

        if ($doc) {
            if (!$this->leads_model->staff_can_access_lead($doc->lead_id)) {
                ajax_access_denied();
            }

            if (file_exists(FCPATH . $doc->file_path)) {
                @unlink(FCPATH . $doc->file_path);
            }

            $this->db->where('id', $doc_id);
            $success = $this->db->delete(db_prefix() . 'lead_loan_documents');

            echo json_encode([
                'success' => $success ? true : false,
                'message' => 'Document deleted.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Document not found.'
            ]);
        }
    }

    public function print_lead_details($lead_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($lead_id)) {
            access_denied('Print Lead');
        }

        $lead = $this->leads_model->get($lead_id);
        
        $this->db->where('lead_id', $lead_id);
        $details = $this->db->get(db_prefix() . 'lead_loan_details')->row();

        $this->db->where('lead_id', $lead_id);
        $documents = $this->db->get(db_prefix() . 'lead_loan_documents')->result_array();

        $this->db->where('lead_id', $lead_id);
        $this->db->where('proof_path IS NOT NULL');
        $proofs = $this->db->get(db_prefix() . 'lead_loan_status_history')->result_array();

        $data = [
            'lead' => $lead,
            'details' => $details ?: (object)[],
            'documents' => $documents,
            'proofs' => $proofs,
            'title' => 'Lead Summary - ' . $lead->name
        ];

        $this->load->view('admin/leads/print_lead_details', $data);
    }

    public function converted_leads()
    {
        if (!staff_can('view', 'converted_leads')) {
            access_denied('Converted Leads');
        }

        $data['switch_kanban'] = false;
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['title']    = 'Converted Leads';
        $data['table'] = App_table::find('converted_leads');

        $this->load->view('admin/leads/manage_converted_leads', $data);
    }

    public function converted_leads_table()
    {
        if (!staff_can('view', 'converted_leads')) {
            ajax_access_denied();
        }

        App_table::find('converted_leads')->output();
    }

    public function update_converted_lead_status()
    {
        try {
            if (!staff_can('edit', 'converted_leads') && !staff_can('create', 'converted_leads')) {
                ajax_access_denied();
            }

            if ($this->input->post()) {
                $lead_id = $this->input->post('leadid');
                $status_id = $this->input->post('status');

                // 1. Get old status name
                $this->db->select('status');
                $this->db->where('id', $lead_id);
                $_old = $this->db->get(db_prefix() . 'leads')->row();
                $old_status_name = 'N/A';
                if ($_old) {
                    $old_status_obj = $this->leads_model->get_status($_old->status);
                    if ($old_status_obj) {
                        $old_status_name = $old_status_obj->name;
                    }
                }

                // 2. Get new status name
                $new_status_obj = $this->leads_model->get_status($status_id);
                $new_status_name = $new_status_obj ? $new_status_obj->name : 'Unknown';

                $proof_path = null;

                // 3. If new status is "Printed" (ID 8), handle proof upload
                if ($status_id == 8) {
                    if (!empty($_FILES['proof']['name'])) {
                        $path = FCPATH . 'uploads/lead_loan_documents/';
                        if (!is_dir($path)) {
                            mkdir($path, 0777, true);
                        }

                        $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
                        $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
                        if (!in_array($ext, $allowed_exts)) {
                            echo json_encode(['success' => false, 'message' => 'Proof must be an image or PDF file.']);
                            return;
                        }

                        $config['upload_path']   = $path;
                        $config['allowed_types'] = '*';
                        $config['max_size']      = 20480;
                        $config['encrypt_name']  = true;

                        $this->load->library('upload', $config);
                        if ($this->upload->do_upload('proof')) {
                            $upload_data = $this->upload->data();
                            $proof_path = 'uploads/lead_loan_documents/' . $upload_data['file_name'];
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Failed to upload proof: ' . $this->upload->display_errors('', '')]);
                            return;
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Proof file is required when marking as Printed.']);
                        return;
                    }
                }

                // 4. Update lead status in leads table
                $this->db->where('id', $lead_id);
                $this->db->update(db_prefix() . 'leads', ['status' => $status_id]);

                // 5. Get current logged in staff name
                $changed_by = get_staff_full_name();

                // 6. Save history record
                $history_data = [
                    'lead_id' => $lead_id,
                    'old_status' => $old_status_name,
                    'new_status' => $new_status_name,
                    'changed_by' => $changed_by,
                    'proof_path' => $proof_path
                ];
                $this->db->insert(db_prefix() . 'lead_loan_status_history', $history_data);

                // Log activity in standard Perfex lead activity log
                $this->leads_model->log_lead_activity($lead_id, 'Status changed from ' . $old_status_name . ' to ' . $new_status_name . ' by ' . $changed_by);

                echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
                return;
            }
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'PHP Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine()
            ]);
            return;
        }
    }
}

