<?php
session_start();
// 1. Nhúng file kết nối CSDL và kiểm tra đăng nhập
require_once __DIR__ . '/../config/db.php';

// Giả sử bạn có file kiểm tra đăng nhập để lấy thông tin khách hàng, nếu chưa có hãy bật lên
// require_once __DIR__ . '/../includes/auth_check.php';

$tieu_de = 'Đặt sân bóng';
require_once __DIR__ . '/../includes/header.php';

// 2. Xử lý ID sân bóng (Mẹo tránh lỗi: Nếu URL không có field_id, tự động lấy sân số 1 để test)
$field_id = isset($_GET['field_id']) ? (int)$_GET['field_id'] : 1;

// 3. Lấy thông tin sân bóng từ database để hiển thị lên form
$stmt_field = $pdo->prepare("SELECT * FROM fields WHERE id = ?");
$stmt_field->execute([$field_id]);
$field = $stmt_field->fetch();

// Nếu trong database hoàn toàn trống rỗng không có sân nào
if (!$field) {
    echo "<div class='container mt-5'><div class='alert alert-danger text-center'>Lỗi: Không tìm thấy thông tin sân bóng nào trong hệ thống. Vui lòng chạy lại file SQL chèn sân mẫu.</div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// 4. Khai báo biến thông báo lỗi / thành công
$loi = "";
$thongbao = "";

// 5. Xử lý khi người dùng nhấn nút "Xác nhận đặt sân" (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ngay_dat     = $_POST['ngay_dat'];
    $gio_bat_dau = $_POST['gio_bat_dau'];
    $gio_ket_thuc= $_POST['gio_ket_thuc'];
    $ghi_chu     = trim($_POST['ghi_chu']);
    
    // Giả lập ID người dùng nếu chưa làm chức năng Đăng nhập (Thay bằng ID user có trong bảng users của bạn)
    $user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 1; 

    // Kiểm tra tính hợp lệ cơ bản
    if (empty($ngay_dat) || empty($gio_bat_dau) || empty($gio_ket_thuc)) {
        $loi = "Vui lòng nhập đầy đủ ngày và khung giờ muốn đặt.";
    } elseif (strtotime($gio_bat_dau) >= strtotime($gio_ket_thuc)) {
        $loi = "Giờ kết thúc phải lớn hơn giờ bắt đầu.";
    } else {
        // Kiểm tra xem khung giờ này trên sân này đã có ai đặt chưa
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                                     WHERE field_id = ? AND ngay_dat = ? 
                                     AND ((gio_bat_dau < ? AND gio_ket_thuc > ?) 
                                     OR (gio_bat_dau >= ? AND gio_bat_dau < ?))
                                     AND trang_thai != 'da_huy'");
        $stmt_check->execute([$field_id, $ngay_dat, $gio_ket_thuc, $gio_bat_dau, $gio_bat_dau, $gio_ket_thuc]);
        $trung_lich = $stmt_check->fetchColumn();

        if ($trung_lich > 0) {
            $loi = "Khung giờ này đã có người đặt trước rồi. Vui lòng chọn giờ khác hoặc sân khác nhé!";
        } else {
            // Chèn dữ liệu đặt sân mới vào bảng bookings (ĐÃ LOẠI BỎ CỘT TONG_TIEN KHỎI CÂU LỆNH SQL)
            $sql_insert = "INSERT INTO bookings (user_id, field_id, ngay_dat, gio_bat_dau, gio_ket_thuc, ghi_chu, trang_thai, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, 'cho_duyet', NOW())";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$user_id, $field_id, $ngay_dat, $gio_bat_dau, $gio_ket_thuc, $ghi_chu]);

            $thongbao = "🎉 Đặt sân bóng thành công! Vui lòng chờ Admin xác nhận lịch.";
        }
    }
}
?>

<div class="container mt-4" style="max-width: 800px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white text-center py-3">
            <h4 class="mb-0 fw-bold">FORM ĐĂNG KÝ ĐẶT SÂN BÓNG</h4>
        </div>
        <div class="card-body p-4">
            
            <!-- Hiển thị thông báo trạng thái -->
            <?php if (!empty($loi)): ?>
                <div class="alert alert-danger shadow-sm">⚠️ <?= $loi ?></div>
            <?php endif; ?>
            <?php if (!empty($thongbao)): ?>
                <div class="alert alert-success shadow-sm">✅ <?= $thongbao ?></div>
            <?php endif; ?>

            <!-- Tóm tắt thông tin sân đang chọn -->
            <div class="bg-light p-3 rounded mb-4 border-start border-success border-4">
                <h5 class="text-success fw-bold mb-1"><?= htmlspecialchars($field['ten_san']) ?></h5>
                <p class="mb-1 text-muted"><strong>Loại sân:</strong> <?= htmlspecialchars($field['loai_san']) ?> | <strong>Vị trí:</strong> <?= htmlspecialchars($field['vi_tri']) ?></p>
                <p class="mb-0 text-danger fw-bold">Giá thuê: <?= number_format($field['gia_thue'], 0, ',', '.') ?> VNĐ / Giờ</p>
            </div>

            <!-- Form nhập thông tin lịch đặt -->
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-bold">1. Chọn ngày đá bóng:</label>
                    <input type="date" name="ngay_dat" class="form-control form-control-lg" min="<?= date('Y-m-d') ?>" value="<?= isset($_POST['ngay_dat']) ? htmlspecialchars($_POST['ngay_dat']) : date('Y-m-d') ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">2. Giờ bắt đầu:</label>
                        <input type="time" name="gio_bat_dau" class="form-control form-control-lg" value="<?= isset($_POST['gio_bat_dau']) ? htmlspecialchars($_POST['gio_bat_dau']) : '17:00' ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">3. Giờ kết thúc (Trả sân):</label>
                        <input type="time" name="gio_ket_thuc" class="form-control form-control-lg" value="<?= isset($_POST['gio_ket_thuc']) ? htmlspecialchars($_POST['gio_ket_thuc']) : '18:30' ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">4. Ghi chú thêm (Nếu có):</label>
                    <textarea name="ghi_chu" class="form-control" rows="3" placeholder="Ví dụ: Mượn thêm 2 bộ áo lưới, lấy thêm 1 bình nước nước uống..."><?= isset($_POST['ghi_chu']) ? htmlspecialchars($_POST['ghi_chu']) : '' ?></textarea>
                </div>

                <div class="row mt-4 pt-2">
                    <div class="col-6">
                        <a href="../fields/list.php" class="btn btn-outline-secondary w-100 btn-lg">← Chọn sân khác</a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-success w-100 btn-lg fw-bold shadow-sm">XÁC NHẬN ĐẶT SÂN ⚽</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>