<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';

$stmtCheck = $pdo->query("SELECT * FROM fields LIMIT 1");
$sampleRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

$colId    = isset($sampleRow['ma_san']) ? 'ma_san' : (isset($sampleRow['field_id']) ? 'field_id' : 'id');
$colName  = isset($sampleRow['ten_san']) ? 'ten_san' : 'name';
$colType  = isset($sampleRow['loai_san']) ? 'loai_san' : 'type';
$colPrice = isset($sampleRow['gia_san']) ? 'gia_san' : (isset($sampleRow['gia']) ? 'gia' : 'price');
$colImg   = isset($sampleRow['hinhanh']) ? 'hinhanh' : (isset($sampleRow['anh_san']) ? 'anh_san' : 'image');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM fields WHERE $colId = ?");
$stmt->execute([$id]);
$field = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$field) {
    die("Không tìm thấy sân cần chỉnh sửa.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $type  = trim($_POST['type']);
    $price = floatval($_POST['price']);
    $image = $field[$colImg]; 

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../assets/uploads/";
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
    }

    $sql = "UPDATE fields SET $colName = :name, $colType = :type, $colPrice = :price, $colImg = :image WHERE $colId = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name'  => $name,
        'type'  => $type,
        'price' => $price,
        'image' => $image,
        'id'    => $id
    ]);

    $_SESSION['success'] = "Cập nhật thông tin sân thành công!";
    header("Location: admin.php");
    exit;
}

$tieu_de = 'Chỉnh Sửa Sân';
require_once '../includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow border-0">
        <div class="card-header bg-warning text-dark py-3">
            <h4 class="m-0 fw-bold">✏️ Chỉnh Sửa Thông Tin Sân</h4>
        </div>
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên sân</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($field[$colName] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Loại sân</label>
                    <select name="type" class="form-select" required>
                        <option value="Bóng đá" <?= ($field[$colType] ?? '') == 'Bóng đá' ? 'selected' : '' ?>>Bóng đá</option>
                        <option value="Cầu lông" <?= ($field[$colType] ?? '') == 'Cầu lông' ? 'selected' : '' ?>>Cầu lông</option>
                        <option value="Tennis" <?= ($field[$colType] ?? '') == 'Tennis' ? 'selected' : '' ?>>Tennis</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Giá thuê (VNĐ/giờ)</label>
                    <input type="number" name="price" class="form-control" value="<?= intval($field[$colPrice] ?? 0) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Thay hình ảnh mới (Nếu muốn)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Cập nhật</button>
                    <a href="admin.php" class="btn btn-secondary w-100 fw-bold">Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>