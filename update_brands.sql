-- Script cập nhật database: Thêm bảng brands và cột brand_id
USE db_quanlydienthoai;

-- 1. Tạo bảng brands nếu chưa có
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Thêm dữ liệu brands
INSERT IGNORE INTO `brands` (`id`, `name`, `logo`, `description`) VALUES
(1, 'Apple', 'apple.png', 'Thương hiệu Apple - iPhone, iPad, Mac'),
(2, 'Samsung', 'samsung.png', 'Thương hiệu Samsung - Galaxy series'),
(3, 'Xiaomi', 'xiaomi.png', 'Thương hiệu Xiaomi - Redmi, POCO'),
(4, 'OPPO', 'oppo.png', 'Thương hiệu OPPO - Reno, Find series'),
(5, 'Vivo', 'vivo.png', 'Thương hiệu Vivo'),
(6, 'Realme', 'realme.png', 'Thương hiệu Realme'),
(7, 'Nokia', 'nokia.png', 'Thương hiệu Nokia'),
(8, 'Tecno', 'tecno.png', 'Thương hiệu Tecno'),
(9, 'Lenovo', 'lenovo.png', 'Thương hiệu Lenovo'),
(10, 'Anker', 'anker.png', 'Thương hiệu Anker - Phụ kiện');

-- 3. Thêm cột brand_id vào bảng products nếu chưa có
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'db_quanlydienthoai' 
                   AND TABLE_NAME = 'products' 
                   AND COLUMN_NAME = 'brand_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN brand_id int(11) DEFAULT NULL AFTER category_id',
    'SELECT "Column brand_id already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Thêm foreign key nếu chưa có
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                  WHERE TABLE_SCHEMA = 'db_quanlydienthoai' 
                  AND TABLE_NAME = 'products' 
                  AND CONSTRAINT_NAME = 'products_ibfk_2');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE products ADD CONSTRAINT products_ibfk_2 FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Cập nhật brand_id cho các sản phẩm dựa trên tên
UPDATE products SET brand_id = 1 WHERE name LIKE '%iPhone%' OR name LIKE '%iPad%' OR name LIKE '%AirPods%' OR name LIKE '%Apple%';
UPDATE products SET brand_id = 2 WHERE name LIKE '%Samsung%' OR name LIKE '%Galaxy%';
UPDATE products SET brand_id = 3 WHERE name LIKE '%Xiaomi%' OR name LIKE '%Redmi%' OR name LIKE '%POCO%';
UPDATE products SET brand_id = 4 WHERE name LIKE '%OPPO%';
UPDATE products SET brand_id = 5 WHERE name LIKE '%Vivo%';
UPDATE products SET brand_id = 6 WHERE name LIKE '%Realme%';
UPDATE products SET brand_id = 7 WHERE name LIKE '%Nokia%';
UPDATE products SET brand_id = 8 WHERE name LIKE '%Tecno%';
UPDATE products SET brand_id = 9 WHERE name LIKE '%Lenovo%';
UPDATE products SET brand_id = 10 WHERE name LIKE '%Anker%';

SELECT 'Database updated successfully!' as Result;
