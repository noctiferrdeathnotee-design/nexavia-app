<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kota extends Model
{
    use HasFactory;

    protected $table = 'kota';

    protected $fillable = [
        'nama_kota',
        'provinsi',
        'kode_pos',
        'latitude',
        'longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function tarifAsal()
    {
        return $this->hasMany(Tarif::class, 'kota_asal_id');
    }

    public function tarifTujuan()
    {
        return $this->hasMany(Tarif::class, 'kota_tujuan_id');
    }

    public function pengirimanSebagaiAsal()
    {
        return $this->hasMany(Pengiriman::class, 'pengirim_kota_id');
    }

    public function pengirimanSebagaiTujuan()
    {
        return $this->hasMany(Pengiriman::class, 'penerima_kota_id');
    }
}
