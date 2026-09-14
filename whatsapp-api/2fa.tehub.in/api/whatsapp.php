<?php
// API endpoint for external developers to send WhatsApp messages
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Helper function to return JSON response and exit
function json_response($success, $message, $http_code = 200, $extra_data = []) {
    http_response_code($http_code);
    $response = ['success' => $success];
    if ($success) {
        $response['message'] = $message;
    } else {
        $response['error'] = $message;
    }
    if (!empty($extra_data)) {
        $response = array_merge($response, $extra_data);
    }
    echo json_encode($response);
    exit;
}

// 1. Authenticate Request
$api_key = '';

// Check Authorization Header first
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
if (!empty($auth_header) && preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
    $api_key = trim($matches[1]);
}

// Fallback: Check Query Params or Post Body
if (empty($api_key)) {
    $api_key = $_GET['api_key'] ?? $_POST['api_key'] ?? '';
}

// Check JSON Input if still empty
if (empty($api_key)) {
    $json_input = json_decode(file_get_contents('php://input'), true);
    $api_key = $json_input['api_key'] ?? '';
}

if (empty($api_key)) {
    json_response(false, 'Unauthorized: API Key is required. Please pass it in the Authorization header as Bearer token or as a parameter.', 401);
}

// 2. Validate API Key in Database (either slot-specific or primary)
$auth_device_slot = 0; // 0 means not resolved from slot-specific key yet
try {
    // A. Check if it's a slot-specific API key
    $stmt_dev = $pdo->prepare("SELECT * FROM client_devices WHERE api_key = ? LIMIT 1");
    $stmt_dev->execute([$api_key]);
    $device_row = $stmt_dev->fetch();
    
    if ($device_row) {
        $auth_device_slot = intval($device_row['slot_number']);
        // Load the main client account
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE id = ? LIMIT 1");
        $stmt->execute([$device_row['client_id']]);
        $client = $stmt->fetch();
    } else {
        // B. Fallback: Check if it's the primary client account key
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$api_key]);
        $client = $stmt->fetch();
    }
} catch (PDOException $e) {
    json_response(false, 'Database lookup error: ' . $e->getMessage(), 500);
}

if (!$client) {
    json_response(false, 'Unauthorized: Invalid API Key.', 401);
}

if (($client['status'] ?? 'active') !== 'active') {
    json_response(false, 'Forbidden: This API key is suspended.', 403);
}

$credits = intval($client['credits'] ?? 0);
if ($credits !== -1 && $credits <= 0) {
    json_response(false, 'Payment Required: Your account is out of credits. Please purchase more credits.', 402);
}

// 3. Parse Request Data (JSON or POST)
$raw_body = file_get_contents('php://input');
$json_data = json_decode($raw_body, true) ?: [];

$to = trim($_POST['to'] ?? $json_data['to'] ?? $_POST['phone'] ?? $json_data['phone'] ?? '');
$message = trim($_POST['message'] ?? $json_data['message'] ?? '');
$type = strtolower(trim($_POST['type'] ?? $json_data['type'] ?? 'general'));
$pdf = trim($_POST['pdf'] ?? $json_data['pdf'] ?? '');
$filename = trim($_POST['filename'] ?? $json_data['filename'] ?? 'document.pdf');

// Validate inputs
if (empty($to)) {
    json_response(false, 'Bad Request: Recipient phone number (to) is required.', 400);
}

$allowed_types = ['otp', 'promotion', 'invoice', 'report', 'general'];
if (!in_array($type, $allowed_types)) {
    $type = 'general';
}

// Apply client templates if saved in their developer dashboard
if ($type === 'otp' && !empty($client['template_otp'])) {
    $otp_val = trim($json_data['otp_code'] ?? $_POST['otp_code'] ?? $json_data['otp'] ?? $_POST['otp'] ?? $message);
    $message = str_replace(['{otp_code}', '{otp}'], $otp_val, $client['template_otp']);
} elseif ($type === 'invoice' && !empty($client['template_invoice'])) {
    $c_name = trim($json_data['client_name'] ?? $_POST['client_name'] ?? '');
    $inv_no = trim($json_data['invoice_number'] ?? $_POST['invoice_number'] ?? '');
    $g_total = trim($json_data['grand_total'] ?? $_POST['grand_total'] ?? '');
    $w_link = trim($json_data['web_link'] ?? $_POST['web_link'] ?? '');
    
    $message = str_replace(
        ['{client_name}', '{invoice_number}', '{grand_total}', '{web_link}'],
        [$c_name, $inv_no, $g_total, $w_link],
        $client['template_invoice']
    );
} elseif ($type === 'general' && !empty($client['template_general'])) {
    $message = str_replace('{message}', $message, $client['template_general']);
}

if (empty($message)) {
    json_response(false, 'Bad Request: Message content (message) or template parameters are required.', 400);
}

if (strpos($to, '@g.us') !== false) {
    $clean_phone = trim($to);
} else {
    $clean_phone = preg_replace('/[^0-9]/', '', $to);
    if (strlen($clean_phone) < 8) {
        json_response(false, 'Bad Request: Invalid recipient phone number.', 400);
    }
}

// 4. Validate Client-Specific WhatsApp Gateway Settings (Using Admin's Global URL but Client's Linked Session)
try {
    $settings_stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $settings_stmt->fetch();
} catch (PDOException $e) {
    json_response(false, 'Database configuration error: ' . $e->getMessage(), 500);
}

