<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kota_asal_id')->constrained('kota')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('kota_tujuan_id')->constrained('kota')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('jenis_layanan', ['ekonomis', 'reguler', 'express', 'same_day']);
            $table->decimal('harga_per_kg', 12, 2);
            $table->integer('estimasi_hari');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index(['kota_asal_id', 'kota_tujuan_id'], 'idx_rute');
            $table->unique(
                ['kota_asal_id', 'kota_tujuan_id', 'jenis_layanan'],
                'tarif_unique_route_service'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif');
    }
};
