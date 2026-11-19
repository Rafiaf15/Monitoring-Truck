-- Query SQL Update: Tambah ENUM status 'otw_bpi'
-- Jalankan query ini di phpMyAdmin (baik lokal maupun InfinityFree)

ALTER TABLE `loads` MODIFY COLUMN `status` ENUM('muat', 'otw', 'otw_bpi', 'bpi') NOT NULL DEFAULT 'bpi';
ALTER TABLE `load_logs` MODIFY COLUMN `status` ENUM('muat', 'otw', 'otw_bpi', 'bpi') NOT NULL;
