<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Bật kiểm tra đăng nhập nếu hệ thống của bạn đã có trang auth
// require_once __DIR__ . '/../includes/auth_check.php';

$tieu_de = 'Lịch sử đặt sân';
require_once __DIR__ . '/../includes/header.php';

// Giả lập ID người dùng nếu chưa chạy chức năng đăng nhập
$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 1; 

/**
 * THAY ĐỔI QUAN TRỌNG:
 * - Sử dụng TIMEDIFF để tính khoảng thời gian giữa giờ kết thúc và giờ bắt đầu.
 * - TIME_TO_SEC đổi khoảng thời gian đó ra giây, chia cho 3600 để ra số giờ (ví dụ: 1.5 giờ).
 * - Nhân với f.gia_thue từ bảng fields để ra tổng tiền và đặt tên giả (AS) là tong_tien.
 */
$sql = "SELECT b.*, f.ten_san, f.loai_san,
        (TIME_TO_SEC(TIMEDIFF(b.gio_ket_thuc, b.gio_bat_dau)) / 3600) * f.gia_thue AS tong_tien
        FROM bookings b
        JOIN fields f ON b.field_id = f.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark text-uppercase">⚽ Lịch Sử Đặt Sân Của Bạn</h4>
        <a href="../fields/list.php" class="btn btn-sm btn-success fw-bold">+ Đặt sân mới</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Tên Sân</th>
                        <th>Ngày Đá</th>
                        <th>Khung Giờ</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) == 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Bạn chưa có lịch sử đặt sân nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td class="text-center fw-bold">#<?= $b['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($b['ten_san']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($b['loai_san']) ?></small>
                                </td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($b['ngay_dat'])) ?></td>
                                <td class="text-center fw-bold text-primary">
                                    <?= date('H:i', strtotime($b['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($b['gio_ket_thuc'])) ?>
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    <!-- Hiển thị tổng tiền đã được tính toán tự động từ SQL -->
                                    <?= number_format((float)($b['tong_tien'] ?? 0), 0, ',', '.') ?> đ
                                </td>
                                <td class="text-center">
                                    <!-- ĐÃ SỬA: Khớp với enum ('cho_duyet', 'da_duyet', 'da_huy') trong DB -->
                                    <?php if ($b['trang_thai'] == 'cho_duyet'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php elseif ($b['trang_thai'] == 'da_duyet'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đã hủy</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- ĐÃ SỬA: Cho phép sửa/xóa khi trạng thái là 'cho_duyet' -->
                                    <?php if ($b['trang_thai'] == 'cho_duyet'): ?>
                                        <!-- Nút Sửa -->
                                        <a href="update.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-warning fw-bold text-dark me-1">
                                            ✏️ Sửa
                                        </a>
                                        
                                        <!-- Nút Xóa -->
                                        <a href="delete.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger fw-bold" onclick="return confirm('Bạn có chắc chắn muốn XÓA hẳn đơn đặt sân này?')">
                                            🗑️ Xóa
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Không thể thao tác</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>