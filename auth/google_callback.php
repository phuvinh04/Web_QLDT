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
                // Chỉ cập nhật avatar từ Google nếu user chưa có avatar local (vẫn dùng avatar Google hoặc default)
                $should_update_avatar = empty($user['avatar']) || 
                                       $user['avatar'] === 'default-avatar.png' || 
                                       filter_var($user['avatar'], FILTER_VALIDATE_URL);
                
                if ($should_update_avatar) {
                    $update_avatar = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $update_avatar->bind_param("si", $avatar, $user['id']);
                    $update_avatar->execute();
                    $user_avatar = $avatar;
                } else {
                    // Giữ avatar local hiện tại
                    $user_avatar = $user['avatar'];
                }
                
                // Tự động tạo customer nếu là khách hàng và chưa có
                if ($user['role_id'] == 5) {
                    $check_customer = $conn->prepare("SELECT id FROM customers WHERE email = ?");
                    $check_customer->bind_param("s", $user['email']);
                    $check_customer->execute();
                    
                    if ($check_customer->get_result()->num_rows == 0) {
                        $create_customer = $conn->prepare("INSERT INTO customers (name, phone, email, status) VALUES (?, ?, ?, 'active')");
                        $create_customer->bind_param("sss", $user['full_name'], $user['phone'], $user['email']);
                        
                        if (!$create_customer->execute()) {
                            error_log("Không thể tạo customer cho user_id: " . $user['id'] . " - " . $conn->error);
                        }
                    }
                }
                
                // Đăng nhập ngay
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['avatar'] = $user_avatar; // Dùng avatar đã xác định
                
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
                // Email đã tồn tại -> Chỉ cập nhật Google ID, giữ nguyên avatar nếu đã có avatar local
                $should_update_avatar = empty($user_by_email['avatar']) || 
                                       $user_by_email['avatar'] === 'default-avatar.png' || 
                                       filter_var($user_by_email['avatar'], FILTER_VALIDATE_URL);
                
                if ($should_update_avatar) {
                    $update = $conn->prepare("UPDATE users SET google_id = ?, avatar = ? WHERE id = ?");
                    $update->bind_param("ssi", $google_id, $avatar, $user_by_email['id']);
                    $user_avatar = $avatar;
                } else {
                    $update = $conn->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                    $update->bind_param("si", $google_id, $user_by_email['id']);
                    $user_avatar = $user_by_email['avatar'];
                }
                $update->execute();

                // Kiểm tra lại status
                if ($user_by_email['status'] === 'active') {
                    // Tự động tạo customer nếu là khách hàng và chưa có
                    if ($user_by_email['role_id'] == 5) {
                        $check_customer = $conn->prepare("SELECT id FROM customers WHERE email = ?");
                        $check_customer->bind_param("s", $user_by_email['email']);
                        $check_customer->execute();
                        
                        if ($check_customer->get_result()->num_rows == 0) {
                            $create_customer = $conn->prepare("INSERT INTO customers (name, phone, email, status) VALUES (?, ?, ?, 'active')");
                            $create_customer->bind_param("sss", $user_by_email['full_name'], $user_by_email['phone'], $user_by_email['email']);
                            
                            if (!$create_customer->execute()) {
                                error_log("Không thể tạo customer cho user_id: " . $user_by_email['id'] . " - " . $conn->error);
                            }
                        }
                    }
                    
                    $_SESSION['user_id'] = $user_by_email['id'];
                    $_SESSION['username'] = $user_by_email['username'];
                    $_SESSION['full_name'] = $user_by_email['full_name'];
                    $_SESSION['role_id'] = $user_by_email['role_id'];
                    $_SESSION['avatar'] = $user_avatar; // Dùng avatar đã xác định
                    
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
