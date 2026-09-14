<?php
header('Content-Type: text/plain');
$restart_file = '/home/shacartc/whatsapp-service/tmp/restart.txt';
if (touch($restart_file)) {
    echo "Successfully touched $restart_file\n";
} else {
    echo "Failed to touch $restart_file\n";
}
