-- =================================================================
-- SCRIPT CẬP NHẬT DỮ LIỆU SẢN PHẨM TỪ THEGIOIDIDONG.COM
-- Crawl ngày: 16:58:27 14/1/2026
-- Tổng sản phẩm: 80
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

(1, 1, 'iPhone 17 Pro 256GB', 'IP17PR25100', 
'Hệ điều hành: iOS 26 Chip xử lý (CPU): Apple A19 Pro 6 nhân Tốc độ CPU: Hãng không công bố Chip đồ họa (GPU): Apple GPU 6 nhân Dung lượng lưu trữ: 256 GB Dung lượng còn lại (khả dụng) khoảng: 241 GB', 
34690000, 30527200, 50, 10, 'iphone-17-pro-256gb.jpg', 'active'),

(1, 1, 'iPhone 17 Pro Max 256GB', 'IP17PRMA25101', 
'Hệ điều hành: iOS 26 Chip xử lý (CPU): Apple A19 Pro 6 nhân Tốc độ CPU: Hãng không công bố Chip đồ họa (GPU): Apple GPU 6 nhân Dung lượng lưu trữ: 256 GB Dung lượng còn lại (khả dụng) khoảng: 241 GB', 
37990000, 33431200, 50, 10, 'iphone-17-pro-max-256gb.jpg', 'active'),

(1, 2, 'Samsung Galaxy S25 FE 5G 8GB/128GB', 'SAGAS2FE5G102', 
'Hệ điều hành: Android 16 Chip xử lý (CPU): Exynos 2400 10 nhân Tốc độ CPU: 3.2 GHz Chip đồ họa (GPU): Đang cập nhật Dung lượng lưu trữ: 128 GB Dung lượng còn lại (khả dụng) khoảng: 104 GB', 
14050000, 12364000, 50, 10, 'samsung-galaxy-s25-fe-5g-8gb128gb.jpg', 'active'),

(1, 1, 'iPhone 16 Pro Max 256GB', 'IP16PRMA25103', 
'Hệ điều hành: iOS 18 Chip xử lý (CPU): Apple A18 Pro 6 nhân Tốc độ CPU: Hãng không công bố Chip đồ họa (GPU): Apple GPU 6 nhân Dung lượng lưu trữ: 256 GB Dung lượng còn lại (khả dụng) khoảng: 241 GB', 
31990000, 28151200, 50, 10, 'iphone-16-pro-max-256gb.jpg', 'active'),

(1, 1, 'iPhone 17 256GB', 'IP1725104', 
'Hệ điều hành: iOS 26 Chip xử lý (CPU): Apple A19 6 nhân Tốc độ CPU: Hãng không công bố Chip đồ họa (GPU): Apple GPU 5 nhân Dung lượng lưu trữ: 256 GB Dung lượng còn lại (khả dụng) khoảng: 241 GB', 
24990000, 21991200, 50, 10, 'iphone-17-256gb.jpg', 'active'),

(1, 2, 'Samsung Galaxy S25 Ultra 5G 12GB/256GB', 'SAGAS2UL5G105', 
'Hệ điều hành: Android 15 Chip xử lý (CPU): Qualcomm Snapdragon 8 Elite For Galaxy 8 nhân Tốc độ CPU: 4.47 GHz Chip đồ họa (GPU): Adreno 830 Dung lượng lưu trữ: 256 GB Dung lượng còn lại (khả dụng) khoảng: 222.7 GB', 
29080000, 25590400, 50, 10, 'samsung-galaxy-s25-ultra-5g-12gb256gb.jpg', 'active');


-- =================================================================
-- ĐIỆN THOẠI TẦM TRUNG (category_id = 2)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(2, 4, 'OPPO Reno15 F 5G 8GB/256GB Mẫu mới', 'OPREF5G8GM100', 
'Màn hình: AMOLED 6.57" Full HD+ Hệ điều hành: ColorOS 16 (Android 16) Camera sau: Chính 50 MP & Phụ 8 MP, 2 MP Camera trước: 50 MP Chip: Snapdragon 6 Gen 1 5G 8 nhân Dung lượng lưu trữ: 256 GB', 
11990000, 10551200, 50, 10, 'oppo-reno15-f-5g-8gb256gb-mu-mi.jpg', 'active'),

(2, 2, 'Samsung Galaxy A17 5G 8GB/128GB', 'SAGAA15G8G101', 
'Hệ điều hành: Android 15 Chip xử lý (CPU): Exynos 1330 Tốc độ CPU: 2 nhân 2.4 GHz & 6 nhân 2 GHz Chip đồ họa (GPU): Đang cập nhật Dung lượng lưu trữ: 128 GB Dung lượng còn lại (khả dụng) khoảng: 107 GB', 
6190000, 5447200, 50, 10, 'samsung-galaxy-a17-5g-8gb128gb.jpg', 'active'),

(2, 4, 'OPPO Reno15 5G 8GB/256GB Mẫu mới', 'OPRE5G8GMU102', 
'Màn hình: AMOLED 6.57" Full HD+ Hệ điều hành: ColorOS 16 (Android 16) Camera sau: Chính 50 MP & Phụ 8 MP, 2 MP Camera trước: 50 MP Chip: Snapdragon 6 Gen 1 5G 8 nhân Dung lượng lưu trữ: 256 GB', 
15990000, 14071200, 50, 10, 'oppo-reno15-5g-8gb256gb-mu-mi.jpg', 'active'),

(2, 4, 'OPPO Reno15 Pro 5G 12GB/256GB Mẫu mới', 'OPREPR5G12103', 
'Màn hình: AMOLED 6.57" Full HD+ Hệ điều hành: ColorOS 16 (Android 16) Camera sau: Chính 50 MP & Phụ 8 MP, 2 MP Camera trước: 50 MP Chip: Snapdragon 6 Gen 1 5G 8 nhân Dung lượng lưu trữ: 256 GB', 
18990000, 16711200, 50, 10, 'oppo-reno15-pro-5g-12gb256gb-mu-mi.jpg', 'active'),

