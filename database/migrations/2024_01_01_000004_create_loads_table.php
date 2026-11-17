<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('pipe_count')->default(29);
            $table->enum('status', ['muat', 'otw', 'bpi'])->default('muat');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loads');
    }
};
?>