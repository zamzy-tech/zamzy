<?php
header('Content-Type: text/plain');
$content = file_get_contents('/home/shacartc/whatsapp-service/server.js');
echo $content;
?>