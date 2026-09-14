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

    echo "\n=== STDERR LOG (LAST 100 LINES) ===\n";
    $log_path = $sms_dir . '/stderr.log';
    if (file_exists($log_path)) {
        $file = escapeshellarg($log_path);
        $last_lines = `tail -n 100 $file 2>&1`;
        if (empty($last_lines)) {
            // Fallback if tail is not available
            $content = file_get_contents($log_path);
            $lines = explode("\n", $content);
            $last_lines = implode("\n", array_slice($lines, -100));
        }
        echo $last_lines . "\n";
    } else {
        echo "No stderr.log found.\n";
    }
    
} else {
    echo "$sms_dir is not a directory.\n";
}
?>
