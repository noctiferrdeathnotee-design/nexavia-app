<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengiriman_barang', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengiriman_id')
                ->constrained('pengiriman')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('nama_barang', 100);
            $table->decimal('berat_asli_kg', 8, 2);
            $table->decimal('panjang_cm', 8, 2)->default(0);
            $table->decimal('lebar_cm', 8, 2)->default(0);
            $table->decimal('tinggi_cm', 8, 2)->default(0);

            // Computed at application layer instead of DB virtual columns
            // (SQLite virtual columns cause schema corruption on some PHP builds)
            $table->decimal('berat_volumetrik_kg', 8, 2)->default(0);
            $table->decimal('berat_tagihan_kg', 8, 2)->default(0);

            $table->string('keterangan', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('pengiriman_id', 'idx_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman_barang');
    }
};
