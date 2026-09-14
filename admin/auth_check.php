<?php
if (!ob_get_level()) {
    ob_start();
}
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../db.php';

function checkAdminAuth() {
    if (!isset($_SESSION['zamzy_admin_logged']) || $_SESSION['zamzy_admin_logged'] !== true) {
        $loginUrl = 'login.php';
        if (!headers_sent()) {
            header('Location: ' . $loginUrl);
        }
        echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($loginUrl) . '"><script>window.location.href="' . addslashes($loginUrl) . '";</script></head><body>Redirecting to <a href="' . htmlspecialchars($loginUrl) . '">Login</a>...</body></html>';
        exit;
    }
}
?>
