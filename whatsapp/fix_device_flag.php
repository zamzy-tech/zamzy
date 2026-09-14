<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM api_keys WHERE api_key = 'b0b306dc4bf090c19f85c584906a967c'");
    $keyRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($keyRow) {
        $cid = $keyRow['id'];
        $pdo->exec("UPDATE client_devices SET whatsapp_is_connected = 1 WHERE client_id = $cid");
        $pdo->exec("INSERT INTO client_devices (client_id, slot_number, whatsapp_is_connected, api_key) VALUES ($cid, 1, 1, 'b0b306dc4bf090c19f85c584906a967c') ON DUPLICATE KEY UPDATE whatsapp_is_connected = 1");
        echo json_encode(['success' => true, 'client_id' => $cid]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Key not found in api_keys']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
