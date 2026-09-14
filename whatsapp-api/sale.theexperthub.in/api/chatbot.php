<?php
// Webhook endpoint to process incoming WhatsApp messages and match chatbot rules
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php';

// Disable error display to prevent output corruption
ini_set('display_errors', 0);
error_reporting(0);

// Helper function to return JSON and exit
function webhook_response($reply = null, $image_url = null, $buttons = null, $error = null) {
    $res = [];
    if ($reply !== null) {
        $res['reply'] = $reply;
    }
    if ($image_url !== null && !empty($image_url)) {
        $res['image_url'] = $image_url;
    }
    if ($buttons !== null && !empty($buttons)) {
        $res['buttons'] = $buttons;
    }
    if ($error !== null) {
        $res['error'] = $error;
    }
    echo json_encode($res);
    exit;
}

// 1. Parse JSON input payload
$raw_body = file_get_contents('php://input');
$data = json_decode($raw_body, true);

if (!$data) {
    webhook_response(null, null, null, 'Invalid payload');
}

$sender = trim($data['sender'] ?? '');
$message = trim($data['message'] ?? '');
$session_id = trim($data['session_id'] ?? 'default');

if (empty($sender) || empty($message)) {
    webhook_response(null, null, null, 'Missing sender or message content');
}

// 2. Resolve Client ID by Session ID
$client_id = null;
$chatbot_enabled = 1;

if ($session_id === 'default') {
    // Admin chatbot
    $client_id = 0;
    try {
        $stmt = $pdo->query("SELECT chatbot_enabled FROM settings LIMIT 1");
        $chatbot_enabled = intval($stmt->fetchColumn() ?? 1);
    } catch (PDOException $e) {
        $chatbot_enabled = 1;
    }
} else {
    // Client device slot session (Format: phone or phone_slot)
    $parts = explode('_', $session_id);
    $login_id = $parts[0];
    
    try {
        $stmt = $pdo->prepare("SELECT id, chatbot_enabled FROM api_keys WHERE login_id = ? LIMIT 1");
        $stmt->execute([$login_id]);
        $client = $stmt->fetch();
        if ($client) {
            $client_id = intval($client['id']);
            $chatbot_enabled = intval($client['chatbot_enabled']);
        }
    } catch (PDOException $e) {
        webhook_response(null, null, null, 'Database error: ' . $e->getMessage());
    }
}

if ($client_id === null || $chatbot_enabled === 0) {
    webhook_response(null, null, null, 'Chatbot disabled or client not found');
}

// 3. Match Keyword Rules (Case-insensitive)
$msg_clean = strtolower($message);
$reply = null;
$image_url = null;
$buttons = null;

try {
    // Rule A: Exact match
    $stmt_exact = $pdo->prepare("SELECT reply_text, image_url, buttons_json FROM chatbot_rules WHERE client_id = ? AND LOWER(keyword) = ? LIMIT 1");
    $stmt_exact->execute([$client_id, $msg_clean]);
    $rule = $stmt_exact->fetch(PDO::FETCH_ASSOC);
    
    if ($rule) {
        $reply = $rule['reply_text'];
        $image_url = $rule['image_url'];
        if (!empty($rule['buttons_json'])) {
            $buttons = json_decode($rule['buttons_json'], true);
        }
    } else {
        // Rule B: Partial match (if exact match fails)
        $stmt_all = $pdo->prepare("SELECT keyword, reply_text, image_url, buttons_json FROM chatbot_rules WHERE client_id = ?");
        $stmt_all->execute([$client_id]);
        $rules = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rules as $r) {
            $kw = strtolower($r['keyword']);
            if (strpos($msg_clean, $kw) !== false) {
                $reply = $r['reply_text'];
                $image_url = $r['image_url'];
                if (!empty($r['buttons_json'])) {
                    $buttons = json_decode($r['buttons_json'], true);
                }
                break;
            }
        }
    }
} catch (PDOException $e) {
    webhook_response(null, null, null, 'Rule lookup failed: ' . $e->getMessage());
}

if ($reply) {
    webhook_response($reply, $image_url, $buttons);
} else {
    webhook_response(null, null, null, 'No keyword match found');
}
