<?php
header('Content-Type: text/plain');
if (file_exists('../.env')) {
    $content = file_get_contents('../.env');
    // Sanitize passwords and secrets before printing
    $lines = explode("\n", $content);
    foreach ($lines as $line) {
        if (preg_match('/(PASSWORD|KEY|SECRET)/i', $line)) {
            $parts = explode('=', $line, 2);
            echo $parts[0] . "=********\n";
        } else {
            echo $line . "\n";
        }
    }
} else {
    echo "No .env found at ../.env";
}
?>
