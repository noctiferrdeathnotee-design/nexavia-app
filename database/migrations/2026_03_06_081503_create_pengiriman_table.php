<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengiriman', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_resi', 25)->unique();

            $table->string('pengirim_nama', 100);
            $table->string('pengirim_hp', 20);
            $table->text('pengirim_alamat');
            $table->foreignId('pengirim_kota_id')->constrained('kota')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('penerima_nama', 100);
            $table->string('penerima_hp', 20);
            $table->text('penerima_alamat');
            $table->foreignId('penerima_kota_id')->constrained('kota')->cascadeOnUpdate()->restrictOnDelete();

            $table->decimal('total_berat_asli', 10, 2)->default(0);
            $table->decimal('total_berat_volumetrik', 10, 2)->default(0);
            $table->decimal('total_berat_tagihan', 10, 2)->default(0);

            $table->unsignedInteger('jumlah_barang')->default(1);

            $table->enum('jenis_layanan', ['ekonomis', 'reguler', 'express', 'same_day']);
            $table->decimal('biaya_pengiriman', 12, 2);
            $table->decimal('biaya_tambahan', 12, 2)->default(0);
            $table->decimal('biaya_asuransi', 12, 2)->default(0);

            $table->enum('metode_pembayaran', ['dibayar_pengirim', 'dibayar_penerima', 'cod']);

            $table->enum('status', [
                'pending',
                'diproses',
                'dalam_perjalanan',
                'tiba_di_kota_tujuan',
                'sedang_diantar',
                'terkirim',
                'gagal',
                'dibatalkan',
            ])->default('pending');

            $table->date('estimasi_tiba');
            $table->timestamp('tanggal_terkirim')->nullable();
            $table->text('catatan')->nullable();
            $table->text('alasan_batal')->nullable();

            $table->foreignId('admin_id')->constrained('admins')->cascadeOnUpdate()->restrictOnDelete();

            $table->timestamps();

            $table->index('nomor_resi', 'idx_resi');
            $table->index('status', 'idx_status');
            $table->index('estimasi_tiba', 'idx_estimasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
