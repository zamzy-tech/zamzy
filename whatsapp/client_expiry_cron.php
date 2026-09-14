<?php
// Included dynamically in yt_cron.php or run directly
require_once __DIR__ . '/db.php';

try {
    echo "=== Running Developer Subscription Expiry Checker ===\n";

    // 1. Fetch settings for gateway and templates
    $settings_stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $settings_stmt->fetch();
    
    if (!$settings || empty($settings['whatsapp_gateway_url'])) {
        echo "ERROR: Admin WhatsApp gateway is not configured.\n";
        return;
    }
    
    $gateway_url = $settings['whatsapp_gateway_url'];
    $gateway_token = $settings['whatsapp_gateway_token'] ?? '';
    $template = $settings['template_expiry_alert'] ?? '';
    
    if (empty($template)) {
        $template = "Dear {client_name}, your WhatsApp API Gateway subscription is going to expire on {due_date}. Please renew your plan to avoid service interruption: {web_link}";
    }

    // Determine link portal URL dynamically
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? '2fa.tehub.in';
    $portal_url = $protocol . $host . '/api_link.php';

    // 2. Fetch active API Clients
    $stmt = $pdo->query("SELECT * FROM api_keys WHERE status = 'active' AND expiry_date IS NOT NULL AND expiry_date != ''");
    $clients = $stmt->fetchAll();

    foreach ($clients as $client) {
        $expiry_str = $client['expiry_date'];
        $expiry_time = strtotime($expiry_str);
        $now = time();

        // Check if expired
        $is_expired = ($expiry_time < $now);

        if ($is_expired) {
            // Expired accounts: Force credits to 0 and disconnect gateway
            if (intval($client['credits']) !== 0 || intval($client['whatsapp_is_connected']) !== 0) {
                echo "Client '{$client['client_name']}' is EXPIRED. Disconnecting sessions & resetting credits...\n";
                
                // Disconnect gateway session
                $scanners_count = intval($client['allowed_scanners'] ?? 1);
                for ($i = 1; $i <= $scanners_count; $i++) {
                    $sessionSuffix = $i > 1 ? ('_' . $i) : '';
                    $sessionId = $client['login_id'] . $sessionSuffix;
                    $disconnect_url = str_replace('/send', '/disconnect?session=' . urlencode($sessionId), $gateway_url);
                    if (strpos($disconnect_url, '/disconnect') === false) {
                        $disconnect_url = rtrim($gateway_url, '/') . '/disconnect?session=' . urlencode($sessionId);
                    }
                    
                    $ch = curl_init($disconnect_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_exec($ch);
                    curl_close($ch);
                }

                // Reset in DB
                $upd = $pdo->prepare("UPDATE api_keys SET credits = 0, whatsapp_is_connected = 0, whatsapp_linked_number = NULL WHERE id = ?");
                $upd->execute([$client['id']]);

                $dev_upd = $pdo->prepare("UPDATE client_devices SET whatsapp_is_connected = 0, whatsapp_linked_number = NULL WHERE client_id = ?");
                $dev_upd->execute([$client['id']]);
            }
            continue;
        }

        // Active accounts: check if within 48 hours (172800 seconds) of expiry
        $time_to_expiry = $expiry_time - $now;
        if ($time_to_expiry <= 172800) {
            // Check alert conditions:
            // 1. Sent count < 4
            // 2. Either last_expiry_alert_at is null OR 12 hours (43200 seconds) have passed since last send
            $sent_count = intval($client['expiry_alerts_sent'] ?? 0);
            $last_sent_str = $client['last_expiry_alert_at'] ?? '';
            $last_sent_time = !empty($last_sent_str) ? strtotime($last_sent_str) : 0;

            if ($sent_count < 4 && ($last_sent_time === 0 || ($now - $last_sent_time >= 43200))) {
                echo "Sending expiry alert #{$sent_count} to client: '{$client['client_name']}'...\n";

                // Format message
                $due_formatted = date('d-M-Y H:i', $expiry_time);
                $message_text = str_replace(
                    ['{client_name}', '{due_date}', '{web_link}'],
                    [$client['client_name'], $due_formatted, $portal_url],
                    $template
                );

                // Send via Admin gateway
                $clean_phone = preg_replace('/[^0-9]/', '', $client['client_phone']);
                if (strlen($clean_phone) >= 10) {
                    $target_url = $gateway_url;
                    if (strpos($target_url, '?') !== false) {
                        $target_url .= '&session=default';
                    } else {
                        $target_url .= '?session=default';
                    }

                    $payload = [
                        'to' => $clean_phone,
                        'phone' => $clean_phone,
                        'number' => $clean_phone,
                        'body' => $message_text,
                        'message' => $message_text
                    ];

                    $ch = curl_init($target_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ]);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $res = curl_exec($ch);
                    curl_close($ch);
                }

                // Update database
                $upd_stmt = $pdo->prepare("UPDATE api_keys SET expiry_alerts_sent = expiry_alerts_sent + 1, last_expiry_alert_at = CURRENT_TIMESTAMP WHERE id = ?");
                $upd_stmt->execute([$client['id']]);
            }
        }
    }

} catch (PDOException $e) {
    echo "DATABASE ERROR in Expiry Checker: " . $e->getMessage() . "\n";
}
