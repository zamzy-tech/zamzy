<?php
header('Content-Type: text/plain');
$log = '/home/shacartc/whatsapp-service/stderr.log';
if (file_exists($log)) {
    $lines = file($log);
    $last_lines = array_slice($lines, -100);
    echo implode("", $last_lines);
} else {
    echo "NO_LOG_FILE";
}
