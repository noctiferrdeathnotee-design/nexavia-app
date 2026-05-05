<?php

namespace App\Services;

use App\Models\Tarif;

class TarifService
{
    public function hitung(int $asalId, int $tujuanId, float $beratKg): array
    {
        $beratKg = max(0, round($beratKg, 2));

        $tarifList = Tarif::query()
            ->where('kota_asal_id', $asalId)
            ->where('kota_tujuan_id', $tujuanId)
            ->where('status', 'aktif')
            ->orderBy('estimasi_hari')
            ->orderBy('harga_per_kg')
            ->get();

        if ($tarifList->isEmpty()) {
            return [];
        }

        return $tarifList
            ->map(function (Tarif $tarif) use ($beratKg): array {
                $hargaPerKg = (float) $tarif->harga_per_kg;
                $total = (int) round($beratKg * $hargaPerKg);

                return [
                    'id' => $tarif->id,
                    'jenis_layanan' => $tarif->jenis_layanan,
                    'per_kg' => $hargaPerKg,
                    'total' => $total,
                    'estimasi_hari' => (int) $tarif->estimasi_hari,
                ];
            })
            ->values()
            ->toArray();
    }
}
