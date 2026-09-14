<?php
header('Content-Type: text/plain');
$pid_file = '/home/shacartc/whatsapp-service/tmp/whatsapp.pid';
if (file_exists($pid_file)) {
    $pid = intval(file_get_contents($pid_file));
    if ($pid > 0 && function_exists('posix_kill')) {
        @posix_kill($pid, 9);
        echo "Killed process $pid\n";
    }
}
@unlink($pid_file);
echo "Cleaned PID lock\n";
?>
