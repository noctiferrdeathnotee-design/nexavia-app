<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifSeeder extends Seeder
{
    public function run(): void
    {
        $cities = DB::table('kota')
            ->where('status', 'aktif')
            ->select('id', 'nama_kota', 'provinsi', 'latitude', 'longitude')
            ->orderBy('id')
            ->get();

        foreach ($cities as $asal) {
            foreach ($cities as $tujuan) {
                $distanceKm = $this->haversine(
                    (float) $asal->latitude,
                    (float) $asal->longitude,
                    (float) $tujuan->latitude,
                    (float) $tujuan->longitude
                );

                $services = $this->buildServices($asal, $tujuan, $distanceKm);

                foreach ($services as $service) {
                    DB::table('tarif')->updateOrInsert(
                        [
                            'kota_asal_id' => $asal->id,
                            'kota_tujuan_id' => $tujuan->id,
                            'jenis_layanan' => $service['jenis_layanan'],
                        ],
                        [
                            'harga_per_kg' => $service['harga_per_kg'],
                            'estimasi_hari' => $service['estimasi_hari'],
                            'status' => 'aktif',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function buildServices(object $asal, object $tujuan, float $distanceKm): array
    {
        $isSameCity = (int) $asal->id === (int) $tujuan->id;
        $isSameProvince = trim((string) $asal->provinsi) === trim((string) $tujuan->provinsi);

        if ($isSameCity) {
            return [
                [
                    'jenis_layanan' => 'ekonomis',
                    'harga_per_kg' => 4000,
                    'estimasi_hari' => 1,
                ],
                [
                    'jenis_layanan' => 'reguler',
                    'harga_per_kg' => 7000,
                    'estimasi_hari' => 1,
                ],
                [
                    'jenis_layanan' => 'express',
                    'harga_per_kg' => 12000,
                    'estimasi_hari' => 0,
                ],
                [
                    'jenis_layanan' => 'same_day',
                    'harga_per_kg' => 20000,
                    'estimasi_hari' => 0,
                ],
            ];
        }

        if ($distanceKm <= 120) {
            $economis = [
                'jenis_layanan' => 'ekonomis',
                'harga_per_kg' => 6000,
                'estimasi_hari' => 2,
            ];
            $reguler = [
                'jenis_layanan' => 'reguler',
                'harga_per_kg' => 10000,
                'estimasi_hari' => 1,
            ];
            $express = [
                'jenis_layanan' => 'express',
                'harga_per_kg' => 18000,
                'estimasi_hari' => 1,
            ];
        } elseif ($distanceKm <= 350) {
            $economis = [
                'jenis_layanan' => 'ekonomis',
                'harga_per_kg' => 7000,
                'estimasi_hari' => 3,
            ];
            $reguler = [
                'jenis_layanan' => 'reguler',
                'harga_per_kg' => 12000,
                'estimasi_hari' => 2,
            ];
            $express = [
                'jenis_layanan' => 'express',
                'harga_per_kg' => 20000,
                'estimasi_hari' => 1,
            ];
        } elseif ($distanceKm <= 800) {
            $economis = [
                'jenis_layanan' => 'ekonomis',
                'harga_per_kg' => 8500,
                'estimasi_hari' => 5,
            ];
            $reguler = [
                'jenis_layanan' => 'reguler',
                'harga_per_kg' => 14000,
                'estimasi_hari' => 3,
            ];
            $express = [
                'jenis_layanan' => 'express',
                'harga_per_kg' => 24000,
                'estimasi_hari' => 2,
            ];
        } elseif ($distanceKm <= 1500) {
            $economis = [
                'jenis_layanan' => 'ekonomis',
                'harga_per_kg' => 10000,
                'estimasi_hari' => 7,
            ];
            $reguler = [
                'jenis_layanan' => 'reguler',
                'harga_per_kg' => 17000,
                'estimasi_hari' => 4,
            ];
            $express = [
                'jenis_layanan' => 'express',
                'harga_per_kg' => 28000,
                'estimasi_hari' => 2,
            ];
        } else {
            $economis = [
                'jenis_layanan' => 'ekonomis',
                'harga_per_kg' => 12000,
                'estimasi_hari' => 10,
            ];
            $reguler = [
                'jenis_layanan' => 'reguler',
                'harga_per_kg' => 20000,
                'estimasi_hari' => 5,
            ];
            $express = [
                'jenis_layanan' => 'express',
                'harga_per_kg' => 35000,
                'estimasi_hari' => 3,
            ];
        }

        $services = [$economis, $reguler, $express];

        if ($isSameProvince || $distanceKm <= 120) {
            $services[] = [
                'jenis_layanan' => 'same_day',
                'harga_per_kg' => $distanceKm <= 30 ? 25000 : 35000,
                'estimasi_hari' => 0,
            ];
        }

        return $services;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
