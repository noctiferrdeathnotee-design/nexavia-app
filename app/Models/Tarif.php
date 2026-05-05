<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    protected $table = 'tarif';

    protected $fillable = [
        'kota_asal_id',
        'kota_tujuan_id',
        'jenis_layanan',
        'harga_per_kg',
        'estimasi_hari',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga_per_kg' => 'decimal:2',
            'estimasi_hari' => 'integer',
        ];
    }

    public function kotaAsal()
    {
        return $this->belongsTo(Kota::class, 'kota_asal_id');
    }

    public function kotaTujuan()
    {
        return $this->belongsTo(Kota::class, 'kota_tujuan_id');
    }
}
