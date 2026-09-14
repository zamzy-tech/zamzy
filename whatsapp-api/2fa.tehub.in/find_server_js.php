<?php
header('Content-Type: text/plain');

echo "=== Searching for server JS files on server ===\n";

$dir = new RecursiveDirectoryIterator('/home/shacartc');
$iterator = new RecursiveIteratorIterator($dir);
$files = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'js') {
        $name = strtolower($file->getFilename());
        if (strpos($name, 'whatsapp') !== false || strpos($name, 'server') !== false) {
            $files[] = [
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'mtime' => date('Y-m-d H:i:s', $file->getMTime())
            ];
        }
    }
}

foreach ($files as $f) {
    echo "Path: {$f['path']} | Size: {$f['size']} bytes | Modified: {$f['mtime']}\n";
}