(2, 2, 'Samsung Galaxy A07 4GB/64GB', 'SAGAA04G104', 
'Hệ điều hành: Android 15 Chip xử lý (CPU): MediaTek Helio G99 Tốc độ CPU: 2 nhân 2.2 GHz & 6 nhân 2.0 GHz Chip đồ họa (GPU): Đang cập nhật Dung lượng lưu trữ: 64 GB Thẻ nhớ: MicroSD, hỗ trợ tối đa 1.5 TB', 
2950000, 2596000, 50, 10, 'samsung-galaxy-a07-4gb64gb.jpg', 'active');


-- =================================================================
-- ĐIỆN THOẠI PHỔ THÔNG (category_id = 3)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(3, 3, 'Xiaomi Redmi Note 15 Pro 8GB/256GB Mẫu mới', 'XIRENO15PR100', 
'Màn hình: AMOLED 6.77" Full HD+ Hệ điều hành: Xiaomi HyperOS 2 Camera sau: Chính 108 MP & Phụ 2 MP Camera trước: 20 MP Chip: MediaTek Helio G100-Ultra 8 nhân Dung lượng lưu trữ: 128 GB', 
8990000, 7911200, 50, 10, 'xiaomi-redmi-note-15-pro-8gb256gb-mu-mi.jpg', 'active'),

(3, 10, 'vivo Y31d 6GB/128GB Mẫu mới', 'VIY36GMUMI101', 
'Màn hình: LCD 6.75" HD+ Hệ điều hành: Android 16 Camera sau: Chính 50 MP & Phụ 2 MP Camera trước: 8 MP Chip: Snapdragon 6s Gen 2 4G 8 nhân Dung lượng lưu trữ: 128 GB', 
7680000, 6758400, 50, 10, 'vivo-y31d-6gb128gb-mu-mi.jpg', 'active'),

(3, 3, 'Xiaomi Redmi Note 15 Pro+ 5G 12GB/256GB Mẫu mới', 'XIRENO15PR102', 
'Màn hình: AMOLED 6.77" Full HD+ Hệ điều hành: Xiaomi HyperOS 2 Camera sau: Chính 108 MP & Phụ 2 MP Camera trước: 20 MP Chip: MediaTek Helio G100-Ultra 8 nhân Dung lượng lưu trữ: 128 GB', 
12340000, 10859200, 50, 10, 'xiaomi-redmi-note-15-pro-5g-12gb256gb-mu-mi.jpg', 'active'),

(3, 3, 'Xiaomi Redmi Note 15 5G 6GB/128GB Mẫu mới', 'XIRENO155G103', 
'Màn hình: AMOLED 6.77" Full HD+ Hệ điều hành: Xiaomi HyperOS 2 Camera sau: Chính 108 MP & Phụ 2 MP Camera trước: 20 MP Chip: MediaTek Helio G100-Ultra 8 nhân Dung lượng lưu trữ: 128 GB', 
7490000, 6591200, 50, 10, 'xiaomi-redmi-note-15-5g-6gb128gb-mu-mi.jpg', 'active'),

(3, 10, 'realme C85 Pro 8GB/128GB', 'REC8PR8G104', 
'Hệ điều hành: Android 15 Chip xử lý (CPU): Snapdragon 685 8 nhân Tốc độ CPU: 4 nhân 2.8 GHz & 4 nhân 1.9 GHz Chip đồ họa (GPU): Adreno 610 Dung lượng lưu trữ: 128 GB Dung lượng còn lại (khả dụng) khoảng: 97 GB', 
6990000, 6151200, 50, 10, 'realme-c85-pro-8gb128gb.jpg', 'active'),

(3, 10, 'Motorola G06 POWER 4GB/128GB', 'MOG0PO4G105', 
'Hệ điều hành: Android 15 Chip xử lý (CPU): MediaTek Helio G81 Extreme 8 nhân Tốc độ CPU: 2 nhân 2.0 GHz & 6 nhân 1.7 GHz Chip đồ họa (GPU): Mali-G52 MC2 Dung lượng lưu trữ: 128 GB Dung lượng còn lại (khả dụng) khoảng: 115 GB', 
2980000, 2622400, 50, 10, 'motorola-g06-power-4gb128gb.jpg', 'active'),

(3, 10, 'Honor X7d 5G 8GB/256GB', 'HOX75G8G106', 
'Hệ điều hành: Android 15 Chip xử lý (CPU): Snapdragon 6s Gen 3 8 nhân Tốc độ CPU: Đang cập nhật Chip đồ họa (GPU): Đang cập nhật Dung lượng lưu trữ: 256 GB Dung lượng còn lại (khả dụng) khoảng: 220 GB', 
7090000, 6239200, 50, 10, 'honor-x7d-5g-8gb256gb.jpg', 'active'),

(3, 10, 'Tecno Spark 40C 4GB/128GB Mẫu mới', 'TESP404GMU107', 
'Hệ điều hành: Android 15 Chip xử lý (CPU): MediaTek Helio G81 8 nhân Tốc độ CPU: 2 nhân 2.0 GHz & 6 nhân 1.8 GHz Chip đồ họa (GPU): Mali-G52 Dung lượng lưu trữ: 128 GB Dung lượng còn lại (khả dụng) khoảng: 115 GB', 
2890000, 2543200, 50, 10, 'tecno-spark-40c-4gb128gb-mu-mi.jpg', 'active'),

