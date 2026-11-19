<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ubah ENUM status di tabel loads dan load_logs untuk menyertakan 'otw_bpi'
        DB::statement("ALTER TABLE `loads` MODIFY COLUMN `status` ENUM('muat', 'otw', 'otw_bpi', 'bpi') NOT NULL DEFAULT 'bpi'");
        DB::statement("ALTER TABLE `load_logs` MODIFY COLUMN `status` ENUM('muat', 'otw', 'otw_bpi', 'bpi') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `loads` MODIFY COLUMN `status` ENUM('muat', 'otw', 'bpi') NOT NULL DEFAULT 'bpi'");
        DB::statement("ALTER TABLE `load_logs` MODIFY COLUMN `status` ENUM('muat', 'otw', 'bpi') NOT NULL");
    }
};
