<?php
header('Content-Type: text/plain');
$logFile = '/home/shacartc/school.tehub.in/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last_lines = array_slice($lines, -150);
    echo implode("", $last_lines);
} else {
    echo "Log file not found: $logFile";
}
?>
