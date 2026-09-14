<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/db.php';

try {
    echo "=== Running YouTube Auto-Poster Cron ===\n";

    // 1. Fetch active settings for WhatsApp gateway
    $gateway_stmt = $pdo->query("SELECT whatsapp_gateway_url, whatsapp_gateway_token FROM settings LIMIT 1");
    $gateway = $gateway_stmt->fetch();
    
    if (!$gateway || empty($gateway['whatsapp_gateway_url'])) {
        echo "ERROR: WhatsApp gateway is not configured.\n";
        exit;
    }
    
    $gateway_url = $gateway['whatsapp_gateway_url'];
    $gateway_token = $gateway['whatsapp_gateway_token'];

    // 2. Fetch active YouTubers channels
    $stmt = $pdo->query("SELECT yc.*, y.username FROM youtuber_channels yc JOIN youtubers y ON yc.youtuber_id = y.id WHERE yc.is_active = 1 AND yc.channel_id IS NOT NULL AND yc.whatsapp_target_jid IS NOT NULL");
    $channels = $stmt->fetchAll();

    if (empty($channels)) {
        echo "No active YouTube channels configured for auto-posting.\n";
        exit;
    }

    foreach ($channels as $yt) {
        $channel_id = $yt['channel_id'];
        $youtuber_id = $yt['youtuber_id'];
        $channel_link_id = $yt['id'];
        echo "Processing YouTuber: @{$yt['username']} (Channel: {$yt['channel_name']})\n";

        // Fetch RSS Feed
        $feed_url = "https://www.youtube.com/feeds/videos.xml?channel_id=" . urlencode($channel_id);
        
        // Use stream context with a short timeout to prevent cron hangs
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ]
        ]);
        
        $feed_content = @file_get_contents($feed_url, false, $context);
        if (!$feed_content) {
            echo "  -> Failed to fetch RSS feed for channel: $channel_id\n";
            continue;
        }

        $xml = @simplexml_load_string($feed_content);
        if (!$xml) {
            echo "  -> Failed to parse XML feed for channel: $channel_id\n";
            continue;
        }

        $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
        $xml->registerXPathNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');
        $entries = $xml->xpath('//atom:entry');
        if (!$entries || count($entries) === 0) {
            echo "  -> No videos found in feed.\n";
            continue;
        }

        // Get latest video entry
        $latest = $entries[0];
        $video_id_nodes = $latest->xpath('yt:videoId');
        $video_id = $video_id_nodes ? (string)$video_id_nodes[0] : '';
        $title = (string)$latest->title;
        $url = "https://youtu.be/" . $video_id;

        echo "  -> Latest video in feed: $title ($video_id)\n";

        // If last_video_id is empty, this is the first run.
        // We save the latest video ID to prevent spamming old videos on initial setup.
        if (empty($yt['last_video_id'])) {
            $upd = $pdo->prepare("UPDATE youtuber_channels SET last_video_id = ? WHERE id = ?");
            $upd->execute([$video_id, $channel_link_id]);
            echo "  -> Initialized last_video_id to: $video_id (no notification sent)\n";
            continue;
        }

        // Check if there is a new video
        if ($video_id !== $yt['last_video_id']) {
            echo "  -> NEW VIDEO DETECTED! Preparing alert...\n";

            // Determine if it is a live stream based on title keywords
            $is_live = (stripos($title, 'live') !== false || stripos($title, 'stream') !== false);
            $type = $is_live ? 'live' : 'upload';
            
            $template = $is_live ? ($yt['template_live'] ?: $yt['template_upload']) : $yt['template_upload'];
            if (empty($template)) {
                $template = "🎥 *New Video Alert!*\n\n{title}\n\nWatch now: {url}";
            }

            $message = str_replace(['{title}', '{url}'], [$title, $url], $template);

            $target_url = $gateway_url;
            if (strpos($target_url, '?') !== false) {
                $target_url .= '&session=youtuber_' . $youtuber_id;
            } else {
                $target_url .= '?session=youtuber_' . $youtuber_id;
            }

            $jids = array_filter(array_map('trim', explode(',', $yt['whatsapp_target_jid'])));
            $sent_count = 0;

            foreach ($jids as $jid) {
                $payload = json_encode([
                    'to' => $jid,
                    'message' => $message,
                    'token' => $gateway_token,
                    'apikey' => $gateway_token
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
                }
            }

            $status = ($sent_count > 0) ? 'sent' : 'failed';

            echo "  -> Dispatch Status: $status (Sent to $sent_count of " . count($jids) . " targets)\n";

            // Update YouTuber last_video_id so we do not post this video again
            $upd = $pdo->prepare("UPDATE youtuber_channels SET last_video_id = ? WHERE id = ?");
            $upd->execute([$video_id, $channel_link_id]);

            // Log history
            $log_stmt = $pdo->prepare("INSERT INTO youtube_history (youtuber_id, video_id, title, type, status) VALUES (?, ?, ?, ?, ?)");
            $log_stmt->execute([$youtuber_id, $video_id, $title, $type, $status]);
        } else {
            echo "  -> Already up to date (no new videos).\n";
        }
    }

    echo "=== Cron Completed Successfully ===\n";
} catch (Exception $e) {
    echo "CRON ERROR: " . $e->getMessage() . "\n";
}


// Trigger Developer Subscription Expiry Checker
include_once __DIR__ . '/client_expiry_cron.php';
