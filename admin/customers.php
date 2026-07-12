<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';

$tieu_de = "Quản lý khách hàng";

require_once __DIR__ . '/../includes/header.php';

// Lấy danh sách khách hàng
if (!empty($_GET['keyword'])) {

    $keyword = "%" . $_GET['keyword'] . "%";

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE ho_ten LIKE ?
           OR email LIKE ?
        ORDER BY id DESC
    ");

    $stmt->execute([$keyword, $keyword]);

} else {

    $stmt = $pdo->query("
        SELECT *
        FROM users
        ORDER BY id DESC
    ");

}

$users = $stmt->fetchAll();
?>

<h2>Quản lý khách hàng</h2>
<form method="GET" style="margin:15px 0;">
    <input
        type="text"
        name="keyword"
        placeholder="Nhập tên hoặc email..."
        value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>"
    >

    <button type="submit">Tìm kiếm</button>
</form>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <tr>
        <th>ID</th>
        <th>Họ tên</th>
        <th>Email</th>
        <th>Số điện thoại</th>
        <th>Vai trò</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
    </tr>

    <?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user['id']; ?></td>
        <td><?= htmlspecialchars($user['ho_ten']); ?></td>
        <td><?= htmlspecialchars($user['email']); ?></td>
        <td><?= htmlspecialchars($user['so_dien_thoai']); ?></td>
        <td><?= htmlspecialchars($user['vai_tro']); ?></td>
        <td><?= $user['created_at']; ?></td>
        <td>
    <?php if ($user['vai_tro'] != 'admin'): ?>
        <a href="customers.php?delete=<?= $user['id'] ?>"
           onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?')">
            Xóa
        </a>
    <?php else: ?>
        -
    <?php endif; ?>
</td>
    </tr>
    <?php endforeach; ?>
</table>

<?php
require_once __DIR__ . '/../includes/footer.php';
// Xóa khách hàng
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE id = ?
        AND vai_tro != 'admin'
    ");

    $stmt->execute([$id]);

    header("Location: customers.php");
    exit;
}
?>