<?php
/**
 * Script đồng bộ dữ liệu: Tạo customers từ users có role_id = 5
 * Chạy 1 lần để đồng bộ dữ liệu cũ
 */

require_once __DIR__ . '/config/db.php';

echo "<h2>Đồng bộ khách hàng từ bảng users sang customers</h2>";
echo "<hr>";

try {
    // Lấy tất cả users có role_id = 5 (khách hàng)
    $stmt = $conn->prepare("
        SELECT u.id, u.full_name, u.phone, u.email, u.created_at
        FROM users u
        WHERE u.role_id = 5
        AND u.status = 'active'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total = 0;
    $created = 0;
    $skipped = 0;
    $errors = 0;
    
    while ($user = $result->fetch_assoc()) {
        $total++;
        
        // Kiểm tra xem customer đã tồn tại chưa (dựa vào email)
        $check = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $check->bind_param("s", $user['email']);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        
        if ($existing) {
            echo "⏭️ Bỏ qua: <strong>{$user['full_name']}</strong> ({$user['email']}) - Đã tồn tại<br>";
            $skipped++;
        } else {
            // Tạo customer mới
            // Xử lý phone: nếu rỗng hoặc NULL thì set NULL, nếu trùng thì thêm suffix
            $phone_value = !empty($user['phone']) ? $user['phone'] : null;
            
            // Kiểm tra phone trùng nếu có giá trị
            if ($phone_value !== null) {
                $check_phone = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
                $check_phone->bind_param("s", $phone_value);
                $check_phone->execute();
                
                if ($check_phone->get_result()->num_rows > 0) {
                    // Phone đã tồn tại, set NULL để tránh lỗi
                    $phone_value = null;
                }
            }
            
            $insert = $conn->prepare("
                INSERT INTO customers (name, phone, email, status, created_at) 
                VALUES (?, ?, ?, 'active', ?)
            ");
            $insert->bind_param("ssss", $user['full_name'], $phone_value, $user['email'], $user['created_at']);
            
            if ($insert->execute()) {
                echo "✅ Tạo mới: <strong>{$user['full_name']}</strong> ({$user['email']})<br>";
                $created++;
            } else {
                echo "❌ Lỗi: <strong>{$user['full_name']}</strong> - " . $conn->error . "<br>";
                $errors++;
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Kết quả:</h3>";
    echo "<ul>";
    echo "<li>Tổng số users khách hàng: <strong>$total</strong></li>";
    echo "<li>Đã tạo mới: <strong style='color: green;'>$created</strong></li>";
    echo "<li>Đã tồn tại (bỏ qua): <strong style='color: orange;'>$skipped</strong></li>";
    echo "<li>Lỗi: <strong style='color: red;'>$errors</strong></li>";
    echo "</ul>";
    
    if ($created > 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ Đồng bộ thành công! Bây giờ các khách hàng sẽ hiển thị trong trang quản lý.</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Không có khách hàng mới nào cần đồng bộ.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='pages/customers.php'>← Quay lại trang quản lý khách hàng</a></p>";
?>
