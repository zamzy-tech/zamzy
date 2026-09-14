<?php
header('Content-Type: text/plain');

$chatbot_file = '/home/shacartc/2fa.tehub.in/api/chatbot.php';

if (file_exists($chatbot_file)) {
    echo file_get_contents($chatbot_file);
} else {
    echo "api/chatbot.php not found on server!\n";
}