(3, 10, 'Điện thoại định vị trẻ em Masstel Alfa 5', 'INTHNHVTRE108', 
'Hệ điều hành: RTOS Chip xử lý (CPU): ASR3603S Tốc độ CPU: 1 nhân 832 MHz Dung lượng lưu trữ: 24 MB Thẻ nhớ: MicroSD, hỗ trợ tối đa 32 GB Danh bạ: 250 số', 
1390000, 1223200, 50, 10, 'in-thoi-nh-v-tr-em-masstel-alfa-5.jpg', 'active');


-- =================================================================
-- MÁY TÍNH BẢNG (category_id = 4)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(4, 4, 'OPPO Pad 5 8GB/256GB Mẫu mới', 'OPPA58GMUM100', 
'Màn hình: 12.1" LTPS LCD Hệ điều hành: Android 16 Chip: MediaTek Dimensity 7300-Ultra Dung lượng lưu trữ: 256 GB Camera sau: 8 MP Camera trước: 8 MP', 
11790000, 10375200, 50, 10, 'oppo-pad-5-8gb256gb-mu-mi.jpg', 'active'),

(4, 1, 'iPad Pro M5 11 inch WiFi 256GB', 'IPPRM511IN101', 
'Công nghệ màn hình: Ultra Retina XDR Độ phân giải: 1668 x 2420 Pixels Màn hình rộng: 11" - Tần số quét 120 Hz Hệ điều hành: iPadOS 26 Chip xử lý (CPU): Apple M5 9 nhân Tốc độ CPU: Hãng không công bố', 
29790000, 26215200, 50, 10, 'ipad-pro-m5-11-inch-wifi-256gb.jpg', 'active'),

(4, 2, 'Samsung Galaxy Tab A11+ 5G 6GB/128GB', 'SAGATAA15G102', 
'Công nghệ màn hình: TFT LCD Độ phân giải: 1200 x 1920 Pixels Màn hình rộng: 11" - Tần số quét 90 Hz Hệ điều hành: Android 16 Chip xử lý (CPU): MediaTek Dimensity 7300 8 nhân Tốc độ CPU: 4 nhân 2.5 GHz & 4 nhân 2 GHz', 
8190000, 7207200, 50, 10, 'samsung-galaxy-tab-a11-5g-6gb128gb.jpg', 'active'),

(4, 2, 'Samsung Galaxy Tab A11+ WiFi 6GB/128GB', 'SAGATAA1WI103', 
'Công nghệ màn hình: TFT LCD Độ phân giải: 1200 x 1920 Pixels Màn hình rộng: 11" - Tần số quét 90 Hz Hệ điều hành: Android 16 Chip xử lý (CPU): MediaTek Dimensity 7300 8 nhân Tốc độ CPU: 4 nhân 2.5 GHz & 4 nhân 2 GHz', 
6690000, 5887200, 50, 10, 'samsung-galaxy-tab-a11-wifi-6gb128gb.jpg', 'active'),

(4, 4, 'OPPO Pad SE WiFi màn hình nhám 4GB/128GB', 'OPPASEWIMN104', 
'Công nghệ màn hình: IPS LCD Độ phân giải: 1200 x 2000 Pixels Màn hình rộng: 11" - Tần số quét 90 Hz Hệ điều hành: Android 15 Chip xử lý (CPU): MediaTek Helio G100 8 nhân Tốc độ CPU: 2 nhân 2.2 GHz & 6 nhân 2 GHz', 
5890000, 5183200, 50, 10, 'oppo-pad-se-wifi-mn-hnh-nhm-4gb128gb.jpg', 'active'),

(4, 1, 'iPad A16 WiFi 128GB', 'IPA1WI12105', 
'Công nghệ màn hình: Retina IPS LCD Độ phân giải: 1640 x 2360 Pixels Màn hình rộng: 11" - Tần số quét 60 Hz Hệ điều hành: iPadOS 18 Chip xử lý (CPU): Apple A16 5 nhân Tốc độ CPU: Hãng không công bố', 
9490000, 8351200, 50, 10, 'ipad-a16-wifi-128gb.jpg', 'active'),

(4, 1, 'iPad Air M3 11 inch WiFi 128GB', 'IPAIM311IN106', 
'Công nghệ màn hình: Retina IPS LCD Độ phân giải: 1640 x 2360 Pixels Màn hình rộng: 11" - Tần số quét Hãng không công bố Hệ điều hành: iPadOS 18 Chip xử lý (CPU): Apple M3 8 nhân Tốc độ CPU: Hãng không công bố', 
13690000, 12047200, 50, 10, 'ipad-air-m3-11-inch-wifi-128gb.jpg', 'active'),

(4, 1, 'iPad A16 5G 128GB', 'IPA15G12107', 
'Công nghệ màn hình: Retina IPS LCD Độ phân giải: 1640 x 2360 Pixels Màn hình rộng: 11" - Tần số quét 60 Hz Hệ điều hành: iPadOS 18 Chip xử lý (CPU): Apple A16 5 nhân Tốc độ CPU: Hãng không công bố', 
13790000, 12135200, 50, 10, 'ipad-a16-5g-128gb.jpg', 'active'),

(4, 4, 'OPPO Pad Neo WiFi 6GB/128GB', 'OPPANEWI6G108', 
'Công nghệ màn hình: IPS LCD Độ phân giải: 1720 x 2408 Pixels Màn hình rộng: 11.4" - Tần số quét 90 Hz Hệ điều hành: Android 13 Chip xử lý (CPU): MediaTek Helio G99 Tốc độ CPU: 2.2 GHz', 
6070000, 5341600, 50, 10, 'oppo-pad-neo-wifi-6gb128gb.jpg', 'active'),

