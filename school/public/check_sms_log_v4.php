<?php
header('Content-Type: text/plain; charset=UTF-8');

$sms_dir = '/home/shacartc/sms.tehub.in';

if (is_dir($sms_dir)) {
    echo "=== NODE ENV ===\n";
    $env_path = $sms_dir . '/.env';
    if (file_exists($env_path)) {
        $env_lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($env_lines as $line) {
            if (preg_match('/(PASSWORD|KEY|SECRET)/i', $line)) {
                $parts = explode('=', $line, 2);
                echo $parts[0] . "=********\n";
            } else {
                echo $line . "\n";
            }
        }
    } else {
        echo "No Node .env found.\n";
    }
} else {
    echo "$sms_dir is not a directory.\n";
}
?>
