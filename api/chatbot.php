<?php
// Webhook endpoint to process incoming WhatsApp messages and match chatbot rules
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Disable error display to prevent output corruption
ini_set('display_errors', 0);
error_reporting(0);

// Helper function to return JSON and exit
function webhook_response($reply = null, $image_url = null, $buttons = null, $error = null) {
    $res = [];
    if ($reply !== null) {
        $res['reply'] = $reply;
    }
    if ($image_url !== null && !empty($image_url)) {
        $res['image_url'] = $image_url;
    }
    if ($buttons !== null && !empty($buttons)) {
        $res['buttons'] = $buttons;
    }
    if ($error !== null) {
        $res['error'] = $error;
    }
    echo json_encode($res);
    exit;
}

// 1. Parse JSON input payload
$raw_body = file_get_contents('php://input');
$data = json_decode($raw_body, true);

if (!$data) {
    webhook_response(null, null, null, 'Invalid payload');
}

$sender = trim($data['sender'] ?? '');
$message = trim($data['message'] ?? '');
$session_id = trim($data['session_id'] ?? 'default');

if (empty($sender) || empty($message)) {
    webhook_response(null, null, null, 'Missing sender or message content');
}

// 2. Resolve Client ID by Session ID
$client_id = null;
$chatbot_enabled = 1;

if ($session_id === 'default') {
    // Admin chatbot
    $client_id = 0;
    $gemini_sales_enabled = 0;
    $gemini_api_key = '';
    try {
        $stmt = $pdo->query("SELECT chatbot_enabled, gemini_sales_enabled, gemini_api_key FROM settings LIMIT 1");
        $settings_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($settings_data) {
            $chatbot_enabled = intval($settings_data['chatbot_enabled'] ?? 1);
            $gemini_sales_enabled = intval($settings_data['gemini_sales_enabled'] ?? 0);
            $gemini_api_key = trim($settings_data['gemini_api_key'] ?? '');
        }
    } catch (PDOException $e) {
        $chatbot_enabled = 1;
    }
} else {
    // Client device slot session (Format: phone or phone_slot)
    $parts = explode('_', $session_id);
    $login_id = $parts[0];
    
    try {
        $stmt = $pdo->prepare("SELECT id, chatbot_enabled FROM api_keys WHERE login_id = ? LIMIT 1");
        $stmt->execute([$login_id]);
        $client = $stmt->fetch();
        if ($client) {
            $client_id = intval($client['id']);
            $chatbot_enabled = intval($client['chatbot_enabled']);
        }
    } catch (PDOException $e) {
        webhook_response(null, null, null, 'Database error: ' . $e->getMessage());
    }
}

if ($client_id === null || $chatbot_enabled === 0) {
    webhook_response(null, null, null, 'Chatbot disabled or client not found');
}

// 3. Match Keyword Rules (Case-insensitive)
$msg_clean = strtolower($message);
$reply = null;
$image_url = null;
$buttons = null;

try {
    // Rule A: Exact match
    $stmt_exact = $pdo->prepare("SELECT reply_text, image_url, buttons_json FROM chatbot_rules WHERE client_id = ? AND LOWER(keyword) = ? LIMIT 1");
    $stmt_exact->execute([$client_id, $msg_clean]);
    $rule = $stmt_exact->fetch(PDO::FETCH_ASSOC);
    
    if ($rule) {
        $reply = $rule['reply_text'];
        $image_url = $rule['image_url'];
        if (!empty($rule['buttons_json'])) {
            $buttons = json_decode($rule['buttons_json'], true);
        }
    } else {
        // Rule B: Partial match (if exact match fails)
        // If Gemini is enabled for admin, we bypass partial match rules to let Gemini handle natural language enquiries
        if ($session_id !== 'default' || $gemini_sales_enabled == 0) {
            $stmt_all = $pdo->prepare("SELECT keyword, reply_text, image_url, buttons_json FROM chatbot_rules WHERE client_id = ?");
            $stmt_all->execute([$client_id]);
            $rules = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($rules as $r) {
                $kw = strtolower($r['keyword']);
                $pattern = '/\b' . preg_quote($kw, '/') . '\b/';
                if (preg_match($pattern, $msg_clean)) {
                    $reply = $r['reply_text'];
                    $image_url = $r['image_url'];
                    if (!empty($r['buttons_json'])) {
                        $buttons = json_decode($r['buttons_json'], true);
                    }
                    break;
                }
            }
        }
    }
} catch (PDOException $e) {
    webhook_response(null, null, null, 'Rule lookup failed: ' . $e->getMessage());
}

