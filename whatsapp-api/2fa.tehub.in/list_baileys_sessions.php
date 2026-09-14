<?php
header('Content-Type: text/plain');

$sessions = ['default', 'default_2', 'default_3', 'default_4'];
echo "=== WHATSAPP SESSIONS STATUS ===\n";
foreach ($sessions as $session) {
    $url = "https://2fa.tehub.in/whatsapp/status?session=" . urlencode($session);
    $res = @file_get_contents($url);
    if ($res) {
        $data = json_decode($res, true);
        echo "Session '$session': Status = {$data['status']}, Number = " . ($data['number'] ?: 'NULL') . "\n";
    } else {
        echo "Session '$session': Failed to query status.\n";
    }
}

echo "\n=== Listing auth directories ===\n";
echo shell_exec("ls -la /home/shacartc/whatsapp-service/auth_info_baileys/ 2>&1");
