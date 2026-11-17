<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tambah kolom trip_count (ritase) pada tabel loads
        Schema::table('loads', function (Blueprint $table) {
            $table->unsignedInteger('trip_count')->default(0)->after('pipe_count');
        });

        // 2. Buat tabel load_logs untuk riwayat/audit log
        Schema::create('load_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('pipe_count');
            $table->enum('status', ['muat', 'otw', 'bpi']);
            $table->unsignedInteger('trip_count');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_logs');
        Schema::table('loads', function (Blueprint $table) {
            $table->dropColumn('trip_count');
        });
    }
};
