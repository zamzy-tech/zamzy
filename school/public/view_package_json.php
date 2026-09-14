<?php
header('Content-Type: text/plain');
$file = '/home/shacartc/sms.tehub.in/package.json';
if (file_exists($file)) {
    echo "Content of package.json:\n";
    echo file_get_contents($file);
} else {
    echo "package.json not found!\n";
}
?>
