<?php
// 1. Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Nạp file config (Đi lùi ra 1 cấp thư mục để vào config/config.php)
require_once __DIR__ . '/../config/config.php';

// 3. Xóa sạch dữ liệu đăng nhập của user
$_SESSION['user'] = null;
unset($_SESSION['user']);
session_destroy();

// 4. Chuyển hướng an toàn về trang chủ sử dụng BASE_URL đã được nạp
header("Location: " . BASE_URL . "/index.php");
exit();