<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['yt_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

require_once __DIR__ . '/db.php';
$youtuber_id = $_SESSION['yt_user_id'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_groups') {
    // Call WhatsApp Gateway `/groups` API
    $gateway_url = '';
    try {
        $stmt = $pdo->query("SELECT whatsapp_gateway_url FROM settings LIMIT 1");
        $gateway_url = $stmt->fetchColumn();
    } catch (PDOException $e) {}

    if (empty($gateway_url)) {
        echo json_encode(['success' => false, 'error' => 'WhatsApp gateway URL is not configured in settings.']);
        exit;
    }

    // Convert e.g., 'https://2fa.tehub.in/whatsapp/send' to 'https://2fa.tehub.in/whatsapp/groups?session=youtuber_<id>'
    $groups_url = str_replace('/send', '/groups?session=youtuber_' . $youtuber_id, $gateway_url);
    if (strpos($groups_url, '/groups') === false) {
        $groups_url = rtrim($gateway_url, '/') . '/groups?session=youtuber_' . $youtuber_id;
    }

    $ch = curl_init($groups_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($res) {
        $data = json_decode($res, true);
        if (isset($data['success']) && $data['success']) {
            echo json_encode(['success' => true, 'groups' => $data['groups']]);
        } else {
            echo json_encode(['success' => false, 'error' => $data['error'] ?? 'Failed to retrieve groups.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to connect to WhatsApp Gateway. Please check if gateway is online.']);
    }
    exit;
}

elseif ($action === 'connect_whatsapp') {
    $linked_number = trim($_POST['whatsapp_linked_number'] ?? '');
    if (empty($linked_number)) {
        echo json_encode(['success' => false, 'error' => 'Linked number is required.']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("UPDATE youtubers SET whatsapp_is_connected = 1, whatsapp_linked_number = ? WHERE id = ?");
        $stmt->execute([$linked_number, $youtuber_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

elseif ($action === 'disconnect_whatsapp') {
    try {
        // 1. Fetch settings to get gateway URL
        $gateway_stmt = $pdo->query("SELECT whatsapp_gateway_url FROM settings LIMIT 1");
        $gateway_url = $gateway_stmt->fetchColumn();
        
        if (!empty($gateway_url)) {
            $disconnect_url = str_replace('/send', '/disconnect?session=youtuber_' . $youtuber_id, $gateway_url);
            if (strpos($disconnect_url, '/disconnect') === false) {
                $disconnect_url = rtrim($gateway_url, '/') . '/disconnect?session=youtuber_' . $youtuber_id;
            }
            
            // Call gateway disconnect
            $ch = curl_init($disconnect_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
        }
        
        $stmt = $pdo->prepare("UPDATE youtubers SET whatsapp_is_connected = 0, whatsapp_linked_number = NULL WHERE id = ?");
        $stmt->execute([$youtuber_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

elseif ($action === 'add_channel') {
    $channel_id = trim($_POST['channel_id'] ?? '');
    $whatsapp_target_jid = trim($_POST['whatsapp_target_jid'] ?? '');
    $whatsapp_target_name = trim($_POST['whatsapp_target_name'] ?? '');
    $template_upload = trim($_POST['template_upload'] ?? '');
    $template_live = trim($_POST['template_live'] ?? '');

    if (empty($channel_id)) {
        echo json_encode(['success' => false, 'error' => 'YouTube Channel ID is required.']);
        exit;
    }

    try {
        // Fetch channel name from RSS feed as verification
        $feed_url = "https://www.youtube.com/feeds/videos.xml?channel_id=" . urlencode($channel_id);
        $feed_content = @file_get_contents($feed_url);
        $channel_name = 'YouTube Channel';
        if ($feed_content) {
            $xml = @simplexml_load_string($feed_content);
            if ($xml && isset($xml->title)) {
                $channel_name = (string)$xml->title;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid YouTube Channel ID (could not fetch RSS feed).']);
            exit;
        }

        // Verify if already linked
        $chk = $pdo->prepare("SELECT COUNT(*) FROM youtuber_channels WHERE youtuber_id = ? AND channel_id = ?");
        $chk->execute([$youtuber_id, $channel_id]);
        if ($chk->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'This YouTube Channel is already connected.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO youtuber_channels (youtuber_id, channel_id, channel_name, whatsapp_target_jid, whatsapp_target_name, template_upload, template_live) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $youtuber_id,
            $channel_id,
            $channel_name,
            $whatsapp_target_jid,
            $whatsapp_target_name,
            $template_upload,
            $template_live
        ]);

        echo json_encode(['success' => true, 'channel_name' => $channel_name]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

elseif ($action === 'update_channel') {
    $channel_link_id = intval($_POST['channel_link_id'] ?? 0);
    $whatsapp_target_jid = trim($_POST['whatsapp_target_jid'] ?? '');
    $whatsapp_target_name = trim($_POST['whatsapp_target_name'] ?? '');
    $template_upload = trim($_POST['template_upload'] ?? '');
    $template_live = trim($_POST['template_live'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE youtuber_channels SET whatsapp_target_jid = ?, whatsapp_target_name = ?, template_upload = ?, template_live = ? WHERE id = ? AND youtuber_id = ?");
        $stmt->execute([
            $whatsapp_target_jid,
            $whatsapp_target_name,
            $template_upload,
            $template_live,
            $channel_link_id,
            $youtuber_id
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

elseif ($action === 'delete_channel') {
    $channel_link_id = intval($_POST['channel_link_id'] ?? $_GET['channel_link_id'] ?? 0);

    try {
        $stmt = $pdo->prepare("DELETE FROM youtuber_channels WHERE id = ? AND youtuber_id = ?");
        $stmt->execute([$channel_link_id, $youtuber_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

elseif ($action === 'test_post') {
    $channel_link_id = intval($_GET['channel_link_id'] ?? $_POST['channel_link_id'] ?? 0);

    try {
        // Fetch current setting from youtuber_channels
        $stmt = $pdo->prepare("SELECT * FROM youtuber_channels WHERE id = ? AND youtuber_id = ? LIMIT 1");
        $stmt->execute([$channel_link_id, $youtuber_id]);
        $yt = $stmt->fetch();

        if (!$yt || empty($yt['channel_id']) || empty($yt['whatsapp_target_jid'])) {
            echo json_encode(['success' => false, 'error' => 'Please configure the Channel ID and Target Chat first.']);
            exit;
        }

        // Fetch latest video
        $feed_url = "https://www.youtube.com/feeds/videos.xml?channel_id=" . urlencode($yt['channel_id']);
        $feed_content = @file_get_contents($feed_url);
        if (!$feed_content) {
            echo json_encode(['success' => false, 'error' => 'Failed to fetch YouTube feed.']);
            exit;
        }

        $xml = @simplexml_load_string($feed_content);
        if (!$xml) {
            echo json_encode(['success' => false, 'error' => 'Failed to parse YouTube feed.']);
            exit;
        }

        $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
        $xml->registerXPathNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');
        $entries = $xml->xpath('//atom:entry');
        if (!$entries || count($entries) === 0) {
            echo json_encode(['success' => false, 'error' => 'No videos found in your YouTube Channel.']);
            exit;
        }

        $latest = $entries[0];
        $video_id_nodes = $latest->xpath('yt:videoId');
        $video_id = $video_id_nodes ? (string)$video_id_nodes[0] : '';
        $title = (string)$latest->title;
        $url = "https://youtu.be/" . $video_id;

        // Render template
        $template = $yt['template_upload'] ?: "🎥 *New Video Alert!*\n\n{title}\n\nWatch now: {url}";
        $message = str_replace(['{title}', '{url}'], [$title, $url], $template);

        // Call WhatsApp gateway
        $gateway_stmt = $pdo->query("SELECT whatsapp_gateway_url, whatsapp_gateway_token FROM settings LIMIT 1");
        $gateway = $gateway_stmt->fetch();
        
        if (!$gateway || empty($gateway['whatsapp_gateway_url'])) {
            echo json_encode(['success' => false, 'error' => 'WhatsApp gateway URL is not configured.']);
            exit;
        }

        $target_url = $gateway['whatsapp_gateway_url'];
        if (strpos($target_url, '?') !== false) {
            $target_url .= '&session=youtuber_' . $youtuber_id;
        } else {
            $target_url .= '?session=youtuber_' . $youtuber_id;
        }

        $jids = array_filter(array_map('trim', explode(',', $yt['whatsapp_target_jid'])));
        $sent_count = 0;
        $error_details = 'No valid targets';

        foreach ($jids as $jid) {
            $payload = json_encode([
                'to' => $jid,
                'message' => $message,
                'token' => $gateway['whatsapp_gateway_token'],
                'apikey' => $gateway['whatsapp_gateway_token']
            ]);

            $ch = curl_init($target_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $res = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $gateway_res = json_decode($res, true);
            if ($http_code === 200 && isset($gateway_res['success']) && $gateway_res['success']) {
                $sent_count++;
            } else {
                $error_details = $gateway_res['error'] ?? 'Gateway returned HTTP ' . $http_code;
            }
        }

        $status = ($sent_count > 0) ? 'sent' : 'failed';

        // Log in history
        $log_stmt = $pdo->prepare("INSERT INTO youtube_history (youtuber_id, video_id, title, type, status) VALUES (?, ?, ?, 'test_upload', ?)");
        $log_stmt->execute([$youtuber_id, $video_id, $title, $status]);

        if ($status === 'sent') {
            echo json_encode(['success' => true, 'message' => 'Test message sent successfully to WhatsApp!']);
        } else {
            $error_details = $gateway_res['error'] ?? 'Gateway returned HTTP ' . $http_code;
            echo json_encode(['success' => false, 'error' => 'Failed to send test message: ' . $error_details]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'System error: ' . $e->getMessage()]);
    }
    exit;
}

else {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}
