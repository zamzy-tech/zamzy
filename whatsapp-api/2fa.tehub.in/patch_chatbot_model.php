<?php
header('Content-Type: text/plain');

$chatbot_file = '/home/shacartc/2fa.tehub.in/api/chatbot.php';

if (file_exists($chatbot_file)) {
    $content = file_get_contents($chatbot_file);
    
    $target = 'models/gemini-1.5-flash:generateContent';
    $replacement = 'models/gemini-3.5-flash:generateContent';
    
    if (strpos($content, $target) !== false) {
        $content = str_replace($target, $replacement, $content);
        file_put_contents($chatbot_file, $content);
        echo "Successfully updated Gemini model to gemini-3.5-flash in api/chatbot.php on the server.\n";
    } else {
        echo "Gemini model gemini-1.5-flash target not found in api/chatbot.php (it might already be patched or model changed).\n";
    }
} else {
    echo "api/chatbot.php not found on server!\n";
}
