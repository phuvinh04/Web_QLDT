-- =================================================================
-- SCRIPT CẬP NHẬT DỮ LIỆU SẢN PHẨM THỰC TẾ THỊ TRƯỜNG VIỆT NAM
-- Chỉ sử dụng các trường có sẵn trong database
-- =================================================================

USE db_quanlydienthoai;

-- 1. Xóa sản phẩm cũ
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM order_items;
DELETE FROM stock_movements;
DELETE FROM products;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Đảm bảo categories đúng
DELETE FROM categories;
INSERT INTO categories (id, name, description) VALUES
(1, 'Điện thoại cao cấp', 'Smartphone flagship từ 20 triệu'),
(2, 'Điện thoại tầm trung', 'Smartphone từ 7-20 triệu'),
(3, 'Điện thoại phổ thông', 'Smartphone dưới 7 triệu'),
(4, 'Máy tính bảng', 'Tablet các hãng'),
(5, 'Đồng hồ thông minh', 'Smartwatch'),
(6, 'Tai nghe', 'Tai nghe không dây, có dây'),
(7, 'Sạc và cáp', 'Củ sạc, cáp sạc, sạc dự phòng'),
(8, 'Ốp lưng và bao da', 'Phụ kiện bảo vệ'),
(9, 'Phụ kiện khác', 'Kính cường lực, giá đỡ...');

-- 3. Đảm bảo brands đúng
DELETE FROM brands;
INSERT INTO brands (id, name, logo, description) VALUES
(1, 'Apple', 'apple.png', 'iPhone, iPad, Mac'),
(2, 'Samsung', 'samsung.png', 'Galaxy series'),
(3, 'Xiaomi', 'xiaomi.png', 'Redmi, POCO'),
(4, 'OPPO', 'oppo.png', 'Reno, Find series'),
(5, 'Vivo', 'vivo.png', 'Vivo smartphones'),
(6, 'Realme', 'realme.png', 'Realme phones'),
(7, 'Nokia', 'nokia.png', 'Nokia phones'),
(8, 'Sony', 'sony.png', 'Sony audio'),
(9, 'Google', 'google.png', 'Pixel phones'),
(10, 'Anker', 'anker.png', 'Phụ kiện');

-- =================================================================
-- ĐIỆN THOẠI CAO CẤP (category_id = 1)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

-- iPhone 16 Series
(1, 1, 'iPhone 16 Pro Max 256GB', 'IP16PM256', 
'Màn hình: 6.9" Super Retina XDR OLED, 120Hz ProMotion
Chip: Apple A18 Pro | RAM: 8GB | Bộ nhớ: 256GB
Camera: 48MP + 12MP + 48MP (5x zoom) | Selfie: 12MP
Pin: 4685mAh, sạc 27W, MagSafe 25W
Khung Titanium, IP68, iOS 18', 
34990000, 31000000, 50, 10, 'iphone-16-pro-max.jpg', 'active'),

(1, 1, 'iPhone 16 Pro Max 512GB', 'IP16PM512',
'Màn hình: 6.9" Super Retina XDR OLED, 120Hz ProMotion
Chip: Apple A18 Pro | RAM: 8GB | Bộ nhớ: 512GB
Camera: 48MP + 12MP + 48MP (5x zoom) | Selfie: 12MP
Pin: 4685mAh, sạc 27W, MagSafe 25W
Khung Titanium, IP68, iOS 18',
40990000, 37000000, 30, 5, 'iphone-16-pro-max.jpg', 'active'),

(1, 1, 'iPhone 16 Pro 256GB', 'IP16P256',
'Màn hình: 6.3" Super Retina XDR OLED, 120Hz ProMotion
Chip: Apple A18 Pro | RAM: 8GB | Bộ nhớ: 256GB
Camera: 48MP + 12MP + 12MP (5x zoom) | Selfie: 12MP
Pin: 3582mAh, sạc 27W, MagSafe 25W
Khung Titanium, IP68, iOS 18',
28990000, 26000000, 45, 10, 'iphone-16-pro.jpg', 'active'),

-- Samsung Galaxy S25 Series
(1, 2, 'Samsung Galaxy S25 Ultra 256GB', 'SS25U256',
'Màn hình: 6.9" Dynamic AMOLED 2X, QHD+, 120Hz
Chip: Snapdragon 8 Elite | RAM: 12GB | Bộ nhớ: 256GB
Camera: 200MP + 50MP + 10MP + 50MP | Selfie: 12MP
Pin: 5000mAh, sạc 45W, wireless 15W
S Pen tích hợp, IP68, Galaxy AI',
33990000, 30000000, 40, 10, 'samsung-s25-ultra.jpg', 'active'),

(1, 2, 'Samsung Galaxy S25 Ultra 512GB', 'SS25U512',
'Màn hình: 6.9" Dynamic AMOLED 2X, QHD+, 120Hz
Chip: Snapdragon 8 Elite | RAM: 12GB | Bộ nhớ: 512GB
Camera: 200MP + 50MP + 10MP + 50MP | Selfie: 12MP
Pin: 5000mAh, sạc 45W, wireless 15W
S Pen tích hợp, IP68, Galaxy AI',
38990000, 35000000, 25, 5, 'samsung-s25-ultra.jpg', 'active'),

(1, 2, 'Samsung Galaxy S25+ 256GB', 'SS25P256',
'Màn hình: 6.7" Dynamic AMOLED 2X, QHD+, 120Hz
Chip: Snapdragon 8 Elite | RAM: 12GB | Bộ nhớ: 256GB
Camera: 50MP + 12MP + 10MP (3x zoom) | Selfie: 12MP
Pin: 4900mAh, sạc 45W, wireless 15W
IP68, Galaxy AI',
26990000, 24000000, 35, 10, 'samsung-s25-plus.jpg', 'active'),

