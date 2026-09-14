<?php
header('Content-Type: text/plain');
$lines = file('/home/shacartc/2fa.tehub.in/api/chatbot.php');
for ($i = 129; $i < 240; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
