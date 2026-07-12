<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';

// Nhận diện tên cột động trong database để không bị lỗi câu lệnh INSERT
$stmtCheck = $pdo->query("SELECT * FROM fields LIMIT 1");
$sampleRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

$colName  = isset($sampleRow['ten_san']) ? 'ten_san' : 'name';
$colType  = isset($sampleRow['loai_san']) ? 'loai_san' : 'type';
$colPrice = isset($sampleRow['gia_san']) ? 'gia_san' : (isset($sampleRow['gia']) ? 'gia' : 'price');
$colImg   = isset($sampleRow['hinhanh']) ? 'hinhanh' : (isset($sampleRow['anh_san']) ? 'anh_san' : 'image');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $type  = trim($_POST['type']);
    $price = floatval($_POST['price']);
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../assets/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
    }

    $sql = "INSERT INTO fields ($colName, $colType, $colPrice, $colImg) VALUES (:name, :type, :price, :image)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name'  => $name,
        'type'  => $type,
        'price' => $price,
        'image' => $image
    ]);

    $_SESSION['success'] = "Thêm sân mới thành công!";
    header("Location: admin.php");
    exit;
}

$tieu_de = 'Thêm Sân Mới';
require_once '../includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="m-0 fw-bold">➕ Thêm Sân Thể Thao Mới</h4>
        </div>
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên sân</label>
                    <input type="text" name="name" class="form-control" placeholder="Ví dụ: Sân Cỏ Nhân Tạo A2" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Loại sân</label>
                    <select name="type" class="form-select" required>
                        <option value="Bóng đá">Bóng đá</option>
                        <option value="Cầu lông">Cầu lông</option>
                        <option value="Tennis">Tennis</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Giá thuê (VNĐ/giờ)</label>
                    <input type="number" name="price" class="form-control" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Hình ảnh sân</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-success w-100 fw-bold">Lưu lại</button>
                    <a href="admin.php" class="btn btn-secondary w-100 fw-bold">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>