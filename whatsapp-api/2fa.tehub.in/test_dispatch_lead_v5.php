<?php
function test_url_with_host($gateway_url, $host) {
    $name = 'Test Yaseett';
    $clean_phone = '9150137159';
    $msg_text = "Hi {$name}, test message via Host header.";
    
    echo "Testing URL: $gateway_url with Host: $host\n";
    
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
        "Host: $host",
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout
    
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        echo "  CURL ERROR: $err\n";
    } else {
        echo "  HTTP CODE: $http_code\n";
        echo "  RESPONSE: " . substr($res, 0, 300) . "\n";
    }
}

test_url_with_host('http://127.0.0.1/whatsapp/send', 'sale.theexperthub.in');
test_url_with_host('https://127.0.0.1/whatsapp/send', 'sale.theexperthub.in');
test_url_with_host('http://127.0.0.1/whatsapp/send', '2fa.tehub.in');
test_url_with_host('https://127.0.0.1/whatsapp/send', '2fa.tehub.in');
