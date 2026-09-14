<?php
$pdfContent = file_get_contents('/home/shacartc/school.tehub.in/public/test_receipt_output.pdf');

$words = ['YASEER', 'CASH', 'Particulars', 'TOTAL AMOUNT PAID', 'Anupama', 'Thank you'];
foreach ($words as $word) {
    $found = (strpos($pdfContent, $word) !== false) ? "FOUND" : "NOT FOUND";
    echo "$word: $found\n";
}
// Clean up the temp pdf file
@unlink('/home/shacartc/school.tehub.in/public/test_receipt_output.pdf');
?>