-- Xiaomi 15 Series
(1, 3, 'Xiaomi 15 Pro 512GB', 'XM15P512',
'Màn hình: 6.73" LTPO AMOLED, 2K+, 120Hz
Chip: Snapdragon 8 Elite | RAM: 16GB | Bộ nhớ: 512GB
Camera: 50MP Leica + 50MP + 50MP | Selfie: 32MP
Pin: 5400mAh, sạc 90W, wireless 50W
IP68, HyperOS 2',
24990000, 22000000, 30, 10, 'xiaomi-15-pro.jpg', 'active'),

(1, 3, 'Xiaomi 15 Ultra 512GB', 'XM15U512',
'Màn hình: 6.73" LTPO AMOLED, 2K+, 120Hz
Chip: Snapdragon 8 Elite | RAM: 16GB | Bộ nhớ: 512GB
Camera: 50MP 1-inch Leica + 50MP + 200MP periscope | Selfie: 32MP
Pin: 5500mAh, sạc 90W, wireless 80W
IP68, HyperOS 2',
29990000, 27000000, 20, 5, 'xiaomi-15-ultra.jpg', 'active'),

-- OPPO & Vivo
(1, 4, 'OPPO Find X8 Pro 512GB', 'OPFX8P512',
'Màn hình: 6.78" LTPO AMOLED, 120Hz
Chip: Dimensity 9400 | RAM: 16GB | Bộ nhớ: 512GB
Camera: 50MP Hasselblad + 50MP + 50MP periscope | Selfie: 32MP
Pin: 5910mAh, sạc 80W, wireless 50W
IP69, ColorOS 15',
27990000, 25000000, 25, 5, 'oppo-find-x8-pro.jpg', 'active'),

(1, 5, 'Vivo X200 Pro 512GB', 'VVX200P512',
'Màn hình: 6.78" LTPO AMOLED, 120Hz
Chip: Dimensity 9400 | RAM: 16GB | Bộ nhớ: 512GB
Camera: 50MP ZEISS + 50MP + 200MP periscope | Selfie: 32MP
Pin: 6000mAh, sạc 90W, wireless 30W
IP68, Funtouch OS 15',
25990000, 23000000, 25, 5, 'vivo-x200-pro.jpg', 'active'),

-- Samsung Z Series
(1, 2, 'Samsung Galaxy Z Fold6 256GB', 'SSZF6256',
'Màn hình: 7.6" + 6.3" Dynamic AMOLED 2X, 120Hz
Chip: Snapdragon 8 Gen 3 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 50MP + 12MP + 10MP | Selfie: 10MP + 4MP
Pin: 4400mAh, sạc 25W, wireless 15W
Màn hình gập, IPX8, Galaxy AI',
43990000, 40000000, 15, 5, 'samsung-z-fold6.jpg', 'active'),

(1, 2, 'Samsung Galaxy Z Flip6 256GB', 'SSZFL6256',
'Màn hình: 6.7" + 3.4" Dynamic AMOLED 2X, 120Hz
Chip: Snapdragon 8 Gen 3 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 50MP + 12MP | Selfie: 10MP
Pin: 4000mAh, sạc 25W, wireless 15W
Màn hình gập nhỏ gọn, IPX8',
25990000, 23000000, 25, 5, 'samsung-z-flip6.jpg', 'active');


-- =================================================================
-- ĐIỆN THOẠI TẦM TRUNG (category_id = 2)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(2, 1, 'iPhone 15 128GB', 'IP15128',
'Màn hình: 6.1" Super Retina XDR OLED, 60Hz
Chip: Apple A16 Bionic | RAM: 6GB | Bộ nhớ: 128GB
Camera: 48MP + 12MP | Selfie: 12MP TrueDepth
Pin: 3349mAh, sạc 20W, MagSafe 15W
Dynamic Island, USB-C, IP68, iOS 17',
19990000, 17500000, 60, 15, 'iphone-15.jpg', 'active'),

(2, 1, 'iPhone 15 Plus 128GB', 'IP15PL128',
'Màn hình: 6.7" Super Retina XDR OLED, 60Hz
Chip: Apple A16 Bionic | RAM: 6GB | Bộ nhớ: 128GB
Camera: 48MP + 12MP | Selfie: 12MP TrueDepth
Pin: 4383mAh, sạc 20W, MagSafe 15W
Dynamic Island, USB-C, IP68, iOS 17',
22990000, 20500000, 40, 10, 'iphone-15-plus.jpg', 'active'),

(2, 2, 'Samsung Galaxy S24 256GB', 'SS24256',
'Màn hình: 6.2" Dynamic AMOLED 2X, FHD+, 120Hz
Chip: Snapdragon 8 Gen 3 | RAM: 8GB | Bộ nhớ: 256GB
Camera: 50MP + 12MP + 10MP | Selfie: 12MP
Pin: 4000mAh, sạc 25W, wireless 15W
IP68, Galaxy AI',
19990000, 17500000, 50, 15, 'samsung-s24.jpg', 'active'),

(2, 2, 'Samsung Galaxy S24+ 256GB', 'SS24P256',
'Màn hình: 6.7" Dynamic AMOLED 2X, QHD+, 120Hz
Chip: Snapdragon 8 Gen 3 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 50MP + 12MP + 10MP | Selfie: 12MP
Pin: 4900mAh, sạc 45W, wireless 15W
IP68, Galaxy AI',
23990000, 21500000, 35, 10, 'samsung-s24-plus.jpg', 'active'),

(2, 3, 'Xiaomi 14 256GB', 'XM14256',
'Màn hình: 6.36" LTPO AMOLED, 120Hz
Chip: Snapdragon 8 Gen 3 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 50MP Leica + 50MP + 50MP | Selfie: 32MP
Pin: 4610mAh, sạc 90W, wireless 50W
IP68, HyperOS, Camera Leica',
17990000, 15500000, 45, 10, 'xiaomi-14.jpg', 'active'),

