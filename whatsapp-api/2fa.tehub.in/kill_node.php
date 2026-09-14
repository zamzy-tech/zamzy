<?php
header('Content-Type: text/plain');

$pid = 2917237;
echo "Attempting to kill Node.js process PID: $pid...\n";
$out = shell_exec("kill -9 $pid 2>&1");
echo "Output: $out\n";

// Verify if it is gone
echo "\n=== Running Processes ===\n";
echo shell_exec('ps -ef | grep node 2>&1');