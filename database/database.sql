-- ================================================================
-- Little Salt Bread Blok M — POS Database Schema
-- Database: litdig_kelompok11
-- Charset: utf8mb4 / utf8mb4_unicode_ci
-- ================================================================

CREATE DATABASE IF NOT EXISTS `litdig_kelompok11`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `litdig_kelompok11`;

-- ================================================================
-- 1. MEMBERS (Customer & Admin Accounts)
-- ================================================================
CREATE TABLE IF NOT EXISTS `members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(25) DEFAULT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    `tier` ENUM('regular','member','vip') NOT NULL DEFAULT 'regular',
    `total_spent` INT NOT NULL DEFAULT 0,
    `avatar_url` VARCHAR(255) DEFAULT NULL,
    `reset_token` VARCHAR(100) DEFAULT NULL,
    `reset_token_expires` DATETIME DEFAULT NULL,
    `email_verified_at` DATETIME DEFAULT NULL,
    `verification_token` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_tier` (`tier`)
) ENGINE=InnoDB;

-- ================================================================
-- 2. CATEGORIES
-- ================================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================================================================
-- 3. PRODUCTS
-- ================================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `price` INT NOT NULL,
    `discount_price` INT DEFAULT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `bake_time_mins` INT NOT NULL DEFAULT 12,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `options_json` JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB;

-- ================================================================
-- 4. TABLES (Meja)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_number` VARCHAR(10) NOT NULL UNIQUE,
    `capacity` INT NOT NULL DEFAULT 4,
    `zone` ENUM('indoor','outdoor','vip') NOT NULL DEFAULT 'indoor',
    `status` ENUM('available','occupied','reserved','maintenance') NOT NULL DEFAULT 'available',
    `pos_x` INT NOT NULL DEFAULT 0,
    `pos_y` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================================================================
-- 5. ORDERS
-- ================================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT DEFAULT NULL,
    `table_id` INT DEFAULT NULL,
    `queue_number` VARCHAR(20) NOT NULL,
    `order_code` VARCHAR(50) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(25) DEFAULT NULL,
    `customer_email` VARCHAR(150) DEFAULT NULL,
    `order_type` ENUM('dine_in','takeaway') NOT NULL DEFAULT 'takeaway',
    `notes` TEXT DEFAULT NULL,
    `subtotal` INT NOT NULL DEFAULT 0,
    `discount_amount` INT NOT NULL DEFAULT 0,
    `tax_amount` INT NOT NULL DEFAULT 0,
    `service_charge` INT NOT NULL DEFAULT 0,
    `total_amount` INT NOT NULL,
    `payment_method` VARCHAR(30) DEFAULT NULL,
    `payment_status` ENUM('unpaid','pending','paid','refunded') NOT NULL DEFAULT 'unpaid',
    `payment_reference` VARCHAR(100) DEFAULT NULL,
    `order_status` ENUM('pending','confirmed','processing','ready','shelf','completed','cancelled') NOT NULL DEFAULT 'pending',
    `estimated_minutes` INT NOT NULL DEFAULT 15,
    `ready_at` DATETIME DEFAULT NULL,
    `shelf_slot` VARCHAR(20) DEFAULT 'Rak A-01',
    `picked_up_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`table_id`) REFERENCES `tables`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_order_status` (`order_status`),
    INDEX `idx_payment_status` (`payment_status`),
    INDEX `idx_queue` (`queue_number`),
    INDEX `idx_member` (`member_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB;

