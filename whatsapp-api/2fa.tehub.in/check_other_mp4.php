<?php
header('Content-Type: text/plain');

function find_all_mp4($dir) {
    $r = [];
    if (!is_dir($dir)) return $r;
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) {
            $r = array_merge($r, find_all_mp4($path));
        } else {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'mp4') {
                $r[] = $path;
            }
        }
    }
    return $r;
}

$roots = ['/home/shacartc/2fa.tehub.in', '/home/shacartc/whatsapp-service', '/home/shacartc/public_html', '/home/shacartc/sale.theexperthub.in'];
foreach ($roots as $root) {
    echo "=== Searching $root ===\n";
    $list = find_all_mp4($root);
    foreach ($list as $item) {
         echo "$item (" . filesize($item) . " bytes)\n";
    }
}
