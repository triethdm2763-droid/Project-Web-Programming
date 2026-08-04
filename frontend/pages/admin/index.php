<?php
require_once __DIR__ . '/../../components/session.php';

// Kiểm tra quyền Admin, nếu không phải Admin thì chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
<<<<<<< HEAD
    header("Location: " . app_url('/frontend/pages/auth/login.php'));
=======
    header("Location: /frontend/pages/auth/login.php");
>>>>>>> 798812e7ff3d82d10aedc22d1123ffffaa1407f2
    exit;
}

// Chuyển hướng ngay sang trang dashboard.php
header("Location: dashboard.php");
exit;
