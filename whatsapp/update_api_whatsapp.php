<?php
header('Content-Type: text/plain');
$file = '/home/shacartc/2fa.tehub.in/api/whatsapp.php';
$content = file_get_contents($file);

$target = '$clean_phone = preg_replace(\'/[^0-9]/\', \'\', $to);
if (strlen($clean_phone) < 8) {
    json_response(false, \'Bad Request: Invalid recipient phone number.\', 400);
}';

$replacement = 'if (strpos($to, \'@g.us\') !== false) {
    $clean_phone = trim($to);
} else {
    $clean_phone = preg_replace(\'/[^0-9]/\', \'\', $to);
    if (strlen($clean_phone) < 8) {
        json_response(false, \'Bad Request: Invalid recipient phone number.\', 400);
    }
}';

if (strpos($content, $target) !== false) {
    $newContent = str_replace($target, $replacement, $content);
    file_put_contents($file, $newContent);
    echo "Successfully updated api/whatsapp.php!\n";
} else {
    echo "Target string not found in api/whatsapp.php\n";
}
?>
