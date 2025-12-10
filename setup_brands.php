<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();
$db->exec("SET NAMES utf8mb4");

// Tạo bảng brands
$db->exec("CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    logo VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Đã tạo bảng brands\n";

// Thêm cột brand_id vào products nếu chưa có
try {
    $db->exec("ALTER TABLE products ADD COLUMN brand_id INT NULL AFTER category_id");
    $db->exec("ALTER TABLE products ADD FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL");
    echo "Đã thêm cột brand_id vào products\n";
} catch (Exception $e) {
    echo "Cột brand_id đã tồn tại\n";
}

// Thêm các thương hiệu
$brands = [
    ['Apple', 'apple.png', 'Thương hiệu Apple - iPhone, iPad, Mac'],
    ['Samsung', 'samsung.png', 'Thương hiệu Samsung - Galaxy series'],
    ['Xiaomi', 'xiaomi.png', 'Thương hiệu Xiaomi - Redmi, POCO'],
    ['OPPO', 'oppo.png', 'Thương hiệu OPPO - Reno, Find series'],
    ['Vivo', 'vivo.png', 'Thương hiệu Vivo'],
    ['Realme', 'realme.png', 'Thương hiệu Realme'],
    ['Nokia', 'nokia.png', 'Thương hiệu Nokia'],
    ['Tecno', 'tecno.png', 'Thương hiệu Tecno'],
    ['Lenovo', 'lenovo.png', 'Thương hiệu Lenovo'],
    ['Anker', 'anker.png', 'Thương hiệu Anker - Phụ kiện'],
];

$stmt = $db->prepare("INSERT IGNORE INTO brands (name, logo, description) VALUES (?, ?, ?)");
foreach ($brands as $b) {
    $stmt->execute($b);
}
echo "Đã thêm " . count($brands) . " thương hiệu\n";

// Cập nhật brand_id cho sản phẩm dựa trên tên
$updates = [
    'Apple' => ['iPhone', 'iPad', 'AirPods', 'Apple Watch', 'Sạc nhanh Apple', 'Cáp Lightning', 'Ốp lưng iPhone', 'Kính cường lực iPhone'],
    'Samsung' => ['Samsung', 'Galaxy'],
    'Xiaomi' => ['Xiaomi', 'Redmi', 'Xiaomi Pad'],
    'OPPO' => ['OPPO'],
    'Vivo' => ['Vivo'],
    'Realme' => ['Realme'],
    'Nokia' => ['Nokia'],
    'Tecno' => ['Tecno'],
    'Lenovo' => ['Lenovo'],
    'Anker' => ['Anker'],
];

foreach ($updates as $brandName => $keywords) {
    $brandId = $db->query("SELECT id FROM brands WHERE name = '$brandName'")->fetchColumn();
    if ($brandId) {
        foreach ($keywords as $kw) {
            $db->exec("UPDATE products SET brand_id = $brandId WHERE name LIKE '%$kw%' AND brand_id IS NULL");
        }
    }
}
echo "Đã cập nhật brand_id cho sản phẩm\n\n";

// Kiểm tra kết quả
echo "=== Kết quả ===\n";
$result = $db->query("SELECT b.name, COUNT(p.id) as cnt FROM brands b LEFT JOIN products p ON b.id = p.brand_id GROUP BY b.id ORDER BY cnt DESC");
foreach ($result as $r) {
    echo $r['name'] . ": " . $r['cnt'] . " sản phẩm\n";
}