(4, 2, 'Samsung Galaxy Tab S10 Lite 5G 6GB/128GB', 'SAGATAS1LI109', 
'Công nghệ màn hình: TFT LCD Độ phân giải: 1320 x 2112 Pixels Màn hình rộng: 10.9" - Tần số quét 90 Hz Hệ điều hành: Android 15 Chip xử lý (CPU): Exynos 1380 8 nhân Tốc độ CPU: 4 nhân 2.4 GHz & 4 nhân 2 GHz', 
9990000, 8791200, 50, 10, 'samsung-galaxy-tab-s10-lite-5g-6gb128gb.jpg', 'active'),

(4, 2, 'Samsung Galaxy Tab A11 4G 4GB/64GB', 'SAGATAA14G110', 
'Công nghệ màn hình: TFT LCD Độ phân giải: 800 x 1340 Pixels Màn hình rộng: 8.7" - Tần số quét 90 Hz Hệ điều hành: Android 15 Chip xử lý (CPU): MediaTek Helio G99 Tốc độ CPU: 2.2 GHz', 
5490000, 4831200, 50, 10, 'samsung-galaxy-tab-a11-4g-4gb64gb.jpg', 'active'),

(4, 10, 'HONOR Pad X7 WiFi 4GB/64GB', 'HOPAX7WI4G111', 
'Công nghệ màn hình: IPS LCD Độ phân giải: 800 x 1340 Pixels Màn hình rộng: 8.7" - Tần số quét 90 Hz Hệ điều hành: Android 15 Chip xử lý (CPU): Snapdragon 680 8 nhân Tốc độ CPU: 4 nhân 2.4 GHz & 4 nhân 1.9 GHz', 
3290000, 2895200, 50, 10, 'honor-pad-x7-wifi-4gb64gb.jpg', 'active'),

(4, 2, 'Samsung Galaxy Tab S11 5G 12GB/128GB', 'SAGATAS15G112', 
'Công nghệ màn hình: Dynamic AMOLED 2X Độ phân giải: 1600 x 2560 Pixels Màn hình rộng: 11" - Tần số quét 120 Hz Hệ điều hành: Android 16 Chip xử lý (CPU): MediaTek Dimensity 9400+ 8 nhân Tốc độ CPU: 3.73 GHz', 
22990000, 20231200, 50, 10, 'samsung-galaxy-tab-s11-5g-12gb128gb.jpg', 'active'),

(4, 3, 'Xiaomi Redmi Pad 2 Pro WiFi 6GB/128GB', 'XIREPA2PRW113', 
'Công nghệ màn hình: IPS LCD Độ phân giải: 1600 x 2560 Pixels Màn hình rộng: 12.1" - Tần số quét 120 Hz Hệ điều hành: Xiaomi HyperOS 2 Chip xử lý (CPU): Snapdragon 7s Gen 4 8 nhân Tốc độ CPU: 1 nhân 2.7 GHz, 3 nhân 2.4 GHz & 4 nhân 1.8 GHz', 
7990000, 7031200, 50, 10, 'xiaomi-redmi-pad-2-pro-wifi-6gb128gb.jpg', 'active'),

(4, 10, 'Lenovo Idea Tab 5G 8GB/128GB', 'LEIDTA5G8G114', 
'Công nghệ màn hình: IPS LCD Độ phân giải: 1600 x 2560 Pixels Màn hình rộng: 11" - Tần số quét 90 Hz Hệ điều hành: Android 15 Chip xử lý (CPU): MediaTek Dimensity 6300 8 nhân Tốc độ CPU: 2 nhân 2.4 GHz & 6 nhân 2 GHz', 
7390000, 6503200, 50, 10, 'lenovo-idea-tab-5g-8gb128gb.jpg', 'active'),

(4, 3, 'Xiaomi Redmi Pad 2 WiFi 4GB/128GB', 'XIREPA2WI4115', 
'Công nghệ màn hình: IPS LCD Độ phân giải: 1600 x 2560 Pixels Màn hình rộng: 11" - Tần số quét 90 Hz Hệ điều hành: Xiaomi HyperOS 2 Chip xử lý (CPU): MediaTek Helio G100-Ultra 8 nhân Tốc độ CPU: 2 nhân 2.2 GHz & 6 nhân 2 GHz', 
5200000, 4576000, 50, 10, 'xiaomi-redmi-pad-2-wifi-4gb128gb.jpg', 'active'),

(4, 10, 'Masstel Tab 11 Ultra 4G 4GB/128GB', 'MATA11UL4G116', 
'Công nghệ màn hình: IPS LCD Độ phân giải: 1200 x 1920 Pixels Màn hình rộng: 10.95" - Tần số quét 60 Hz Hệ điều hành: Android 15 Chip xử lý (CPU): Unisoc T606 8 nhân Tốc độ CPU: 1.6 GHz', 
3990000, 3511200, 50, 10, 'masstel-tab-11-ultra-4g-4gb128gb.jpg', 'active'),

(4, 1, 'iPad mini 7 WiFi 128GB', 'IPMI7WI12117', 
'Công nghệ màn hình: LED-backlit IPS LCD Độ phân giải: 1488 x 2266 Pixels Màn hình rộng: 8.3" - Tần số quét Hãng không công bố Hệ điều hành: iPadOS 18 Chip xử lý (CPU): Apple A17 Pro 6 nhân Tốc độ CPU: 3.78 GHz', 
13590000, 11959200, 50, 10, 'ipad-mini-7-wifi-128gb.jpg', 'active'),

(4, 1, 'iPad Air M3 13 inch WiFi 128GB', 'IPAIM313IN118', 
'Công nghệ màn hình: Retina IPS LCD Độ phân giải: 2048 x 2732 Pixels Màn hình rộng: 13" - Tần số quét Hãng không công bố Hệ điều hành: iPadOS 18 Chip xử lý (CPU): Apple M3 8 nhân Tốc độ CPU: Hãng không công bố', 
19490000, 17151200, 50, 10, 'ipad-air-m3-13-inch-wifi-128gb.jpg', 'active'),

