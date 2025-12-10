<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();
$db->exec("SET NAMES utf8mb4");

// Xóa dữ liệu cũ
$db->exec("DELETE FROM products");
$db->exec("DELETE FROM categories");

// Thêm danh mục
$categories = [
    [1, 'Điện thoại cao cấp', 'Điện thoại flagship cao cấp'],
    [2, 'Điện thoại tầm trung', 'Điện thoại tầm trung giá tốt'],
    [3, 'Điện thoại giá rẻ', 'Điện thoại phổ thông giá rẻ'],
    [4, 'Máy tính bảng', 'Tablet các loại'],
    [5, 'Phụ kiện', 'Phụ kiện điện thoại'],
];

$stmt = $db->prepare("INSERT INTO categories (id, name, description) VALUES (?, ?, ?)");
foreach ($categories as $cat) {
    $stmt->execute($cat);
}
echo "Đã thêm " . count($categories) . " danh mục\n";

// Thêm sản phẩm
$products = [
    // Điện thoại cao cấp
    [1, 'iPhone 15 Pro Max 256GB', 'IP15PM256', 32990000, 28000000, 50, 10, 'iPhone 15 Pro Max chính hãng Apple'],
    [1, 'iPhone 15 Pro Max 512GB', 'IP15PM512', 38990000, 33000000, 30, 10, 'iPhone 15 Pro Max 512GB chính hãng'],
    [1, 'iPhone 15 Pro 256GB', 'IP15P256', 28990000, 24000000, 45, 10, 'iPhone 15 Pro chính hãng Apple'],
    [1, 'iPhone 15 Plus 256GB', 'IP15PL256', 24990000, 21000000, 40, 10, 'iPhone 15 Plus chính hãng'],
    [1, 'iPhone 14 Pro Max 256GB', 'IP14PM256', 26990000, 22000000, 35, 10, 'iPhone 14 Pro Max chính hãng'],
    [1, 'Samsung Galaxy S24 Ultra 256GB', 'SS24U256', 31990000, 27000000, 40, 10, 'Samsung Galaxy S24 Ultra flagship'],
    [1, 'Samsung Galaxy S24 Ultra 512GB', 'SS24U512', 36990000, 31000000, 25, 10, 'Samsung Galaxy S24 Ultra 512GB'],
    [1, 'Samsung Galaxy S24+ 256GB', 'SS24P256', 24990000, 21000000, 35, 10, 'Samsung Galaxy S24 Plus'],
    [1, 'Samsung Galaxy Z Fold5 256GB', 'SSZF5256', 41990000, 35000000, 20, 5, 'Samsung Galaxy Z Fold5 màn gập'],
    [1, 'Samsung Galaxy Z Flip5 256GB', 'SSZFL5256', 25990000, 22000000, 30, 10, 'Samsung Galaxy Z Flip5 màn gập'],
    [1, 'OPPO Find X7 Ultra', 'OPFX7U', 23990000, 20000000, 25, 10, 'OPPO Find X7 Ultra cao cấp'],
    [1, 'Xiaomi 14 Ultra', 'XI14U', 24990000, 21000000, 30, 10, 'Xiaomi 14 Ultra flagship'],
    
    // Điện thoại tầm trung
    [2, 'iPhone 14 128GB', 'IP14128', 18990000, 16000000, 60, 10, 'iPhone 14 chính hãng'],
    [2, 'iPhone 13 128GB', 'IP13128', 15990000, 13000000, 55, 10, 'iPhone 13 chính hãng'],
    [2, 'Samsung Galaxy S24 256GB', 'SS24256', 21990000, 18000000, 50, 10, 'Samsung Galaxy S24 tầm trung cao cấp'],
    [2, 'Samsung Galaxy A55 5G', 'SSA555G', 10990000, 9000000, 70, 15, 'Samsung Galaxy A55 5G'],
    [2, 'Samsung Galaxy A54 5G', 'SSA545G', 9490000, 8000000, 65, 15, 'Samsung Galaxy A54 5G'],
    [2, 'OPPO Reno11 5G', 'OPRN115G', 10990000, 9000000, 45, 10, 'OPPO Reno11 5G'],
    [2, 'OPPO Reno10 Pro+ 5G', 'OPRN10P5G', 13990000, 11000000, 40, 10, 'OPPO Reno10 Pro Plus 5G'],
    [2, 'Xiaomi 14', 'XI14', 15990000, 13000000, 50, 10, 'Xiaomi 14 tầm trung cao cấp'],
    [2, 'Xiaomi Redmi Note 13 Pro+ 5G', 'XIRN13P5G', 9990000, 8000000, 80, 15, 'Redmi Note 13 Pro Plus 5G'],
    [2, 'Xiaomi Redmi Note 13 Pro', 'XIRN13P', 7990000, 6500000, 90, 15, 'Redmi Note 13 Pro'],
    [2, 'Vivo V30 5G', 'VIV305G', 10990000, 9000000, 40, 10, 'Vivo V30 5G camera đẹp'],
    [2, 'Realme GT5 Pro', 'RLGT5P', 13990000, 11000000, 35, 10, 'Realme GT5 Pro hiệu năng cao'],
    
    // Điện thoại giá rẻ
    [3, 'Samsung Galaxy A15', 'SSA15', 4990000, 4000000, 100, 20, 'Samsung Galaxy A15 giá rẻ'],
    [3, 'Samsung Galaxy A05s', 'SSA05S', 3490000, 2800000, 120, 25, 'Samsung Galaxy A05s phổ thông'],
    [3, 'OPPO A58 4G', 'OPA584G', 4990000, 4000000, 80, 15, 'OPPO A58 4G giá tốt'],
    [3, 'OPPO A18', 'OPA18', 3490000, 2800000, 100, 20, 'OPPO A18 giá rẻ'],
    [3, 'Xiaomi Redmi 13C', 'XIR13C', 3290000, 2600000, 150, 25, 'Redmi 13C giá rẻ'],
    [3, 'Xiaomi Redmi A3', 'XIRA3', 2490000, 2000000, 200, 30, 'Redmi A3 siêu rẻ'],
    [3, 'Vivo Y17s', 'VIY17S', 3990000, 3200000, 90, 15, 'Vivo Y17s phổ thông'],
    [3, 'Realme C67', 'RLC67', 4490000, 3600000, 85, 15, 'Realme C67 giá tốt'],
    [3, 'Nokia G42 5G', 'NOG425G', 4990000, 4000000, 60, 10, 'Nokia G42 5G bền bỉ'],
    [3, 'Tecno Spark 20 Pro+', 'TCS20PP', 3990000, 3200000, 70, 15, 'Tecno Spark 20 Pro Plus'],
    
    // Máy tính bảng
    [4, 'iPad Pro M4 11 inch 256GB', 'IPDPM4256', 28990000, 25000000, 25, 5, 'iPad Pro M4 11 inch mới nhất'],
    [4, 'iPad Pro M4 13 inch 256GB', 'IPDPM413256', 35990000, 31000000, 20, 5, 'iPad Pro M4 13 inch'],
    [4, 'iPad Air M2 11 inch 128GB', 'IPDAM2128', 16990000, 14000000, 35, 10, 'iPad Air M2 11 inch'],
    [4, 'iPad Gen 10 64GB', 'IPD1064', 10990000, 9000000, 50, 10, 'iPad Gen 10 phổ thông'],
    [4, 'iPad Mini 6 64GB', 'IPDM664', 13990000, 11000000, 30, 10, 'iPad Mini 6 nhỏ gọn'],
    [4, 'Samsung Galaxy Tab S9 Ultra', 'SSTS9U', 27990000, 24000000, 20, 5, 'Samsung Galaxy Tab S9 Ultra'],
    [4, 'Samsung Galaxy Tab S9+', 'SSTS9P', 22990000, 19000000, 25, 10, 'Samsung Galaxy Tab S9 Plus'],
    [4, 'Samsung Galaxy Tab A9+', 'SSTA9P', 7990000, 6500000, 60, 15, 'Samsung Galaxy Tab A9 Plus'],
    [4, 'Xiaomi Pad 6', 'XIP6', 8990000, 7500000, 45, 10, 'Xiaomi Pad 6 giá tốt'],
    [4, 'Lenovo Tab P12', 'LNTP12', 9990000, 8000000, 40, 10, 'Lenovo Tab P12'],
    
    // Phụ kiện
    [5, 'AirPods Pro 2', 'APDP2', 6290000, 5000000, 80, 15, 'AirPods Pro 2 chống ồn'],
    [5, 'AirPods 3', 'APD3', 4290000, 3500000, 100, 20, 'AirPods 3 chính hãng'],
    [5, 'Samsung Galaxy Buds3 Pro', 'SSGB3P', 4990000, 4000000, 60, 15, 'Samsung Galaxy Buds3 Pro'],
    [5, 'Apple Watch Series 9 GPS 41mm', 'AWS941', 10990000, 9000000, 40, 10, 'Apple Watch Series 9 GPS'],
    [5, 'Apple Watch SE 2 GPS 40mm', 'AWSE240', 6490000, 5200000, 50, 10, 'Apple Watch SE 2 GPS'],
    [5, 'Samsung Galaxy Watch6 44mm', 'SSGW644', 7490000, 6000000, 45, 10, 'Samsung Galaxy Watch6'],
    [5, 'Sạc nhanh Apple 20W', 'APCH20W', 490000, 350000, 200, 30, 'Củ sạc Apple 20W chính hãng'],
    [5, 'Sạc nhanh Samsung 25W', 'SSCH25W', 390000, 280000, 180, 30, 'Củ sạc Samsung 25W'],
    [5, 'Cáp Lightning Apple 1m', 'APLC1M', 490000, 350000, 250, 40, 'Cáp Lightning Apple chính hãng'],
    [5, 'Cáp USB-C Samsung 1m', 'SSUC1M', 290000, 200000, 300, 50, 'Cáp USB-C Samsung'],
    [5, 'Ốp lưng iPhone 15 Pro Max MagSafe', 'OLIP15PMM', 1290000, 900000, 100, 20, 'Ốp lưng MagSafe chính hãng'],
    [5, 'Ốp lưng Samsung S24 Ultra', 'OLSS24U', 590000, 400000, 120, 25, 'Ốp lưng Samsung S24 Ultra'],
    [5, 'Kính cường lực iPhone 15 Series', 'KCIP15', 150000, 80000, 500, 100, 'Kính cường lực iPhone 15'],
    [5, 'Kính cường lực Samsung S24', 'KCSS24', 120000, 60000, 450, 100, 'Kính cường lực Samsung S24'],
    [5, 'Pin dự phòng Anker 10000mAh', 'AK10K', 590000, 450000, 150, 30, 'Pin sạc dự phòng Anker'],
    [5, 'Pin dự phòng Xiaomi 20000mAh', 'XI20K', 490000, 380000, 180, 30, 'Pin sạc dự phòng Xiaomi'],
];

$stmt = $db->prepare("INSERT INTO products (category_id, name, sku, price, cost, quantity, min_quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($products as $prod) {
    $stmt->execute($prod);
}
echo "Đã thêm " . count($products) . " sản phẩm\n";

echo "\nHoàn tất! Kiểm tra:\n";
$cats = $db->query("SELECT id, name FROM categories")->fetchAll();
foreach ($cats as $c) {
    echo "- " . $c['name'] . "\n";
}
