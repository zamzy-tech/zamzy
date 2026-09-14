<?php
header('Content-Type: text/plain; charset=UTF-8');
$logPath = '/home/shacartc/school.tehub.in/storage/logs/laravel.log';
if (file_exists($logPath)) {
    // Read the last 500 lines of the log file
    $lines = file($logPath);
    $lastLines = array_slice($lines, -500);
    $content = implode("", $lastLines);
    echo base64_encode($content);
} else {
    echo base64_encode("Log file not found at $logPath");
}
?>