(4, 3, 'Xiaomi Pad 7 WiFi 8GB/256GB', 'XIPA7WI8G119', 
'Công nghệ màn hình: IPS LCD Độ phân giải: 2136 x 3200 Pixels Màn hình rộng: 11.2" - Tần số quét 144 Hz Hệ điều hành: Xiaomi HyperOS 2 Chip xử lý (CPU): Snapdragon 7+ Gen 3 8 nhân Tốc độ CPU: 2.8 GHz', 
10490000, 9231200, 50, 10, 'xiaomi-pad-7-wifi-8gb256gb.jpg', 'active');


-- =================================================================
-- ĐỒNG HỒ THÔNG MINH (category_id = 5)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(5, 2, 'Samsung Galaxy Watch8 40mm dây silicone', 'SAGAWA40DY100', 
'Công nghệ màn hình: Super AMOLED Kích thước màn hình: 1.34 inch Độ phân giải: 438 x 438 pixels Kích thước mặt: 40 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Nhôm nguyên khối', 
8190000, 7207200, 50, 10, 'samsung-galaxy-watch8-40mm-dy-silicone.jpg', 'active'),

(5, 2, 'Samsung Galaxy Watch8 LTE 44mm dây silicone', 'SAGAWALT44101', 
'Công nghệ màn hình: Super AMOLED Kích thước màn hình: 1.47 inch Độ phân giải: 480 x 480 pixels Kích thước mặt: 44 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Nhôm nguyên khối', 
10190000, 8967200, 50, 10, 'samsung-galaxy-watch8-lte-44mm-dy-silicone.jpg', 'active'),

(5, 2, 'Samsung Galaxy Watch8 44mm dây silicone', 'SAGAWA44DY102', 
'Công nghệ màn hình: Super AMOLED Kích thước màn hình: 1.47 inch Độ phân giải: 480 x 480 pixels Kích thước mặt: 44 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Nhôm nguyên khối', 
7490000, 6591200, 50, 10, 'samsung-galaxy-watch8-44mm-dy-silicone.jpg', 'active'),

(5, 2, 'Samsung Galaxy Watch8 Classic LTE 46mm dây da', 'SAGAWACLLT103', 
'Công nghệ màn hình: Super AMOLED Kích thước màn hình: 1.34 inch Độ phân giải: 438 x 438 pixels Kích thước mặt: 46 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Thép không gỉ', 
13190000, 11607200, 50, 10, 'samsung-galaxy-watch8-classic-lte-46mm-dy-da.jpg', 'active'),

(5, 3, 'Xiaomi Watch S4 41mm dây Milanese', 'XIWAS441DY104', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.32 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 41 mm Chất liệu mặt: Kính cường lực Chất liệu khung viền: Thép không gỉ', 
5690000, 5007200, 50, 10, 'xiaomi-watch-s4-41mm-dy-milanese.jpg', 'active'),

(5, 10, 'Huawei Watch Ultimate 2 47.8mm dây cao su', 'HUWAUL247D105', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.5 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 47.8 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Mặt trước kim loại lỏng zirconium pha thép - Mặt sau gốm tinh thể nano', 
21990000, 19351200, 50, 10, 'huawei-watch-ultimate-2-478mm-dy-cao-su.jpg', 'active'),

(5, 10, 'Garmin Venu 4 41mm dây silicone', 'GAVE441DYS106', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.2 inch Độ phân giải: 390 x 390 pixels Kích thước mặt: 41 mm Chất liệu mặt: Kính cường lực Gorilla Glass 3 Chất liệu khung viền: Khung Polyme cốt sợi - Viền thép không gỉ', 
14990000, 13191200, 50, 10, 'garmin-venu-4-41mm-dy-silicone.jpg', 'active'),

(5, 10, 'Huawei Watch Ultimate 48.5mm viền gốm dây Titanium', 'HUWAUL48VI107', 
'Công nghệ màn hình: LTPO AMOLED Kích thước màn hình: 1.5 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 48.5 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Khung kim loại lỏng Zirconium - viền gốm tinh thể nano', 
15990000, 14071200, 50, 10, 'huawei-watch-ultimate-485mm-vin-gm-dy-titanium.jpg', 'active'),

(5, 10, 'Garmin Venu 4 45mm dây silicone', 'GAVE445DYS108', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.4 inch Độ phân giải: 454 x 454 pixels Kích thước mặt: 45 mm Chất liệu mặt: Kính cường lực Gorilla Glass 3 Chất liệu khung viền: Khung Polyme cốt sợi - Viền thép không gỉ', 
14990000, 13191200, 50, 10, 'garmin-venu-4-45mm-dy-silicone.jpg', 'active'),

(5, 10, 'Huawei Watch Ultimate 2 48.5mm dây cao su', 'HUWAUL248D109', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.5 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 48.5 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Mặt trước kim loại lỏng zirconium - Mặt sau gốm tinh thể nano', 
17990000, 15831200, 50, 10, 'huawei-watch-ultimate-2-485mm-dy-cao-su.jpg', 'active'),

(5, 10, 'Kidcare Sight S25 44.5mm dây cao su', 'KISIS244DY110', 
'Công nghệ màn hình: TFT Kích thước màn hình: 1.8 inch Độ phân giải: 240 x 296 pixels Kích thước mặt: 44.45 mm Chất liệu mặt: Kính cường lực Chất liệu khung viền: Nhựa', 
1690000, 1487200, 50, 10, 'kidcare-sight-s25-445mm-dy-cao-su.jpg', 'active'),

(5, 10, 'Kidcare K25 42.5mm dây silicone', 'KIK242DYSI111', 
'Công nghệ màn hình: TFT Kích thước màn hình: 1.4 inch Độ phân giải: 240 x 240 pixels Kích thước mặt: 42.5 mm Chất liệu mặt: Kính cường lực Chất liệu khung viền: Nhựa', 
1890000, 1663200, 50, 10, 'kidcare-k25-425mm-dy-silicone.jpg', 'active'),

