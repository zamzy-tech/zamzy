<?php
header('Content-Type: text/plain');

$chatbot_file = '/home/shacartc/2fa.tehub.in/api/chatbot.php';

if (file_exists($chatbot_file)) {
    $content = file_get_contents($chatbot_file);
    
    // Target Rule B block
    $target = '    } else {
        // Rule B: Partial match (if exact match fails)
        $stmt_all = $pdo->prepare("SELECT keyword, reply_text, image_url, buttons_json FROM chatbot_rules WHERE client_id = ?");
        $stmt_all->execute([$client_id]);
        $rules = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rules as $r) {
            $kw = strtolower($r[\'keyword\']);
            $pattern = \'/\b\' . preg_quote($kw, \'/\') . \'\b/\';
            if (preg_match($pattern, $msg_clean)) {
                $reply = $r[\'reply_text\'];
                $image_url = $r[\'image_url\'];
                if (!empty($r[\'buttons_json\'])) {
                    $buttons = json_decode($r[\'buttons_json\'], true);
                }
                break;
            }
        }
    }';
    
    $replacement = '    } else {
        // Rule B: Partial match (if exact match fails)
        // If Gemini is enabled for admin, we bypass partial match rules to let Gemini handle natural language enquiries
        if ($session_id !== \'default\' || $gemini_sales_enabled == 0) {
            $stmt_all = $pdo->prepare("SELECT keyword, reply_text, image_url, buttons_json FROM chatbot_rules WHERE client_id = ?");
            $stmt_all->execute([$client_id]);
            $rules = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($rules as $r) {
                $kw = strtolower($r[\'keyword\']);
                $pattern = \'/\b\' . preg_quote($kw, \'/\') . \'\b/\';
                if (preg_match($pattern, $msg_clean)) {
                    $reply = $r[\'reply_text\'];
                    $image_url = $r[\'image_url\'];
                    if (!empty($r[\'buttons_json\'])) {
                        $buttons = json_decode($r[\'buttons_json\'], true);
                    }
                    break;
                }
            }
        }
    }';
    
    if (strpos($content, '$session_id !== \'default\' || $gemini_sales_enabled == 0') === false) {
        if (strpos($content, $target) !== false) {
            $content = str_replace($target, $replacement, $content);
            file_put_contents($chatbot_file, $content);
            echo "Successfully updated chatbot logic to bypass partial matching when Gemini is enabled.\n";
        } else {
            // Try matching target with carriage returns
            $target_cr = str_replace("\n", "\r\n", $target);
            $replacement_cr = str_replace("\n", "\r\n", $replacement);
            if (strpos($content, $target_cr) !== false) {
                $content = str_replace($target_cr, $replacement_cr, $content);
                file_put_contents($chatbot_file, $content);
                echo "Successfully updated chatbot logic to bypass partial matching when Gemini is enabled (CRLF).\n";
            } else {
                echo "Error: Target Rule B block not found in api/chatbot.php.\n";
            }
        }
    } else {
        echo "Partial matching bypass when Gemini is enabled is already present in api/chatbot.php.\n";
    }
} else {
    echo "api/chatbot.php not found on server!\n";
}
