<?php
header('Content-Type: text/plain');
$content = file_get_contents('/home/shacartc/whatsapp-service/server.js');
$lines = explode("\n", $content);
for ($i = 570; $i <= 630; $i++) {
    if (isset($lines[$i])) echo ($i+1) . ": " . $lines[$i] . "\n";
}
?>
