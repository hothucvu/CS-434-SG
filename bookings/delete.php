<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 1;

if ($booking_id > 0) {
    // Chỉ cho phép xóa đơn khi trạng thái là 'cho_xac_nhan' để bảo vệ logic hệ thống
    $stmt_check = $pdo->prepare("SELECT trang_thai FROM bookings WHERE id = ? AND user_id = ?");
    $stmt_check->execute([$booking_id, $user_id]);
    $booking = $stmt_check->fetch();

    if ($booking && $booking['trang_thai'] == 'cho_xac_nhan') {
        // Thực thi lệnh xóa dữ liệu hoàn toàn khỏi bảng bookings
        $stmt_delete = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt_delete->execute([$booking_id]);
    }
}

// Xóa xong chuyển hướng ngay lập tức về trang lịch sử đặt sân
header("Location: list.php");
exit;