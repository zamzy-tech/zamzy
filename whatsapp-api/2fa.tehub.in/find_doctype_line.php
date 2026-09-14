<?php
header('Content-Type: text/plain');

$content = file_get_contents(__DIR__ . '/api_link.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, '<!DOCTYPE') !== false) {
        $ln = $i + 1;
        echo "Found at line $ln: $line\n";
    }
}
