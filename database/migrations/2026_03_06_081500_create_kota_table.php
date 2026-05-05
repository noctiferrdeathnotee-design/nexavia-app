<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kota', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_kota', 100);
            $table->string('provinsi', 100);
            $table->string('kode_pos', 10);
            $table->decimal('latitude', 10, 7)->default(0);
            $table->decimal('longitude', 10, 7)->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index('nama_kota', 'idx_nama_kota');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kota');
    }
};
