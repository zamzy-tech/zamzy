<?php
header('Content-Type: text/plain');

echo "=== Running Node.js processes ===\n";
$output = [];
exec("ps aux | grep node", $output);
foreach ($output as $line) {
    echo $line . "\n";
}
