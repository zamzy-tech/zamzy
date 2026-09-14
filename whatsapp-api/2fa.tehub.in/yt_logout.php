<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['yt_user_id']);
unset($_SESSION['yt_username']);

header('Location: yt_login.php');
exit;
