<?php
require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'db_quanlydienthoai') . ";charset=utf8mb4",
        env('DB_USER', 'root'),
        env('DB_PASS', ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    // Cập nhật categories
    $categories = [
        1 => ['name' => 'Điện thoại cao cấp', 'description' => 'Điện thoại flagship cao cấp'],
        2 => ['name' => 'Điện thoại tầm trung', 'description' => 'Điện thoại tầm trung giá tốt'],
        3 => ['name' => 'Điện thoại giá rẻ', 'description' => 'Điện thoại phổ thông giá rẻ'],
        4 => ['name' => 'Máy tính bảng', 'description' => 'Tablet các loại'],
        5 => ['name' => 'Phụ kiện', 'description' => 'Phụ kiện điện thoại'],
    ];

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
    foreach ($categories as $id => $data) {
        $stmt->execute([$data['name'], $data['description'], $id]);
    }

    // Cập nhật brands
    $brands = [
        1 => ['name' => 'Apple', 'description' => 'Thương hiệu Apple - iPhone, iPad, Mac'],
        2 => ['name' => 'Samsung', 'description' => 'Thương hiệu Samsung - Galaxy series'],
        3 => ['name' => 'Xiaomi', 'description' => 'Thương hiệu Xiaomi - Redmi, POCO'],
        4 => ['name' => 'OPPO', 'description' => 'Thương hiệu OPPO - Reno, Find series'],
        5 => ['name' => 'Vivo', 'description' => 'Thương hiệu Vivo'],
        6 => ['name' => 'Realme', 'description' => 'Thương hiệu Realme'],
        7 => ['name' => 'Nokia', 'description' => 'Thương hiệu Nokia'],
        8 => ['name' => 'Tecno', 'description' => 'Thương hiệu Tecno'],
        9 => ['name' => 'Lenovo', 'description' => 'Thương hiệu Lenovo'],
        10 => ['name' => 'Anker', 'description' => 'Thương hiệu Anker - Phụ kiện'],
    ];

    $stmt = $pdo->prepare("UPDATE brands SET name = ?, description = ? WHERE id = ?");
    foreach ($brands as $id => $data) {
        $stmt->execute([$data['name'], $data['description'], $id]);
    }

    // Cập nhật products description
    $products = [
        ['sku' => 'IP15PM256', 'description' => 'iPhone 15 Pro Max chính hãng Apple'],
        ['sku' => 'IP15PM512', 'description' => 'iPhone 15 Pro Max 512GB chính hãng'],
        ['sku' => 'IP15P256', 'description' => 'iPhone 15 Pro chính hãng Apple'],
        ['sku' => 'SS24U256', 'description' => 'Samsung Galaxy S24 Ultra flagship'],
        ['sku' => 'SS24U512', 'description' => 'Samsung Galaxy S24 Ultra 512GB'],
        ['sku' => 'SSZF5256', 'description' => 'Samsung Galaxy Z Fold5 màn gập'],
        ['sku' => 'OPFX7U', 'description' => 'OPPO Find X7 Ultra cao cấp'],
        ['sku' => 'XI14U', 'description' => 'Xiaomi 14 Ultra flagship'],
        ['sku' => 'IP14128', 'description' => 'iPhone 14 chính hãng'],
        ['sku' => 'IP13128', 'description' => 'iPhone 13 chính hãng'],
        ['sku' => 'SS24256', 'description' => 'Samsung Galaxy S24 tầm trung cao cấp'],
        ['sku' => 'OPRN115G', 'description' => 'OPPO Reno11 5G'],
        ['sku' => 'XI14', 'description' => 'Xiaomi 14 tầm trung cao cấp'],
        ['sku' => 'XIRN13P5G', 'description' => 'Redmi Note 13 Pro Plus 5G'],
        ['sku' => 'VIV305G', 'description' => 'Vivo V30 5G camera đẹp'],
        ['sku' => 'RLGT5P', 'description' => 'Realme GT5 Pro hiệu năng cao'],
        ['sku' => 'SSA15', 'description' => 'Samsung Galaxy A15 giá rẻ'],
        ['sku' => 'SSA05S', 'description' => 'Samsung Galaxy A05s phổ thông'],
        ['sku' => 'OPA584G', 'description' => 'OPPO A58 4G giá tốt'],
        ['sku' => 'OPA18', 'description' => 'OPPO A18 giá rẻ'],
        ['sku' => 'XIR13C', 'description' => 'Redmi 13C giá rẻ'],
        ['sku' => 'XIRA3', 'description' => 'Redmi A3 siêu rẻ'],
        ['sku' => 'VIY17S', 'description' => 'Vivo Y17s phổ thông'],
        ['sku' => 'RLC67', 'description' => 'Realme C67 giá tốt'],
        ['sku' => 'NOG425G', 'description' => 'Nokia G42 5G bền bỉ'],
        ['sku' => 'TCS20PP', 'description' => 'Tecno Spark 20 Pro Plus'],
        ['sku' => 'IPDPM4256', 'description' => 'iPad Pro M4 11 inch mới nhất'],
        ['sku' => 'IPDAM2128', 'description' => 'iPad Air M2 11 inch'],
        ['sku' => 'IPD1064', 'description' => 'iPad Gen 10 phổ thông'],
        ['sku' => 'SSTS9U', 'description' => 'Samsung Galaxy Tab S9 Ultra'],
        ['sku' => 'SSTA9P', 'description' => 'Samsung Galaxy Tab A9 Plus'],
        ['sku' => 'XIP6', 'description' => 'Xiaomi Pad 6 giá tốt'],
        ['sku' => 'APDP2', 'description' => 'AirPods Pro 2 chống ồn'],
        ['sku' => 'APD3', 'description' => 'AirPods 3 chính hãng'],
        ['sku' => 'AWS941', 'description' => 'Apple Watch Series 9 GPS'],
        ['sku' => 'APCH20W', 'description' => 'Củ sạc Apple 20W chính hãng'],
        ['sku' => 'SSCH25W', 'description' => 'Củ sạc Samsung 25W'],
        ['sku' => 'APLC1M', 'description' => 'Cáp Lightning Apple chính hãng'],
        ['sku' => 'AK10K', 'description' => 'Pin sạc dự phòng Anker'],
        ['sku' => 'XI20K', 'description' => 'Pin sạc dự phòng Xiaomi'],
    ];

    $stmt = $pdo->prepare("UPDATE products SET description = ? WHERE sku = ?");
    foreach ($products as $p) {
        $stmt->execute([$p['description'], $p['sku']]);
    }

    echo "✅ Đã cập nhật encoding thành công!\n";
    
    // Hiển thị kết quả
    echo "\n📁 Categories:\n";
    $result = $pdo->query("SELECT id, name FROM categories ORDER BY id");
    foreach ($result as $row) {
        echo "  {$row['id']}. {$row['name']}\n";
    }
    
    echo "\n🏷️ Brands:\n";
    $result = $pdo->query("SELECT id, name FROM brands ORDER BY id");
    foreach ($result as $row) {
        echo "  {$row['id']}. {$row['name']}\n";
    }

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
