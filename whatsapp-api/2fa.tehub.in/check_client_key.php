<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$stmt = $pdo->query("SELECT id, login_id, api_key FROM clients");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
