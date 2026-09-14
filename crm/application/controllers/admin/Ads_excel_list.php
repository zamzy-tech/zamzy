<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ads_excel_list extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (!is_admin()) {
            access_denied('Ads Excel List');
        }

        $sheet_url = get_option('excel_sheet_url');
        if (empty($sheet_url)) {
            $sheet_url = 'https://docs.google.com/spreadsheets/d/17hEUmsz8Q8Q32KDKO7qi0uTdhAXIDz7vRvPmkMS7Yv8/edit?usp=sharing';
        }

        $lead_count = (int) get_option('excel_lead_count');
        if ($lead_count == -1) {
            $lead_count = 999999;
        } elseif ($lead_count <= 0) {
            $lead_count = 30;
        }

        $show_count = (int) get_option('excel_lead_show_count');
        if ($show_count == -1) {
            $show_count = 999999;
        } elseif ($show_count <= 0) {
            $show_count = 30;
        }

        // Convert Google Sheet URL to CSV export link
        $csv_url = $sheet_url;
        if (preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $sheet_url, $matches)) {
            $csv_url = "https://docs.google.com/spreadsheets/d/" . $matches[1] . "/export?format=csv";
        }

        // Fetch CSV contents
        $csvContent = '';
        $fetch_error = '';

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                "timeout" => 15
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];
        $context = stream_context_create($opts);

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $csv_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            $csvContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_err = curl_error($ch);
            curl_close($ch);

            if (($httpCode == 301 || $httpCode == 302) && ini_get('open_basedir')) {
                $csvContent = @file_get_contents($csv_url, false, $context);
            }
            
            if (empty($csvContent)) {
                $fetch_error = "cURL error: " . $curl_err . " (HTTP Status " . $httpCode . ")";
            }
        }

        if (empty($csvContent)) {
            $csvContent = @file_get_contents($csv_url, false, $context);
            if (empty($csvContent)) {
                $fetch_error = "Failed to fetch Google Sheet data. Please check your sheet URL and internet connection.";
            } else {
                $fetch_error = "";
            }
        }

        // Check if returned content is HTML (meaning the Google Sheet is private and redirected to Google Login)
        if (!empty($csvContent) && (strpos($csvContent, '<!DOCTYPE html>') !== false || strpos($csvContent, '<html') !== false)) {
            $fetch_error = "The Google Sheet is private. Please share it as 'Anyone with the link can view' so the CRM can read it.";
            $csvContent = '';
        }

        $headers    = [];
        $data_rows  = [];
        $total_sheet_rows = 0;

        if (!empty($csvContent)) {
            $lines = explode("\n", str_replace("\r", "", $csvContent));
            if (count($lines) > 0) {
                // Find the header row by looking for 'created_time' or 'form_name'
                $headerLineIndex = 0;
                foreach ($lines as $idx => $line) {
                    $rowValues = str_getcsv($line);
                    if (in_array('created_time', $rowValues) || in_array('form_name', $rowValues)) {
                        $headerLineIndex = $idx;
                        break;
                    }
                }

                $rawHeaders = str_getcsv($lines[$headerLineIndex]);
                $headerMap  = [];
                
                // Define unwanted technical and metadata columns
                $unwanted = [
                    'id', 'ad id', 'adset id', 'campaign id', 'form id', 
                    'ad name', 'adset name', 'campaign name', 'form name', 
                    'is organic', 'organic', 'lead status', 'note', 'cold wp send', 
                    'cold wp send status', 'created time'
                ];

                foreach ($rawHeaders as $idx => $h) {
                    $hTrim = trim($h);
                    $hLower = str_replace('_', ' ', strtolower($hTrim));
                    
                    // Skip unwanted columns if they match or contain unwanted tags
                    $shouldSkip = false;
                    foreach ($unwanted as $u) {
                        if ($hLower === $u || strpos($hLower, $u) !== false) {
                            $shouldSkip = true;
                            break;
                        }
                    }
                    if ($shouldSkip) {
                        continue;
                    }
                    if ($hTrim !== '') {
                        $headerMap[$idx] = $hTrim;
                        $headers[]       = $hTrim;
                    }
                }

                // Retrieve DB leads to match by phone number
                $db_leads = $this->db->select('id, name, phonenumber, click_1, click_2, click_1_time, click_2_time')->get(db_prefix() . 'leads')->result_array();
                $leads_by_phone = [];
                foreach ($db_leads as $dl) {
                    $clean = preg_replace('/[^0-9]/', '', $dl['phonenumber']);
                    if (strlen($clean) >= 10) {
                        $key = substr($clean, -10);
                        $leads_by_phone[$key] = $dl;
                    }
                }

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

                    // Skip the row if it contains key strings
                    if (isset($rowValues[0]) && (trim($rowValues[0]) === 'id' || trim($rowValues[0]) === 'created_time')) {
                        continue;
                    }
                    if (isset($rowValues[1]) && trim($rowValues[1]) === 'created_time') {
                        continue;
                    }

                    $total_sheet_rows++;

                    if (count($data_rows) < $show_count) {
                        $row = [];
                        foreach ($headerMap as $idx => $hName) {
                            $row[$hName] = isset($rowValues[$idx]) ? trim($rowValues[$idx]) : '';
                        }

                        // Try to find matching phone in CRM
                        $row['db_lead'] = null;
                        
                        // Find the phone field in the rowValues using the raw header index
                        $phoneIdx = -1;
                        foreach ($rawHeaders as $idx => $h) {
                            if (strpos(strtolower(trim($h)), 'phone') !== false) {
                                $phoneIdx = $idx;
                                break;
                            }
                        }

                        if ($phoneIdx !== -1 && isset($rowValues[$phoneIdx])) {
                            $pClean = preg_replace('/[^0-9]/', '', $rowValues[$phoneIdx]);
                            if (strlen($pClean) >= 10) {
                                $pKey = substr($pClean, -10);
                                if (isset($leads_by_phone[$pKey])) {
                                    $row['db_lead'] = $leads_by_phone[$pKey];
                                }
                            }
                        }

                        $data_rows[] = $row;
                    }
                }
            }
        }

        $data['title']            = 'Ads Excel List';
        $data['sheet_url']        = $sheet_url;
        $data['lead_count']       = $lead_count;
        $data['headers']          = $headers;
        $data['rows']             = $data_rows;
        $data['total_sheet_rows'] = $total_sheet_rows;
        $data['fetch_error']      = $fetch_error;
        $data['is_ajax']          = $this->input->is_ajax_request();

        $this->load->view('admin/ads_excel_list/index', $data);
    }

    public function import_lead_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $name = $this->input->post('name');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');

        // Check for duplicate phone number (match last 10 digits)
        $pClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($pClean) >= 10) {
            $pKey = substr($pClean, -10);
            $this->db->like('phonenumber', $pKey);
            $existing = $this->db->get(db_prefix() . 'leads')->row();
            if ($existing) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Duplicate lead: A lead with this phone number already exists in CRM.'
                ]);
                exit;
            }
        }
        
        // Clean phone number format for lead creation
        $phoneClean = preg_replace('/[^0-9+]/', '', $phone);

        $description = '';

        // Determine Loan Type
        $loan_type = 'Personal';
        foreach ($this->input->post() as $key => $val) {
            $keyLower = strtolower($key);
            $valLower = strtolower($val);
            if (strpos($keyLower, 'funding') !== false || strpos($keyLower, 'describes') !== false) {
                if (strpos($valLower, 'business') !== false || strpos($valLower, 'owner') !== false) {
                    $loan_type = 'Business';
                    break;
                }
            }
        }

        $lead_data = [
            'name'        => $name ?: 'Excel Lead',
            'phonenumber' => $phoneClean,
            'email'       => $email,
            'description' => trim($description),
            'source'      => 1, // default fallback source ID
            'status'      => 1, // default status ID (usually customer/created)
            'assigned'    => get_staff_user_id() ?: 1,
            'dateadded'   => date('Y-m-d H:i:s'),
            'loan_type'   => $loan_type
        ];

        // Find or create 'Ads Excel List' source
        $this->db->where('name', 'Ads Excel List');
        $source = $this->db->get(db_prefix() . 'leads_sources')->row();
        if ($source) {
            $lead_data['source'] = $source->id;
        } else {
            // Check 'Ads WhatsApp'
            $this->db->where('name', 'Ads WhatsApp');
            $source2 = $this->db->get(db_prefix() . 'leads_sources')->row();
            if ($source2) {
                $lead_data['source'] = $source2->id;
            }
        }

        $success = $this->db->insert(db_prefix() . 'leads', $lead_data);
        $insert_id = $this->db->insert_id();

        if ($success && $insert_id) {
            if (!empty($lead_data['phonenumber'])) {
                send_automation_whatsapp_reply($insert_id, $lead_data['phonenumber']);
            }
            echo json_encode([
                'success' => true,
                'lead_id' => $insert_id,
                'message' => 'Lead successfully imported to CRM.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to import lead. Please try again.'
            ]);
        }
        exit;
    }

    public function import_all_leads_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $sheet_url = get_option('excel_sheet_url');
        if (empty($sheet_url)) {
            $sheet_url = 'https://docs.google.com/spreadsheets/d/17hEUmsz8Q8Q32KDKO7qi0uTdhAXIDz7vRvPmkMS7Yv8/edit?usp=sharing';
        }

        $lead_count = (int) get_option('excel_lead_count');
        if ($lead_count == -1) {
            $lead_count = 999999;
        } elseif ($lead_count <= 0) {
            $lead_count = 30;
        }

        // Convert Google Sheet URL to CSV export link
        $csv_url = $sheet_url;
        if (preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $sheet_url, $matches)) {
            $csv_url = "https://docs.google.com/spreadsheets/d/" . $matches[1] . "/export?format=csv";
        }

        $csvContent = '';
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                "timeout" => 15
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];
        $context = stream_context_create($opts);

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $csv_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            $csvContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($httpCode == 301 || $httpCode == 302) && ini_get('open_basedir')) {
                $csvContent = @file_get_contents($csv_url, false, $context);
            }
        }

        if (empty($csvContent)) {
            $csvContent = @file_get_contents($csv_url, false, $context);
        }

        if (empty($csvContent) || strpos($csvContent, '<!DOCTYPE html>') !== false || strpos($csvContent, '<html') !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch Google Sheet data. Make sure it is shared publicly.'
            ]);
            exit;
        }

        $lines = explode("\n", str_replace("\r", "", $csvContent));
        if (count($lines) < 2) {
            echo json_encode([
                'success' => false,
                'message' => 'The Google Sheet is empty.'
            ]);
            exit;
        }

        // Find header index
        $headerLineIndex = 0;
        foreach ($lines as $idx => $line) {
            $rowValues = str_getcsv($line);
            if (in_array('created_time', $rowValues) || in_array('form_name', $rowValues)) {
                $headerLineIndex = $idx;
                break;
            }
        }

        $rawHeaders = str_getcsv($lines[$headerLineIndex]);
        
        // Find important column indices
        $phoneIdx = -1;
        $nameIdx = -1;
        $emailIdx = -1;
        foreach ($rawHeaders as $idx => $h) {
            $hLower = strtolower(trim($h));
            if (strpos($hLower, 'phone') !== false) {
                $phoneIdx = $idx;
            } elseif (strpos($hLower, 'name') !== false || $hLower === 'full_name') {
                $nameIdx = $idx;
            } elseif (strpos($hLower, 'email') !== false) {
                $emailIdx = $idx;
            }
        }

        // Fetch existing leads to check duplicates
        $db_leads = $this->db->select('phonenumber')->get(db_prefix() . 'leads')->result_array();
        $existing_phones = [];
        foreach ($db_leads as $dl) {
            $clean = preg_replace('/[^0-9]/', '', $dl['phonenumber']);
            if (strlen($clean) >= 10) {
                $existing_phones[substr($clean, -10)] = true;
            }
        }

        // Find or create 'Ads Excel List' source
        $source_id = 1;
        $this->db->where('name', 'Ads Excel List');
        $source = $this->db->get(db_prefix() . 'leads_sources')->row();
        if ($source) {
            $source_id = $source->id;
        } else {
            $this->db->where('name', 'Ads WhatsApp');
            $source2 = $this->db->get(db_prefix() . 'leads_sources')->row();
            if ($source2) {
                $source_id = $source2->id;
            }
        }

        $imported_count = 0;
        $duplicate_count = 0;

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

            // Limit check just like normal list
            if ($imported_count + $duplicate_count >= $lead_count) {
                break;
            }

            $phone = ($phoneIdx !== -1 && isset($rowValues[$phoneIdx])) ? trim($rowValues[$phoneIdx]) : '';
            $name = ($nameIdx !== -1 && isset($rowValues[$nameIdx])) ? trim($rowValues[$nameIdx]) : '';
            $email = ($emailIdx !== -1 && isset($rowValues[$emailIdx])) ? trim($rowValues[$emailIdx]) : '';

            if (empty($phone)) {
                continue;
            }

            // Extract last 10 digits
            $pClean = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($pClean) < 10) {
                continue;
            }
            $pKey = substr($pClean, -10);

            if (isset($existing_phones[$pKey])) {
                $duplicate_count++;
                continue;
            }

            // Build description from other fields
            $description = '';
            foreach ($rawHeaders as $idx => $h) {
                if (in_array($idx, [$phoneIdx, $nameIdx, $emailIdx])) {
                    continue;
                }
                $hTrim = trim($h);
                if (empty($hTrim)) {
                    continue;
                }
                // Skip Perfex CRM/Facebook technical fields
                $hLower = strtolower($hTrim);
                if (strpos($hLower, 'id') !== false || strpos($hLower, 'time') !== false) {
                    continue;
                }
                if (isset($rowValues[$idx]) && !empty(trim($rowValues[$idx]))) {
                    $cleanKey = ucwords(str_replace(['_', '?', '.'], [' ', '', ''], $hTrim));
                    $description .= $cleanKey . ': ' . trim($rowValues[$idx]) . "\n";
                }
            }

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
                'name'        => $name ?: 'Excel Lead',
                'phonenumber' => preg_replace('/[^0-9+]/', '', $phone),
                'email'       => $email,
                'description' => trim($description),
                'source'      => $source_id,
                'status'      => 1, // default status
                'assigned'    => get_staff_user_id() ?: 1,
                'dateadded'   => date('Y-m-d H:i:s'),
                'loan_type'   => $loan_type
            ];

            if ($this->db->insert(db_prefix() . 'leads', $lead_data)) {
                $insert_id = $this->db->insert_id();
                if ($insert_id && !empty($lead_data['phonenumber'])) {
                    send_automation_whatsapp_reply($insert_id, $lead_data['phonenumber']);
                }
                $imported_count++;
                $existing_phones[$pKey] = true;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Successfully imported {$imported_count} new leads. {$duplicate_count} duplicates skipped."
        ]);
        exit;
    }
}
