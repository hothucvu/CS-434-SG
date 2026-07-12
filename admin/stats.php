<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';

$tieu_de = "Thống kê";

require_once __DIR__ . '/../includes/header.php';

// Tổng khách hàng
$users = $pdo->query("SELECT COUNT(*) FROM users WHERE vai_tro='user'")->fetchColumn();

// Tổng sân
$fields = $pdo->query("SELECT COUNT(*) FROM fields")->fetchColumn();

// Tổng lượt đặt
$bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

// Tổng doanh thu
$revenue = $pdo->query("
SELECT SUM((TIMESTAMPDIFF(HOUR, gio_bat_dau, gio_ket_thuc))*gia_thue)
FROM bookings
JOIN fields ON bookings.field_id = fields.id
WHERE bookings.trang_thai='da_duyet'
")->fetchColumn();

if ($revenue == null) $revenue = 0;
?>

<h2>Thống kê hệ thống</h2>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Tổng khách hàng</th>
        <td><?= $users ?></td>
    </tr>

    <tr>
        <th>Tổng sân</th>
        <td><?= $fields ?></td>
    </tr>

    <tr>
        <th>Tổng lượt đặt</th>
        <td><?= $bookings ?></td>
    </tr>

    <tr>
        <th>Doanh thu</th>
        <td><?= number_format($revenue,0,",",".") ?> VNĐ</td>
    </tr>
</table>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>