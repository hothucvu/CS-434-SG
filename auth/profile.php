<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = $_SESSION['user'];

$thongbao = "";

// Cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $ho_ten = trim($_POST['ho_ten']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);

    $stmt = $pdo->prepare("
        UPDATE users
        SET ho_ten = ?, so_dien_thoai = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $ho_ten,
        $so_dien_thoai,
        $user['id']
    ]);

    // Cập nhật lại session
    $_SESSION['user']['ho_ten'] = $ho_ten;
    $_SESSION['user']['so_dien_thoai'] = $so_dien_thoai;

    $user = $_SESSION['user'];

    $thongbao = "Cập nhật thành công!";
}

$tieu_de = "Thông tin cá nhân";

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-xl mx-auto bg-white rounded-xl shadow p-8 mt-6">

    <h2 class="text-2xl font-bold mb-6 text-center">
        Thông tin cá nhân
    </h2>

    <?php if($thongbao): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            <?= $thongbao ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-4">
            <label>Họ tên</label>
            <input
                type="text"
                name="ho_ten"
                value="<?= htmlspecialchars($user['ho_ten']) ?>"
                class="w-full border rounded-lg px-3 py-2"
                required>
        </div>

        <div class="mb-4">
            <label>Email</label>
            <input
                type="email"
                value="<?= htmlspecialchars($user['email']) ?>"
                class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                readonly>
        </div>

        <div class="mb-4">
            <label>Số điện thoại</label>
            <input
                type="text"
                name="so_dien_thoai"
                value="<?= htmlspecialchars($user['so_dien_thoai']) ?>"
                class="w-full border rounded-lg px-3 py-2">
        </div>

        <div class="mb-4">
            <label>Vai trò</label>
            <input
                type="text"
                value="<?= htmlspecialchars($user['vai_tro']) ?>"
                class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                readonly>
        </div>

        <button
            class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">
            Cập nhật
        </button>

    </form>

</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>