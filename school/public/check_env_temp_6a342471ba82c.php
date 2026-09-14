<?php
header("Content-Type: text/plain");
$envFile = "/home/shacartc/school.tehub.in/.env";
if (file_exists($envFile)) {
    $lines = file($envFile);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || strpos($trimmed, "#") === 0) continue;
        if (strpos($trimmed, "DB_") === 0 || strpos($trimmed, "STANDALONE") === 0 || strpos($trimmed, "APP_URL") === 0) {
            echo $trimmed . "\n";
        }
    }
} else {
    echo ".env not found\n";
}
?>