<?php
// =========================================================
// auth/forgot_password.php — TRANG YÊU CẦU QUÊN MẬT KHẨU
// =========================================================
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php'; // Nạp cấu hình BASE_URL

$loi = '';
$thanh_cong = '';
$buoc = 1; // Bước 1: Nhập email, Bước 2: Nhập mật khẩu mới

// Xử lý khi người dùng gửi Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // XỬ LÝ BƯỚC 1: Kiểm tra email
    if (isset($_POST['action_gui_email'])) {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $loi = 'Vui lòng nhập địa chỉ email.';
        } else {
            // Kiểm tra email trong DB
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Email hợp lệ, lưu tạm email vào session và chuyển sang Bước 2
                $_SESSION['reset_email'] = $email;
                $buoc = 2;
            } else {
                $loi = 'Email này không tồn tại trong hệ thống của chúng tôi.';
            }
        }
    }

    // XỬ LÝ BƯỚC 2: Cập nhật mật khẩu mới
    if (isset($_POST['action_dat_lai_mk'])) {
        $mat_khau_moi = $_POST['mat_khau_moi'] ?? '';
        $nhap_lai_mk  = $_POST['nhap_lai_mk'] ?? '';
        $email_reset  = $_SESSION['reset_email'] ?? '';

        if (empty($email_reset)) {
            $loi = 'Có lỗi xảy ra, vui lòng thực hiện lại từ đầu.';
            $buoc = 1;
        } elseif (empty($mat_khau_moi) || empty($nhap_lai_mk)) {
            $loi = 'Vui lòng nhập đầy đủ mật khẩu mới.';
            $buoc = 2;
        } elseif (strlen($mat_khau_moi) < 6) {
            $loi = 'Mật khẩu phải có ít nhất 6 ký tự.';
            $buoc = 2;
        } elseif ($mat_khau_moi !== $nhap_lai_mk) {
            $loi = 'Mật khẩu nhập lại không trùng khớp.';
            $buoc = 2;
        } else {
            // Mã hóa mật khẩu mới
            $mat_khau_ma_hoa = password_hash($mat_khau_moi, PASSWORD_DEFAULT);

            // Cập nhật vào DB
            $stmt = $pdo->prepare("UPDATE users SET mat_khau = ? WHERE email = ?");
            if ($stmt->execute([$mat_khau_ma_hoa, $email_reset])) {
                $thanh_cong = 'Cập nhật mật khẩu thành công! Bạn có thể đăng nhập bằng mật khẩu mới.';
                unset($_SESSION['reset_email']); // Xóa session tạm
                $buoc = 3; // Chuyển sang bước hoàn thành để hiển thị nút Đăng nhập
            } else {
                $loi = 'Có lỗi xảy ra khi cập nhật mật khẩu, vui lòng thử lại.';
                $buoc = 2;
            }
        }
    }
}

$tieu_de = 'Quên mật khẩu';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white rounded-xl shadow p-8 mt-6">
  <h2 class="text-2xl font-bold mb-6 text-center">Quên mật khẩu</h2>

  <?php if ($loi): ?>
    <div class="mb-4 p-3 bg-red-50 text-red-600 rounded text-sm"><?= htmlspecialchars($loi) ?></div>
  <?php endif; ?>

  <?php if ($thanh_cong): ?>
    <div class="mb-4 p-3 bg-green-50 text-green-600 rounded text-sm font-medium">
      <?= htmlspecialchars($thanh_cong) ?>
    </div>
  <?php endif; ?>


  <?php if ($buoc === 1): ?>
    <p class="text-sm text-slate-500 mb-6 text-center">
      Nhập địa chỉ email của tài khoản đã đăng ký. Hệ thống sẽ xác minh và cho phép bạn thiết lập mật khẩu mới ngay lập tức.
    </p>

    <form action="" method="POST" class="space-y-4">
      <div>
        <label class="block text-sm mb-1 font-medium">Email của bạn</label>
        <input type="email" name="email" required
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" 
               placeholder="admin@quanlysan.vn"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <button type="submit" name="action_gui_email" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 font-semibold transition">
          Xác minh Email
      </button>
    </form>


  <?php elseif ($buoc === 2): ?>
    <p class="text-sm text-green-600 mb-6 text-center font-medium bg-green-50 p-2 rounded">
      ✓ Xác minh thành công cho: <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong>
    </p>

    <form action="" method="POST" class="space-y-4">
      <div>
        <label class="block text-sm mb-1 font-medium">Mật khẩu mới (Tối thiểu 6 ký tự)</label>
        <input type="password" name="mat_khau_moi" required minlength="6"
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" 
               placeholder="••••••">
      </div>

      <div>
        <label class="block text-sm mb-1 font-medium font-medium">Nhập lại mật khẩu mới</label>
        <input type="password" name="nhap_lai_mk" required minlength="6"
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" 
               placeholder="••••••">
      </div>

      <button type="submit" name="action_dat_lai_mk" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 font-semibold transition">
          Đặt lại mật khẩu
      </button>
    </form>


  <?php elseif ($buoc === 3): ?>
    <div class="text-center mt-4">
      <a href="<?= BASE_URL ?>/auth/login.php" class="inline-block w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 font-semibold transition">
          Đăng nhập ngay
      </a>
    </div>
  <?php endif; ?>


  <?php if ($buoc !== 3): ?>
    <p class="text-sm text-slate-500 mt-6 text-center">
      Quay lại trang <a href="<?= BASE_URL ?>/auth/login.php" class="text-indigo-600 font-medium hover:underline">Đăng nhập</a>
    </p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>