(5, 10, 'Huawei Watch GT 6 46mm viền thép dây cao su', 'HUWAGT646V112', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.47 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 46 mm Chất liệu mặt: Kính cường lực Chất liệu khung viền: Thép không gỉ', 
4990000, 4391200, 50, 10, 'huawei-watch-gt-6-46mm-vin-thp-dy-cao-su.jpg', 'active'),

(5, 10, 'Huawei Watch GT 6 Pro 46mm viền Titanium dây cao su', 'HUWAGT6PR4113', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.47 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 45.6 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Hợp kim Titanium', 
7690000, 6767200, 50, 10, 'huawei-watch-gt-6-pro-46mm-vin-titanium-dy-cao-su.jpg', 'active'),

(5, 3, 'Xiaomi Watch S4 41mm dây cao su Fluoro', 'XIWAS441DY114', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.32 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 41 mm Chất liệu mặt: Kính cường lực Chất liệu khung viền: Thép không gỉ', 
3790000, 3335200, 50, 10, 'xiaomi-watch-s4-41mm-dy-cao-su-fluoro.jpg', 'active'),

(5, 10, 'Huawei Watch GT 6 46mm viền thép dây da', 'HUWAGT646V115', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.47 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 46 mm Chất liệu mặt: Kính cường lực Chất liệu khung viền: Thép không gỉ', 
5490000, 4831200, 50, 10, 'huawei-watch-gt-6-46mm-vin-thp-dy-da.jpg', 'active'),

(5, 10, 'Huawei Watch GT 6 41mm viền thép dây da', 'HUWAGT641V116', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.32 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 41.3 mm Chất liệu mặt: Kính cường lực Chất liệu khung viền: Thép không gỉ', 
5490000, 4831200, 50, 10, 'huawei-watch-gt-6-41mm-vin-thp-dy-da.jpg', 'active'),

(5, 10, 'Huawei Watch GT 6 Pro 46mm viền Titanium dây Woven', 'HUWAGT6PR4117', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.47 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 45.6 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Hợp kim Titanium', 
8190000, 7207200, 50, 10, 'huawei-watch-gt-6-pro-46mm-vin-titanium-dy-woven.jpg', 'active'),

(5, 2, 'Samsung Galaxy Watch8 Classic 46mm dây da', 'SAGAWACL46118', 
'Công nghệ màn hình: Super AMOLED Kích thước màn hình: 1.34 inch Độ phân giải: 438 x 438 pixels Kích thước mặt: 46 mm Chất liệu mặt: Kính Sapphire Chất liệu khung viền: Thép không gỉ', 
10280000, 9046400, 50, 10, 'samsung-galaxy-watch8-classic-46mm-dy-da.jpg', 'active'),

(5, 10, 'Huawei Watch GT 6 41mm viền thép dây cao su', 'HUWAGT641V119', 
'Công nghệ màn hình: AMOLED Kích thước màn hình: 1.32 inch Độ phân giải: 466 x 466 pixels Kích thước mặt: 41.3 mm Chất liệu mặt: Kính cường lực Chất liệu khung viền: Thép không gỉ', 
4990000, 4391200, 50, 10, 'huawei-watch-gt-6-41mm-vin-thp-dy-cao-su.jpg', 'active');


-- =================================================================
-- TAI NGHE (category_id = 6)
-- =================================================================
INSERT INTO products (category_id, brand_id, name, sku, description, price, cost, quantity, min_quantity, image, status) VALUES

(6, 1, 'AirPods 4', 'AI4100', 
'Thời lượng pin tai nghe: Dùng 5 giờ - Sạc Hãng không công bố Thời lượng pin hộp sạc: Dùng 30 giờ - Sạc Hãng không công bố Cổng sạc: Type-C Công nghệ âm thanh: Voice Isolation Chip Apple H2 Adaptive EQ Tương thích: Android, iOS, Windows macOS (Macbook, iMac) Tiện ích: Trợ lý ảo Siri Chống nước & bụi IP54 Có mic thoại Sạc nhanh', 
3590000, 3159200, 50, 10, 'airpods-4.jpg', 'active'),

(6, 2, 'Tai nghe TWS Samsung Galaxy Buds Core R410N', 'TANGTWSAGA101', 
'Thời lượng pin tai nghe: Dùng Khoảng 8.5 giờ (khi tắt ANC) - Sạc Khoảng 1.7 giờ Thời lượng pin hộp sạc: Dùng 35 giờ - Sạc 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Active Noise Cancellation Công nghệ ENC Ambient Sound Tương thích: Thiết bị Android phiên bản 8.0 trở lên iOS (iPhone) macOS (Macbook, iMac) Windows Ứng dụng kết nối: Galaxy Wearable', 
1190000, 1047200, 50, 10, 'tai-nghe-tws-samsung-galaxy-buds-core-r410n.jpg', 'active'),

(6, 1, 'AirPods Pro 3', 'AIPR3102', 
'Thời lượng pin tai nghe: Dùng Khoảng 8 giờ (khi bật ANC) - Sạc Hãng không công bố Thời lượng pin hộp sạc: Dùng 32 giờ - Sạc Hãng không công bố Cổng sạc: Sạc MagSafe Type-C Tương thích: watchOS visionOS tvOS iOS (iPhone) iPadOS (iPad) Kết nối cùng lúc: 1 thiết bị Công nghệ kết nối: Bluetooth 5.3', 
6790000, 5975200, 50, 10, 'airpods-pro-3.jpg', 'active'),

