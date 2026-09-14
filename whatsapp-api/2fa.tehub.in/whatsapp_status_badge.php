<?php
// Load the base TEH logo
$logo_path = __DIR__ . '/teh_logo.png';
if (!file_exists($logo_path)) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

// Output the image directly
header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
readfile($logo_path);
exit;
