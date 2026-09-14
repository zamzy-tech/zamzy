<?php
header('Content-Type: text/plain; charset=UTF-8');
$logPath = '/home/shacartc/school.tehub.in/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    echo "Logs from today around 15:49 to 15:51:\n";
    $print = false;
    $printedLines = 0;
    foreach ($lines as $line) {
        if (preg_match('/^\[2026-06-13 15:(49|50|51)/', $line)) {
            $print = true;
        } elseif (preg_match('/^\[2026-06-13/', $line)) {
            $print = false; // Stop printing when a new log entry from another time starts
        }
        
        if ($print) {
            echo $line;
            $printedLines++;
            if ($printedLines > 300) {
                echo "... truncated ...\n";
                break;
            }
        }
    }
} else {
    echo "Log file not found\n";
}
?>
