-- Script cập nhật đầy đủ database
USE db_quanlydienthoai;

-- 1. Xóa sản phẩm cũ (để tránh conflict)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM products;
DELETE FROM categories;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Thêm categories mới theo loại sản phẩm
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Điện thoại cao cấp', 'Điện thoại flagship cao cấp'),
(2, 'Điện thoại tầm trung', 'Điện thoại tầm trung giá tốt'),
(3, 'Điện thoại giá rẻ', 'Điện thoại phổ thông giá rẻ'),
(4, 'Máy tính bảng', 'Tablet các loại'),
(5, 'Phụ kiện', 'Phụ kiện điện thoại');

-- 3. Thêm sản phẩm mẫu
-- Điện thoại cao cấp
INSERT INTO products (category_id, brand_id, name, sku, price, cost, quantity, min_quantity, description) VALUES
(1, 1, 'iPhone 15 Pro Max 256GB', 'IP15PM256', 32990000, 28000000, 50, 10, 'iPhone 15 Pro Max chính hãng Apple'),
(1, 1, 'iPhone 15 Pro Max 512GB', 'IP15PM512', 38990000, 33000000, 30, 10, 'iPhone 15 Pro Max 512GB chính hãng'),
(1, 1, 'iPhone 15 Pro 256GB', 'IP15P256', 28990000, 24000000, 45, 10, 'iPhone 15 Pro chính hãng Apple'),
(1, 2, 'Samsung Galaxy S24 Ultra 256GB', 'SS24U256', 31990000, 27000000, 40, 10, 'Samsung Galaxy S24 Ultra flagship'),
(1, 2, 'Samsung Galaxy S24 Ultra 512GB', 'SS24U512', 36990000, 31000000, 25, 10, 'Samsung Galaxy S24 Ultra 512GB'),
(1, 2, 'Samsung Galaxy Z Fold5 256GB', 'SSZF5256', 41990000, 35000000, 20, 5, 'Samsung Galaxy Z Fold5 màn gập'),
(1, 4, 'OPPO Find X7 Ultra', 'OPFX7U', 23990000, 20000000, 25, 10, 'OPPO Find X7 Ultra cao cấp'),
(1, 3, 'Xiaomi 14 Ultra', 'XI14U', 24990000, 21000000, 30, 10, 'Xiaomi 14 Ultra flagship');

-- Điện thoại tầm trung
INSERT INTO products (category_id, brand_id, name, sku, price, cost, quantity, min_quantity, description) VALUES
(2, 1, 'iPhone 14 128GB', 'IP14128', 18990000, 16000000, 60, 10, 'iPhone 14 chính hãng'),
(2, 1, 'iPhone 13 128GB', 'IP13128', 15990000, 13000000, 55, 10, 'iPhone 13 chính hãng'),
(2, 2, 'Samsung Galaxy S24 256GB', 'SS24256', 21990000, 18000000, 50, 10, 'Samsung Galaxy S24 tầm trung cao cấp'),
(2, 2, 'Samsung Galaxy A55 5G', 'SSA555G', 10990000, 9000000, 70, 15, 'Samsung Galaxy A55 5G'),
(2, 4, 'OPPO Reno11 5G', 'OPRN115G', 10990000, 9000000, 45, 10, 'OPPO Reno11 5G'),
(2, 3, 'Xiaomi 14', 'XI14', 15990000, 13000000, 50, 10, 'Xiaomi 14 tầm trung cao cấp'),
(2, 3, 'Xiaomi Redmi Note 13 Pro+ 5G', 'XIRN13P5G', 9990000, 8000000, 80, 15, 'Redmi Note 13 Pro Plus 5G'),
(2, 5, 'Vivo V30 5G', 'VIV305G', 10990000, 9000000, 40, 10, 'Vivo V30 5G camera đẹp'),
(2, 6, 'Realme GT5 Pro', 'RLGT5P', 13990000, 11000000, 35, 10, 'Realme GT5 Pro hiệu năng cao');

