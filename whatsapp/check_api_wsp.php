<?php
header('Content-Type: text/plain');
$content = file_get_contents('/home/shacartc/2fa.tehub.in/api/whatsapp.php');
$lines = explode("\n", $content);
for ($i = 115; $i <= 140; $i++) {
    if (isset($lines[$i])) echo ($i+1) . ": " . $lines[$i] . "\n";
}
?>