-- ================================================================
-- 6. ORDER_ITEMS
-- ================================================================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `item_name` VARCHAR(100) NOT NULL,
    `quantity` INT NOT NULL,
    `price` INT NOT NULL,
    `subtotal` INT NOT NULL,
    `options_json` JSON DEFAULT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_order` (`order_id`)
) ENGINE=InnoDB;

-- ================================================================
-- 7. SETTINGS (App Configuration Key-Value Store)
-- ================================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(50) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================================================================
-- SEED DATA: Categories
-- ================================================================
INSERT INTO `categories` (`name`, `slug`, `icon`, `sort_order`) VALUES
('Salt Bread Varian', 'roti', '', 1),
('Minuman & Kopi', 'minuman', '', 2)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ================================================================
-- SEED DATA: Products (11 items from existing menu)
-- ================================================================
INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `price`, `stock`, `image_url`, `bake_time_mins`, `is_featured`, `options_json`) VALUES
(1, 'Salt Bread Kaya', 'salt-bread-kaya', 'Salt bread lembut dengan olesan kaya pandan homemade yang manis dan creamy.', 28000, 50, 'assets/img/salt_bread_kaya.jpg', 12, 1, '{"notes": true}'),
(1, 'Salt Bread Plain', 'salt-bread-plain', 'Salt bread original dengan taburan garam laut premium. Renyah di luar, lembut di dalam.', 14000, 50, 'assets/img/salt_bread_plain.jpg', 10, 0, '{"notes": true}'),
(1, 'Salt Bread Cheese', 'salt-bread-cheese', 'Salt bread dengan lelehan keju mozarella premium yang stretchy.', 22000, 50, 'assets/img/salt_bread_cheese.jpg', 12, 1, '{"notes": true}'),
(1, 'Salt Bread Kinako Chocolate', 'salt-bread-kinako-chocolate', 'Perpaduan unik kinako (soybean powder) Jepang dengan cokelat Belgium.', 21000, 50, 'assets/img/salt_bread_kinako_chocolate.jpg', 12, 0, '{"notes": true}'),
(1, 'Salt Bread Truffle Egg', 'salt-bread-truffle-egg', 'Premium salt bread dengan telur asin dan truffle oil. Best seller!', 29000, 50, 'assets/img/salt_bread_truffle_egg.jpg', 15, 1, '{"notes": true}'),
(1, 'Salt Bread Garlic', 'salt-bread-garlic', 'Salt bread dengan garlic butter yang harum dan savory.', 21000, 50, 'assets/img/salt_bread_garlic.jpg', 12, 0, '{"notes": true}'),
(1, 'Sugar Salt Bread', 'sugar-salt-bread', 'Kombinasi gula dan garam yang addictive. Sweet & salty perfection.', 21000, 50, 'assets/img/sugar_salt_bread.jpg', 12, 0, '{"notes": true}'),
(2, 'Hand Drip Coffee', 'hand-drip-coffee', 'Kopi single origin yang diseduh manual dengan metode pour-over.', 48000, 99, 'assets/img/hand_drip_coffee.jpg', 5, 1, '{"sugar_levels": ["normal", "less", "none"], "sizes": ["regular", "large"], "notes": true}'),
(2, 'Sweet Japanese Iced Coffee', 'sweet-japanese-iced-coffee', 'Japanese-style iced coffee dengan sentuhan manis yang refreshing.', 49000, 99, 'assets/img/sweet_japanese_iced_coffee.jpg', 5, 0, '{"sugar_levels": ["normal", "less", "none"], "sizes": ["regular", "large"], "notes": true}'),
(2, 'Honey Milk Tea', 'honey-milk-tea', 'Teh susu premium dengan madu asli. Creamy dan menenangkan.', 38000, 99, 'assets/img/honey_milk_tea.jpg', 5, 0, '{"sugar_levels": ["normal", "less", "none"], "sizes": ["regular", "large"], "notes": true}'),
(2, 'Deep Roast Oolong Milk Tea', 'deep-roast-oolong-milk-tea', 'Oolong tea yang di-roast dalam untuk rasa yang bold dan aromatic.', 38000, 99, 'assets/img/deep_roast_oolong_milk_tea.jpg', 5, 0, '{"sugar_levels": ["normal", "less", "none"], "sizes": ["regular", "large"], "notes": true}')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ================================================================
-- SEED DATA: Tables (8 tables)
-- ================================================================
INSERT INTO `tables` (`table_number`, `capacity`, `zone`, `pos_x`, `pos_y`) VALUES
('T-01', 2, 'indoor', 1, 1),
('T-02', 2, 'indoor', 2, 1),
('T-03', 4, 'indoor', 3, 1),
('T-04', 4, 'indoor', 1, 2),
('T-05', 4, 'indoor', 2, 2),
('T-06', 6, 'indoor', 3, 2),
('T-07', 4, 'outdoor', 1, 3),
('T-08', 4, 'outdoor', 2, 3)
ON DUPLICATE KEY UPDATE `capacity` = VALUES(`capacity`);

-- ================================================================
-- SEED DATA: Default Settings
-- ================================================================
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'Little Salt Bread Blok M'),
('store_tagline', 'Salt Bread Renyah Gurih Langsung Dari Oven'),
('store_address', 'Blok M, Jakarta Selatan'),
('store_phone', '+62 812-0000-0000'),
('store_hours', 'Senin - Minggu 10:00 - 20:00'),
('tax_rate', '11'),
('service_charge_rate', '5'),
('member_discount', '5'),
('vip_discount', '10'),
('member_threshold', '500000'),
('vip_threshold', '2000000'),
('shelf_timeout_minutes', '20'),
('currency', 'IDR'),
('timezone', 'Asia/Jakarta')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ================================================================
-- SEED DATA: Default Admin Account
-- Password: admin123 (bcrypt hash)
-- ================================================================
INSERT INTO `members` (`name`, `email`, `phone`, `password_hash`, `role`, `email_verified_at`) VALUES
('Admin Salt Bread', 'admin@saltbread.id', '+62 812-0000-0001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

