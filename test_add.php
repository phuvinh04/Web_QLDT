<?php
session_start();

// Giả lập đăng nhập admin nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['full_name'] = 'Admin Test';
}

require_once __DIR__ . '/config/database.php';

$db = getDB();
$message = '';
$error = '';

// Lấy danh mục
$categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO products (category_id, name, sku, description, price, cost, quantity, min_quantity, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $_POST['category_id'],
            $_POST['name'],
            $_POST['sku'],
            $_POST['description'] ?? '',
            $_POST['price'],
            $_POST['cost'] ?? 0,
            $_POST['quantity'] ?? 0,
            $_POST['min_quantity'] ?? 10
        ]);
        $message = "Thêm sản phẩm thành công! ID: " . $db->lastInsertId();
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Thêm Sản Phẩm</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #1d4ed8; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .success { background: #d1fae5; color: #047857; padding: 10px; margin-bottom: 15px; }
        .error { background: #fecaca; color: #b91c1c; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Test Thêm Sản Phẩm</h1>
    
    <?php if ($message): ?>
    <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Tên sản phẩm *</label>
            <input type="text" name="name" required>
        </div>
        
        <div class="form-group">
            <label>Mã SKU *</label>
            <input type="text" name="sku" required>
        </div>
        
        <div class="form-group">
            <label>Danh mục *</label>
            <select name="category_id" required>
                <option value="">-- Chọn --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Giá bán *</label>
            <input type="number" name="price" required>
        </div>
        
        <div class="form-group">
            <label>Giá nhập</label>
            <input type="number" name="cost">
        </div>
        
        <div class="form-group">
            <label>Số lượng</label>
            <input type="number" name="quantity" value="0">
        </div>
        
        <div class="form-group">
            <label>Mô tả</label>
            <textarea name="description" rows="3"></textarea>
        </div>
        
        <button type="submit">Thêm sản phẩm</button>
    </form>
    
    <hr>
    <p><a href="pages/products.php">← Quay lại trang sản phẩm</a></p>
</body>
</html>
