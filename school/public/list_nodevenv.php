<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/plain');

echo "Locating npm...\n";
echo "================\n";

function search_npm($dir) {
    if (!is_dir($dir)) return;
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if (basename($file) === 'npm') {
            echo "Found npm at: $file\n";
        }
    }
}

try {
    search_npm('/home/shacartc/nodevenv');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
