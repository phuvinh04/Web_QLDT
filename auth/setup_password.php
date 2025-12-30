<?php
session_start();
require_once '../config/db.php';

// Kiểm tra xem có phải đang trong luồng Google Login không
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit;
}

$page_title = "Thiết lập tài khoản";
$base_url = "../";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['temp_user_id'];
    
    // Validate tên đăng nhập
    if (empty($username)) {
        $errors['username'] = "Vui lòng nhập tên đăng nhập.";
    } elseif (strlen($username) > 50) {
        $errors['username'] = "Tên đăng nhập không được vượt quá 50 ký tự.";
    }
    
    // Check username exist (trừ chính user này)
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check->bind_param("si", $username, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $errors['username'] = "Tên đăng nhập đã tồn tại.";
    }
    
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
    if (!preg_match($password_pattern, $password)) {
        $errors['password'] = "Mật khẩu quá yếu (Cần 8 ký tự, Hoa, thường, số, đặc biệt).";
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Mật khẩu xác nhận không khớp.";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = 'active';
        
        // Nếu username rỗng trong DB (do tạo từ Google), code này sẽ update nó
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $hashed_password, $status, $user_id);
        
        if ($stmt->execute()) {
            // Lấy thông tin user để set session chính thức
            $get_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $get_user->bind_param("i", $user_id);
            $get_user->execute();
            $user_info = $get_user->get_result()->fetch_assoc();
            
            $_SESSION['user_id'] = $user_info['id'];
            $_SESSION['username'] = $user_info['username'];
            $_SESSION['full_name'] = $user_info['full_name'];
            $_SESSION['role_id'] = $user_info['role_id'];
            $_SESSION['avatar'] = $user_info['avatar'];
            
            // Xóa session tạm
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_email']);
            
            // Redirect based on role
            if ($user_info['role_id'] == 5) {
                header("Location: ../shop/index.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $errors['common'] = "Lỗi cập nhật: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include '../components/auth_head.php'; ?>
</head>
<body class="auth-body">
  
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        
        <div class="auth-card">
          
          <div class="auth-header">
            <h3 class="fw-bold">Hoàn tất đăng ký</h3>
            <p class="text-muted small">Vui lòng thiết lập mật khẩu cho tài khoản Google: <br><strong><?php echo $_SESSION['temp_email']; ?></strong></p>
          </div>

          <?php if(isset($errors['common'])): ?>
            <div class="alert alert-danger"><?php echo $errors['common']; ?></div>
          <?php endif; ?>

          <form method="post" novalidate>
            
            <div class="form-group mb-3">
              <label>Tên đăng nhập mong muốn <span class="text-danger">*</span></label>
              <div class="input-wrapper">
                <input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" required placeholder="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" maxlength="50">
                <i class="bi bi-person input-icon left"></i>
              </div>
              <?php if(isset($errors['username'])): ?>
                <div class="invalid-feedback d-block"><?php echo $errors['username']; ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group mb-2">
              <label>Mật khẩu mới <span class="text-danger">*</span></label>
              <div class="input-wrapper">
                <input type="password" name="password" id="new_password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" required placeholder="VD: P@ssw0rd123" style="padding-right: 40px; background-image: none;">
                <i class="bi bi-lock input-icon left"></i>
                <i class="bi bi-eye-slash input-icon right" onclick="togglePass('new_password', this)" style="cursor: pointer;"></i>
              </div>
              <?php if(isset($errors['password'])): ?>
                <div class="invalid-feedback d-block"><?php echo $errors['password']; ?></div>
              <?php endif; ?>
              
              <!-- Password Rules List -->
              <ul class="list-unstyled mt-2 mb-0 small text-secondary">
                  <li id="rule-length"><i class="bi bi-x-circle"></i> Tối thiểu 8 ký tự</li>
                  <li id="rule-upper"><i class="bi bi-x-circle"></i> Chữ cái viết hoa (A-Z)</li>
                  <li id="rule-lower"><i class="bi bi-x-circle"></i> Chữ cái thường (a-z)</li>
                  <li id="rule-number"><i class="bi bi-x-circle"></i> Số (0-9)</li>
                  <li id="rule-special"><i class="bi bi-x-circle"></i> Ký tự đặc biệt (!@#$...)</li>
              </ul>
            </div>

            <div class="form-group mb-4">
              <label>Xác nhận mật khẩu <span class="text-danger">*</span></label>
              <div class="input-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" required placeholder="Nhập lại mật khẩu" style="padding-right: 40px; background-image: none;">
                <i class="bi bi-lock input-icon left"></i>
                <i class="bi bi-eye-slash input-icon right" onclick="togglePass('confirm_password', this)" style="cursor: pointer;"></i>
              </div>
              <?php if(isset($errors['confirm_password'])): ?>
                <div class="invalid-feedback d-block"><?php echo $errors['confirm_password']; ?></div>
              <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary auth-btn">Kích hoạt tài khoản</button>
          </form>

        </div>

      </div>
    </div>
  </div>

  <script>
    // Hàm ẩn hiện mật khẩu
    function togglePass(inputId, icon) {
        const input = document.getElementById(inputId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Xóa trạng thái lỗi khi người dùng nhập liệu
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const formGroup = this.closest('.form-group');
                const errorMsg = formGroup.querySelector('.invalid-feedback');
                if (errorMsg) errorMsg.style.display = 'none';
            });
        });

        // Kiểm tra độ mạnh mật khẩu Real-time
        const passwordInput = document.getElementById('new_password');
        const rules = {
            'rule-length': /.{8,}/,
            'rule-upper': /[A-Z]/,
            'rule-lower': /[a-z]/,
            'rule-number': /[0-9]/,
            'rule-special': /[\W_]/
        };

        passwordInput.addEventListener('input', function() {
            const val = this.value;
            for (const [id, regex] of Object.entries(rules)) {
                const element = document.getElementById(id);
                const icon = element.querySelector('i');
                if (regex.test(val)) {
                    element.classList.remove('text-danger');
                    element.classList.add('text-success');
                    icon.classList.remove('bi-x-circle');
                    icon.classList.add('bi-check-circle-fill');
                } else {
                    element.classList.remove('text-success');
                    // Chỉ hiện màu đỏ nếu đã nhập gì đó
                    if(val.length > 0) element.classList.add('text-danger');
                    else element.classList.remove('text-danger');
                    
                    icon.classList.remove('bi-check-circle-fill');
                    icon.classList.add('bi-x-circle');
                }
            }
        });

        // Kiểm tra khớp mật khẩu Real-time
        const confirmInput = document.getElementById('confirm_password');
        confirmInput.addEventListener('input', function() {
            if(this.value !== '' && this.value !== passwordInput.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
  </script>

</body>
</html>
