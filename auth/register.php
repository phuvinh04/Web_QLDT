<?php 
session_start();
require_once '../config/db.php'; // Adjusted path
require_once '../config/google_config.php'; // Include Google Config

$page_title = "Đăng ký tài khoản";
$base_url = "../"; // Adjusted path
$errors = []; 
$system_error = ""; 
$success = "";

$full_name = $username = $email = $phone = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $role_id = 3; 
    $status = 'active';
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

    // Validate họ và tên
    if (empty($full_name)) {
        $errors['full_name'] = "Vui lòng nhập họ và tên.";
    } elseif (strlen($full_name) > 100) {
        $errors['full_name'] = "Họ và tên không được vượt quá 100 ký tự.";
    }
    
    // Validate tên đăng nhập
    if (empty($username)) {
        $errors['username'] = "Vui lòng nhập tên đăng nhập.";
    } elseif (strlen($username) > 50) {
        $errors['username'] = "Tên đăng nhập không được vượt quá 50 ký tự.";
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = "Vui lòng nhập email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email không hợp lệ.";
    }
    
    if (!preg_match($password_pattern, $password)) {
        $errors['password'] = "Mật khẩu quá yếu (Cần 8 ký tự, Hoa, thường, số, đặc biệt).";
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Mật khẩu xác nhận không khớp.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                if ($row['username'] === $username) $errors['username'] = "Tên đăng nhập này đã được sử dụng.";
                if ($row['email'] === $email) $errors['email'] = "Email này đã được đăng ký.";
            }
        } else {
            $avatar = 'default-avatar.png'; 
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                // Validate file type - chỉ cho phép ảnh
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['avatar']['type'];
                $file_extension = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($file_type, $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
                    $errors['avatar'] = "Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP).";
                } elseif ($_FILES['avatar']['size'] > 5 * 1024 * 1024) { // 5MB
                    $errors['avatar'] = "Kích thước file không được vượt quá 5MB.";
                } else {
                    // Adjusted upload path relative to auth/ directory
                    $target_dir = "../assets/uploads/avatars/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $new_filename = $username . "_" . time() . "." . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                        $avatar = $new_filename;
                    } else {
                        $errors['avatar'] = "Không thể tải file lên. Vui lòng thử lại.";
                    }
                }
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (role_id, username, password, full_name, email, phone, avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $role_id, $username, $hashed_password, $full_name, $email, $phone, $avatar, $status);
            
            // Chỉ thực hiện insert nếu không có lỗi avatar
            if (empty($errors)) {
                if ($stmt->execute()) {
                    $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.";
                } else {
                    $system_error = "Lỗi hệ thống: " . $conn->error;
                }
            }
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
      <div class="col-md-6 col-lg-5">
        
        <div class="auth-card">
          
          <div class="auth-header">
            <h1 class="fw-bold text-dark fs-3">Đăng ký tài khoản</h1>
            <p class="text-muted small">Tham gia hệ thống quản lý PhoneStore</p>
          </div>

          <?php if($system_error): ?>
            <div class="alert alert-danger"><?php echo $system_error; ?></div>
          <?php endif; ?>

          <?php if($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?> 
                <a href="login.php" class="text-decoration-underline ms-2">Đăng nhập ngay</a>
            </div>
          <?php endif; ?>

          <form action="register.php" method="post" enctype="multipart/form-data" novalidate>
            
            <div class="form-group">
              <label>Họ và tên <span class="text-danger">*</span></label>
              <input type="text" name="full_name" class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($full_name); ?>" required placeholder="Nguyễn Văn A" maxlength="100">
              <?php if(isset($errors['full_name'])): ?>
                <div class="invalid-feedback"><?php echo $errors['full_name']; ?></div>
              <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>" required placeholder="username" maxlength="50">
                        <?php if(isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" placeholder="09xxxx">
                    </div>
                </div>
            </div>

            <div class="form-group">
              <label>Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required placeholder="email@example.com">
              <?php if(isset($errors['email'])): ?>
                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
              <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Mật khẩu <span class="text-danger">*</span></label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="reg_password" class="form-control ps-3 <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" required style="padding-right: 40px; background-image: none;" placeholder="VD: P@ssw0rd123">
                    <i class="bi bi-eye-slash input-icon right" onclick="togglePass('reg_password', this)"></i>
                </div>
                <?php if(isset($errors['password'])): ?>
                    <div class="invalid-feedback" style="display:block;"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
                <ul class="list-unstyled mt-2 mb-0 small text-secondary">
                    <li id="rule-length"><i class="bi bi-x-circle"></i> Tối thiểu 8 ký tự</li>
                    <li id="rule-upper"><i class="bi bi-x-circle"></i> Chữ cái viết hoa (A-Z)</li>
                    <li id="rule-lower"><i class="bi bi-x-circle"></i> Chữ cái thường (a-z)</li>
                    <li id="rule-number"><i class="bi bi-x-circle"></i> Số (0-9)</li>
                    <li id="rule-special"><i class="bi bi-x-circle"></i> Ký tự đặc biệt (!@#$...)</li>
                </ul>
            </div>

            <div class="form-group">
                <label>Nhập lại mật khẩu <span class="text-danger">*</span></label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" id="reg_confirm" class="form-control ps-3 <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" required style="padding-right: 40px; background-image: none;" placeholder="Nhập lại">
                    <i class="bi bi-eye-slash input-icon right" onclick="togglePass('reg_confirm', this)"></i>
                </div>
                <?php if(isset($errors['confirm_password'])): ?>
                    <div class="invalid-feedback" style="display:block;"><?php echo $errors['confirm_password']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Ảnh đại diện</label>
                <input type="file" name="avatar" class="form-control ps-3 <?php echo isset($errors['avatar']) ? 'is-invalid' : ''; ?>" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                <?php if(isset($errors['avatar'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['avatar']; ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary auth-btn">
              <i class="bi bi-person-plus"></i> Đăng ký
            </button>

            <!-- Google Register Button -->
            <div class="text-center mt-3 mb-3">
                <span class="text-muted small">HOẶC</span>
            </div>
            
            <a href="<?php echo $login_url; ?>" class="btn btn-light w-100 d-flex align-items-center justify-content-center gap-2 border">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" style="width: 20px; height: 20px;">
                <span>Đăng ký bằng Google</span>
            </a>

          </form>

          <div class="auth-link-box">
            <p class="text-muted small m-0">
              Đã có tài khoản? <a href="login.php" class="text-primary fw-bold text-decoration-none">Đăng nhập</a>
            </p>
          </div>

        </div>

        <?php include '../components/auth_footer.php'; ?>

      </div>
    </div>
  </div>

  <script>
    function togglePass(inputId, icon) {
        const input = document.getElementById(inputId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const formGroup = this.closest('.form-group');
                const errorMsg = formGroup.querySelector('.invalid-feedback');
                if (errorMsg) {
                    errorMsg.style.display = 'none';
                }
            });
        });

        const passwordInput = document.getElementById('reg_password');
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
                    if(val.length > 0) {
                        element.classList.add('text-danger');
                    } else {
                        element.classList.remove('text-danger');
                    }
                    icon.classList.remove('bi-check-circle-fill');
                    icon.classList.add('bi-x-circle');
                }
            }
        });

        const confirmInput = document.getElementById('reg_confirm');
        confirmInput.addEventListener('input', function() {
            if(this.value !== '' && this.value !== passwordInput.value) {
                this.classList.add('is-invalid');
            }
        });
    });
  </script>
</body>
</html>
