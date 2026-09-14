<?php
header('Content-Type: text/plain');

echo "=== Searching for .log files on server ===\n";

$dir = new RecursiveDirectoryIterator('/home/shacartc/2fa.tehub.in');
$iterator = new RecursiveIteratorIterator($dir);
$files = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'log') {
        $files[] = [
            'path' => $file->getPathname(),
            'size' => $file->getSize(),
            'mtime' => date('Y-m-d H:i:s', $file->getMTime())
        ];
    }
}

// Sort by modified time descending
usort($files, function($a, $b) {
    return strcmp($b['mtime'], $a['mtime']);
});

foreach ($files as $f) {
    echo "Path: {$f['path']} | Size: {$f['size']} bytes | Modified: {$f['mtime']}\n";
}
