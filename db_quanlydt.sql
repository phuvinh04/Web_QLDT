-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 10, 2025 lúc 02:32 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `db_quanlydienthoai`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `brands`
--

INSERT INTO `brands` (`id`, `name`, `logo`, `description`, `created_at`) VALUES
(1, 'Apple', 'apple.png', 'Thương hiệu Apple - iPhone, iPad, Mac', '2025-12-10 01:20:50'),
(2, 'Samsung', 'samsung.png', 'Thương hiệu Samsung - Galaxy series', '2025-12-10 01:20:50'),
(3, 'Xiaomi', 'xiaomi.png', 'Thương hiệu Xiaomi - Redmi, POCO', '2025-12-10 01:20:50'),
(4, 'OPPO', 'oppo.png', 'Thương hiệu OPPO - Reno, Find series', '2025-12-10 01:20:50'),
(5, 'Vivo', 'vivo.png', 'Thương hiệu Vivo', '2025-12-10 01:20:50'),
(6, 'Realme', 'realme.png', 'Thương hiệu Realme', '2025-12-10 01:20:50'),
(7, 'Nokia', 'nokia.png', 'Thương hiệu Nokia', '2025-12-10 01:20:50'),
(8, 'Tecno', 'tecno.png', 'Thương hiệu Tecno', '2025-12-10 01:20:50'),
(9, 'Lenovo', 'lenovo.png', 'Thương hiệu Lenovo', '2025-12-10 01:20:50'),
(10, 'Anker', 'anker.png', 'Thương hiệu Anker - Phụ kiện', '2025-12-10 01:20:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Điện thoại cao cấp', 'Điện thoại flagship cao cấp', '2025-12-10 01:15:21', '2025-12-10 01:15:21'),
(2, 'Điện thoại tầm trung', 'Điện thoại tầm trung giá tốt', '2025-12-10 01:15:21', '2025-12-10 01:15:21'),
(3, 'Điện thoại giá rẻ', 'Điện thoại phổ thông giá rẻ', '2025-12-10 01:15:21', '2025-12-10 01:15:21'),
(4, 'Máy tính bảng', 'Tablet các loại', '2025-12-10 01:15:21', '2025-12-10 01:15:21'),
(5, 'Phụ kiện', 'Phụ kiện điện thoại', '2025-12-10 01:15:21', '2025-12-10 01:15:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `total_purchases` decimal(15,2) DEFAULT 0.00,
  `purchase_count` int(11) DEFAULT 0,
  `loyalty_points` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`, `city`, `total_purchases`, `purchase_count`, `loyalty_points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn A', '0912345678', 'nguyenvana@gmail.com', '123 Nguyễn Trãi', NULL, 4290000.00, 1, 0, 'active', '2025-12-10 00:58:54', '2025-12-10 01:28:59'),
(2, 'Trần Thị B', '0987654321', 'tranthib@gmail.com', '456 Lê Lợi', NULL, 0.00, 0, 0, 'active', '2025-12-10 00:58:54', '2025-12-10 00:58:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `discount` decimal(15,2) DEFAULT 0.00,
  `tax` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','card','transfer','cod') DEFAULT 'cash',
  `status` enum('pending','completed','cancelled','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `user_id`, `subtotal`, `discount`, `tax`, `total_amount`, `payment_method`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD202512109177', 1, 1, 4290000.00, 0.00, 0.00, 4290000.00, 'cash', 'completed', '', '2025-12-10 01:28:59', '2025-12-10 01:28:59');

--
-- Bẫy `orders`
--
DELIMITER $$
CREATE TRIGGER `after_order_completed` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' AND NEW.customer_id IS NOT NULL THEN
        UPDATE `customers`
        SET
            `total_purchases` = `total_purchases` + NEW.total_amount,
            `purchase_count` = `purchase_count` + 1
        WHERE `id` = NEW.customer_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `discount_applied` decimal(12,2) DEFAULT 0.00,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`quantity` * `unit_price` - `discount_applied`) STORED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `discount_applied`, `created_at`) VALUES
