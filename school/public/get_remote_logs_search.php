<!DOCTYPE html>
<html>
<body>
<pre>
<?php
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    $lines = explode("\n", $content);
    $matches = [];
    foreach ($lines as $i => $line) {
        if (stripos($line, 'migrate') !== false || stripos($line, 'seed') !== false || stripos($line, 'restore') !== false || stripos($line, 'rollback') !== false || stripos($line, 'backup') !== false) {
            $matches[] = ($i + 1) . ": " . $line;
        }
    }
    
    echo "Found " . count($matches) . " matches:\n";
    $last_matches = array_slice($matches, -50);
    echo implode("\n", $last_matches) . "\n";
} else {
    echo "Log file not found";
}
?>
</pre>
</body>
</html>
