<?php
session_start();

// 檢查用戶是否已登入
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // 如果用戶未登入，重定向到登入頁面
    header('Location: login.php');
    exit;
}
?> 