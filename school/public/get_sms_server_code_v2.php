<?php
header('Content-Type: text/plain');
$files = [
    '/home/shacartc/sms.tehub.in/routes/campaign.js',
    '/home/shacartc/sms.tehub.in/routes/billing.js'
];
foreach ($files as $file) {
    echo "=== FILE: $file ===\n";
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo "File not found: $file";
    }
    echo "\n\n";
}
?>
