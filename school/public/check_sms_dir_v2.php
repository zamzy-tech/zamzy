<?php
header('Content-Type: text/plain');
echo "Checking SMS Directory:\n";
echo "=======================\n";

$sms_dir = '/home/shacartc/sms.tehub.in';

if (is_dir($sms_dir)) {
    echo "Files in $sms_dir:\n";
    $items = @scandir($sms_dir);
    if ($items === false) {
        echo "Error: Cannot read directory $sms_dir\n";
    } else {
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $sms_dir . '/' . $item;
            $is_directory = is_dir($path);
            $size = $is_directory ? '' : ' (' . @filesize($path) . ' bytes)';
            echo "  - $item | " . ($is_directory ? "DIR" : "FILE") . $size . "\n";
        }
    }
    
    $node_modules_dir = $sms_dir . '/node_modules';
    if (is_dir($node_modules_dir)) {
        echo "\nFiles in $node_modules_dir (limit 50):\n";
        $nm_items = @scandir($node_modules_dir);
        if ($nm_items === false) {
            echo "Error: Cannot read node_modules directory\n";
        } else {
            $count = 0;
            foreach ($nm_items as $nm) {
                if ($nm === '.' || $nm === '..') continue;
                $count++;
                if ($count > 50) {
                    echo "  ... and more\n";
                    break;
                }
                $path = $node_modules_dir . '/' . $nm;
                echo "  - $nm | " . (is_dir($path) ? "DIR" : "FILE") . "\n";
            }
        }
    } else {
        echo "\nnode_modules directory does not exist in $sms_dir!\n";
    }
} else {
    echo "$sms_dir is not a directory!\n";
}
?>