(2, 3, 'Xiaomi 14T Pro 512GB', 'XM14TP512',
'Màn hình: 6.67" AMOLED, 144Hz
Chip: Dimensity 9300+ | RAM: 12GB | Bộ nhớ: 512GB
Camera: 50MP Leica + 50MP + 12MP | Selfie: 32MP
Pin: 5000mAh, sạc siêu nhanh 120W
IP68, HyperOS, Camera Leica',
15990000, 14000000, 50, 15, 'xiaomi-14t-pro.jpg', 'active'),

(2, 4, 'OPPO Reno12 Pro 5G 512GB', 'OPR12P512',
'Màn hình: 6.7" AMOLED, 120Hz
Chip: Dimensity 9200+ | RAM: 12GB | Bộ nhớ: 512GB
Camera: 50MP + 50MP + 8MP | Selfie: 50MP
Pin: 5000mAh, sạc nhanh 80W
IP65, ColorOS 14, AI Portrait',
14990000, 13000000, 45, 10, 'oppo-reno12-pro.jpg', 'active'),

(2, 5, 'Vivo V40 5G 256GB', 'VVV40256',
'Màn hình: 6.78" AMOLED, 120Hz
Chip: Snapdragon 7 Gen 3 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 50MP ZEISS + 50MP | Selfie: 50MP
Pin: 5500mAh, sạc nhanh 80W
IP68, Camera ZEISS Aura Light',
12990000, 11500000, 50, 15, 'vivo-v40.jpg', 'active'),

(2, 6, 'Realme GT6 256GB', 'RMGT6256',
'Màn hình: 6.78" LTPO AMOLED, 120Hz
Chip: Snapdragon 8s Gen 3 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 50MP + 8MP + 2MP | Selfie: 32MP
Pin: 5500mAh, sạc siêu nhanh 120W
IP65, Gaming mode',
13990000, 12000000, 40, 10, 'realme-gt6.jpg', 'active'),

(2, 9, 'Google Pixel 8 128GB', 'GP8128',
'Màn hình: 6.2" OLED, 60-120Hz
Chip: Google Tensor G3 | RAM: 8GB | Bộ nhớ: 128GB
Camera: 50MP + 12MP | Selfie: 10.5MP
Pin: 4575mAh, sạc 27W, wireless 18W
IP68, 7 năm cập nhật Android, AI Camera',
16990000, 15000000, 30, 10, 'google-pixel-8.jpg', 'active'),

(2, 9, 'Google Pixel 8 Pro 128GB', 'GP8P128',
'Màn hình: 6.7" LTPO OLED, 120Hz
Chip: Google Tensor G3 | RAM: 12GB | Bộ nhớ: 128GB
Camera: 50MP + 48MP + 48MP (5x zoom) | Selfie: 10.5MP
Pin: 5050mAh, sạc 30W, wireless 23W
IP68, 7 năm cập nhật, Temperature sensor',
23990000, 21500000, 25, 5, 'google-pixel-8-pro.jpg', 'active');

-- =================================================================
-- ĐIỆN THOẠI PHỔ THÔNG (category_id = 3)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(3, 2, 'Samsung Galaxy A55 5G 128GB', 'SSA55128',
'Màn hình: 6.6" Super AMOLED, FHD+, 120Hz
Chip: Exynos 1480 | RAM: 8GB | Bộ nhớ: 128GB
Camera: 50MP OIS + 12MP + 5MP | Selfie: 32MP
Pin: 5000mAh, sạc 25W
IP67, One UI 6.1',
9990000, 8500000, 80, 20, 'samsung-a55.jpg', 'active'),

(3, 2, 'Samsung Galaxy A35 5G 128GB', 'SSA35128',
'Màn hình: 6.6" Super AMOLED, FHD+, 120Hz
Chip: Exynos 1380 | RAM: 8GB | Bộ nhớ: 128GB
Camera: 50MP OIS + 8MP + 5MP | Selfie: 13MP
Pin: 5000mAh, sạc 25W
IP67, One UI 6.1',
7490000, 6500000, 90, 25, 'samsung-a35.jpg', 'active'),

(3, 2, 'Samsung Galaxy A15 5G 128GB', 'SSA15128',
'Màn hình: 6.5" Super AMOLED, FHD+, 90Hz
Chip: Dimensity 6100+ | RAM: 8GB | Bộ nhớ: 128GB
Camera: 50MP + 5MP + 2MP | Selfie: 13MP
Pin: 5000mAh, sạc 25W
One UI 6.1',
5490000, 4500000, 100, 30, 'samsung-a15.jpg', 'active'),

(3, 3, 'Redmi Note 14 Pro+ 5G 256GB', 'RN14PP256',
'Màn hình: 6.67" AMOLED, 120Hz
Chip: Snapdragon 7s Gen 3 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 200MP + 8MP + 2MP | Selfie: 20MP
Pin: 5110mAh, sạc siêu nhanh 120W
IP68, Camera 200MP',
9990000, 8500000, 70, 20, 'redmi-note-14-pro-plus.jpg', 'active'),

(3, 3, 'Redmi Note 14 Pro 5G 128GB', 'RN14P128',
'Màn hình: 6.67" AMOLED, 120Hz
Chip: Dimensity 7300 Ultra | RAM: 8GB | Bộ nhớ: 128GB
Camera: 50MP OIS + 8MP + 2MP | Selfie: 20MP
Pin: 5500mAh, sạc 45W
IP64, HyperOS',
7990000, 6800000, 85, 25, 'redmi-note-14-pro.jpg', 'active'),

(3, 3, 'Redmi Note 13 5G 128GB', 'RN13128',
'Màn hình: 6.67" AMOLED, 120Hz
Chip: Dimensity 6080 | RAM: 8GB | Bộ nhớ: 128GB
Camera: 108MP + 8MP + 2MP | Selfie: 16MP
Pin: 5000mAh, sạc 33W
MIUI 14',
5490000, 4500000, 100, 30, 'redmi-note-13.jpg', 'active'),

