-- Dump SQL Database untuk Monitoring Truk Pipe
-- Kompatibel dengan MySQL / MariaDB (phpMyAdmin)

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Struktur tabel `companies`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `companies` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'PT MTS', NOW(), NOW()),
(2, 'PT TTS', NOW(), NOW()),
(3, 'PT Badui', NOW(), NOW()),
(4, 'PT Tjp', NOW(), NOW());

-- --------------------------------------------------------
-- Struktur tabel `drivers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `drivers`;
CREATE TABLE `drivers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `drivers` (`id`, `name`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Wardo Widodo', '+62 858-1705-6201', NOW(), NOW()),
(2, 'Kasimun', '+62 858-6830-0240', NOW(), NOW()),
(3, 'Sujatna', '+62 877-8875-2999', NOW(), NOW()),
(4, 'Deni', '+62 858-8252-8201', NOW(), NOW()),
(5, 'Nur Alpian', '+62 857-7295-4804', NOW(), NOW()),
(6, 'Ombih', '+62 878-7353-9808', NOW(), NOW()),
(7, 'Ece Taryadi', '0838-7333-6394', NOW(), NOW()),
(8, 'Wahyu Hari', '0819-2863-1723', NOW(), NOW()),
(9, 'Ujang', '0813-8964-7933', NOW(), NOW()),
(10, 'Roni', '0819-9355-0234', NOW(), NOW()),
(11, 'Darwis', '0858-1074-5382', NOW(), NOW()),
(12, 'Adang', '+62 812-8132-3220', NOW(), NOW()),
(13, 'Trimo', '+62 821-1312-3337', NOW(), NOW()),
(14, 'Rohendi', '+62 856-9274-6398', NOW(), NOW()),
(15, 'Aris', '+62 896-7635-9875', NOW(), NOW()),
(16, 'Johar', '+62 856-9348-0944', NOW(), NOW()),
(17, 'Nirin', '+62 857-1626-9291', NOW(), NOW()),
(18, 'AMJAH', '+62 881-0231-45457', NOW(), NOW()),
(19, 'RUDI', '+62 812-1326-8892', NOW(), NOW()),
(20, 'SUHADA', '+62 858-8064-6613', NOW(), NOW()),
(21, 'SUKMAWI', '+62 857-7664-3492', NOW(), NOW()),
(22, 'Hikmah', '+62 815-7225-0428', NOW(), NOW()),
(23, 'Miswan', '+62 877-7562-1785', NOW(), NOW()),
(24, 'Sungari', '+62 821-4630-5411', NOW(), NOW()),
(25, 'Erpan', '+62 838-3414-5481', NOW(), NOW()),
(26, 'Kiswanto', '+62 878-4092-5447', NOW(), NOW());

-- --------------------------------------------------------
-- Struktur tabel `trucks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `trucks`;
CREATE TABLE `trucks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trucks_plate_number_unique` (`plate_number`),
  KEY `trucks_driver_id_foreign` (`driver_id`),
  KEY `trucks_company_id_foreign` (`company_id`),
  CONSTRAINT `trucks_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trucks_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trucks` (`id`, `plate_number`, `driver_id`, `company_id`, `created_at`, `updated_at`) VALUES
(1, 'B 9285 BEL', 1, 1, NOW(), NOW()),
(2, 'B 9290 BEL', 2, 1, NOW(), NOW()),
(3, 'B 9291 BEL', 3, 1, NOW(), NOW()),
(4, 'B 9389 BEL', 4, 1, NOW(), NOW()),
(5, 'B 9392 BEL', 5, 1, NOW(), NOW()),
(6, 'B 9397 BEL', 6, 1, NOW(), NOW()),
(7, 'B 9601 UIZ', 7, 2, NOW(), NOW()),
(8, 'B 9373 UWW', 8, 2, NOW(), NOW()),
(9, 'B 9398 UWW', 9, 2, NOW(), NOW()),
(10, 'B 9879 UWV', 10, 2, NOW(), NOW()),
(11, 'B 9345 UFY', 11, 2, NOW(), NOW()),
(12, 'B 9540 UFZ', 12, 3, NOW(), NOW()),
(13, 'B 9137 UWV', 13, 3, NOW(), NOW()),
(14, 'B 9890 UWV', 14, 3, NOW(), NOW()),
(15, 'B 9629 UFZ', 15, 3, NOW(), NOW()),
(16, 'B 9628 UFZ', 16, 3, NOW(), NOW()),
(17, 'B 9526 JEH', 17, 4, NOW(), NOW()),
(18, 'B 9936 UEL', 18, 4, NOW(), NOW()),
(19, 'B 9303 UEL', 19, 4, NOW(), NOW()),
(20, 'B 9327 UEI', 20, 4, NOW(), NOW()),
(21, 'B 9640 UEJ', 21, 4, NOW(), NOW()),
(22, 'B 9133 UEH', 22, 4, NOW(), NOW()),
(23, 'B 9834 UEL', 23, 4, NOW(), NOW()),
(24, 'B 9828 UEL', 24, 4, NOW(), NOW()),
(25, 'B 9519 PEI', 25, 4, NOW(), NOW()),
(26, 'B 9250 UEJ', 26, 4, NOW(), NOW());

-- --------------------------------------------------------
-- Struktur tabel `loads` (Inisialisasi 0 pipa dan status BPI)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `loads`;
CREATE TABLE `loads` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `truck_id` bigint(20) UNSIGNED NOT NULL,
  `pipe_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('muat','otw','bpi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bpi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loads_truck_id_foreign` (`truck_id`),
  CONSTRAINT `loads_truck_id_foreign` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `loads` (`id`, `truck_id`, `pipe_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 'bpi', NOW(), NOW()),
(2, 2, 0, 'bpi', NOW(), NOW()),
(3, 3, 0, 'bpi', NOW(), NOW()),
(4, 4, 0, 'bpi', NOW(), NOW()),
(5, 5, 0, 'bpi', NOW(), NOW()),
(6, 6, 0, 'bpi', NOW(), NOW()),
(7, 7, 0, 'bpi', NOW(), NOW()),
(8, 8, 0, 'bpi', NOW(), NOW()),
(9, 9, 0, 'bpi', NOW(), NOW()),
(10, 10, 0, 'bpi', NOW(), NOW()),
(11, 11, 0, 'bpi', NOW(), NOW()),
(12, 12, 0, 'bpi', NOW(), NOW()),
(13, 13, 0, 'bpi', NOW(), NOW()),
(14, 14, 0, 'bpi', NOW(), NOW()),
(15, 15, 0, 'bpi', NOW(), NOW()),
(16, 16, 0, 'bpi', NOW(), NOW()),
(17, 17, 0, 'bpi', NOW(), NOW()),
(18, 18, 0, 'bpi', NOW(), NOW()),
(19, 19, 0, 'bpi', NOW(), NOW()),
(20, 20, 0, 'bpi', NOW(), NOW()),
(21, 21, 0, 'bpi', NOW(), NOW()),
(22, 22, 0, 'bpi', NOW(), NOW()),
(23, 23, 0, 'bpi', NOW(), NOW()),
(24, 24, 0, 'bpi', NOW(), NOW()),
(25, 25, 0, 'bpi', NOW(), NOW()),
(26, 26, 0, 'bpi', NOW(), NOW());

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
