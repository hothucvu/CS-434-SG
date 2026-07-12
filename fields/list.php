<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Kết nối PDO theo cấu trúc nhóm

// 1. XỬ LÝ TÌM KIẾM
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

// Lấy danh sách sân
$query = "SELECT * FROM fields WHERE 1=1";
$params = [];

// Đoán tên cột động để thực hiện câu query tìm kiếm không bị lỗi
$stmtCheck = $pdo->query("SELECT * FROM fields LIMIT 1");
$sampleRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

$colName = isset($sampleRow['ten_san']) ? 'ten_san' : (isset($sampleRow['name']) ? 'name' : '');
$colType = isset($sampleRow['loai_san']) ? 'loai_san' : (isset($sampleRow['type']) ? 'type' : '');

if (!empty($search) && $colName) {
    $query .= " AND $colName LIKE :search";
    $params['search'] = "%$search%";
}
if (!empty($type) && $colType) {
    $query .= " AND $colType = :type";
    $params['type'] = $type;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tieu_de = 'Danh Sách Sân Thể Thao';
require_once '../includes/header.php';
?>

<!-- Khai báo Bootstrap CSS nếu header chưa có để giao diện đẹp hơn -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <div class="d-flex align-items-center mb-4">
        <h2 class="fw-bold text-dark m-0">🏟️ Danh Sách Sân Thể Thao</h2>
    </div>
    
    <!-- FORM TÌM KIẾM -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control form-control-lg" placeholder="Nhập tên sân cần tìm..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select form-control-lg">
                        <option value="">-- Chọn loại sân --</option>
                        <option value="Sân 5" <?= $type == 'Sân 5' ? 'selected' : '' ?>>Sân 5 người</option>
                        <option value="Sân 7" <?= $type == 'Sân 7' ? 'selected' : '' ?>>Sân 7 người</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-lg w-100 text-white fw-bold">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DANH SÁCH SÂN -->
    <div class="row">
        <?php if (empty($fields)): ?>
            <div class="col-12 text-center my-5">
                <p class="text-muted fs-5">Không tìm thấy sân nào phù hợp với yêu cầu của bạn.</p>
            </div>
        <?php else: ?>
            <?php foreach ($fields as $field): 
                // Cơ chế tự động ánh xạ tên cột động để tránh lỗi Undefined Array Key
                $id    = $field['id'] ?? $field['ma_san'] ?? $field['field_id'] ?? 0;
                $name  = $field['name'] ?? $field['ten_san'] ?? 'Chưa đặt tên sân';
                $type  = $field['type'] ?? $field['loai_san'] ?? 'Chưa phân loại';
                $price = $field['price'] ?? $field['gia'] ?? $field['gia_san'] ?? $field['price_per_hour'] ?? 0;
                $img   = $field['image'] ?? $field['hinhanh'] ?? $field['anh_san'] ?? '';

                // Chuyển chữ về viết thường để kiểm tra từ khóa cho chính xác
                $name_lower = mb_strtolower($name, 'UTF-8');
                $type_lower = mb_strtolower($type, 'UTF-8');

                // 🟢 ĐOẠN XỬ LÝ LINK ẢNH DO BẠN CUNG CẤP
                if (!empty($img) && file_exists("../assets/uploads/" . $img)) {
                    // Nếu có ảnh admin upload thật thì ưu tiên dùng trước
                    $image_src = "../assets/uploads/" . $img;
                } else {
                    // Nếu không có ảnh upload, tự động nhận diện theo tên/loại sân bóng
                    if (strpos($name_lower, 'cỏ') !== false || strpos($name_lower, 'bóng') !== false || strpos($name_lower, 'banh') !== false || strpos($type_lower, '5') !== false || strpos($type_lower, '7') !== false) {
                        $image_src = "https://gcs.tripi.vn/public-tripi/tripi-feed/img/482761Gnx/anh-mo-ta.png"; // Sân cỏ nhân tạo
                    } elseif (strpos($name_lower, 'lông') !== false || strpos($name_lower, 'badminton') !== false) {
                        $image_src = "https://thanhnhua.vn/media/data/tin-tuc/sancaulong1.jpg"; // Sân cầu lông
                    } elseif (strpos($name_lower, 'tennis') !== false || strpos($name_lower, 'quần vợt') !== false) {
                        $image_src = "https://vuatennis.com/wp-content/uploads/2018/03/San-tennis.jpg"; // Sân tennis
                    } else {
                        // Ảnh dự phòng cuối cùng nếu không khớp từ khóa nào ở trên
                        $image_src = "https://gcs.tripi.vn/public-tripi/tripi-feed/img/482761Gnx/anh-mo-ta.png"; 
                    }
                }
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 overflow-hidden style-card">
                        <!-- Thẻ ảnh sân -->
                        <div class="position-relative" style="height: 220px; overflow: hidden;">
                            <img src="<?= $image_src ?>" class="w-100 h-100 object-cover" alt="<?= htmlspecialchars($name) ?>" style="object-fit: cover;">
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($name) ?></h5>
                            <p class="card-text text-muted mb-1">Loại sân: <span class="badge bg-secondary text-white"><?= htmlspecialchars($type) ?></span></p>
                            <?php 
                        if ($price == 0) {
                        if (strpos($name_lower, 'cỏ') !== false || strpos($name_lower, 'bóng') !== false || strpos($name_lower, 'banh') !== false) {
                            $price = 300000; // Sân bóng đá cỏ nhân tạo: 300k/giờ
                        } elseif (strpos($name_lower, 'lông') !== false || strpos($name_lower, 'badminton') !== false) {
                            $price = 80000;  // Sân cầu lông: 80k/giờ
                        } elseif (strpos($name_lower, 'tennis') !== false || strpos($name_lower, 'quần vợt') !== false) {
                            $price = 150000; // Sân tennis: 150k/giờ
                        } else {
                         $price = 200000; // Giá mặc định nếu không khớp sân nào
                    }
                }
            ?>
                            <p class="card-text text-danger fw-bold fs-5 mb-3">Giá: <?= number_format($price) ?> VNĐ/giờ</p>
                            
                            <!-- Nút Đặt Sân liên kết sang Module C -->
                            <div class="mt-auto">
                                <a href="../bookings/create.php?field_id=<?= $id ?>" class="btn btn-success btn-lg w-100 text-white fw-bold">Đặt sân ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>