(6, 2, 'Tai nghe TWS Samsung Galaxy Buds3 FE R420N', 'TANGTWSAGA103', 
'Thời lượng pin tai nghe: Dùng Khoảng 8.5 giờ (khi tắt ANC) - Sạc Khoảng 1.7 giờ Thời lượng pin hộp sạc: Dùng 30 giờ - Sạc Khoảng 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Active Noise Cancelling Adaptive EQ Ambient Sound 360 Reality Audio Tương thích: macOS Thiết bị Android phiên bản 8.0 trở lên iOS (iPhone) Windows Ứng dụng kết nối: Galaxy Wearable', 
2990000, 2631200, 50, 10, 'tai-nghe-tws-samsung-galaxy-buds3-fe-r420n.jpg', 'active'),

(6, 4, 'Tai nghe TWS OPPO ENCO Buds 3 ETEG1', 'TANGTWOPEN104', 
'Thời lượng pin tai nghe: Dùng Khoảng 9.5 giờ - Sạc Khoảng 1 giờ Thời lượng pin hộp sạc: Dùng 48 giờ - Sạc Khoảng 1.3 giờ Cổng sạc: Type-C Công nghệ âm thanh: Dynamic Driver 12.4 mm Enco Master Âm thanh vòm OPPO Alive Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: HeyMelody App', 
890000, 783200, 50, 10, 'tai-nghe-tws-oppo-enco-buds-3-eteg1.jpg', 'active'),

(6, 1, 'AirPods 4 (chống ồn)', 'AI4CHN105', 
'Thời lượng pin tai nghe: Dùng 5 giờ - Sạc Hãng không công bố Thời lượng pin hộp sạc: Dùng 30 giờ - Sạc Hãng không công bố Cổng sạc: Sạc MagSafe Type-C Công nghệ âm thanh: Voice Isolation Adaptive Audio Active Noise Cancellation Chip Apple H2 Adaptive EQ Transparency Mode Ambient Sound Tương thích: Android, iOS, Windows macOS (Macbook, iMac) Tiện ích: Sạc không dây Trợ lý ảo Siri Chống nước & bụi IP54 Có mic thoại Sạc nhanh Chống ồn', 
4790000, 4215200, 50, 10, 'airpods-4-chng-n.jpg', 'active'),

(6, 1, 'AirPods Pro Gen 2 (USB-C)', 'AIPRGE2US106', 
'Thời lượng pin tai nghe: Dùng 6 giờ - Sạc Hãng không công bố Thời lượng pin hộp sạc: Dùng 30 giờ - Sạc Hãng không công bố Cổng sạc: Sạc MagSafe Type-C Công nghệ âm thanh: Active Noise Cancellation Chip Apple H2 Adaptive EQ Ambient Sound Tương thích: Android, iOS, Windows macOS (Macbook, iMac) Tiện ích: Sạc không dây Trợ lý ảo Siri Chống nước & bụi IP54 Có mic thoại Sạc nhanh Chống ồn chủ động ANC', 
4990000, 4391200, 50, 10, 'airpods-pro-gen-2-usb-c.jpg', 'active'),

(6, 10, 'Tai nghe Open-Ear OWS Soundcore AeroFit 2 A3874 Mẫu mới', 'TANGOPOWSO107', 
'Thời lượng pin tai nghe: Dùng 10 giờ - Sạc Khoảng 1.5 giờ Thời lượng pin hộp sạc: Dùng 42 giờ - Sạc Khoảng 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Âm thanh 3D BassTurbo codec LDAC Hires Audio Driver 20 x 11.5 mm Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: Soundcore', 
1890000, 1663200, 50, 10, 'tai-nghe-open-ear-ows-soundcore-aerofit-2-a3874-mu.jpg', 'active'),

(6, 10, 'Tai nghe TWS Soundcore P41i A3937 Mẫu mới', 'TANGTWSOP4108', 
'Thời lượng pin tai nghe: Dùng 12 giờ (khi tắt ANC) - Sạc 1.5 giờ Thời lượng pin hộp sạc: Dùng 192 giờ (khi tắt ANC) - Sạc 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Driver 11 mm Adaptive Active Noise Cancellation Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: Soundcore', 
1390000, 1223200, 50, 10, 'tai-nghe-tws-soundcore-p41i-a3937-mu-mi.jpg', 'active'),

(6, 10, 'Tai nghe Chụp Tai Soundcore Space One Pro A3062 Mẫu mới', 'TANGCHTASO109', 
'Thời lượng pin tai nghe: Dùng 60 giờ (khi tắt ANC) - Sạc 1.5 giờ Cổng sạc: Type-C Công nghệ âm thanh: Active Noise Cancellation Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: Soundcore Tiện ích: Tùy chỉnh EQ và cá nhân hóa âm thanh qua ứng dụng 4 Micro khử ồn AI Kết nối đa điểm', 
3290000, 2895200, 50, 10, 'tai-nghe-chp-tai-soundcore-space-one-pro-a3062-mu-.jpg', 'active'),

(6, 10, 'Tai nghe TWS Soundcore R60i NC D1202 Mẫu mới', 'TANGTWSOR6110', 
'Thời lượng pin tai nghe: Dùng 10 giờ - Sạc Khoảng 1.5 giờ Thời lượng pin hộp sạc: Dùng 50 giờ - Sạc Khoảng 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Active Noise Cancellation Hi-Res Audio Công nghệ ENC codec LDAC Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: Soundcore', 
760000, 668800, 50, 10, 'tai-nghe-tws-soundcore-r60i-nc-d1202-mu-mi.jpg', 'active'),

(6, 3, 'Tai nghe TWS Xiaomi Redmi Buds 8 Lite Mẫu mới', 'TANGTWXIRE111', 
'Thời lượng pin tai nghe: Dùng Khoảng 8 giờ (khi tắt ANC) - Sạc Khoảng 1.5 giờ Thời lượng pin hộp sạc: Dùng 36 giờ - Sạc Khoảng 1.5 giờ Cổng sạc: Type-C Công nghệ âm thanh: EQ codec SBC Driver 12.4 mm codec AAC Hybrid ANC Tương thích: Các thiết bị có kết nối Bluetooth trong phạm vi 10m Ứng dụng kết nối: Xiaomi Earbuds', 
790000, 695200, 50, 10, 'tai-nghe-tws-xiaomi-redmi-buds-8-lite-mu-mi.jpg', 'active'),

