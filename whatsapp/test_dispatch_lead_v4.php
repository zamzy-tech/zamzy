<?php
function test_url($gateway_url) {
    $name = 'Test Yaseett';
    $clean_phone = '9150137159';
    $msg_text = "Hi {$name}, test message.";
    
    echo "Testing URL: $gateway_url\n";
    
    $ch = curl_init($gateway_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $payload = json_encode([
        'to' => $clean_phone,
        'phone' => $clean_phone,
        'number' => $clean_phone,
        'body' => $msg_text,
        'message' => $msg_text,
        'token' => '',
        'apikey' => ''
    ]);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // 3 seconds timeout
    
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        echo "  CURL ERROR: $err\n";
    } else {
        echo "  HTTP CODE: $http_code\n";
        echo "  RESPONSE: $res\n";
    }
}

test_url('http://127.0.0.1/whatsapp/send');
test_url('http://localhost/whatsapp/send');
test_url('http://127.0.0.1/send');
test_url('http://localhost/send');
test_url('http://127.0.0.1:3000/send');
test_url('http://localhost:3000/send');
