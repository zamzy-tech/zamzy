<?php
header('Content-Type: text/plain');

$dir = __DIR__;
$files = scandir($dir);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $content = file_get_contents($file);
        if (strpos($content, 'youtubers') !== false) {
            echo "Found in file: $file\n";
            // Print matching lines
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line) {
                if (strpos($line, 'CREATE TABLE') !== false || strpos($line, 'youtubers') !== false) {
                    echo "  Line " . ($i + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}
