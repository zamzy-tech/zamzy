<?php
header('Content-Type: application/json');

// Call local Baileys gateway to inspect sessions and groups
$url = 'https://2fa.tehub.in/whatsapp/chats?session=8106653373';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode(['http_code' => $code, 'response' => json_decode($res, true) ?: $res]);
?>
