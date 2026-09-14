<?php
require_once __DIR__ . '/db.php';

try {
    $name = 'Test Yaseett';
    $clean_phone = '9150137159';
    $msg_text = "Hi {$name}, test message via localhost:3000.";
    
    $gateway_url = 'http://localhost:3000/send';
    $gateway_token = '';
    
    echo "Gateway URL: $gateway_url\n";
    
    $ch = curl_init($gateway_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    $payload = json_encode([
        'to' => $clean_phone,
        'phone' => $clean_phone,
        'number' => $clean_phone,
        'body' => $msg_text,
        'message' => $msg_text,
        'token' => $gateway_token,
        'apikey' => $gateway_token
    ]);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        echo "CURL ERROR: $err\n";
    } else {
        echo "HTTP CODE: $http_code\n";
        echo "RESPONSE: $res\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
