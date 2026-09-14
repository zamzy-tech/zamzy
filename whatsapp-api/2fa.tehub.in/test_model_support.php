<?php
header('Content-Type: text/plain');

require_once '/home/shacartc/2fa.tehub.in/db.php';

$stmt = $pdo->query("SELECT gemini_api_key FROM settings LIMIT 1");
$settings_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings_data || empty($settings_data['gemini_api_key'])) {
    die("Error: Gemini API Key is empty.\n");
}

$key = $settings_data['gemini_api_key'];
$models = ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-3.5-flash'];

foreach ($models as $model) {
    echo "=== Testing Model: $model ===\n";
    $api_url = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . urlencode($key);
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => "Say yes if you can hear me."]
                ]
            ]
        ]
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: $http_code\n";
    echo "Response: $response\n\n";
}
