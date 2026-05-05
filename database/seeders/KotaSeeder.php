<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KotaSeeder extends Seeder
{
    public function run(): void
    {
        $kota = [
            ['nama_kota' => 'Jakarta', 'provinsi' => 'DKI Jakarta', 'kode_pos' => '10110', 'latitude' => -6.2087634, 'longitude' => 106.8455990],
            ['nama_kota' => 'Surabaya', 'provinsi' => 'Jawa Timur', 'kode_pos' => '60111', 'latitude' => -7.2574719, 'longitude' => 112.7520883],
            ['nama_kota' => 'Bandung', 'provinsi' => 'Jawa Barat', 'kode_pos' => '40111', 'latitude' => -6.9174639, 'longitude' => 107.6191228],
            ['nama_kota' => 'Medan', 'provinsi' => 'Sumatera Utara', 'kode_pos' => '20111', 'latitude' => 3.5951956, 'longitude' => 98.6722227],
            ['nama_kota' => 'Semarang', 'provinsi' => 'Jawa Tengah', 'kode_pos' => '50135', 'latitude' => -6.9666670, 'longitude' => 110.4166640],
            ['nama_kota' => 'Makassar', 'provinsi' => 'Sulawesi Selatan', 'kode_pos' => '90111', 'latitude' => -5.1476651, 'longitude' => 119.4327314],
            ['nama_kota' => 'Palembang', 'provinsi' => 'Sumatera Selatan', 'kode_pos' => '30111', 'latitude' => -2.9909340, 'longitude' => 104.7565540],
            ['nama_kota' => 'Tangerang', 'provinsi' => 'Banten', 'kode_pos' => '15111', 'latitude' => -6.1783061, 'longitude' => 106.6318895],
            ['nama_kota' => 'Depok', 'provinsi' => 'Jawa Barat', 'kode_pos' => '16411', 'latitude' => -6.4024844, 'longitude' => 106.7942405],
            ['nama_kota' => 'Bekasi', 'provinsi' => 'Jawa Barat', 'kode_pos' => '17121', 'latitude' => -6.2415860, 'longitude' => 106.9924169],
            ['nama_kota' => 'Yogyakarta', 'provinsi' => 'DI Yogyakarta', 'kode_pos' => '55111', 'latitude' => -7.7955798, 'longitude' => 110.3694896],
            ['nama_kota' => 'Malang', 'provinsi' => 'Jawa Timur', 'kode_pos' => '65111', 'latitude' => -7.9666204, 'longitude' => 112.6326321],
            ['nama_kota' => 'Padang', 'provinsi' => 'Sumatera Barat', 'kode_pos' => '25111', 'latitude' => -0.9470832, 'longitude' => 100.4171810],
            ['nama_kota' => 'Pekanbaru', 'provinsi' => 'Riau', 'kode_pos' => '28111', 'latitude' => 0.5333330, 'longitude' => 101.4500000],
            ['nama_kota' => 'Balikpapan', 'provinsi' => 'Kalimantan Timur', 'kode_pos' => '76111', 'latitude' => -1.2379274, 'longitude' => 116.8528526],
            ['nama_kota' => 'Banjarmasin', 'provinsi' => 'Kalimantan Selatan', 'kode_pos' => '70111', 'latitude' => -3.3186067, 'longitude' => 114.5943784],
            ['nama_kota' => 'Manado', 'provinsi' => 'Sulawesi Utara', 'kode_pos' => '95111', 'latitude' => 1.4748305, 'longitude' => 124.8420794],
            ['nama_kota' => 'Denpasar', 'provinsi' => 'Bali', 'kode_pos' => '80111', 'latitude' => -8.6704582, 'longitude' => 115.2126293],
            ['nama_kota' => 'Batam', 'provinsi' => 'Kepulauan Riau', 'kode_pos' => '29411', 'latitude' => 1.0456264, 'longitude' => 104.0304571],
            ['nama_kota' => 'Bogor', 'provinsi' => 'Jawa Barat', 'kode_pos' => '16111', 'latitude' => -6.5950380, 'longitude' => 106.8166350],
            ['nama_kota' => 'Samarinda', 'provinsi' => 'Kalimantan Timur', 'kode_pos' => '75111', 'latitude' => -0.5021830, 'longitude' => 117.1537090],
            ['nama_kota' => 'Pontianak', 'provinsi' => 'Kalimantan Barat', 'kode_pos' => '78111', 'latitude' => -0.0263303, 'longitude' => 109.3425039],
            ['nama_kota' => 'Jambi', 'provinsi' => 'Jambi', 'kode_pos' => '36111', 'latitude' => -1.6101229, 'longitude' => 103.6131203],
            ['nama_kota' => 'Bandar Lampung', 'provinsi' => 'Lampung', 'kode_pos' => '35111', 'latitude' => -5.4291620, 'longitude' => 105.2667920],
            ['nama_kota' => 'Bengkulu', 'provinsi' => 'Bengkulu', 'kode_pos' => '38111', 'latitude' => -3.8006510, 'longitude' => 102.2655400],
            ['nama_kota' => 'Mataram', 'provinsi' => 'Nusa Tenggara Barat', 'kode_pos' => '83111', 'latitude' => -8.5833330, 'longitude' => 116.1166670],
            ['nama_kota' => 'Kupang', 'provinsi' => 'Nusa Tenggara Timur', 'kode_pos' => '85111', 'latitude' => -10.1771997, 'longitude' => 123.6070329],
            ['nama_kota' => 'Ambon', 'provinsi' => 'Maluku', 'kode_pos' => '97111', 'latitude' => -3.6954301, 'longitude' => 128.1814110],
            ['nama_kota' => 'Jayapura', 'provinsi' => 'Papua', 'kode_pos' => '99111', 'latitude' => -2.5337100, 'longitude' => 140.7181320],
            ['nama_kota' => 'Sorong', 'provinsi' => 'Papua Barat Daya', 'kode_pos' => '98411', 'latitude' => -0.8761620, 'longitude' => 131.2558280],
            ['nama_kota' => 'Kendari', 'provinsi' => 'Sulawesi Tenggara', 'kode_pos' => '93111', 'latitude' => -3.9984597, 'longitude' => 122.5129742],
            ['nama_kota' => 'Palu', 'provinsi' => 'Sulawesi Tengah', 'kode_pos' => '94111', 'latitude' => -0.8917000, 'longitude' => 119.8707000],
            ['nama_kota' => 'Gorontalo', 'provinsi' => 'Gorontalo', 'kode_pos' => '96111', 'latitude' => 0.5435442, 'longitude' => 123.0567693],
            ['nama_kota' => 'Ternate', 'provinsi' => 'Maluku Utara', 'kode_pos' => '97711', 'latitude' => 0.7900000, 'longitude' => 127.3842400],
            ['nama_kota' => 'Mamuju', 'provinsi' => 'Sulawesi Barat', 'kode_pos' => '91511', 'latitude' => -2.6800000, 'longitude' => 118.8867000],
        ];

        foreach ($kota as $item) {
            DB::table('kota')->updateOrInsert(
                ['nama_kota' => $item['nama_kota']],
                [
                    'provinsi' => $item['provinsi'],
                    'kode_pos' => $item['kode_pos'],
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
