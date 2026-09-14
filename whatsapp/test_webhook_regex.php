<?php
header('Content-Type: text/plain');

$chatbot_url = "http://127.0.0.1/api/chatbot.php";
$webhook_payload = [
    'sender' => '1234567890',
    'message' => 'Hello, this is John. I am looking to buy Red Sandalwood Plantation, what are the details?',
    'session_id' => 'default'
];

$ch = curl_init($chatbot_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Host: 2fa.tehub.in'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$web_res = curl_exec($ch);
$web_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$web_err = curl_error($ch);
curl_close($ch);

echo "=== Boundary Regex Check ===\n";
echo "Incoming Message: '{$webhook_payload['message']}'\n";
echo "Webhook HTTP Code: $web_code\n";
if (!empty($web_err)) {
    echo "cURL Error: $web_err\n";
}
echo "Webhook Response:\n" . $web_res . "\n";
