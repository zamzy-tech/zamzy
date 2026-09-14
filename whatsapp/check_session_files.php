<?php
header('Content-Type: text/plain');

$dir = '/home/shacartc/whatsapp-service/auth_info_baileys/session_default';
echo "=== Listing files in $dir ===\n";
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = "$dir/$file";
        echo "$file - Size: " . filesize($path) . " bytes - Modified: " . date("Y-m-d H:i:s", filemtime($path)) . "\n";
    }
} else {
    echo "Directory does not exist.\n";
}
