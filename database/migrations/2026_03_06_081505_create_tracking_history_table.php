<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengiriman_id')
                ->constrained('pengiriman')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('status_lama', 50)->nullable();
            $table->string('status_baru', 50);
            $table->string('lokasi', 200);
            $table->text('keterangan');
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('pengiriman_id', 'idx_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_history');
    }
};