(3, 3, 'POCO X6 Pro 5G 256GB', 'POCOX6P256',
'Màn hình: 6.67" AMOLED, 120Hz
Chip: Dimensity 8300 Ultra | RAM: 12GB | Bộ nhớ: 256GB
Camera: 64MP + 8MP + 2MP | Selfie: 16MP
Pin: 5000mAh, sạc 67W
IP54, Gaming phone',
8990000, 7800000, 60, 15, 'poco-x6-pro.jpg', 'active'),

(3, 4, 'OPPO A79 5G 128GB', 'OPA79128',
'Màn hình: 6.72" AMOLED, 90Hz
Chip: Dimensity 6020 | RAM: 8GB | Bộ nhớ: 128GB
Camera: 50MP + 2MP | Selfie: 8MP
Pin: 5000mAh, sạc 33W
ColorOS 13',
6490000, 5500000, 80, 20, 'oppo-a79.jpg', 'active'),

(3, 5, 'Vivo Y100 5G 256GB', 'VVY100256',
'Màn hình: 6.67" AMOLED, 120Hz
Chip: Snapdragon 4 Gen 2 | RAM: 8GB | Bộ nhớ: 256GB
Camera: 64MP + 2MP | Selfie: 16MP
Pin: 5000mAh, sạc 44W
Funtouch OS 14',
7490000, 6500000, 70, 20, 'vivo-y100.jpg', 'active'),

(3, 6, 'Realme 12 Pro+ 5G 256GB', 'RM12PP256',
'Màn hình: 6.7" AMOLED cong, 120Hz
Chip: Snapdragon 7s Gen 2 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 50MP + 8MP + 64MP periscope 3x | Selfie: 32MP
Pin: 5000mAh, sạc 67W
Camera periscope 3x',
10990000, 9500000, 50, 15, 'realme-12-pro-plus.jpg', 'active'),

(3, 6, 'Realme C67 128GB', 'RMC67128',
'Màn hình: 6.72" IPS LCD, 90Hz
Chip: Snapdragon 685 | RAM: 8GB | Bộ nhớ: 128GB
Camera: 108MP + 2MP | Selfie: 8MP
Pin: 5000mAh, sạc 33W
Realme UI 5, Camera 108MP giá rẻ',
4290000, 3600000, 100, 30, 'realme-c67.jpg', 'active'),

(3, 7, 'Nokia G42 5G 128GB', 'NKG42128',
'Màn hình: 6.56" IPS LCD, 90Hz
Chip: Snapdragon 480+ | RAM: 6GB | Bộ nhớ: 128GB
Camera: 50MP + 2MP + 2MP | Selfie: 8MP
Pin: 5000mAh, sạc 20W
3 năm cập nhật Android',
5490000, 4700000, 60, 15, 'nokia-g42.jpg', 'active');


-- =================================================================
-- MÁY TÍNH BẢNG (category_id = 4)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(4, 1, 'iPad Pro M4 13 inch 256GB WiFi', 'IPDPM4-13-256',
'Màn hình: 13" Ultra Retina XDR OLED, 120Hz ProMotion
Chip: Apple M4 | RAM: 8GB | Bộ nhớ: 256GB
Camera: 12MP + 10MP + LiDAR | Selfie: 12MP TrueDepth
Pin: 38.99Wh, sạc USB-C 30W
Apple Pencil Pro, Magic Keyboard',
32990000, 29000000, 20, 5, 'ipad-pro-m4-13.jpg', 'active'),

(4, 1, 'iPad Pro M4 11 inch 256GB WiFi', 'IPDPM4-11-256',
'Màn hình: 11" Ultra Retina XDR OLED, 120Hz ProMotion
Chip: Apple M4 | RAM: 8GB | Bộ nhớ: 256GB
Camera: 12MP + 10MP + LiDAR | Selfie: 12MP TrueDepth
Pin: 31.29Wh, sạc USB-C 30W
Apple Pencil Pro, Magic Keyboard',
25990000, 23000000, 25, 5, 'ipad-pro-m4-11.jpg', 'active'),

(4, 1, 'iPad Air M2 13 inch 128GB WiFi', 'IPDAM2-13-128',
'Màn hình: 13" Liquid Retina IPS, 60Hz
Chip: Apple M2 | RAM: 8GB | Bộ nhớ: 128GB
Camera: 12MP Wide | Selfie: 12MP Ultra Wide
Pin: 36.59Wh, sạc USB-C 30W
Apple Pencil Pro, Magic Keyboard',
21990000, 19500000, 30, 10, 'ipad-air-m2-13.jpg', 'active'),

(4, 1, 'iPad Air M2 11 inch 128GB WiFi', 'IPDAM2-11-128',
'Màn hình: 11" Liquid Retina IPS, 60Hz
Chip: Apple M2 | RAM: 8GB | Bộ nhớ: 128GB
Camera: 12MP Wide | Selfie: 12MP Ultra Wide
Pin: 28.93Wh, sạc USB-C 30W
Apple Pencil Pro, Magic Keyboard',
16990000, 15000000, 35, 10, 'ipad-air-m2-11.jpg', 'active'),

(4, 1, 'iPad Gen 10 64GB WiFi', 'IPD10-64',
'Màn hình: 10.9" Liquid Retina IPS, 60Hz
Chip: Apple A14 Bionic | RAM: 4GB | Bộ nhớ: 64GB
Camera: 12MP Wide | Selfie: 12MP Ultra Wide
Pin: 28.6Wh, sạc USB-C 20W
Apple Pencil 1, Magic Keyboard Folio',
10990000, 9500000, 50, 15, 'ipad-gen10.jpg', 'active'),

(4, 1, 'iPad mini 6 64GB WiFi', 'IPDMINI6-64',
'Màn hình: 8.3" Liquid Retina IPS, 60Hz
Chip: Apple A15 Bionic | RAM: 4GB | Bộ nhớ: 64GB
Camera: 12MP Wide | Selfie: 12MP Ultra Wide
Pin: 19.3Wh, sạc USB-C 20W
Apple Pencil 2, nhỏ gọn tiện lợi',
13990000, 12000000, 40, 10, 'ipad-mini-6.jpg', 'active'),

