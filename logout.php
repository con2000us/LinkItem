<?php
session_start();

// 清除所有會話變數
$_SESSION = array();

// 如果使用了會話cookie，也清除它
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 清除記住我的cookie
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// 銷毀會話
session_destroy();

// 重定向到登入頁面
header('Location: login.php');
exit; 