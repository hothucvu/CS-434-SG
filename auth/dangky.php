<?php
// =========================================================
// auth/register.php — TRANG ĐĂNG KÝ TÀI KHOẢN
// =========================================================
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php'; // Đảm bảo nạp cấu hình để có BASE_URL

$loi = '';
$thanh_cong = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten   = trim($_POST['ho_ten']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $mat_khau = $_POST['mat_khau']      ?? '';

    // 1. Kiểm tra dữ liệu đầu vào cơ bản
    if (empty($ho_ten) || empty($email) || empty($mat_khau)) {
        $loi = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (strlen($mat_khau) < 6) {
        $loi = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        // 2. Kiểm tra xem Email đã tồn tại trong hệ thống chưa
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $loi = 'Email này đã được sử dụng, vui lòng chọn email khác.';
        } else {
            // 3. Mã hóa mật khẩu an toàn
            $mat_khau_ma_hoa = password_hash($mat_khau, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (ho_ten, email, mat_khau, vai_tro) VALUES (?, ?, ?, 'user')");
            
            if ($stmt->execute([$ho_ten, $email, $mat_khau_ma_hoa])) {
                $thanh_cong = 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay.';
                // Reset lại form dữ liệu sau khi thành công
                $ho_ten = $email = '';
            } else {
                $loi = 'Có lỗi xảy ra trong quá trình đăng ký, vui lòng thử lại.';
            }
        }
    }
}

$tieu_de = 'Đăng ký';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white rounded-xl shadow p-8 mt-6">
  <h2 class="text-2xl font-bold mb-6 text-center">Đăng ký tài khoản</h2>

  <?php if ($loi): ?>
    <div class="mb-4 p-3 bg-red-50 text-red-600 rounded text-sm"><?= htmlspecialchars($loi) ?></div>
  <?php endif; ?>

  <?php if ($thanh_cong): ?>
    <div class="mb-4 p-3 bg-green-50 text-green-600 rounded text-sm flex flex-col gap-2">
        <span><?= htmlspecialchars($thanh_cong) ?></span>
        <a href="<?= BASE_URL ?>/auth/login.php" class="text-indigo-600 font-bold underline">👉 Đến trang Đăng nhập ngay</a>
    </div>
  <?php endif; ?>

  <form action="" method="POST" class="space-y-4">
    <div>
      <label class="block text-sm mb-1">Họ và tên</label>
      <input type="text" name="ho_ten" required
             value="<?= htmlspecialchars($ho_ten ?? '') ?>"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" 
             placeholder="Nguyễn Văn A">
    </div>

    <div>
      <label class="block text-sm mb-1">Email</label>
      <input type="email" name="email" required
             value="<?= htmlspecialchars($email ?? '') ?>"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" 
             placeholder="nguyenvana@gmail.com">
    </div>

    <div>
      <label class="block text-sm mb-1">Mật khẩu (Tối thiểu 6 ký tự)</label>
      <input type="password" name="mat_khau" required minlength="6"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" 
             placeholder="••••••">
    </div>

    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 font-semibold transition">
        Đăng ký
    </button>
  </form>

  <p class="text-sm text-slate-500 mt-4 text-center">
    Đã có tài khoản? <a href="<?= BASE_URL ?>/auth/login.php" class="text-indigo-600 font-medium hover:underline">Đăng nhập</a>
  </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>