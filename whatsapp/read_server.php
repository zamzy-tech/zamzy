<?php
header('Content-Type: text/plain');
$files = ['/home/shacartc/whatsapp-service/server.js', '/home/shacartc/whatsapp-service/app.js', '/home/shacartc/whatsapp-service/index.js'];
foreach ($files as $f) {
    if (file_exists($f)) {
        echo "=== $f ===\n";
        echo file_get_contents($f);
    }
}
?>
