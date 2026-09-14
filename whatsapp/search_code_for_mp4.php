<?php
header('Content-Type: text/plain');

function search_in_code($dir, $pattern) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), ['php', 'js', 'html', 'css'])) {
            $content = file_get_contents($file->getPathname());
            if (stripos($content, $pattern) !== false) {
                $results[] = $file->getPathname();
            }
        }
    }
    return $results;
}

$dirs_to_search = [
    '/home/shacartc/2fa.tehub.in',
    '/home/shacartc/whatsapp-service',
    '/home/shacartc/sale.theexperthub.in'
];

foreach ($dirs_to_search as $d) {
    if (is_dir($d)) {
        echo "Searching for WHATSAPP.MP4 in $d...\n";
        $found = search_in_code($d, 'whatsapp.mp4');
        foreach ($found as $f) {
            echo " - $f\n";
        }
    }
}
