<?php
header('Content-Type: text/plain');
if (file_exists(__DIR__ . '/read_console_log_target_send.php')) {
    unlink(__DIR__ . '/read_console_log_target_send.php');
    echo "Deleted read_console_log_target_send.php\n";
}
if (file_exists(__DIR__ . '/read_latest_history_v2.php')) {
    unlink(__DIR__ . '/read_latest_history_v2.php');
    echo "Deleted read_latest_history_v2.php\n";
}
if (file_exists(__DIR__ . '/cleanup_final.php')) {
    unlink(__DIR__ . '/cleanup_final.php');
    echo "Deleted cleanup_final.php\n";
}
