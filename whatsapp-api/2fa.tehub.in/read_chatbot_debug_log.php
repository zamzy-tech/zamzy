<?php
header('Content-Type: text/plain');

$log_file = '/home/shacartc/2fa.tehub.in/tmp/chatbot_debug.log';

if (file_exists($log_file)) {
    echo "=== chatbot_debug.log (Last 50 lines) ===\n";
    $lines = file($log_file);
    $last_lines = array_slice($lines, -50);
    echo implode("", $last_lines);
} else {
    echo "Log file not found at: $log_file\n";
}
