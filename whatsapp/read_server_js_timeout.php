<?php
header('Content-Type: text/plain');

$js_file = '/home/shacartc/whatsapp-service/server.js';
if (file_exists($js_file)) {
    $lines = file($js_file);
    for ($i = 29; $i < 75; $i++) {
        if (isset($lines[$i])) {
            echo ($i + 1) . ": " . $lines[$i];
        }
    }
} else {
    echo "Server file not found!\n";
}
