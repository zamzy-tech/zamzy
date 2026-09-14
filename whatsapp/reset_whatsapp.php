<?php
header('Content-Type: text/plain');

$ch = curl_init("https://2fa.tehub.in/whatsapp/disconnect?session=default");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch);
curl_close($ch);

echo "Disconnect response: $res\n";

$dir = '/home/shacartc/whatsapp-service/auth_info_baileys/session_default';
if (is_dir($dir)) {
    echo "Directory still exists. Cleaning it manually...\n";
    shell_exec("rm -rf $dir");
} else {
    echo "Directory deleted successfully.\n";
}

// Restart Node app
touch('/home/shacartc/whatsapp-service/tmp/restart.txt');
echo "Node app restarted.\n";
