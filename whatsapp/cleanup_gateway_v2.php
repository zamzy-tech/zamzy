<?php
header('Content-Type: text/plain');
echo "=== WhatsApp Gateway Cleanup & Reset ===\n";

$dir = '/home/shacartc/whatsapp-service';

// 1. Delete huge logs
$logs = [
    "$dir/stderr.log",
    "$dir/tmp/console.log",
    "$dir/tmp/chatbot_debug.log"
];
foreach ($logs as $log) {
    if (file_exists($log)) {
        if (unlink($log)) {
            echo "Deleted log file: $log\n";
        } else {
            echo "Failed to delete log: $log\n";
        }
    } else {
        echo "Log not found: $log\n";
    }
}

// 2. Clear default session files
$session_dir = "$dir/auth_info_baileys/default";
if (is_dir($session_dir)) {
    // Delete files inside
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($session_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    rmdir($session_dir);
    echo "Cleared corrupted session folder: $session_dir\n";
} else {
    echo "Default session folder does not exist or already cleared.\n";
}

// 3. Kill Node processes to restart the application
$ps = shell_exec("ps -ef | grep -E 'node|lsnode' | grep -v grep 2>&1");
echo "\nStale processes to kill:\n$ps\n";

if (preg_match_all('/shacartc\s+(\d+)/', $ps, $matches)) {
    foreach ($matches[1] as $pid) {
        echo "Killing PID $pid...\n";
        shell_exec("kill -9 $pid 2>&1");
    }
}

echo "\nVerification of running processes:\n";
echo shell_exec("ps -ef | grep -E 'node|lsnode' | grep -v grep 2>&1");
