<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$tieu_de = 'Chỉnh sửa lịch đặt sân';
require_once __DIR__ . '/../includes/header.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 1;

// Lấy thông tin đơn đặt sân hiện tại kèm thông tin giá sân
$sql = "SELECT b.*, f.ten_san, f.gia_thue 
        FROM bookings b 
        JOIN fields f ON b.field_id = f.id 
        WHERE b.id = ? AND b.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

// Kiểm tra điều kiện chỉnh sửa
if (!$booking) {
    echo "<div class='container mt-4'><div class='alert alert-danger text-center'>Đơn đặt sân không tồn tại.</div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($booking['trang_thai'] != 'cho_xac_nhan') {
    echo "<div class='container mt-4'><div class='alert alert-warning text-center'>Đơn hàng đã được xử lý, không thể chỉnh sửa thông tin.</div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$loi = "";
$thongbao = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ngay_dat     = $_POST['ngay_dat'];
    $gio_bat_dau = $_POST['gio_bat_dau'];
    $gio_ket_thuc= $_POST['gio_ket_thuc'];
    $ghi_chu     = trim($_POST['ghi_chu']);

    if (empty($ngay_dat) || empty($gio_bat_dau) || empty($gio_ket_thuc)) {
        $loi = "Vui lòng điền đầy đủ ngày giờ.";
    } elseif (strtotime($gio_bat_dau) >= strtotime($gio_ket_thuc)) {
        $loi = "Giờ kết thúc phải lớn hơn giờ bắt đầu.";
    } else {
        // Tính toán lại tổng tiền mới
        $thoi_gian_thue = (strtotime($gio_ket_thuc) - strtotime($gio_bat_dau)) / 3600;
        $tong_tien = $thoi_gian_thue * $booking['gia_thue'];

        // Kiểm tra trùng lịch với các đơn khác (trừ chính đơn này ra)
        $sql_check = "SELECT COUNT(*) FROM bookings 
                      WHERE field_id = ? AND ngay_dat = ? AND id != ?
                      AND ((gio_bat_dau < ? AND gio_ket_thuc > ?) 
                      OR (gio_bat_dau >= ? AND gio_bat_dau < ?))
                      AND trang_thai != 'da_huy'";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$booking['field_id'], $ngay_dat, $booking_id, $gio_ket_thuc, $gio_bat_dau, $gio_bat_dau, $gio_ket_thuc]);
        $trung_lich = $stmt_check->fetchColumn();

        if ($trung_lich > 0) {
            $loi = "Khung giờ mới này đã có người đặt trước. Vui lòng chọn giờ khác.";
        } else {
            // Tiến hành cập nhật
            $sql_update = "UPDATE bookings SET ngay_dat = ?, gio_bat_dau = ?, gio_ket_thuc = ?, tong_tien = ?, ghi_chu = ? WHERE id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$ngay_dat, $gio_bat_dau, $gio_ket_thuc, $tong_tien, $ghi_chu, $booking_id]);
            
            $thongbao = "Cập nhật lịch đặt sân thành công!";
            
            // Cập nhật lại biến hiển thị giao diện
            $booking['ngay_dat'] = $ngay_dat;
            $booking['gio_bat_dau'] = $gio_bat_dau;
            $booking['gio_ket_thuc'] = $gio_ket_thuc;
            $booking['ghi_chu'] = $ghi_chu;
        }
    }
}
?>

<div class="container mt-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark py-3">
            <h5 class="mb-0 fw-bold text-center">✏️ CHỈNH SỬA ĐƠN ĐẶT SÂN #<?= $booking['id'] ?></h5>
        </div>
        <div class="card-body p-4">
            <?php if ($loi): ?><div class="alert alert-danger">⚠️ <?= $loi ?></div><?php endif; ?>
            <?php if ($thongbao): ?><div class="alert alert-success">✅ <?= $thongbao ?></div><?php endif; ?>

            <div class="alert alert-secondary py-2">
                <strong>Sân đang chọn:</strong> <?= htmlspecialchars($booking['ten_san']) ?>
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Chọn ngày đá:</label>
                    <input type="date" name="ngay_dat" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($booking['ngay_dat']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold">Giờ bắt đầu:</label>
                        <input type="time" name="gio_bat_dau" class="form-control" value="<?= htmlspecialchars($booking['gio_bat_dau']) ?>" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold">Giờ kết thúc:</label>
                        <input type="time" name="gio_ket_thuc" class="form-control" value="<?= htmlspecialchars($booking['gio_ket_thuc']) ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ghi chú thay đổi:</label>
                    <textarea name="ghi_chu" class="form-control" rows="3"><?= htmlspecialchars($booking['ghi_chu']) ?></textarea>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="list.php" class="btn btn-secondary">← Quay lại danh sách</a>
                    <button type="submit" class="btn btn-warning fw-bold text-dark px-4">Lưu cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>