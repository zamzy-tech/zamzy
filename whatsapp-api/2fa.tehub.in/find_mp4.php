<?php
header('Content-Type: text/plain');

function search_mp4($dir) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'mp4') {
            $results[] = $file->getPathname();
        }
    }
    return $results;
}

$dirs_to_search = [
    '/home/shacartc/2fa.tehub.in',
    '/home/shacartc/whatsapp-service',
    '/home/shacartc/public_html',
    '/home/shacartc/sale.theexperthub.in'
];

foreach ($dirs_to_search as $d) {
    if (is_dir($d)) {
        echo "Searching in $d...\n";
        $found = search_mp4($d);
        foreach ($found as $f) {
            echo " - $f\n";
        }
    }
}
