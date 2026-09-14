<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/plain');

echo "Node.js Application Diagnostic Logs v3\n";
echo "=======================================\n";

$sms_dir = '/home/shacartc/sms.tehub.in';

if (!is_dir($sms_dir)) {
    die("Error: Node.js dir $sms_dir does not exist.\n");
}

echo "1. Checking directory structure:\n";
$items = scandir($sms_dir);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $path = $sms_dir . '/' . $item;
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    echo "  - $item ($perms) | " . (is_dir($path) ? "DIR" : "FILE") . "\n";
}

echo "\n1b. Listing node_modules directory:\n";
$nm_dir = $sms_dir . '/node_modules';
if (is_dir($nm_dir)) {
    $nm_items = scandir($nm_dir);
    echo "  Total items: " . count($nm_items) . "\n";
    foreach (array_slice($nm_items, 0, 40) as $nm) {
        if ($nm === '.' || $nm === '..') continue;
        echo "    * $nm\n";
    }
} else {
    echo "  node_modules folder does not exist!\n";
}

echo "\n2. Reading last 50 lines of stderr.log:\n";
$log_file = $sms_dir . '/stderr.log';
if (file_exists($log_file)) {
    echo "Log file size: " . filesize($log_file) . " bytes\n";
    echo "--- CONTENT START ---\n";
    $lines = file($log_file);
    $last_lines = array_slice($lines, -50);
    echo implode("", $last_lines);
    echo "--- CONTENT END ---\n";
} else {
    echo "stderr.log does not exist!\n";
}
?>
