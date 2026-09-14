<?php
header('Content-Type: text/plain');
$log = '/home/shacartc/whatsapp-service/stderr.log';
if (file_exists($log)) {
    $lines = file($log);
    $matches = [];
    foreach ($lines as $line) {
        if (strpos(strtolower($line), 'passenger') !== false || strpos(strtolower($line), 'litespeed') !== false || strpos(strtolower($line), 'nodevenv') !== false) {
            $matches[] = $line;
        }
    }
    $last_matches = array_slice($matches, -50);
    echo implode("", $last_matches);
} else {
    echo "NO_LOG_FILE\n";
}
