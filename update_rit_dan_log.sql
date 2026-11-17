-- Query SQL Update: Tambah Ritase & Buat Tabel Log Aktivitas
-- Jalankan query ini di phpMyAdmin InfinityFree

-- 1. Tambah kolom ritase (trip_count) ke tabel loads
ALTER TABLE `loads` ADD COLUMN `trip_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `pipe_count`;

-- 2. Buat tabel load_logs untuk menyimpan histori dan jejak audit
CREATE TABLE IF NOT EXISTS `load_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `truck_id` bigint(20) UNSIGNED NOT NULL,
  `pipe_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('muat','otw','bpi') COLLATE utf8mb4_unicode_ci NOT NULL,
  `trip_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `load_logs_truck_id_foreign` (`truck_id`),
  CONSTRAINT `load_logs_truck_id_foreign` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
