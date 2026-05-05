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

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }
}
