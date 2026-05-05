<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';

    protected $fillable = [
        'nomor_resi',
        'pengirim_nama',
        'pengirim_hp',
        'pengirim_alamat',
        'pengirim_kota_id',
        'penerima_nama',
        'penerima_hp',
        'penerima_alamat',
        'penerima_kota_id',
        'total_berat_asli',
        'total_berat_volumetrik',
        'total_berat_tagihan',
        'jumlah_barang',
        'jenis_layanan',
        'biaya_pengiriman',
        'biaya_tambahan',
        'biaya_asuransi',
        'metode_pembayaran',
        'status',
        'estimasi_tiba',
        'tanggal_terkirim',
        'catatan',
        'alasan_batal',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'total_berat_asli' => 'decimal:2',
            'total_berat_volumetrik' => 'decimal:2',
            'total_berat_tagihan' => 'decimal:2',
            'biaya_pengiriman' => 'decimal:2',
            'biaya_tambahan' => 'decimal:2',
            'biaya_asuransi' => 'decimal:2',
            'jumlah_barang' => 'integer',
            'estimasi_tiba' => 'date',
            'tanggal_terkirim' => 'datetime',
        ];
    }

    public function pengirimKota()
    {
        return $this->belongsTo(Kota::class, 'pengirim_kota_id');
    }

    public function penerimaKota()
    {
        return $this->belongsTo(Kota::class, 'penerima_kota_id');
    }

    public function barang()
    {
        return $this->hasMany(PengirimanBarang::class, 'pengiriman_id');
    }

    public function trackingHistories()
    {
        return $this->hasMany(TrackingHistory::class, 'pengiriman_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