(4, 2, 'Samsung Galaxy Tab S9 Ultra 256GB', 'SSTABS9U-256',
'Màn hình: 14.6" Dynamic AMOLED 2X, 120Hz
Chip: Snapdragon 8 Gen 2 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 13MP + 8MP | Selfie: 12MP + 12MP
Pin: 11200mAh, sạc 45W
S Pen, IP68, DeX mode',
28990000, 26000000, 15, 5, 'samsung-tab-s9-ultra.jpg', 'active'),

(4, 2, 'Samsung Galaxy Tab S9+ 256GB', 'SSTABS9P-256',
'Màn hình: 12.4" Dynamic AMOLED 2X, 120Hz
Chip: Snapdragon 8 Gen 2 | RAM: 12GB | Bộ nhớ: 256GB
Camera: 13MP + 8MP | Selfie: 12MP
Pin: 10090mAh, sạc 45W
S Pen, IP68, DeX mode',
22990000, 20500000, 20, 5, 'samsung-tab-s9-plus.jpg', 'active'),

(4, 2, 'Samsung Galaxy Tab S9 FE 128GB', 'SSTABS9FE-128',
'Màn hình: 10.9" TFT LCD, 90Hz
Chip: Exynos 1380 | RAM: 6GB | Bộ nhớ: 128GB
Camera: 8MP | Selfie: 12MP
Pin: 8000mAh, sạc 45W
S Pen, IP68',
10990000, 9500000, 45, 15, 'samsung-tab-s9-fe.jpg', 'active'),

(4, 3, 'Xiaomi Pad 6 128GB', 'XMPAD6-128',
'Màn hình: 11" IPS LCD, 144Hz
Chip: Snapdragon 870 | RAM: 6GB | Bộ nhớ: 128GB
Camera: 13MP | Selfie: 8MP
Pin: 8840mAh, sạc 33W
Xiaomi Smart Pen 2',
7990000, 7000000, 50, 15, 'xiaomi-pad-6.jpg', 'active'),

(4, 3, 'Redmi Pad SE 128GB', 'RMPADSE-128',
'Màn hình: 11" IPS LCD, 90Hz
Chip: Snapdragon 680 | RAM: 4GB | Bộ nhớ: 128GB
Camera: 8MP | Selfie: 5MP
Pin: 8000mAh, sạc 10W
MIUI Pad 14, giá rẻ',
4990000, 4200000, 60, 20, 'redmi-pad-se.jpg', 'active');

-- =================================================================
-- ĐỒNG HỒ THÔNG MINH (category_id = 5)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(5, 1, 'Apple Watch Series 10 46mm GPS', 'AWS10-46-GPS',
'Màn hình: 1.96" OLED LTPO3
Chip: Apple S10 | Bộ nhớ: 64GB
Pin: Lên đến 18 giờ, sạc nhanh
WR50 (50m), watchOS 11
ECG, SpO2, Nhiệt độ, Phát hiện té ngã',
12990000, 11500000, 40, 10, 'apple-watch-s10.jpg', 'active'),

(5, 1, 'Apple Watch Ultra 2 49mm', 'AWULTRA2-49',
'Màn hình: 1.92" OLED LTPO
Chip: Apple S9 | Bộ nhớ: 64GB
Pin: Lên đến 36 giờ, sạc nhanh
Titanium, 100m WR, watchOS 10
Action Button, GPS 2 tần số, Lặn 40m',
22990000, 20500000, 20, 5, 'apple-watch-ultra-2.jpg', 'active'),

(5, 1, 'Apple Watch SE 2 44mm GPS', 'AWSE2-44-GPS',
'Màn hình: 1.78" OLED LTPO
Chip: Apple S8 | Bộ nhớ: 32GB
Pin: Lên đến 18 giờ, sạc nhanh
WR50 (50m), watchOS 10
Phát hiện té ngã, SOS khẩn cấp',
7490000, 6500000, 50, 15, 'apple-watch-se2.jpg', 'active'),

(5, 2, 'Samsung Galaxy Watch Ultra 47mm', 'SSGWULTRA-47',
'Màn hình: 1.5" Super AMOLED
Chip: Exynos W1000 | Bộ nhớ: 32GB
Pin: 590mAh, lên đến 100 giờ
Titanium, 10ATM + IP68, Wear OS 5
Quick Button, GPS 2 tần số',
14990000, 13000000, 25, 5, 'samsung-watch-ultra.jpg', 'active'),

(5, 2, 'Samsung Galaxy Watch7 44mm', 'SSGW7-44',
'Màn hình: 1.5" Super AMOLED
Chip: Exynos W1000 | Bộ nhớ: 32GB
Pin: 425mAh, sạc nhanh
5ATM + IP68, Wear OS 5
BioActive Sensor, Galaxy AI',
8490000, 7500000, 40, 10, 'samsung-watch7.jpg', 'active'),

(5, 2, 'Samsung Galaxy Watch FE 40mm', 'SSGWFE-40',
'Màn hình: 1.2" Super AMOLED
Chip: Exynos W920 | Bộ nhớ: 16GB
Pin: 247mAh
5ATM + IP68, Wear OS 4
BioActive Sensor, giá tốt',
4990000, 4200000, 60, 20, 'samsung-watch-fe.jpg', 'active'),

(5, 3, 'Xiaomi Watch S3', 'XMWATCHS3',
'Màn hình: 1.43" AMOLED
Chip: BES2700 | Bộ nhớ: 1GB
Pin: 486mAh, lên đến 15 ngày
5ATM, HyperOS
Bezel thay đổi, 150+ chế độ thể thao',
3990000, 3400000, 50, 15, 'xiaomi-watch-s3.jpg', 'active'),

