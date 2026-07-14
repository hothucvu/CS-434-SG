<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Bạn nên bật check quyền Admin ở đây nếu có
// require_once __DIR__ . '/../includes/admin_check.php';

// Kiểm tra xem có đủ tham số truyền lên hay không
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = intval($_GET['id']);
    
    // Xác định trạng thái mới dựa vào hành động
    $new_status = '';
    if ($action === 'duyet') {
        $new_status = 'da_duyet';
    } elseif ($action === 'huy') {
        $new_status = 'da_huy';
    }
    
    if (!empty($new_status)) {
        try {
            // Cập nhật trạng thái trong database
            $sql = "UPDATE bookings SET trang_thai = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_status, $booking_id]);
            
            $_SESSION['msg_success'] = "Cập nhật trạng thái đơn đặt sân #" . $booking_id . " thành công!";
        } catch (PDOException $e) {
            $_SESSION['msg_error'] = "Có lỗi xảy ra: " . $e->getMessage();
        }
    } else {
        $_SESSION['msg_error'] = "Hành động xử lý không hợp lệ.";
    }
} else {
    $_SESSION['msg_error'] = "Thiếu thông tin yêu cầu xử lý.";
}

// Chuyển hướng quay trở lại trang quản lý chính
header("Location: manage.php");
exit();