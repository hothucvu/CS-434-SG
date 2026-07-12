<?php
// =========================================================
// includes/header.php  —  PHẦN ĐẦU TRANG (dùng chung)
// Cách dùng trong mỗi trang:
//   $tieu_de = 'Tên trang';
//   require_once __DIR__ . '/../includes/header.php';
// =========================================================
$ten     = $_SESSION['user']['ho_ten'] ?? '';
$vai_tro = $_SESSION['user']['vai_tro'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tieu_de ?? 'Quản Lý Sân') ?> – 4 Anh Em Siêu Nhân</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

  <header class="bg-white shadow sticky top-0 z-10">
    <nav class="max-w-6xl mx-auto flex items-center justify-between px-4 py-3">
      <a href="<?= BASE_URL ?>/index.php" class="font-bold text-lg text-indigo-600">⚽ Quản Lý Sân</a>
      <div class="flex items-center gap-4 text-sm">
        <a href="<?= BASE_URL ?>/index.php"                 class="hover:text-indigo-600">Trang chủ</a>
        <a href="<?= BASE_URL ?>/fields/list.php"           class="hover:text-indigo-600">Tìm sân</a>
        <a href="<?= BASE_URL ?>/bookings/create.php"        class="hover:text-indigo-600">Đặt sân</a>
        <?php if ($vai_tro === 'admin'): ?>
          <a href="<?= BASE_URL ?>/fields/admin.php"         class="hover:text-indigo-600">Quản lý sân</a>
          <a href="<?= BASE_URL ?>/bookings/list.php"        class="hover:text-indigo-600">Lịch đặt</a>
          <a href="<?= BASE_URL ?>/admin/customers.php"      class="hover:text-indigo-600">Khách hàng</a>
        <?php endif; ?>
        <?php if ($ten): ?>
          <a href="<?= BASE_URL ?>/auth/profile.php"
   class="hover:text-indigo-600">
    Thông tin cá nhân
</a>
          <span class="text-slate-500">Xin chào, <?= htmlspecialchars($ten) ?></span>
          <a href="<?= BASE_URL ?>/auth/logout.php" class="text-red-500 hover:underline">Đăng xuất</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/auth/login.php" class="text-indigo-600 font-semibold">Đăng nhập</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <main class="max-w-6xl mx-auto w-full px-4 py-6 flex-1">
