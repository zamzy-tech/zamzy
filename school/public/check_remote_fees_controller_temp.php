<?php
$content = file_get_contents('/home/shacartc/school.tehub.in/app/Http/Controllers/FeesController.php');
$lines = explode("\n", $content);
for ($i = 980; $i <= 1095; $i++) {
    if (isset($lines[$i - 1])) {
        echo "$i: " . $lines[$i - 1] . "\n";
    }
}
?>
