<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$tieu_de = 'Danh sách sân bóng (Dùng tạm để test)';
require_once __DIR__ . '/../includes/header.php';

// Lấy danh sách sân bạn vừa chèn thành công trong database
$stmt = $pdo->query("SELECT id, ten_san, loai_san, gia_thue, vi_tri FROM fields WHERE trang_thai = 'hoat_dong'");
$fields = $stmt->fetchAll();
?>

<div class="container mt-5">
    <div class="alert alert-warning text-center shadow-sm">
    </div>
    <h3 class="mb-4 text-center text-success fw-bold">DANH SÁCH SÂN BÓNG</h3>
    <div class="row">
        <?php foreach ($fields as $field): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-success h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-success fw-bold"><?= htmlspecialchars($field['ten_san']) ?></h5>
                        <p class="card-text mb-1"><strong>Loại:</strong> <?= htmlspecialchars($field['loai_san']) ?></p>
                        <p class="card-text mb-1 text-muted"><strong>Vị trí:</strong> <?= htmlspecialchars($field['vi_tri']) ?></p>
                        <p class="card-text text-danger fw-bold fs-5">Giá: <?= number_format($field['gia_thue'], 0, ',', '.') ?> VNĐ/h</p>
                        
                        <!-- Nút bấm truyền chuẩn ID sang trang đặt sân của bạn -->
                        <a href="../bookings/create.php?field_id=<?= $field['id'] ?>" class="btn btn-success w-100 mt-auto fw-bold shadow-sm">
                            ⚽ Bấm vào đây để ĐẶT SÂN NÀY
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>