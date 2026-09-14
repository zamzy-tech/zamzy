<?php
header('Content-Type: text/plain');

function searchFiles($dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (strpos($content, 'disconnect_whatsapp') !== false || strpos($content, 'whatsapp_is_connected') !== false) {
                echo "Found in: " . $file->getPathname() . "\n";
            }
        }
    }
}

searchFiles(__DIR__);
