<?php
header('Content-Type: text/plain');

$js_file = '/home/shacartc/2fa.tehub.in/whatsapp-service-server.js';
if (file_exists($js_file)) {
    $lines = file($js_file);
    for ($i = 359; $i < 425; $i++) {
        if (isset($lines[$i])) {
            echo ($i + 1) . ": " . $lines[$i];
        }
    }
} else {
    echo "whatsapp-service-server.js not found!\n";
}
