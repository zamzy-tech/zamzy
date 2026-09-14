<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Only POST requests are permitted.']);
    exit;
}

$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Recipient phone number and message content are required.']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    
    if (!$settings) {
        echo json_encode(['success' => false, 'error' => 'Application settings could not be retrieved from the database.']);
        exit;
    }
    
    $gateway_type = $settings['whatsapp_gateway_type'] ?? 'browser';
    $gateway_url = $settings['whatsapp_gateway_url'] ?? '';
    $gateway_token = $settings['whatsapp_gateway_token'] ?? '';
    
    if ($gateway_type !== 'gateway') {
        echo json_encode(['success' => false, 'error' => 'Background API Gateway is not enabled in settings. Please configure it under settings.']);
        exit;
    }
    
    if (empty($gateway_url)) {
        echo json_encode(['success' => false, 'error' => 'Background API Gateway Endpoint URL is not configured.']);
        exit;
    }
    
    $pdf = trim($_POST['pdf'] ?? '');
    $filename = trim($_POST['filename'] ?? 'Invoice.pdf');
    
    $clean_phone = preg_replace('/[^0-9]/', '', $phone);
    
    $ch = curl_init($gateway_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    $payload_data = [
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
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        echo json_encode(['success' => false, 'error' => 'HTTP request failed: ' . $err]);
        exit;
    }
    
    if ($http_code >= 400) {
        echo json_encode([
            'success' => false, 
            'error' => 'Background gateway returned HTTP status ' . $http_code . '. Response: ' . substr($response, 0, 150)
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '🚀 Fast Background Message dispatched successfully!',
        'gateway_response' => substr($response, 0, 150)
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