-- Điện thoại giá rẻ
INSERT INTO products (category_id, brand_id, name, sku, price, cost, quantity, min_quantity, description) VALUES
(3, 2, 'Samsung Galaxy A15', 'SSA15', 4990000, 4000000, 100, 20, 'Samsung Galaxy A15 giá rẻ'),
(3, 2, 'Samsung Galaxy A05s', 'SSA05S', 3490000, 2800000, 120, 25, 'Samsung Galaxy A05s phổ thông'),
(3, 4, 'OPPO A58 4G', 'OPA584G', 4990000, 4000000, 80, 15, 'OPPO A58 4G giá tốt'),
(3, 4, 'OPPO A18', 'OPA18', 3490000, 2800000, 100, 20, 'OPPO A18 giá rẻ'),
(3, 3, 'Xiaomi Redmi 13C', 'XIR13C', 3290000, 2600000, 150, 25, 'Redmi 13C giá rẻ'),
(3, 3, 'Xiaomi Redmi A3', 'XIRA3', 2490000, 2000000, 200, 30, 'Redmi A3 siêu rẻ'),
(3, 5, 'Vivo Y17s', 'VIY17S', 3990000, 3200000, 90, 15, 'Vivo Y17s phổ thông'),
(3, 6, 'Realme C67', 'RLC67', 4490000, 3600000, 85, 15, 'Realme C67 giá tốt'),
(3, 7, 'Nokia G42 5G', 'NOG425G', 4990000, 4000000, 60, 10, 'Nokia G42 5G bền bỉ'),
(3, 8, 'Tecno Spark 20 Pro+', 'TCS20PP', 3990000, 3200000, 70, 15, 'Tecno Spark 20 Pro Plus');

-- Máy tính bảng
INSERT INTO products (category_id, brand_id, name, sku, price, cost, quantity, min_quantity, description) VALUES
(4, 1, 'iPad Pro M4 11 inch 256GB', 'IPDPM4256', 28990000, 25000000, 25, 5, 'iPad Pro M4 11 inch mới nhất'),
(4, 1, 'iPad Air M2 11 inch 128GB', 'IPDAM2128', 16990000, 14000000, 35, 10, 'iPad Air M2 11 inch'),
(4, 1, 'iPad Gen 10 64GB', 'IPD1064', 10990000, 9000000, 50, 10, 'iPad Gen 10 phổ thông'),
(4, 2, 'Samsung Galaxy Tab S9 Ultra', 'SSTS9U', 27990000, 24000000, 20, 5, 'Samsung Galaxy Tab S9 Ultra'),
(4, 2, 'Samsung Galaxy Tab A9+', 'SSTA9P', 7990000, 6500000, 60, 15, 'Samsung Galaxy Tab A9 Plus'),
(4, 3, 'Xiaomi Pad 6', 'XIP6', 8990000, 7500000, 45, 10, 'Xiaomi Pad 6 giá tốt'),
(4, 9, 'Lenovo Tab P12', 'LNTP12', 9990000, 8000000, 40, 10, 'Lenovo Tab P12');

-- Phụ kiện
INSERT INTO products (category_id, brand_id, name, sku, price, cost, quantity, min_quantity, description) VALUES
(5, 1, 'AirPods Pro 2', 'APDP2', 6290000, 5000000, 80, 15, 'AirPods Pro 2 chống ồn'),
(5, 1, 'AirPods 3', 'APD3', 4290000, 3500000, 100, 20, 'AirPods 3 chính hãng'),
(5, 2, 'Samsung Galaxy Buds3 Pro', 'SSGB3P', 4990000, 4000000, 60, 15, 'Samsung Galaxy Buds3 Pro'),
(5, 1, 'Apple Watch Series 9 GPS 41mm', 'AWS941', 10990000, 9000000, 40, 10, 'Apple Watch Series 9 GPS'),
(5, 2, 'Samsung Galaxy Watch6 44mm', 'SSGW644', 7490000, 6000000, 45, 10, 'Samsung Galaxy Watch6'),
(5, 1, 'Sạc nhanh Apple 20W', 'APCH20W', 490000, 350000, 200, 30, 'Củ sạc Apple 20W chính hãng'),
(5, 2, 'Sạc nhanh Samsung 25W', 'SSCH25W', 390000, 280000, 180, 30, 'Củ sạc Samsung 25W'),
(5, 1, 'Cáp Lightning Apple 1m', 'APLC1M', 490000, 350000, 250, 40, 'Cáp Lightning Apple chính hãng'),
(5, 10, 'Pin dự phòng Anker 10000mAh', 'AK10K', 590000, 450000, 150, 30, 'Pin sạc dự phòng Anker'),
(5, 3, 'Pin dự phòng Xiaomi 20000mAh', 'XI20K', 490000, 380000, 180, 30, 'Pin sạc dự phòng Xiaomi');

SELECT 'Data updated successfully!' as Result;
SELECT COUNT(*) as total_products FROM products;
SELECT c.name as category, COUNT(p.id) as products FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id;
