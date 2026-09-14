<?php
require_once __DIR__ . '/db.php';
$stmt = $pdo->query("SELECT whatsapp_gateway_type, whatsapp_gateway_url, whatsapp_gateway_token FROM settings LIMIT 1");
print_r($stmt->fetch());
