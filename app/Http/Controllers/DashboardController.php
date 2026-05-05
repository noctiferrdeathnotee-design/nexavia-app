<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = today();

        $totalPengiriman = Pengiriman::query()->count();

        $totalPendapatan = (float) Pengiriman::query()
            ->where('status', 'terkirim')
            ->sum(DB::raw('(biaya_pengiriman + biaya_tambahan + biaya_asuransi)'));

        $terlambatCount = Pengiriman::query()
            ->whereDate('estimasi_tiba', '<', $today)
            ->whereNotIn('status', ['terkirim', 'dibatalkan', 'gagal'])
            ->count();

        $months = collect(range(5, 0))->map(function (int $offset): array {
            $date = now()->startOfMonth()->subMonths($offset);

            return [
                'year' => (int) $date->format('Y'),
                'month' => (int) $date->format('n'),
                'key' => $date->format('Y-n'),
                'label' => $date->translatedFormat('M Y'),
            ];
        });

        $startMonth = now()->startOfMonth()->subMonths(5);
        $endMonth = now()->endOfMonth();

        $chartRows = Pengiriman::query()
            ->selectRaw('YEAR(created_at) as tahun')
            ->selectRaw('MONTH(created_at) as bulan')
            ->selectRaw('COUNT(*) as jumlah')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN status = 'terkirim'
                        THEN (biaya_pengiriman + biaya_tambahan + biaya_asuransi)
                        ELSE 0
                    END
                ) as revenue
            ")
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn($row) => $row->tahun . '-' . $row->bulan);

        $chartBulanan = [
            'labels' => [],
            'jumlah' => [],
            'revenue' => [],
        ];

        foreach ($months as $month) {
            $row = $chartRows->get($month['key']);

            $chartBulanan['labels'][] = $month['label'];
            $chartBulanan['jumlah'][] = (int) ($row->jumlah ?? 0);
            $chartBulanan['revenue'][] = (float) ($row->revenue ?? 0);
        }

        $terbaru = Pengiriman::query()
            ->with([
                'pengirimKota:id,nama_kota',
                'penerimaKota:id,nama_kota',
            ])
            ->select([
                'id',
                'nomor_resi',
                'pengirim_nama',
                'penerima_kota_id',
                'jenis_layanan',
                'status',
                'estimasi_tiba',
                'created_at',
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (Pengiriman $pengiriman) use ($today): array {
                $estimasiTiba = $pengiriman->estimasi_tiba
                    ? Carbon::parse($pengiriman->estimasi_tiba)
                    : null;

                $isTerlambat = $estimasiTiba !== null
                    && $estimasiTiba->lt($today)
                    && ! in_array($pengiriman->status, ['terkirim', 'dibatalkan', 'gagal'], true);

                return [
                    'id' => $pengiriman->id,
                    'nomor_resi' => $pengiriman->nomor_resi,
                    'pengirim_nama' => $pengiriman->pengirim_nama,
                    'tujuan_kota' => $pengiriman->penerimaKota?->nama_kota,
                    'layanan' => $pengiriman->jenis_layanan,
                    'status' => $pengiriman->status,
                    'estimasi_tiba' => $estimasiTiba?->format('Y-m-d'),
                    'is_terlambat' => $isTerlambat,
                ];
            })
            ->values();

        return Inertia::render('Dashboard/Index', [
            'title' => 'Dashboard',
            'stats' => [
                'total_pengiriman' => $totalPengiriman,
                'total_pendapatan' => $totalPendapatan,
            ],
            'terlambat_count' => $terlambatCount,
            'chart_bulanan' => $chartBulanan,
            'terbaru' => $terbaru,
        ]);
    }
}