(5, 3, 'Redmi Watch 4', 'RMWATCH4',
'Màn hình: 1.97" AMOLED
Pin: 470mAh, lên đến 20 ngày
5ATM, HyperOS
GPS tích hợp, 150+ chế độ thể thao',
1990000, 1700000, 80, 25, 'redmi-watch-4.jpg', 'active');


-- =================================================================
-- TAI NGHE (category_id = 6)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(6, 1, 'AirPods Pro 2 USB-C', 'APP2-USBC',
'Driver: Custom Apple | Chip: Apple H2
Chống ồn chủ động (ANC), Transparency mode
Pin: 6 giờ (ANC), 30 giờ với hộp sạc
Sạc USB-C, MagSafe, Qi | IPX4
Spatial Audio, Adaptive Audio',
6290000, 5500000, 70, 20, 'airpods-pro-2.jpg', 'active'),

(6, 1, 'AirPods 4 ANC', 'AP4-ANC',
'Driver: Custom Apple | Chip: Apple H2
Chống ồn chủ động (ANC)
Pin: 5 giờ (ANC), 30 giờ với hộp sạc
Sạc USB-C, Wireless | IP54
Spatial Audio, Voice Isolation',
5490000, 4800000, 60, 15, 'airpods-4-anc.jpg', 'active'),

(6, 1, 'AirPods 4', 'AP4',
'Driver: Custom Apple | Chip: Apple H2
Thiết kế mới thoải mái
Pin: 5 giờ, 30 giờ với hộp sạc
Sạc USB-C | IP54
Spatial Audio',
3990000, 3500000, 70, 20, 'airpods-4.jpg', 'active'),

(6, 1, 'AirPods Max USB-C', 'APMAX-USBC',
'Driver: 40mm Apple | Chip: Apple H1
Chống ồn chủ động (ANC), Transparency
Pin: 20 giờ | Sạc USB-C
Khung nhôm cao cấp
Spatial Audio, Digital Crown',
13990000, 12500000, 25, 5, 'airpods-max.jpg', 'active'),

(6, 2, 'Samsung Galaxy Buds3 Pro', 'SSGB3PRO',
'Driver: 10.5mm + 6.1mm 2-way
Chống ồn AI, 360 Audio
Pin: 6 giờ (ANC), 30 giờ với hộp sạc
Sạc USB-C, Wireless | IP57
Blade Lights, Galaxy AI',
5490000, 4800000, 50, 15, 'samsung-buds3-pro.jpg', 'active'),

(6, 2, 'Samsung Galaxy Buds3', 'SSGB3',
'Driver: 11mm dynamic
Chống ồn chủ động
Pin: 5 giờ (ANC), 24 giờ với hộp sạc
Sạc USB-C, Wireless | IP57
360 Audio, Blade Lights',
3990000, 3400000, 60, 20, 'samsung-buds3.jpg', 'active'),

(6, 2, 'Samsung Galaxy Buds FE', 'SSGBFE',
'Driver: 6.5mm + 5.3mm 2-way
Chống ồn chủ động, AKG tuning
Pin: 5 giờ (ANC), 21 giờ với hộp sạc
Sạc USB-C, Wireless | IPX2
360 Audio, giá tốt',
2290000, 1900000, 80, 25, 'samsung-buds-fe.jpg', 'active'),

(6, 8, 'Sony WF-1000XM5', 'SONYWF5',
'Driver: 8.4mm | Chip: V2
Chống ồn hàng đầu thế giới
Pin: 8 giờ (ANC), 24 giờ với hộp sạc
Sạc USB-C, Wireless | IPX4
LDAC, DSEE Extreme, 360 Reality Audio',
6990000, 6200000, 35, 10, 'sony-wf-1000xm5.jpg', 'active'),

(6, 8, 'Sony WH-1000XM5', 'SONYWH5',
'Driver: 30mm carbon fiber
Chống ồn hàng đầu thế giới
Pin: 30 giờ (ANC), sạc nhanh 3 phút = 3 giờ
Sạc USB-C | Headphone over-ear
LDAC, DSEE Extreme, Multipoint',
8490000, 7500000, 30, 10, 'sony-wh-1000xm5.jpg', 'active'),

(6, 3, 'Xiaomi Buds 5 Pro', 'XMB5PRO',
'Driver: 11mm + 10mm planar
Chống ồn 52dB
Pin: 6.5 giờ (ANC), 38 giờ với hộp sạc
Sạc USB-C, Wireless | IP54
LDAC, Hi-Res Audio, Spatial Audio',
3490000, 3000000, 50, 15, 'xiaomi-buds-5-pro.jpg', 'active'),

(6, 3, 'Redmi Buds 5 Pro', 'RMB5PRO',
'Driver: 10mm dynamic
Chống ồn 52dB
Pin: 6.5 giờ (ANC), 28 giờ với hộp sạc
Sạc USB-C | IP54
LDAC, Hi-Res Audio, giá tốt',
1290000, 1100000, 80, 25, 'redmi-buds-5-pro.jpg', 'active');

-- =================================================================
-- SẠC VÀ CÁP (category_id = 7)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(7, 1, 'Củ sạc Apple 20W USB-C', 'APCHARGER20W',
'Công suất: 20W | Cổng: USB-C
Output: 9V/2.22A hoặc 5V/3A
Sạc nhanh iPhone 50% trong 30 phút
Chính hãng Apple',
590000, 450000, 150, 50, 'apple-charger-20w.jpg', 'active'),

(7, 1, 'Củ sạc Apple 35W Dual USB-C', 'APCHARGER35W',
'Công suất: 35W (17.5W x 2) | Cổng: 2x USB-C
Sạc 2 thiết bị cùng lúc
Chính hãng Apple',
1490000, 1200000, 80, 30, 'apple-charger-35w.jpg', 'active'),

