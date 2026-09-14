<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_filter('staff_permissions', 'register_converted_leads_permissions', 10, 2);

function register_converted_leads_permissions($permissions, $data = [])
{
    $permissions['converted_leads'] = [
        'name'         => 'Converted Leads',
        'capabilities' => [
            'view'   => 'View (Global)',
            'create' => 'Create',
            'edit'   => 'Edit',
            'delete' => 'Delete',
        ]
    ];
    $permissions['cold_wp_messages'] = [
        'name'         => 'Cold WP Messages',
        'capabilities' => [
            'view'   => 'View (Global)',
            'create' => 'Create',
            'delete' => 'Delete',
        ]
    ];
    return $permissions;
}

if (!function_exists('send_automation_whatsapp_reply')) {
    function send_automation_whatsapp_reply($lead_id, $phonenumber, $message_text = null, $sent_by = 'System Automation')
    {
        if (empty($phonenumber)) {
            return false;
        }

        // Clean phone number (remove non-digits). Prepends 91 if it is 10 digits
        $clean_phone = preg_replace('/[^0-9]/', '', $phonenumber);
        if (strlen($clean_phone) === 10) {
            $clean_phone = '91' . $clean_phone;
        }

        if (empty($message_text)) {
            $message_text = "Hi 👋 Thanks for contacting us!\n\n"
                . "To assist you better, could you please share the following details:\n\n"
                . "1️⃣ *What type of loan are you looking for?*\n"
                . "• Personal Loan\n"
                . "• Business Loan\n"
                . "• Home/Mortgage Loan\n"
                . "• Other\n\n"
                . "2️⃣ *Loan Amount Required:* ₹_____\n\n"
                . "3️⃣ *Your Location/City:* _______\n\n"
                . "4️⃣ *Employment/Business:* _______\n\n"
                . "Once we have these details, our team will get in touch with you and guide you further. 😊";
        }

        $url = 'https://2fa.tehub.in/api/whatsapp.php';
        $api_key = get_option('whatsapp_api_key') ?: 'b0b306dc4bf090c19f85c584906a967c';

        $payload = [
            'to' => $clean_phone,
            'message' => $message_text,
            'type' => 'general'
        ];

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
        curl_close($ch);

        // Also log this message in tblcold_wp_messages if the table exists
        $CI =& get_instance();
        $db_prefix = db_prefix();
        if ($CI->db->table_exists($db_prefix . 'cold_wp_messages')) {
            $CI->db->insert($db_prefix . 'cold_wp_messages', [
                'lead_id'      => $lead_id,
                'phone_number' => $phonenumber,
                'message_text' => $message_text,
                'image_path'   => null,
                'sent_by'      => $sent_by,
                'status'       => 'Sent'
            ]);
        }
        
        return $api_response;
    }
}
