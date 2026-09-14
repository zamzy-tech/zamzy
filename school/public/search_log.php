<?php
header('Content-Type: text/plain');
$logFile = '/home/shacartc/school.tehub.in/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    preg_match_all('/\[2026-06-12.*(WhatsApp|Notification|FeesPaid|Error|Exception).*/i', $content, $matches);
    echo implode("\n", array_slice($matches[0], -100));
} else {
    echo "Log file not found.";
}
?>
