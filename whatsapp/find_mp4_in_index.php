<?php
header('Content-Type: text/plain');

$index_file = '/home/shacartc/2fa.tehub.in/index.php';
if (file_exists($index_file)) {
    $lines = file($index_file);
    foreach ($lines as $i => $line) {
        if (stripos($line, 'mp4') !== false) {
            echo ($i + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "index.php not found!\n";
}
