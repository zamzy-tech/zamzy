<?php
header('Content-Type: text/plain');

$chatbot_file = '/home/shacartc/2fa.tehub.in/api/chatbot.php';

if (file_exists($chatbot_file)) {
    $content = file_get_contents($chatbot_file);
    
    $target = '        foreach ($rules as $r) {
            $kw = strtolower($r[\'keyword\']);
            if (strpos($msg_clean, $kw) !== false) {
                $reply = $r[\'reply_text\'];';
                
    $replacement = '        foreach ($rules as $r) {
            $kw = strtolower($r[\'keyword\']);
            $pattern = \'/\b\' . preg_quote($kw, \'/\') . \'\b/\';
            if (preg_match($pattern, $msg_clean)) {
                $reply = $r[\'reply_text\'];';
                
    if (strpos($content, '$pattern = \'/\b\'') === false) {
        if (strpos($content, $target) !== false) {
            $content = str_replace($target, $replacement, $content);
            file_put_contents($chatbot_file, $content);
            echo "Successfully updated chatbot rule matching to use regex word boundaries in api/chatbot.php on the server.\n";
        } else {
            echo "Error: Target block for regex rules update not found in api/chatbot.php.\n";
        }
    } else {
        echo "Regex word boundaries already enabled in api/chatbot.php.\n";
    }
} else {
    echo "api/chatbot.php not found on server!\n";
}