(6, 4, 'Tai nghe TWS OPPO ENCO Buds 3 Pro ETEK1', 'TANGTWOPEN112', 
'Thời lượng pin tai nghe: Dùng 12 giờ - Sạc Khoảng 1 giờ Thời lượng pin hộp sạc: Dùng 54 giờ - Sạc Khoảng 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Personalized EQ Dynamic Driver 12.4 mm Công nghệ ENC Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: HeyMelody App', 
970000, 853600, 50, 10, 'tai-nghe-tws-oppo-enco-buds-3-pro-etek1.jpg', 'active'),

(6, 10, 'Tai nghe TWS Ugreen HiTune T3 Pro WS206 35725 Mẫu mới', 'TANGTWUGHI113', 
'Thời lượng pin tai nghe: Dùng 7.5 giờ - Sạc 1.5 giờ Thời lượng pin hộp sạc: Dùng 30 giờ - Sạc 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Chống ồn chủ động 4 mic - chống ồn cuộc gọi thông minh Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: UGREEN App', 
590000, 519200, 50, 10, 'tai-nghe-tws-ugreen-hitune-t3-pro-ws206-35725-mu-m.jpg', 'active'),

(6, 10, 'Tai nghe Chụp Tai Ugreen Studio Max2 HP205 Mẫu mới', 'TANGCHTAUG114', 
'Thời lượng pin tai nghe: Dùng 80 giờ - Sạc 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: ANC -35dB 2 Mics - giảm tiếng ồn cuộc gọi AI (ENC AI) Driver 40 mm Tương thích: macOS Android, iOS, Windows Tiện ích: Điều chỉnh EQ Game Mode Sạc nhanh Kết nối cùng lúc: 2 thiết bị', 
390000, 343200, 50, 10, 'tai-nghe-chp-tai-ugreen-studio-max2-hp205-mu-mi.jpg', 'active'),

(6, 10, 'Tai nghe Chụp Tai JBL Tune 520BT', 'TANGCHTAJB115', 
'Thời lượng pin tai nghe: Dùng 57 giờ - Sạc 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: JBL Pure Bass Sound Kích thước driver: 33 mm Tương thích: macOS Android iOS (iPhone) Windows Ứng dụng kết nối: JBL Headphones Tiện ích: Có mic thoại Sạc nhanh Tương thích trợ lý ảo', 
990000, 871200, 50, 10, 'tai-nghe-chp-tai-jbl-tune-520bt.jpg', 'active'),

(6, 4, 'Tai nghe TWS OPPO Enco X3s ETED1', 'TANGTWOPEN116', 
'Thời lượng pin tai nghe: Dùng 11 giờ - Sạc 50 Phút Thời lượng pin hộp sạc: Dùng 45 giờ - Sạc Khoảng 80 phút Cổng sạc: Type-C Công nghệ âm thanh: LHDC 5.0 codec Hi-Res Wireless Âm thanh vòm OPPO Alive Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: HeyMelody App', 
2890000, 2543200, 50, 10, 'tai-nghe-tws-oppo-enco-x3s-eted1.jpg', 'active'),

(6, 10, 'Tai nghe TWS Ugreen HiTune P3 WS207 45110 Mẫu mới', 'TANGTWUGHI117', 
'Thời lượng pin tai nghe: Dùng 5.5 giờ - Sạc 1.5 giờ Thời lượng pin hộp sạc: Dùng 28 giờ - Sạc 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Chống ồn cuộc gọi thông minh (ENC AI) Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: UGREEN App', 
360000, 316800, 50, 10, 'tai-nghe-tws-ugreen-hitune-p3-ws207-45110-mu-mi.jpg', 'active'),

(6, 10, 'Tai nghe TWS Ugreen EchoBuds Magic WS211 55137 Mẫu mới', 'TANGTWUGEC118', 
'Thời lượng pin tai nghe: Dùng 6 giờ (âm lượng 60%) - Sạc Khoảng 1.5 giờ Thời lượng pin hộp sạc: Dùng 30 giờ - Sạc Khoảng 2 giờ Cổng sạc: Type-C Công nghệ âm thanh: Active Noise Cancellation Hi-Res Audio Công nghệ ENC Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: UGREEN App', 
805000, 708400, 50, 10, 'tai-nghe-tws-ugreen-echobuds-magic-ws211-55137-mu-.jpg', 'active'),

(6, 10, 'Tai nghe Chụp Tai Ugreen HiTune Max5c HP203 Mẫu mới', 'TANGCHTAUG119', 
'Thời lượng pin tai nghe: Dùng 75 giờ - Sạc 1.5 giờ Cổng sạc: Type-C Công nghệ âm thanh: Chống ồn chủ động Hi-Res Audio 4 mic - chống ồn cuộc gọi thông minh Tương thích: macOS Android, iOS, Windows Ứng dụng kết nối: UGREEN App Tiện ích: Game Mode Sạc nhanh', 
470000, 413600, 50, 10, 'tai-nghe-chp-tai-ugreen-hitune-max5c-hp203-mu-mi.jpg', 'active');


-- =================================================================
-- HOÀN TẤT
-- =================================================================
SELECT 'Đã cập nhật dữ liệu thành công!' as Result;
SELECT c.name as 'Danh mục', COUNT(p.id) as 'Số sản phẩm' 
FROM categories c 
LEFT JOIN products p ON c.id = p.category_id 
GROUP BY c.id, c.name 
ORDER BY c.id;
