<?php
header('Content-Type: text/plain');

$dir = '/home/shacartc/whatsapp-service/';

// Let's inspect logs or group jids
$log = file_get_contents($dir . 'tmp/console.log');
preg_match_all('/([0-9\-]+@g\.us)/', $log, $matches);
echo "Group JIDs in logs:\n";
print_r(array_unique($matches[0]));
?>
