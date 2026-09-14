<?php
header('Content-Type: text/plain');
echo "=== LISTING /home/shacartc/whatsapp-service/tmp/ ===\n";
$dir = '/home/shacartc/whatsapp-service/tmp';
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = "$dir/$file";
        $type = is_dir($path) ? 'DIR' : 'FILE';
        $size = is_dir($path) ? '' : (filesize($path) . ' bytes');
        echo "[$type] $file $size\n";
    }
} else {
    echo "Directory not found: $dir\n";
}
