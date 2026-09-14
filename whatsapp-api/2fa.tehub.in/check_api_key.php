<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$stmt = $pdo->query("SELECT * FROM api_keys WHERE api_key = 'b0b306dc4bf090c19f85c584906a967c'");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
