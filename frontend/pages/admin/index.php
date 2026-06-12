<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền Admin, nếu không phải Admin thì chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project-Web-Programming/frontend/pages/auth/login.php");
    exit;
}

// Chuyển hướng ngay sang trang dashboard.php
header("Location: dashboard.php");
exit;
?>
