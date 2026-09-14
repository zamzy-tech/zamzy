<?php
header('Content-Type: text/plain; charset=UTF-8');

$sms_dir = '/home/shacartc/sms.tehub.in';
$env_path = $sms_dir . '/.env';

if (file_exists($env_path)) {
    $content = file_get_contents($env_path);
    echo $content;
} else {
    echo "No Node .env found.\n";
}
?>
