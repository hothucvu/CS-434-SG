<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';



$tieu_de = 'Quản lý lịch đặt sân';
require_once __DIR__ . '/../includes/header.php';

// Câu SQL lấy toàn bộ đơn đặt sân của TẤT CẢ mọi người, kèm thông tin sân và thông tin người dùng
// Đồng thời tự tính toán động cột "tong_tien" dựa trên số giờ chơi thực tế * gia_thue
// CÂU SQL ĐÃ SỬA (Bỏ u.username)
$sql = "SELECT b.*, f.ten_san, f.loai_san, u.ho_ten,
        (TIME_TO_SEC(TIMEDIFF(b.gio_ket_thuc, b.gio_bat_dau)) / 3600) * f.gia_thue AS tong_tien
        FROM bookings b
        JOIN fields f ON b.field_id = f.id
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$bookings = $stmt->fetchAll();
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark text-uppercase">📋 Hệ Thống Quản Lý Đặt Sân</h3>
        <span class="badge bg-primary fs-6 py-2 px-3">Tổng số đơn: <?= count($bookings) ?></span>
    </div>

    <!-- Khu vực hiển thị thông báo kết quả từ file xử lý duyệt/hủy -->
    <?php if (isset($_SESSION['msg_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['msg_success']; unset($_SESSION['msg_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['msg_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['msg_error']; unset($_SESSION['msg_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 80px;">Mã Đơn</th>
                            <th>Khách Hàng</th>
                            <th>Sân Bóng</th>
                            <th>Ngày Đá</th>
                            <th>Khung Giờ</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Ghi Chú</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bookings) == 0): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">Hiện chưa có yêu cầu đặt sân nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <!-- Mã Đơn -->
                                    <td class="text-center fw-bold text-secondary">#<?= $b['id'] ?></td>
                                    
                                    <!-- Khách Hàng -->
                                    <td>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($b['fullname'] ?? 'Khách vãng lai') ?></span>
                                        <br><small class="text-muted">ID: <?= htmlspecialchars($b['user_id']) ?></small>
                                    </td>
                                    
                                    <!-- Sân bóng -->
                                    <td>
                                        <span class="fw-bold text-success"><?= htmlspecialchars($b['ten_san']) ?></span>
                                        <br><small class="text-muted"><?= htmlspecialchars($b['loai_san']) ?></small>
                                    </td>
                                    
                                    <!-- Ngày đặt -->
                                    <td class="text-center fw-bold"><?= date('d/m/Y', strtotime($b['ngay_dat'])) ?></td>
                                    
                                    <!-- Khung Giờ -->
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border border-primary fw-bold px-2 py-1.5">
                                            <?= date('H:i', strtotime($b['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($b['gio_ket_thuc'])) ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Tổng Tiền -->
                                    <td class="text-end text-danger fw-bold">
                                        <?= number_format((float)($b['tong_tien'] ?? 0), 0, ',', '.') ?> đ
                                    </td>
                                    
                                    <!-- Trạng Thái -->
                                    <td class="text-center">
                                        <?php if ($b['trang_thai'] == 'cho_duyet'): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1.5">Chờ duyệt</span>
                                        <?php elseif ($b['trang_thai'] == 'da_duyet'): ?>
                                            <span class="badge bg-success px-2 py-1.5">Đã duyệt</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary px-2 py-1.5">Đã hủy</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Ghi chú -->
                                    <td>
                                        <small class="text-muted text-wrap d-inline-block" style="max-width: 150px;">
                                            <?= htmlspecialchars($b['ghi_chu'] ?? '') ?>
                                        </small>
                                    </td>
                                    
                                    <!-- Hành Động Admin -->
                                    <td class="text-center">
                                        <?php if ($b['trang_thai'] == 'cho_duyet'): ?>
                                            <!-- Nút Duyệt Đơn: Gửi action phê duyệt -->
                                            <a href="process_booking.php?action=duyet&id=<?= $b['id'] ?>" 
                                               class="btn btn-sm btn-success fw-bold me-1"
                                               onclick="return confirm('Xác nhận duyệt đơn đặt sân này?')">
                                                ✅ Duyệt
                                            </a>
                                            
                                            <!-- Nút Hủy Đơn: Gửi action hủy đơn -->
                                            <a href="process_booking.php?action=huy&id=<?= $b['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger fw-bold"
                                               onclick="return confirm('Bạn có chắc chắn muốn HỦY đơn đặt sân này?')">
                                                ❌ Hủy
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small italic">Đã xử lý</span>
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
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>