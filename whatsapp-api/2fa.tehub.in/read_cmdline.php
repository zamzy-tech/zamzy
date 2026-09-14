<?php
header('Content-Type: text/plain');
$pid = 2917237;
$cmdline = "/proc/$pid/cmdline";
if (file_exists($cmdline)) {
    $content = file_get_contents($cmdline);
    // Replace null bytes with spaces
    echo str_replace("\0", " ", $content) . "\n";
} else {
    echo "PID $pid NOT found or cmdline not accessible.\n";
}
