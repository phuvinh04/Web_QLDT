<?php
session_start();
require_once '../config/db.php';
require_once '../config/google_config.php';

if (isset($_GET['code'])) {
    try {
        // 1. Lấy Token từ Code
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            throw new Exception("Lỗi xác thực Google: " . $token['error']);
        }

        $client->setAccessToken($token['access_token']);

        // 2. Lấy thông tin người dùng từ Google
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $google_id = $google_account_info->id;
        $email = $google_account_info->email;
        $full_name = $google_account_info->name;
        $avatar = $google_account_info->picture;

        // 3. Kiểm tra xem Google ID đã tồn tại chưa
        $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->bind_param("s", $google_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // TRƯỜNG HỢP A: Đã tồn tại Google ID
            if ($user['status'] === 'active') {
                // Đăng nhập ngay
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['avatar'] = $user['avatar'];
                
                // Redirect based on role
                if ($user['role_id'] == 5) {
                    header("Location: ../shop/index.php");
                } else {
                    header("Location: ../index.php");
                }
                exit;
            } else {
                // Tài khoản chưa kích hoạt (chưa set password/username)
                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['temp_email'] = $user['email'];
                header("Location: setup_password.php");
                exit;
            }
        } else {
            // TRƯỜNG HỢP B: Chưa có Google ID, kiểm tra Email
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_by_email = $result->fetch_assoc();

            if ($user_by_email) {
                // Email đã tồn tại -> Cập nhật Google ID và Avatar
                $update = $conn->prepare("UPDATE users SET google_id = ?, avatar = ? WHERE id = ?");
                $update->bind_param("ssi", $google_id, $avatar, $user_by_email['id']);
                $update->execute();

                // Kiểm tra lại status
                if ($user_by_email['status'] === 'active') {
                    $_SESSION['user_id'] = $user_by_email['id'];
                    $_SESSION['username'] = $user_by_email['username'];
                    $_SESSION['full_name'] = $user_by_email['full_name'];
                    $_SESSION['role_id'] = $user_by_email['role_id'];
                    $_SESSION['avatar'] = $avatar; // Dùng avatar mới từ Google
                    
                    // Redirect based on role
                    if ($user_by_email['role_id'] == 5) {
                        header("Location: ../shop/index.php");
                    } else {
                        header("Location: ../index.php");
                    }
                    exit;
                } else {
                    $_SESSION['temp_user_id'] = $user_by_email['id'];
                    $_SESSION['temp_email'] = $user_by_email['email'];
                    header("Location: setup_password.php");
                    exit;
                }
            } else {
                // TRƯỜNG HỢP C: Người dùng hoàn toàn mới
                // Insert với status = 'inactive', username và password để NULL
                $role_id = 5; // Khách hàng (customer)
                $status = 'inactive';
                
                $insert = $conn->prepare("INSERT INTO users (full_name, email, google_id, avatar, role_id, status) VALUES (?, ?, ?, ?, ?, ?)");
                $insert->bind_param("ssssis", $full_name, $email, $google_id, $avatar, $role_id, $status);
                
                if ($insert->execute()) {
                    $_SESSION['temp_user_id'] = $conn->insert_id;
                    $_SESSION['temp_email'] = $email;
                    header("Location: setup_password.php");
                    exit;
                } else {
                    die("Lỗi hệ thống: Không thể tạo tài khoản. " . $conn->error);
                }
            }
        }

    } catch (Exception $e) {
        echo "Lỗi: " . $e->getMessage();
        echo "<br><a href='login.php'>Quay lại đăng nhập</a>";
    }
} else {
    header("Location: login.php");
    exit;
}
?>
