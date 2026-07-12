<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';

$stmtCheck = $pdo->query("SELECT * FROM fields LIMIT 1");
$sampleRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
$colId = isset($sampleRow['ma_san']) ? 'ma_san' : (isset($sampleRow['field_id']) ? 'field_id' : 'id');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM fields WHERE $colId = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Đã xóa sân thể thao thành công!";
}

header("Location: admin.php");
exit;