(7, 1, 'Cáp USB-C to USB-C 1m Apple', 'APCABLE-CC1M',
'Dài: 1m | Đầu: USB-C to USB-C
Hỗ trợ sạc nhanh 100W
USB 2.0 (480Mbps)
Chính hãng Apple',
590000, 450000, 200, 50, 'apple-cable-usbc.jpg', 'active'),

(7, 2, 'Củ sạc Samsung 25W USB-C', 'SSCHARGER25W',
'Công suất: 25W | Cổng: USB-C
Super Fast Charging
Chính hãng Samsung',
490000, 380000, 150, 50, 'samsung-charger-25w.jpg', 'active'),

(7, 2, 'Củ sạc Samsung 45W USB-C', 'SSCHARGER45W',
'Công suất: 45W | Cổng: USB-C
Super Fast Charging 2.0, PPS
Chính hãng Samsung',
890000, 720000, 100, 30, 'samsung-charger-45w.jpg', 'active'),

(7, 10, 'Anker Nano 3 30W', 'ANKER-NANO3-30W',
'Công suất: 30W | Cổng: USB-C
GaN II, nhỏ gọn
ActiveShield 2.0',
590000, 480000, 100, 30, 'anker-nano3-30w.jpg', 'active'),

(7, 10, 'Anker Prime 67W', 'ANKER-PRIME-67W',
'Công suất: 67W | Cổng: 2x USB-C + 1x USB-A
GaN, sạc laptop và điện thoại
ActiveShield 2.0',
1290000, 1100000, 60, 20, 'anker-prime-67w.jpg', 'active'),

(7, 10, 'Sạc dự phòng Anker 10000mAh', 'ANKER-PB-10K',
'Dung lượng: 10000mAh
Cổng: USB-C + USB-A
Output: 22.5W | Input: 20W
PowerIQ 3.0, nhỏ gọn',
790000, 650000, 100, 30, 'anker-powercore-10k.jpg', 'active'),

(7, 10, 'Sạc dự phòng Anker 20000mAh 65W', 'ANKER-PB-20K',
'Dung lượng: 20000mAh
Cổng: 2x USB-C
Output: 65W | Sạc được laptop
PowerIQ 3.0',
1190000, 980000, 80, 25, 'anker-powercore-20k.jpg', 'active'),

(7, 3, 'Củ sạc Xiaomi 67W GaN', 'XMCHARGER67W',
'Công suất: 67W | Cổng: USB-C
GaN, nhỏ gọn
Xiaomi HyperCharge',
490000, 400000, 120, 40, 'xiaomi-charger-67w.jpg', 'active'),

(7, 3, 'Sạc dự phòng Xiaomi 20000mAh 50W', 'XMPB-20K-50W',
'Dung lượng: 20000mAh
Cổng: USB-C + USB-A
Sạc 2 chiều 50W
Low Current Mode',
690000, 580000, 100, 30, 'xiaomi-powerbank-20k.jpg', 'active');


-- =================================================================
-- ỐP LƯNG VÀ BAO DA (category_id = 8)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(8, 1, 'Ốp lưng iPhone 16 Pro Max Silicone MagSafe', 'OPIP16PM-SIL',
'Chất liệu: Silicone cao cấp
Tương thích: iPhone 16 Pro Max
MagSafe, lót nhung bên trong
Chính hãng Apple
Màu: Đen, Trắng, Xanh, Hồng, Tím',
1290000, 1000000, 100, 30, 'apple-case-silicone.jpg', 'active'),

(8, 1, 'Ốp lưng iPhone 16 Pro Max Clear MagSafe', 'OPIP16PM-CLR',
'Chất liệu: Polycarbonate + TPU
Tương thích: iPhone 16 Pro Max
MagSafe, chống ố vàng
Chính hãng Apple
Trong suốt',
1290000, 1000000, 120, 30, 'apple-case-clear.jpg', 'active'),

(8, 2, 'Ốp lưng Samsung S25 Ultra Clear Standing', 'OPSS25U-CLR',
'Chất liệu: Polycarbonate
Tương thích: Samsung Galaxy S25 Ultra
Có chân đế gập
Chính hãng Samsung
Trong suốt',
790000, 600000, 100, 30, 'samsung-case-clear.jpg', 'active'),

(8, 2, 'Ốp lưng Samsung S25 Ultra Silicone', 'OPSS25U-SIL',
'Chất liệu: Silicone
Tương thích: Samsung Galaxy S25 Ultra
Chống sốc, mềm mại
Chính hãng Samsung
Màu: Đen, Xanh Navy, Kem',
690000, 520000, 120, 30, 'samsung-case-silicone.jpg', 'active'),

(8, 1, 'Smart Folio iPad Pro 13 inch M4', 'BDIPDPRO13-SF',
'Chất liệu: Polyurethane
Tương thích: iPad Pro 13" M4
Bảo vệ 2 mặt, tự động Sleep/Wake
Chính hãng Apple
Màu: Đen, Trắng, Xanh Denim',
2990000, 2500000, 40, 10, 'apple-smartfolio-ipadpro.jpg', 'active'),

(8, 1, 'Smart Folio iPad Air 13 inch M2', 'BDIPDAIR13-SF',
'Chất liệu: Polyurethane
Tương thích: iPad Air 13" M2
Bảo vệ 2 mặt, tự động Sleep/Wake
Chính hãng Apple
Màu: Đen, Xanh Sage, Tím',
2290000, 1900000, 50, 15, 'apple-smartfolio-ipadair.jpg', 'active'),

(8, 2, 'Book Cover Samsung Tab S9 Ultra', 'BDSSTABS9U-BC',
'Chất liệu: Da PU
Tương thích: Samsung Tab S9 Ultra
Chân đế đa góc
Chính hãng Samsung
Màu: Đen',
1890000, 1500000, 35, 10, 'samsung-bookcover-tabs9.jpg', 'active'),

(8, 10, 'Ốp lưng iPhone 16 Pro Max Spigen Ultra Hybrid', 'OPIP16PM-SPIGEN',
'Chất liệu: TPU + Polycarbonate
Tương thích: iPhone 16 Pro Max
Air Cushion, chống ố vàng
Trong suốt, Đen Mờ',
590000, 450000, 150, 40, 'spigen-ultrahybrid.jpg', 'active'),

