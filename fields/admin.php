<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';

// Lấy toàn bộ danh sách sân
$stmt = $pdo->query("SELECT * FROM fields ORDER BY id DESC");
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tieu_de = 'Quản lý sân (Admin)';
require_once '../includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">🛠️ Quản Lý Danh Sách Sân</h2>
        <a href="create.php" class="btn btn-primary btn-lg fw-bold">+ Thêm Sân Mới</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success shadow-sm">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']); 
            ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 140px;">Hình ảnh</th>
                        <th>Tên sân</th>
                        <th>Loại sân</th>
                        <th>Giá thuê</th>
                        <th style="width: 160px;" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fields)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Chưa có sân nào trong hệ thống.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fields as $field): 
                            $id    = $field['id'] ?? $field['ma_san'] ?? $field['field_id'] ?? 0;
                            $name  = $field['name'] ?? $field['ten_san'] ?? 'Chưa đặt tên';
                            $type  = $field['type'] ?? $field['loai_san'] ?? 'Chưa phân loại';
                            $price = $field['price'] ?? $field['gia'] ?? $field['gia_san'] ?? 0;
                            $img   = $field['image'] ?? $field['hinhanh'] ?? $field['anh_san'] ?? '';

                            $name_lower = mb_strtolower($name, 'UTF-8');
                            $type_lower = mb_strtolower($type, 'UTF-8');

                            // Xử lý ảnh thông minh theo link bạn gửi
                            if (!empty($img) && file_exists("../assets/uploads/" . $img)) {
                                $image_src = "../assets/uploads/" . $img;
                            } else {
                                if (strpos($name_lower, 'cỏ') !== false || strpos($name_lower, 'bóng') !== false || strpos($name_lower, 'banh') !== false || strpos($type_lower, '5') !== false || strpos($type_lower, '7') !== false) {
                                    $image_src = "https://gcs.tripi.vn/public-tripi/tripi-feed/img/482761Gnx/anh-mo-ta.png";
                                } elseif (strpos($name_lower, 'lông') !== false || strpos($name_lower, 'badminton') !== false) {
                                    $image_src = "https://thanhnhua.vn/media/data/tin-tuc/sancaulong1.jpg";
                                } elseif (strpos($name_lower, 'tennis') !== false || strpos($name_lower, 'quần vợt') !== false) {
                                    $image_src = "https://vuatennis.com/wp-content/uploads/2018/03/San-tennis.jpg";
                                } else {
                                    $image_src = "https://gcs.tripi.vn/public-tripi/tripi-feed/img/482761Gnx/anh-mo-ta.png"; 
                                }
                            }

                            // Xử lý 3 mức giá tiền khác nhau
                            if ($price == 0) {
                                if (strpos($name_lower, 'cỏ') !== false || strpos($name_lower, 'bóng') !== false || strpos($name_lower, 'banh') !== false) {
                                    $price = 300000;
                                } elseif (strpos($name_lower, 'lông') !== false || strpos($name_lower, 'badminton') !== false) {
                                    $price = 80000;
                                } elseif (strpos($name_lower, 'tennis') !== false || strpos($name_lower, 'quần vợt') !== false) {
                                    $price = 150000;
                                } else {
                                    $price = 200000;
                                }
                            }
                        ?>
                        <tr>
                            <td class="fw-bold text-secondary">#<?= $id ?></td>
                            <td>
                                <img src="<?= $image_src ?>" class="rounded shadow-sm" width="100" height="65" style="object-fit: cover;">
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($name) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($type) ?></span></td>
                            <td class="text-danger fw-bold"><?= number_format($price) ?> VNĐ/giờ</td>
                            <td class="text-center">
                                <a href="update.php?id=<?= $id ?>" class="btn btn-warning btn-sm fw-bold">Sửa</a>
                                <a href="delete.php?id=<?= $id ?>" class="btn btn-danger btn-sm fw-bold" onclick="return confirm('Bạn có chắc chắn muốn xóa sân này?')">Xóa</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>