(1, 1, 168, 1, 4290000.00, 0.00, '2025-12-10 01:28:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `cost` decimal(12,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `min_quantity` int(11) DEFAULT 10,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `sku`, `description`, `price`, `cost`, `quantity`, `min_quantity`, `image`, `status`, `created_at`, `updated_at`) VALUES
(123, 1, 1, 'iPhone 15 Pro Max 256GB', 'IP15PM256', 'iPhone 15 Pro Max chính hãng Apple', 32990000.00, 28000000.00, 50, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(124, 1, 1, 'iPhone 15 Pro Max 512GB', 'IP15PM512', 'iPhone 15 Pro Max 512GB chính hãng', 38990000.00, 33000000.00, 30, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(125, 1, 1, 'iPhone 15 Pro 256GB', 'IP15P256', 'iPhone 15 Pro chính hãng Apple', 28990000.00, 24000000.00, 45, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(126, 1, 1, 'iPhone 15 Plus 256GB', 'IP15PL256', 'iPhone 15 Plus chính hãng', 24990000.00, 21000000.00, 40, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(127, 1, 1, 'iPhone 14 Pro Max 256GB', 'IP14PM256', 'iPhone 14 Pro Max chính hãng', 26990000.00, 22000000.00, 35, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(128, 1, 2, 'Samsung Galaxy S24 Ultra 256GB', 'SS24U256', 'Samsung Galaxy S24 Ultra flagship', 31990000.00, 27000000.00, 40, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(129, 1, 2, 'Samsung Galaxy S24 Ultra 512GB', 'SS24U512', 'Samsung Galaxy S24 Ultra 512GB', 36990000.00, 31000000.00, 25, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(130, 1, 2, 'Samsung Galaxy S24+ 256GB', 'SS24P256', 'Samsung Galaxy S24 Plus', 24990000.00, 21000000.00, 35, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(131, 1, 2, 'Samsung Galaxy Z Fold5 256GB', 'SSZF5256', 'Samsung Galaxy Z Fold5 màn gập', 41990000.00, 35000000.00, 20, 5, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(132, 1, 2, 'Samsung Galaxy Z Flip5 256GB', 'SSZFL5256', 'Samsung Galaxy Z Flip5 màn gập', 25990000.00, 22000000.00, 30, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(133, 1, 4, 'OPPO Find X7 Ultra', 'OPFX7U', 'OPPO Find X7 Ultra cao cấp', 23990000.00, 20000000.00, 25, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(134, 1, 3, 'Xiaomi 14 Ultra', 'XI14U', 'Xiaomi 14 Ultra flagship', 24990000.00, 21000000.00, 30, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(135, 2, 1, 'iPhone 14 128GB', 'IP14128', 'iPhone 14 chính hãng', 18990000.00, 16000000.00, 60, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(136, 2, 1, 'iPhone 13 128GB', 'IP13128', 'iPhone 13 chính hãng', 15990000.00, 13000000.00, 55, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(137, 2, 2, 'Samsung Galaxy S24 256GB', 'SS24256', 'Samsung Galaxy S24 tầm trung cao cấp', 21990000.00, 18000000.00, 50, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(138, 2, 2, 'Samsung Galaxy A55 5G', 'SSA555G', 'Samsung Galaxy A55 5G', 10990000.00, 9000000.00, 70, 15, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(139, 2, 2, 'Samsung Galaxy A54 5G', 'SSA545G', 'Samsung Galaxy A54 5G', 9490000.00, 8000000.00, 65, 15, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(140, 2, 4, 'OPPO Reno11 5G', 'OPRN115G', 'OPPO Reno11 5G', 10990000.00, 9000000.00, 45, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(141, 2, 4, 'OPPO Reno10 Pro+ 5G', 'OPRN10P5G', 'OPPO Reno10 Pro Plus 5G', 13990000.00, 11000000.00, 40, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(142, 2, 3, 'Xiaomi 14', 'XI14', 'Xiaomi 14 tầm trung cao cấp', 15990000.00, 13000000.00, 50, 10, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(143, 2, 3, 'Xiaomi Redmi Note 13 Pro+ 5G', 'XIRN13P5G', 'Redmi Note 13 Pro Plus 5G', 9990000.00, 8000000.00, 80, 15, NULL, 'active', '2025-12-10 01:15:21', '2025-12-10 01:20:50'),
(144, 2, 3, 'Xiaomi Redmi Note 13 Pro', 'XIRN13P', 'Redmi Note 13 Pro', 7990000.00, 6500000.00, 90, 15, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(145, 2, 5, 'Vivo V30 5G', 'VIV305G', 'Vivo V30 5G camera đẹp', 10990000.00, 9000000.00, 40, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(146, 2, 6, 'Realme GT5 Pro', 'RLGT5P', 'Realme GT5 Pro hiệu năng cao', 13990000.00, 11000000.00, 35, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(147, 3, 2, 'Samsung Galaxy A15', 'SSA15', 'Samsung Galaxy A15 giá rẻ', 4990000.00, 4000000.00, 100, 20, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(148, 3, 2, 'Samsung Galaxy A05s', 'SSA05S', 'Samsung Galaxy A05s phổ thông', 3490000.00, 2800000.00, 120, 25, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(149, 3, 4, 'OPPO A58 4G', 'OPA584G', 'OPPO A58 4G giá tốt', 4990000.00, 4000000.00, 80, 15, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(150, 3, 4, 'OPPO A18', 'OPA18', 'OPPO A18 giá rẻ', 3490000.00, 2800000.00, 100, 20, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(151, 3, 3, 'Xiaomi Redmi 13C', 'XIR13C', 'Redmi 13C giá rẻ', 3290000.00, 2600000.00, 150, 25, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(152, 3, 3, 'Xiaomi Redmi A3', 'XIRA3', 'Redmi A3 siêu rẻ', 2490000.00, 2000000.00, 200, 30, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(153, 3, 5, 'Vivo Y17s', 'VIY17S', 'Vivo Y17s phổ thông', 3990000.00, 3200000.00, 90, 15, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(154, 3, 6, 'Realme C67', 'RLC67', 'Realme C67 giá tốt', 4490000.00, 3600000.00, 85, 15, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(155, 3, 7, 'Nokia G42 5G', 'NOG425G', 'Nokia G42 5G bền bỉ', 4990000.00, 4000000.00, 60, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(156, 3, 8, 'Tecno Spark 20 Pro+', 'TCS20PP', 'Tecno Spark 20 Pro Plus', 3990000.00, 3200000.00, 70, 15, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(157, 4, 1, 'iPad Pro M4 11 inch 256GB', 'IPDPM4256', 'iPad Pro M4 11 inch mới nhất', 28990000.00, 25000000.00, 25, 5, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(158, 4, 1, 'iPad Pro M4 13 inch 256GB', 'IPDPM413256', 'iPad Pro M4 13 inch', 35990000.00, 31000000.00, 20, 5, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(159, 4, 1, 'iPad Air M2 11 inch 128GB', 'IPDAM2128', 'iPad Air M2 11 inch', 16990000.00, 14000000.00, 35, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(160, 4, 1, 'iPad Gen 10 64GB', 'IPD1064', 'iPad Gen 10 phổ thông', 10990000.00, 9000000.00, 50, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(161, 4, 1, 'iPad Mini 6 64GB', 'IPDM664', 'iPad Mini 6 nhỏ gọn', 13990000.00, 11000000.00, 30, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(162, 4, 2, 'Samsung Galaxy Tab S9 Ultra', 'SSTS9U', 'Samsung Galaxy Tab S9 Ultra', 27990000.00, 24000000.00, 20, 5, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(163, 4, 2, 'Samsung Galaxy Tab S9+', 'SSTS9P', 'Samsung Galaxy Tab S9 Plus', 22990000.00, 19000000.00, 25, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(164, 4, 2, 'Samsung Galaxy Tab A9+', 'SSTA9P', 'Samsung Galaxy Tab A9 Plus', 7990000.00, 6500000.00, 60, 15, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(165, 4, 3, 'Xiaomi Pad 6', 'XIP6', 'Xiaomi Pad 6 giá tốt', 8990000.00, 7500000.00, 45, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(166, 4, 9, 'Lenovo Tab P12', 'LNTP12', 'Lenovo Tab P12', 9990000.00, 8000000.00, 40, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(167, 5, 1, 'AirPods Pro 2', 'APDP2', 'AirPods Pro 2 chống ồn', 6290000.00, 5000000.00, 80, 15, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(168, 5, 1, 'AirPods 3', 'APD3', 'AirPods 3 chính hãng', 4290000.00, 3500000.00, 99, 20, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:28:59'),
(169, 5, 2, 'Samsung Galaxy Buds3 Pro', 'SSGB3P', 'Samsung Galaxy Buds3 Pro', 4990000.00, 4000000.00, 60, 15, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(170, 5, 1, 'Apple Watch Series 9 GPS 41mm', 'AWS941', 'Apple Watch Series 9 GPS', 10990000.00, 9000000.00, 40, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(171, 5, 1, 'Apple Watch SE 2 GPS 40mm', 'AWSE240', 'Apple Watch SE 2 GPS', 6490000.00, 5200000.00, 50, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(172, 5, 2, 'Samsung Galaxy Watch6 44mm', 'SSGW644', 'Samsung Galaxy Watch6', 7490000.00, 6000000.00, 45, 10, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(173, 5, 1, 'Sạc nhanh Apple 20W', 'APCH20W', 'Củ sạc Apple 20W chính hãng', 490000.00, 350000.00, 200, 30, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(174, 5, 2, 'Sạc nhanh Samsung 25W', 'SSCH25W', 'Củ sạc Samsung 25W', 390000.00, 280000.00, 180, 30, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(175, 5, 1, 'Cáp Lightning Apple 1m', 'APLC1M', 'Cáp Lightning Apple chính hãng', 490000.00, 350000.00, 250, 40, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(176, 5, 2, 'Cáp USB-C Samsung 1m', 'SSUC1M', 'Cáp USB-C Samsung', 290000.00, 200000.00, 300, 50, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(177, 5, 1, 'Ốp lưng iPhone 15 Pro Max MagSafe', 'OLIP15PMM', 'Ốp lưng MagSafe chính hãng', 1290000.00, 900000.00, 100, 20, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(178, 5, 2, 'Ốp lưng Samsung S24 Ultra', 'OLSS24U', 'Ốp lưng Samsung S24 Ultra', 590000.00, 400000.00, 120, 25, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(179, 5, 1, 'Kính cường lực iPhone 15 Series', 'KCIP15', 'Kính cường lực iPhone 15', 150000.00, 80000.00, 500, 100, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(180, 5, 2, 'Kính cường lực Samsung S24', 'KCSS24', 'Kính cường lực Samsung S24', 120000.00, 60000.00, 450, 100, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(181, 5, 10, 'Pin dự phòng Anker 10000mAh', 'AK10K', 'Pin sạc dự phòng Anker', 590000.00, 450000.00, 150, 30, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50'),
(182, 5, 3, 'Pin dự phòng Xiaomi 20000mAh', 'XI20K', 'Pin sạc dự phòng Xiaomi', 490000.00, 380000.00, 180, 30, NULL, 'active', '2025-12-10 01:15:22', '2025-12-10 01:20:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('fixed','percent') NOT NULL,
  `discount_value` decimal(12,2) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `min_amount` decimal(12,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Tên vai trò (admin, manager, sales, warehouse)',
  `description` text DEFAULT NULL COMMENT 'Mô tả chi tiết quyền hạn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Quản trị viên - Tất cả quyền'),
(2, 'manager', 'Quản lý cửa hàng'),
(3, 'sales', 'Nhân viên bán hàng'),
(4, 'warehouse', 'Nhân viên kho');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `stock_movements`
--
DELIMITER $$
CREATE TRIGGER `after_stock_movement_insert` AFTER INSERT ON `stock_movements` FOR EACH ROW BEGIN
    IF NEW.type = 'in' THEN
        UPDATE `products` SET `quantity` = `quantity` + NEW.quantity WHERE `id` = NEW.product_id;
    ELSEIF NEW.type = 'out' THEN
        UPDATE `products` SET `quantity` = `quantity` - NEW.quantity WHERE `id` = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `email`, `address`, `city`, `contact_person`, `tax_id`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'FPT Trading', '02873000911', 'info@fpt.com.vn', 'Số 10, Phạm Văn Bạch', 'Hà Nội', NULL, NULL, NULL, 'active', '2025-12-10 00:58:54', '2025-12-10 00:58:54'),
(2, 'Digiworld', '02839268888', 'contact@dgw.com.vn', '195-197 Nguyễn Thái Bình', 'TP HCM', NULL, NULL, NULL, 'active', '2025-12-10 00:58:54', '2025-12-10 00:58:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 3 COMMENT 'Mặc định là nhân viên sales hoặc khách hàng tùy logic',
  `username` varchar(50) DEFAULT NULL COMMENT 'Tên đăng nhập (Có thể NULL nếu dùng Google)',
  `password` varchar(255) DEFAULT NULL COMMENT 'Mật khẩu (NULL nếu đăng nhập bằng Google)',
  `full_name` varchar(100) NOT NULL COMMENT 'Họ và tên đầy đủ',
  `email` varchar(100) NOT NULL COMMENT 'Email (Dùng để định danh cho cả login thường và Google)',
  `phone` varchar(15) DEFAULT NULL COMMENT 'Số điện thoại',
  `avatar` varchar(255) DEFAULT 'default-avatar.png' COMMENT 'URL ảnh đại diện',
  `google_id` varchar(255) DEFAULT NULL COMMENT 'Lưu ID từ Google API để xác thực',
  `status` enum('active','inactive') DEFAULT 'active' COMMENT 'Trạng thái tài khoản',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lưu thông tin tài khoản người dùng';

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `password`, `full_name`, `email`, `phone`, `avatar`, `google_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'Toàn Diện', 'admin@email.com', NULL, 'admin_face.jpg', NULL, 'active', '2025-12-10 00:58:54', '2025-12-10 00:58:54'),
(2, 2, 'manager01', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'An Nguyễn', 'manager@email.com', NULL, 'default.png', NULL, 'active', '2025-12-10 00:58:54', '2025-12-10 00:58:54'),
(3, 3, 'sales01', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'Bình Minh', 'sales@email.com', NULL, 'sales_girl.jpg', NULL, 'active', '2025-12-10 00:58:54', '2025-12-10 00:58:54'),
(4, 4, 'kho01', '$2y$10$E.gL3k0aR4H8s2p.d8I.iO2U.X1u/i4s/b5F.w8c7J8y9a0b1c2d3', 'Cường Trần', 'warehouse@email.com', NULL, 'default.png', NULL, 'active', '2025-12-10 00:58:54', '2025-12-10 00:58:54'),
(5, 3, NULL, NULL, 'Google User', 'googleuser@gmail.com', NULL, 'https://lh3.googleusercontent.com/a/ACg8oc...', '10293847561029384756', 'active', '2025-12-10 00:58:54', '2025-12-10 00:58:54'),
(6, 3, 'phuvinh', '$2y$10$nDepaXolj9Ch766rHOKWMeingqS7mK9l98ohUFc/CwAYmxNPzpihS', 'phuvinh', '110122235@st.tvu.edu.vn', '', 'default-avatar.png', NULL, 'active', '2025-12-10 01:30:59', '2025-12-10 01:30:59');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
