<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // MySQL: GREATEST() | SQLite: MAX()
        $greatestFn = $isSqlite ? 'MAX' : 'GREATEST';

        // MySQL: storedAs (persisted) | SQLite: virtualAs (computed on read)
        $generatedType = $isSqlite ? 'virtualAs' : 'storedAs';

        Schema::create('pengiriman_barang', function (Blueprint $table) use ($greatestFn, $generatedType): void {
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

            $table->decimal('berat_volumetrik_kg', 8, 2)
                ->{$generatedType}('(panjang_cm * lebar_cm * tinggi_cm) / 6000');

            $table->decimal('berat_tagihan_kg', 8, 2)
                ->{$generatedType}("{$greatestFn}(berat_asli_kg, (panjang_cm * lebar_cm * tinggi_cm) / 6000)");

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
