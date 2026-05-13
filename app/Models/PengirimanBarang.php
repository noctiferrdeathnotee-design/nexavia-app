<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengirimanBarang extends Model
{
    use HasFactory;

    protected $table = 'pengiriman_barang';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'pengiriman_id',
        'nama_barang',
        'berat_asli_kg',
        'panjang_cm',
        'lebar_cm',
        'tinggi_cm',
        'berat_volumetrik_kg',
        'berat_tagihan_kg',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'berat_asli_kg' => 'decimal:2',
            'panjang_cm' => 'decimal:2',
            'lebar_cm' => 'decimal:2',
            'tinggi_cm' => 'decimal:2',
            'berat_volumetrik_kg' => 'decimal:2',
            'berat_tagihan_kg' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($barang) {
            $barang->berat_volumetrik_kg = ($barang->panjang_cm * $barang->lebar_cm * $barang->tinggi_cm) / 6000;
            $barang->berat_tagihan_kg = max($barang->berat_asli_kg, $barang->berat_volumetrik_kg);
        });

        static::updating(function ($barang) {
            $barang->berat_volumetrik_kg = ($barang->panjang_cm * $barang->lebar_cm * $barang->tinggi_cm) / 6000;
            $barang->berat_tagihan_kg = max($barang->berat_asli_kg, $barang->berat_volumetrik_kg);
        });
    }

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }
}
