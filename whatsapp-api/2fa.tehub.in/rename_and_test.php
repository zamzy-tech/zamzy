<?php
header('Content-Type: text/plain');

$src = '/home/shacartc/2fa.tehub.in/test_gemini_webhook.php';
$dest = '/home/shacartc/2fa.tehub.in/test_webhook_new_99.php';

if (file_exists($src)) {
    copy($src, $dest);
    echo "Copied test script to unique filename: test_webhook_new_99.php\n\n";
    
    // Now trigger it via cURL
    $url = "https://2fa.tehub.in/test_webhook_new_99.php";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status Code: $code\n";
    echo "Response:\n$res\n";
    
    // Clean up dest
    unlink($dest);
    echo "\nCleaned up test_webhook_new_99.php\n";
} else {
    echo "Source test file not found on server!\n";
}