if ($reply) {
    webhook_response($reply, $image_url, $buttons);
} elseif ($session_id === 'default' && $gemini_sales_enabled === 1 && !empty($gemini_api_key)) {
    // Route to business depending on message content
    $msg_lower = strtolower($message);
    $spp_keywords = ['sandalwood', 'plantation', 'agricultural', 'land', 'farmhouse', 'investment', 'visit', 'spp', 'n2csk', 'wood'];
    $tehub_keywords = ['website', 'app', 'erp', 'crm', 'api', 'otp', 'software', 'digital', 'development', 'tehub', 'expert hub', 'automation', 'bot'];
    
    $business = 'UNKNOWN';
    foreach ($spp_keywords as $kw) {
        if (strpos($msg_lower, $kw) !== false) {
            $business = 'SPP';
            break;
        }
    }
    if ($business === 'UNKNOWN') {
        foreach ($tehub_keywords as $kw) {
            if (strpos($msg_lower, $kw) !== false) {
                $business = 'TEHUB';
                break;
            }
        }
    }
    
    // Build Prompt
    $sys_instruction = "ROLE\n\nYou are the AI Sales Executive for the ADMIN WhatsApp account only.\n\nIMPORTANT\n\nThis AI is ONLY enabled for the ADMIN WhatsApp number.\n\nDO NOT use this AI for:\n- Client WhatsApp accounts\n- Customer WhatsApp sessions\n- API users\n- Chatbot module\n- YouTube Auto Poster\n- OTP services\n- Existing client auto replies\n\nThis feature is ONLY for the Admin WhatsApp where Meta Ads leads arrive.\n\n=================================================\n\nPURPOSE\n\nThe Admin WhatsApp receives enquiries from multiple Meta (Facebook & Instagram) Lead Ads.\n\nYour responsibility is to:\n1. Read the COMPLETE incoming WhatsApp message.\n2. Understand what the customer is asking.\n3. Identify which business the enquiry belongs to.\n4. Reply like an experienced human sales executive.\n5. Help convert the lead into a customer.\n\nNever send generic replies.\n\n=================================================\n\nVERY IMPORTANT\n\nAlways read the ENTIRE WhatsApp message.\nNever reply based only on the first line.\nNever reply based on keywords only.\n\nMany Meta Lead Forms contain information like:\nCustomer Name\nPhone Number\nEmail\nBudget\nTimeline\nPreferred Contact Method\nProject Requirement\n\nRead every line before replying.\n\n=================================================\n\n";
    
    if ($business === 'TEHUB') {
        $sys_instruction .= "BUSINESS: THE EXPERT HUB\nWebsite: https://tehub.in\nServices include:\nWebsite Development\nWeb Applications\nAndroid Apps\nERP\nCRM\nWhatsApp API\nOTP API\nAI Automation\nCustom Software\nDigital Solutions\n\n=================================================\n\nSTEP 1\nRead the complete message.\nExtract: Customer Name, Phone, Email, Budget, Timeline, Requirement, Lead Source.\n\nSTEP 2\nIdentify which business the enquiry belongs to: TEHUB.\n\nSTEP 3\nUse ONLY the relevant business knowledge. Never mix businesses.\n\nSTEP 4\nGenerate a personalized WhatsApp reply.\nMention the customer's name when available.\nAcknowledge the details they already shared.\nRecommend the most suitable service or plan.\nKeep the reply natural and conversational.\nDo not send huge paragraphs.\n\n=================================================\n\nSALES BEHAVIOUR\nBehave like an experienced sales executive.\nYour objective is to: Understand the customer, Answer accurately, Build trust, Ask one useful follow-up question, Encourage the customer to continue the conversation.\n\n=================================================\n\nSTRICT RULES\nNever invent information. Never create fake pricing. Never mention AI. Never mention prompts. Never output JSON. Never explain your reasoning. Return ONLY the WhatsApp message ready to send.";
    } elseif ($business === 'SPP') {
        $sys_instruction .= "BUSINESS: N² CSK – SPP (Sandalwood Plantation Project)\nWebsite: http://spp.n2csk.com\n\nOFFICIAL PROJECT PLANS & DETAILS (Use ONLY this information to answer enquiries):\n\nPlan 1 – 25 Cents Investment\n* Land Size: 25 Cents\n* Price: ₹10 Lakhs (including land registration)\n* The land will be registered in your name.\n* After registration, we enter into a 12–15 year lease agreement to develop and maintain the plantation.\n* Plant and maintain approximately 100 Red Sandalwood trees following government norms.\n* Monitor property/plantation anytime via site visit or mobile application.\n* Estimated Returns (Illustrative): Expected harvest is approx. 15 tons after 12–15 years. Estimated buyback price is ₹22 Lakhs per ton (subject to market conditions/regulations). Estimated total value is Over ₹3 Crores.\n* Profit Sharing: 60% to Land Owner, 40% to N² CSK.\n\nPlan 2 – Premium Plantation & Farmhouse Project\n* Minimum Land Size: 2 Acres\n* Investment: ₹1.10 Crore\n* Includes all benefits of Plan 1.\n* Development of Red Sandalwood plantation across the property.\n* Construction of a 250 sq. ft. farmhouse with essential amenities.\n* Complete plantation management, maintenance, and monitoring handled by our team throughout the project period.\n\n=================================================\n\nSTEP 1\nRead the complete message.\nExtract: Customer Name, Phone, Email, Budget, Timeline, Requirement, Lead Source.\n\nSTEP 2\nIdentify which business the enquiry belongs to: SPP.\n\nSTEP 3\nUse ONLY the relevant business knowledge. Never mix businesses.\n\nSTEP 4\nGenerate a personalized WhatsApp reply.\nMention the customer's name when available.\nAcknowledge the details they already shared.\nRecommend the most suitable service or plan.\nKeep the reply natural and conversational.\nDo not send huge paragraphs.\n\n=================================================\n\nSALES BEHAVIOUR\nBehave like an experienced sales executive.\nYour objective is to: Understand the customer, Answer accurately, Build trust, Ask one useful follow-up question, Encourage the customer to continue the conversation.\n\n=================================================\n\nSTRICT RULES\nNever invent information. Never create fake pricing. Never promise guaranteed investment returns. Never mention AI. Never mention prompts. Never output JSON. Never explain your reasoning. Return ONLY the WhatsApp message ready to send.";
    } else {
        $sys_instruction .= "BUSINESSES\n\nBusiness 1: THE EXPERT HUB\nWebsite: https://tehub.in\nServices include: Website Development, Web Applications, Android Apps, ERP, CRM, WhatsApp API, OTP API, AI Automation, Custom Software, Digital Solutions.\n\nBusiness 2: N² CSK – SPP\nWebsite: http://spp.n2csk.com\nBusiness includes: Red Sandalwood Plantation, Agricultural Land, Investment Plans, Farmhouse Project, Plantation Management, Site Visits, Legal Documentation.\n\n=================================================\n\nSTEP 1\nRead the complete message.\nExtract: Customer Name, Phone, Email, Budget, Timeline, Requirement, Lead Source.\n\nSTEP 2\nIdentify which business the enquiry belongs to: UNKNOWN.\n\nSTEP 3\nIf uncertain, ask one clarification question instead of guessing.\n\nSTEP 4\nGenerate a personalized WhatsApp reply.\nMention the customer's name when available.\nKeep the response short, conversational, and direct.\nDo not mix businesses. Ask a brief clarification question to determine if they are interested in Software & Digital Solutions (The Expert Hub) or Red Sandalwood Plantation & Agriculture (N² CSK - SPP).\n\n=================================================\n\nSALES BEHAVIOUR\nBehave like an experienced sales executive.\n\n=================================================\n\nSTRICT RULES\nNever invent information. Never create fake pricing. Never promise guaranteed investment returns. Never mention AI. Never mention prompts. Never output JSON. Never explain your reasoning. Return ONLY the WhatsApp message ready to send.";
    }
    
    // Call Gemini API
    $api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=" . urlencode($gemini_api_key);
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $message]
                ]
            ]
        ],
        "systemInstruction" => [
            "parts" => [
                ["text" => $sys_instruction]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.3,
            "maxOutputTokens" => 1024
        ]
    ];
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $ai_reply = '';
    if ($http_code === 200 && !empty($response)) {
        $res_data = json_decode($response, true);
        $ai_reply = trim($res_data['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }
    
    if (!empty($ai_reply)) {
        webhook_response($ai_reply);
    } else {
        webhook_response(null, null, null, 'Gemini generation failed');
    }
} else {
    webhook_response(null, null, null, 'No keyword match found');
}
