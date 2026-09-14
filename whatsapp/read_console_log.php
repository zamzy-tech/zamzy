<?php
header('Content-Type: text/plain');
echo "=== Node.js console.log ===\n";
$log_file = '/home/shacartc/whatsapp-service/tmp/console.log';
if (file_exists($log_file)) {
    $content = file($log_file);
    $last_lines = array_slice($content, -150);
    echo implode("", $last_lines);
} else {
    echo "Console log file not found at: $log_file\n";
}

echo "\n=== Node.js stderr.log ===\n";
$err_file = '/home/shacartc/whatsapp-service/stderr.log';
if (file_exists($err_file)) {
    $content = file($err_file);
    $last_lines = array_slice($content, -150);
    echo implode("", $last_lines);
} else {
    echo "Stderr log file not found at: $err_file\n";
}
