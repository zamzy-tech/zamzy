<?php
if (file_exists('read_log_30.php')) {
    unlink('read_log_30.php');
    echo "Deleted read_log_30.php\n";
}
if (file_exists('read_log_200.php')) {
    unlink('read_log_200.php');
    echo "Deleted read_log_200.php\n";
}
if (file_exists('cleanup_logs.php')) {
    register_shutdown_function(function() {
        unlink('cleanup_logs.php');
    });
    echo "Unlinking cleanup_logs.php on shutdown\n";
}
echo "Log reader cleanup completed!\n";
?>
