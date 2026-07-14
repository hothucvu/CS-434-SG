<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$loi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $mat_khau = $_POST['mat_khau']      ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mat_khau, $user['mat_khau'])) {
        $_SESSION['user'] = $user;                 // lưu cả phiên làm việc
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    } else {
        $loi = 'Email hoặc mật khẩu không đúng.';
    }
}

$tieu_de = 'Đăng nhập';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="max-w-md mx-auto bg-white rounded-xl shadow p-8 mt-6">
  <h2 class="text-2xl font-bold mb-6 text-center">Đăng nhập</h2>

  <?php if ($loi): ?>
    <div class="mb-4 p-3 bg-red-50 text-red-600 rounded"><?= htmlspecialchars($loi) ?></div>
  <?php endif; ?>

  <!-- Quan trọng: action rỗng + method POST (Hướng A) -->
  <form action="" method="POST" class="space-y-4">
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input type="email" name="email" required
             class="w-full border rounded-lg px-3 py-2" placeholder="admin@quanlysan.vn">
    </div>
    <div>
      <label class="block text-sm mb-1">Mật khẩu</label>
      <input type="password" name="mat_khau" required
             class="w-full border rounded-lg px-3 py-2" placeholder="••••••">
    </div>
    <button class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">Đăng nhập</button>
  </form>
  <p class="text-sm text-slate-500 mt-4 text-center">
    <a href="<?= BASE_URL ?>/auth/quenmk.php" class="text-indigo-600">Quên mật khẩu</a>
  </p>

  <p class="text-sm text-slate-500 mt-4 text-center">
    Chưa có tài khoản? <a href="<?= BASE_URL ?>/auth/dangky.php" class="text-indigo-600">Đăng ký</a>
  </p>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
