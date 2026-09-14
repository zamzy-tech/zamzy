<?php
header('Content-Type: text/plain; charset=UTF-8');

$html = file_get_contents('/home/shacartc/school.tehub.in/rendered_view.html');

// Find all occurrences of "razorpay-button-"
$offset = 0;
while (($pos = strpos($html, 'razorpay-button-', $offset)) !== false) {
    echo "Found razorpay-button at position $pos: " . substr($html, $pos - 50, 150) . "\n\n";
    $offset = $pos + 1;
}

// Find all occurrences of "start-immediate-plan"
$offset = 0;
while (($pos = strpos($html, 'start-immediate-plan', $offset)) !== false) {
    echo "Found start-immediate-plan at position $pos: " . substr($html, $pos - 50, 150) . "\n\n";
    $offset = $pos + 1;
}

unlink(__FILE__);
?>
