<?php
header('Content-Type: text/plain; charset=UTF-8');
$envPath = '../.env';
if (file_exists($envPath)) {
    $lines = file($envPath);
    foreach ($lines as $line) {
        if (strpos($line, 'DB_') === 0) {
            echo $line;
        }
    }
} else {
    echo ".env not found at $envPath\n";
}
?>
