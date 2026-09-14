<?php
header('Content-Type: text/plain');
$content = file_get_contents('/home/shacartc/whatsapp-service/server.js');
$lines = explode("\n", $content);
foreach ($lines as $idx => $line) {
    if (strpos($line, 'app.post') !== false || strpos($line, 'recipientJid') !== false || strpos($line, 'targetJid') !== false) {
        for ($i = max(0, $idx-2); $i <= min(count($lines)-1, $idx+30); $i++) {
            echo ($i+1) . ": " . $lines[$i] . "\n";
        }
        echo "===================================\n";
    }
}
?>
