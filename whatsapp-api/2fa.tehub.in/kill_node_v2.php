<?php
header('Content-Type: text/plain');
echo "=== Stale Process Killer ===\n";

// Find processes running Node under the user
$ps = shell_exec("ps -ef | grep -E 'node|lsnode' | grep -v grep 2>&1");
echo "Current Node/lsnode processes:\n$ps\n";

// Extract PIDs and kill them
if (preg_match_all('/shacartc\s+(\d+)/', $ps, $matches)) {
    foreach ($matches[1] as $pid) {
        echo "Killing PID $pid...\n";
        shell_exec("kill -9 $pid 2>&1");
    }
}

echo "\n=== Verification ===\n";
echo shell_exec("ps -ef | grep -E 'node|lsnode' | grep -v grep 2>&1");