$gateway_type = $settings['whatsapp_gateway_type'] ?? 'gateway';
$gateway_url = $settings['whatsapp_gateway_url'] ?? '';
$gateway_token = $settings['whatsapp_gateway_token'] ?? '';

if (empty($gateway_url)) {
    json_response(false, 'Service Unavailable: The global WhatsApp Gateway URL is not configured by the administrator.', 503);
}

if ($gateway_type !== 'gateway') {
    json_response(false, 'Service Unavailable: Background WhatsApp Gateway is disabled.', 503);
}

// Extract device slot parameter (e.g. device=1 or slot=2)
$device_slot = $auth_device_slot;
if ($device_slot <= 0) {
    $device_slot = defined('OVERRIDE_DEVICE_SLOT') ? OVERRIDE_DEVICE_SLOT : intval($_POST['device'] ?? $json_data['device'] ?? $_POST['slot'] ?? $json_data['slot'] ?? 0);
}
$active_slot = 1;

if ($device_slot > 0) {
    // Check if the requested device slot is active and connected
    $stmt_dev = $pdo->prepare("SELECT * FROM client_devices WHERE client_id = ? AND slot_number = ? AND whatsapp_is_connected = 1 LIMIT 1");
    $stmt_dev->execute([$client['id'], $device_slot]);
    $dev = $stmt_dev->fetch();
    
    if (!$dev) {
        json_response(false, "Precondition Failed: WhatsApp Device Slot #{$device_slot} is not connected. Please connect it first in the Developer Portal.", 412);
    }
    $active_slot = $device_slot;
} else {
    // Find the first active connected device slot for this client
    $stmt_dev = $pdo->prepare("SELECT * FROM client_devices WHERE client_id = ? AND whatsapp_is_connected = 1 ORDER BY slot_number ASC LIMIT 1");
    $stmt_dev->execute([$client['id']]);
    $dev = $stmt_dev->fetch();
    
    if (!$dev) {
        json_response(false, "Precondition Failed: No WhatsApp devices are connected for your account. Please link a device first in the Developer Portal.", 412);
    }
    $active_slot = intval($dev['slot_number']);
}

// Build session suffix (Slot 1 remains LOGIN_ID, Slot N becomes LOGIN_ID_N)
$session_name = $client['login_id'] . ($active_slot > 1 ? '_' . $active_slot : '');

// 5. Send WhatsApp Message via API Gateway (with Resolved Session params)
$target_gateway_url = $gateway_url;
$target_gateway_url .= (strpos($target_gateway_url, '?') !== false ? '&' : '?') . 'session=' . urlencode($session_name)
                     . '&id=' . urlencode($session_name)
                     . '&session_id=' . urlencode($session_name)
                     . '&instance=' . urlencode($session_name);

$ch = curl_init($target_gateway_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

$payload_data = [
    'session' => $session_name,
    'id' => $session_name,
    'session_id' => $session_name,
    'instance' => $session_name,
    'to' => $clean_phone,
    'phone' => $clean_phone,
    'number' => $clean_phone,
    'body' => $message,
    'message' => $message,
    'token' => $gateway_token,
    'apikey' => $gateway_token
];

if (!empty($pdf)) {
    $payload_data['pdf'] = $pdf;
    $payload_data['filename'] = $filename;
}

$payload = json_encode($payload_data);

curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$headers = [
    'Content-Type: application/json',
    'Accept: application/json'
];
if (!empty($gateway_token)) {
    $headers[] = "Authorization: Bearer $gateway_token";
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

// 6. Handle Delivery Outcome and Transaction Logging
$log_status = 'failed';
$response_msg = '';

if ($err) {
    $response_msg = 'HTTP Request error: ' . $err;
} elseif ($http_code >= 400) {
    $response_msg = 'Gateway returned HTTP ' . $http_code . ': ' . substr($response, 0, 200);
} else {
    $log_status = 'success';
    $response_msg = 'Message successfully sent via WhatsApp Gateway.';
}

try {
    $pdo->beginTransaction();

    // Deduct credits if client is not unlimited
    $new_credits = $credits;
    if ($log_status === 'success' && $credits !== -1) {
        $new_credits = $credits - 1;
        $deduct_stmt = $pdo->prepare("UPDATE api_keys SET credits = ? WHERE id = ?");
        $deduct_stmt->execute([$new_credits, $client['id']]);
    }

    // Insert transaction log
    $log_stmt = $pdo->prepare("INSERT INTO api_logs (api_key_id, message_type, recipient_phone, status, response_message, credits_used) VALUES (?, ?, ?, ?, ?, ?)");
    $credits_deducted = ($log_status === 'success' && $credits !== -1) ? 1 : 0;
    $log_stmt->execute([$client['id'], $type, $clean_phone, $log_status, $response_msg, $credits_deducted]);

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // We still return the dispatch outcome even if logging transaction fails
    $response_msg .= ' (Warning: Transaction log failed: ' . $e->getMessage() . ')';
}

if ($log_status === 'success') {
    json_response(true, 'Message sent successfully.', 200, [
        'message_id' => bin2hex(random_bytes(8)),
        'credits_remaining' => ($credits === -1) ? 'unlimited' : $new_credits
    ]);
} else {
    json_response(false, 'Message dispatch failed: ' . $response_msg, 502);
}
