-- Database: litdig_kelompok11
-- Skema Tabel Aplikasi Antrian Online Salt Bread

CREATE DATABASE IF NOT EXISTS `litdig_kelompok11` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `litdig_kelompok11`;

-- 1. Tabel Menu & Stok
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `category` ENUM('roti', 'minuman') NOT NULL DEFAULT 'roti',
    `description` TEXT NULL,
    `price` INT NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(255) NULL,
    `bake_time_mins` INT DEFAULT 12,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Pesanan & Antrian
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `queue_number` VARCHAR(20) NOT NULL,
    `order_code` VARCHAR(50) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(25) NOT NULL,
    `order_type` ENUM('takeaway', 'dine_in') DEFAULT 'takeaway',
    `notes` TEXT NULL,
    `total_amount` INT NOT NULL,
    `payment_method` VARCHAR(30) NOT NULL,
    `payment_status` ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    `payment_proof` VARCHAR(255) NULL,
    `order_status` ENUM('menunggu_konfirmasi', 'dipanggang', 'sedang_dikemas', 'dikemas', 'siap_diambil', 'rak_mandiri', 'selesai', 'dibatalkan') DEFAULT 'menunggu_konfirmasi',
    `estimated_minutes` INT DEFAULT 15,
    `ready_at` DATETIME NULL,
    `shelf_slot` VARCHAR(20) DEFAULT 'Rak A-01',
    `picked_up_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`queue_number`),
    INDEX (`order_status`),
    INDEX (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Rincian Menu Pesanan
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `menu_item_id` INT NOT NULL,
    `item_name` VARCHAR(100) NOT NULL,
    `quantity` INT NOT NULL,
    `price` INT NOT NULL,
    `subtotal` INT NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seeder Data Awal Menu Salt Bread & Minuman
-- Menu Little Salt Bread Blok M (Mon-Sun 10:00 - 20:00)
INSERT INTO `menu_items` (`id`, `name`, `category`, `description`, `price`, `stock`, `image_url`, `bake_time_mins`) VALUES
(1,  'Salt Bread Kaya',               'roti',    'Salt bread lembut berisi pasta kaya (selai kacang/telur khas Asia) yang gurih manis.',         28000, 20, 'assets/images/salt_bread_kaya.jpg',                 12),
(2,  'Salt Bread Plain',              'roti',    'Salt bread original klasik, renyah di luar lembut di dalam dengan taburan garam laut.',         14000, 25, 'assets/images/salt_bread_plain.jpg',                10),
(3,  'Salt Bread Cheese',             'roti',    'Salt bread dipenuhi taburan keju parmesan & cheddar panggang yang harum dan gurih.',            22000, 20, 'assets/images/salt_bread_cheese.jpg',               12),
(4,  'Salt Bread Kinako Chocolate',   'roti',    'Perpaduan tepung kedelai panggang (kinako) dengan cokelat lembut di dalam salt bread.',         21000, 15, 'assets/images/salt_bread_kinako_chocolate.jpg',     12),
(5,  'Salt Bread Truffle Egg',        'roti',    'Salt bread premium berisi scrambled egg lembut beraroma truffle hitam yang mewah.',             29000, 10, 'assets/images/salt_bread_truffle_egg.jpg',          15),
(6,  'Salt Bread Garlic',             'roti',    'Salt bread dengan olesan mentega bawang putih panggang yang harum dan gurih.',                  21000, 18, 'assets/images/salt_bread_garlic.jpg',               12),
(7,  'Sugar Salt Bread',              'roti',    'Salt bread ditaburi gula pasir crispy yang manis kontras dengan gurih roti.',                   21000, 20, 'assets/images/sugar_salt_bread.jpg',                12),
(8,  'Hand Drip Coffee',              'minuman', 'Kopi single origin diseduh manual pour-over, menghasilkan cita rasa bersih dan aromatik.',      48000, 30, 'assets/images/hand_drip_coffee.jpg',                 5),
(9,  'Sweet Japanese Iced Coffee',    'minuman', 'Japanese iced coffee dengan metode flash brew, manis seimbang. Bisa tambah cream +7k.',         49000, 25, 'assets/images/sweet_japanese_iced_coffee.jpg',       5),
(10, 'Honey Milk Tea',                'minuman', 'Teh susu dengan sentuhan madu alami, lembut manis dan menyegarkan.',                            38000, 25, 'assets/images/honey_milk_tea.jpg',                   5),
(11, 'Deep Roast Oolong Milk Tea',    'minuman', 'Oolong dark roast dipadukan susu creamy, menghasilkan rasa kaya dan dalam yang unik.',          38000, 20, 'assets/images/deep_roast_oolong_milk_tea.jpg',       5)
ON DUPLICATE KEY UPDATE
    `name`=VALUES(`name`),
    `category`=VALUES(`category`),
    `description`=VALUES(`description`),
    `price`=VALUES(`price`),
    `image_url`=VALUES(`image_url`),
    `bake_time_mins`=VALUES(`bake_time_mins`);

-- Hapus item lama (id > 11) yang tidak ada di menu asli
DELETE FROM `menu_items` WHERE `id` > 11;

