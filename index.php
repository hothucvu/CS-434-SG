<?php
// Trang chủ (ai cũng xem được)
session_start();
require_once __DIR__ . '/config/db.php';

// Đếm nhanh vài số liệu để trang chủ trực quan
$tong_san  = $pdo->query("SELECT COUNT(*) FROM fields WHERE trang_thai='hoat_dong'")->fetchColumn();
$tong_dat  = $pdo->query("SELECT COUNT(*) FROM bookings WHERE trang_thai<>'da_huy'")->fetchColumn();

$tieu_de = 'Trang chủ';
require_once __DIR__ . '/includes/header.php';
?>
<div class="text-center py-10">
  <h1 class="text-3xl font-bold text-indigo-600">Hệ thống Quản Lý Sân</h1>
  <p class="mt-2 text-slate-500">Đặt sân thể thao nhanh chóng – Nhóm 4 Anh Em Siêu Nhân</p>

  <div class="flex justify-center gap-6 mt-8">
    <div class="bg-white rounded-xl shadow p-6 w-40">
      <div class="text-3xl font-bold"><?= $tong_san ?></div>
      <div class="text-sm text-slate-500 mt-1">Sân đang hoạt động</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 w-40">
      <div class="text-3xl font-bold"><?= $tong_dat ?></div>
      <div class="text-sm text-slate-500 mt-1">Lượt đặt sân</div>
    </div>
  </div>

  <div class="mt-8 flex justify-center gap-3">
    <a href="<?= BASE_URL ?>/fields/list.php"    class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Tìm sân ngay</a>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
