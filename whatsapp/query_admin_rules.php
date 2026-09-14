<?php
header('Content-Type: text/plain');

require_once '/home/shacartc/2fa.tehub.in/db.php';

echo "=== Admin Chatbot Rules (client_id = 0) ===\n";
try {
    $stmt = $pdo->query("SELECT id, keyword, reply_text FROM chatbot_rules WHERE client_id = 0");
    $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rules) {
        foreach ($rules as $r) {
            echo "ID: {$r['id']} | Keyword: '{$r['keyword']}'\nReply:\n{$r['reply_text']}\n--------------------\n";
        }
    } else {
        echo "No rules found for client_id = 0.\n";
    }
} catch (PDOException $e) {
    echo "Error querying rules: " . $e->getMessage() . "\n";
}
