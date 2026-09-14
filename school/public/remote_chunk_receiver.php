<?php
header('Content-Type: application/json; charset=utf-8');

$token = 'secure_apk_upload_2026';
$inputToken = $_POST['token'] ?? '';

if ($inputToken !== $token) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit;
}

$fileName = $_POST['file_name'] ?? '';
$chunkIndex = isset($_POST['chunk_index']) ? (int)$_POST['chunk_index'] : 0;
$totalChunks = isset($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : 0;
$chunkDataBase64 = $_POST['chunk_data'] ?? '';

if (empty($fileName) || empty($chunkDataBase64) || $totalChunks <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

// Clean filename to prevent path traversal
$fileName = basename($fileName);
$tempFile = sys_get_temp_dir() . '/upload_' . md5($fileName);

$chunkData = base64_decode($chunkDataBase64);
if ($chunkData === false) {
    echo json_encode(['status' => 'error', 'message' => 'Base64 decode failed']);
    exit;
}

// Append chunk to temp file
$mode = ($chunkIndex === 0) ? 'wb' : 'ab';
$fp = fopen($tempFile, $mode);
if (!$fp) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to open temp file']);
    exit;
}
fwrite($fp, $chunkData);
fclose($fp);

if ($chunkIndex === $totalChunks - 1) {
    // Last chunk, move to destination
    $destDir = __DIR__ . '/downloads';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0777, true);
    }
    
    $destFile = $destDir . '/' . $fileName;
    if (rename($tempFile, $destFile)) {
        chmod($destFile, 0755);
        echo json_encode(['status' => 'success', 'message' => 'File fully assembled at ' . $fileName]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to move temp file to downloads']);
    }
} else {
    echo json_encode(['status' => 'success', 'message' => "Chunk $chunkIndex uploaded successfully"]);
}
?>
