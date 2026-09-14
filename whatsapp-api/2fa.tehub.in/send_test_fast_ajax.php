<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

// Verify if the user is authenticated in any dashboard
$isAuthenticated = false;
$sessionId = 'default';

// 1. Check if logged in as admin/staff
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    $isAuthenticated = true;
    $sessionId = 'default';
}
// 2. Check if logged in as YouTuber creator
elseif (isset($_SESSION['yt_user_id'])) {
    $isAuthenticated = true;
    $sessionId = 'youtuber_' . $_SESSION['yt_user_id'];
}
elseif (isset($_SESSION['client_key'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$_SESSION['client_key']]);
        $client = $stmt->fetch();
        if ($client) {
            $is_expired = (!empty($client['expiry_date']) && strtotime($client['expiry_date']) < time());
            if ($is_expired) {
                echo json_encode(['success' => false, 'error' => 'Your subscription has expired. Please renew your plan to send test messages.']);
                exit;
            }
            $isAuthenticated = true;
            $sessionId = $client['login_id'];
        }
    } catch (PDOException $e) {
        // Fallback or silence
    }
}

if (!$isAuthenticated) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in first.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Only POST is supported.']);
    exit;
}

$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Recipient phone number is required.']);
    exit;
}

if (empty($message)) {
    $message = 'Hello! This is a test message from THE EXPERT HUB 2FA Gateway 🧪';
}

try {
    // Fetch gateway URL from settings table
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    
    if (!$settings) {
        echo json_encode(['success' => false, 'error' => 'Application settings could not be retrieved.']);
        exit;
    }
    
    $gateway_url = $settings['whatsapp_gateway_url'] ?? '';
    
    if (empty($gateway_url)) {
        echo json_encode(['success' => false, 'error' => 'WhatsApp gateway URL is not configured in settings.']);
        exit;
    }
    
    // Replace send endpoint with correct session ID query param
    $target_url = $gateway_url;
    if (strpos($target_url, '?') !== false) {
        $target_url .= '&session=' . urlencode($sessionId);
    } else {
        $target_url .= '?session=' . urlencode($sessionId);
    }
    
    $clean_phone = trim($phone);
    if (strpos($clean_phone, '@g.us') === false) {
        $clean_phone = preg_replace('/[^0-9]/', '', $clean_phone);
    }
    
    $ch = curl_init($target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $payload_data = [
        'to' => $clean_phone,
        'phone' => $clean_phone,
        'number' => $clean_phone,
        'body' => $message,
        'message' => $message
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        echo json_encode(['success' => false, 'error' => 'HTTP request failed: ' . $err]);
        exit;
    }
    
    $res_data = json_decode($response, true);
    if ($http_code >= 400 || (isset($res_data['success']) && !$res_data['success'])) {
        $err_msg = $res_data['error'] ?? $res_data['message'] ?? 'Gateway returned error code ' . $http_code;
        echo json_encode([
            'success' => false, 
            'error' => 'Gateway error: ' . $err_msg . '. Please verify if your device is connected/scanned.'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '🚀 Test message sent successfully via session: ' . htmlspecialchars($sessionId) . '!'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