(8, 10, 'Ốp lưng Samsung S25 Ultra UAG Monarch', 'OPSS25U-UAG',
'Chất liệu: Polycarbonate + TPU + Da
Tương thích: Samsung Galaxy S25 Ultra
MIL-STD-810G, 5 lớp bảo vệ
Chống sốc quân đội
Màu: Đen, Carbon',
1490000, 1200000, 60, 15, 'uag-monarch.jpg', 'active');

-- =================================================================
-- PHỤ KIỆN KHÁC (category_id = 9)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(9, 1, 'Apple Pencil Pro', 'APPENCIL-PRO',
'Tương thích: iPad Pro M4, iPad Air M2
Squeeze gesture, Barrel Roll, Haptic feedback
Sạc từ tính không dây
Find My, Double-tap, Hover
Pin: 12 giờ sử dụng',
3990000, 3500000, 40, 10, 'apple-pencil-pro.jpg', 'active'),

(9, 1, 'Apple Pencil USB-C', 'APPENCIL-USBC',
'Tương thích: iPad Pro, iPad Air, iPad mini, iPad Gen 10
Pixel-perfect precision, Tilt sensitivity
Sạc USB-C
Pin: 12 giờ sử dụng',
2390000, 2100000, 50, 15, 'apple-pencil-usbc.jpg', 'active'),

(9, 1, 'Apple Pencil Gen 2', 'APPENCIL-2',
'Tương thích: iPad Pro 11/12.9 (Gen 3+), iPad Air (Gen 4+), iPad mini 6
Double-tap gesture, Tilt, Pressure sensitivity
Sạc từ tính không dây
Pin: 12 giờ sử dụng',
3590000, 3200000, 45, 10, 'apple-pencil-2.jpg', 'active'),

(9, 2, 'Samsung S Pen Pro', 'SSSPEN-PRO',
'Tương thích: Galaxy S Ultra, Galaxy Tab S, Galaxy Fold
Air Actions, Bluetooth, Sạc được
Sạc USB-C
Pin: 16 ngày standby
Chuyển đổi thiết bị',
2490000, 2100000, 35, 10, 'samsung-spen-pro.jpg', 'active'),

(9, 2, 'Samsung S Pen Fold Edition', 'SSSPEN-FOLD',
'Tương thích: Galaxy Z Fold 5/6
Đầu bút co giãn, bảo vệ màn hình
Không Bluetooth
Thiết kế riêng cho Fold',
1290000, 1000000, 40, 10, 'samsung-spen-fold.jpg', 'active'),

(9, 10, 'Kính cường lực iPhone 16 Pro Max', 'KLIP16PM',
'Độ cứng: 9H
Tương thích: iPhone 16 Pro Max
Full màn hình, chống vân tay
Oleophobic, dày 0.33mm
Dễ dán, không bọt khí',
290000, 180000, 300, 100, 'tempered-glass-iphone.jpg', 'active'),

(9, 10, 'Kính cường lực Samsung S25 Ultra', 'KLSS25U',
'Độ cứng: 9H
Tương thích: Samsung Galaxy S25 Ultra
Full màn hình cong, UV Glue
Hỗ trợ vân tay siêu âm
Dày 0.25mm',
290000, 180000, 250, 80, 'tempered-glass-samsung.jpg', 'active'),

(9, 10, 'Giá đỡ MagSafe Belkin', 'GDDTMS-BELKIN',
'Tương thích: iPhone 12 trở lên (MagSafe)
Sạc không dây 15W
Xoay 360°, điều chỉnh góc
MagSafe chính hãng',
990000, 800000, 60, 20, 'belkin-magsafe-stand.jpg', 'active'),

(9, 10, 'Giá đỡ điện thoại ô tô MagSafe', 'GDDTOTO-MS',
'Tương thích: iPhone 12 trở lên (MagSafe)
Gắn cửa gió điều hòa
Xoay 360°, nam châm N52
Một tay thao tác',
490000, 380000, 100, 30, 'car-mount-magsafe.jpg', 'active'),

(9, 10, 'Gậy selfie Bluetooth tripod', 'GAYSELFIE-BT',
'Dài: 20cm - 100cm
Tripod 3 chân, Bluetooth remote
Tương thích iOS và Android
Remote pin CR2032',
290000, 200000, 120, 40, 'selfie-stick-tripod.jpg', 'active'),

(9, 1, 'Apple AirTag (1 pack)', 'AIRTAG-1',
'Kết nối: Bluetooth, UWB, NFC
Pin: CR2032, thay được, 1 năm
IP67
Precision Finding, Find My network',
890000, 750000, 100, 30, 'apple-airtag.jpg', 'active'),

(9, 1, 'Apple AirTag (4 pack)', 'AIRTAG-4',
'Kết nối: Bluetooth, UWB, NFC
Pin: CR2032, thay được, 1 năm
IP67
Precision Finding, Find My network
Tiết kiệm hơn mua lẻ',
2990000, 2600000, 50, 15, 'apple-airtag-4pack.jpg', 'active'),

(9, 2, 'Samsung Galaxy SmartTag2', 'SSSMARTTAG2',
'Kết nối: Bluetooth 5.3, UWB
Pin: CR2032, thay được, 500 ngày
IP67
Compass View, Lost Mode
SmartThings Find',
690000, 550000, 80, 25, 'samsung-smarttag2.jpg', 'active');

-- =================================================================
-- HOÀN TẤT
-- =================================================================
SELECT 'Đã cập nhật dữ liệu thành công!' as Result;
SELECT c.name as 'Danh mục', COUNT(p.id) as 'Số sản phẩm' 
FROM categories c 
LEFT JOIN products p ON c.id = p.category_id 
GROUP BY c.id, c.name 
ORDER BY c.id;
