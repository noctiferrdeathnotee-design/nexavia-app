<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use TCPDF;

class LaporanController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $data = $this->buildReportData($request);

        return Inertia::render('Laporan/Index', [
            'title' => 'Laporan',
            ...$data,
        ]);
    }

    public function pdf(Request $request): HttpResponse
    {
        $data = $this->buildReportData($request);

        $html = view('laporan.pdf', $data)->render();

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Nexavia');
        $pdf->SetTitle('Laporan Pengiriman');
        $pdf->setPrintHeader(false);
        $pdf->setFooterFont(['helvetica', '', 8]);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();
        
        $pdf->writeHTML($html, true, false, true, false, '');

        return response($pdf->Output('laporan-pengiriman-' . now()->format('Ymd-His') . '.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-pengiriman-' . now()->format('Ymd-His') . '.pdf"'
        ]);
    }

    private function buildReportData(Request $request): array
    {
        Carbon::setLocale(config('app.locale', 'id'));

        $periodData = $this->parsePeriod($request);
        $start = $periodData['start'];
        $end = $periodData['end'];

        $tab = (string) $request->string('tab', 'rekap');

        if (! in_array($tab, ['rekap', 'detail', 'keuangan'], true)) {
            $tab = 'rekap';
        }

        $baseQuery = Pengiriman::query()
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        $statusKeys = [
            'pending',
            'diproses',
            'dalam_perjalanan',
            'tiba_di_kota_tujuan',
            'sedang_diantar',
            'terkirim',
            'gagal',
            'dibatalkan',
        ];

        $layananKeys = [
            'ekonomis',
            'reguler',
            'express',
            'same_day',
        ];

        $rekapStatus = array_fill_keys($statusKeys, 0);
        $rekapLayanan = array_fill_keys($layananKeys, 0);

        $statusRows = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($statusRows as $status => $total) {
            if (array_key_exists($status, $rekapStatus)) {
                $rekapStatus[$status] = (int) $total;
            }
        }

        $layananRows = (clone $baseQuery)
            ->select('jenis_layanan', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_layanan')
            ->pluck('total', 'jenis_layanan');

        foreach ($layananRows as $layanan => $total) {
            if (array_key_exists($layanan, $rekapLayanan)) {
                $rekapLayanan[$layanan] = (int) $total;
            }
        }

        $totalPengiriman = (clone $baseQuery)->count();

        $totalTerkirim = (clone $baseQuery)
            ->where('status', 'terkirim')
            ->count();

        $totalBatal = (clone $baseQuery)
            ->whereIn('status', ['dibatalkan', 'gagal'])
            ->count();

        $totalPendapatan = (float) (clone $baseQuery)
            ->where('status', 'terkirim')
            ->sum(DB::raw('biaya_pengiriman + biaya_tambahan + biaya_asuransi'));

        $rataBeratTagihan = (float) ((clone $baseQuery)->avg('total_berat_tagihan') ?? 0);

        $onTimeCount = 0;
        $lateCount = 0;
        
        $terkirimItems = (clone $baseQuery)
            ->where('status', 'terkirim')
            ->whereNotNull('tanggal_terkirim')
            ->get(['tanggal_terkirim', 'estimasi_tiba']);

        foreach ($terkirimItems as $item) {
            $tglTerkirim = Carbon::parse($item->tanggal_terkirim)->startOfDay();
            if ($item->estimasi_tiba) {
                $estimasi = Carbon::parse($item->estimasi_tiba)->startOfDay();
                if ($tglTerkirim->lte($estimasi)) {
                    $onTimeCount++;
                } else {
                    $lateCount++;
                }
            } else {
                $onTimeCount++; // if no estimation, consider it on time or adjust as needed
            }
        }

        $tabelPengiriman = (clone $baseQuery)
            ->with([
                'pengirimKota:id,nama_kota',
                'penerimaKota:id,nama_kota',
            ])
            ->latest()
            ->limit(100)
            ->get()
            ->map(function (Pengiriman $item): array {
                $totalBiaya = (float) $item->biaya_pengiriman
                    + (float) $item->biaya_tambahan
                    + (float) $item->biaya_asuransi;

                return [
                    'id' => $item->id,
                    'nomor_resi' => $item->nomor_resi,
                    'tanggal' => optional($item->created_at)->format('Y-m-d H:i:s'),
                    'pengirim' => $item->pengirim_nama,
                    'kota_asal' => $item->pengirimKota?->nama_kota,
                    'tujuan' => $item->penerimaKota?->nama_kota,
                    'layanan' => $item->jenis_layanan,
                    'berat_asli' => (float) $item->total_berat_asli,
                    'berat_tagihan' => (float) $item->total_berat_tagihan,
                    'status' => $item->status,
                    'biaya' => $totalBiaya,
                ];
            })
            ->values();

        $breakdownRows = (clone $baseQuery)
            ->select('jenis_layanan')
            ->selectRaw('COUNT(*) as jumlah_pengiriman')
            ->selectRaw(
                "SUM(
                    CASE
                        WHEN status = 'terkirim'
                        THEN biaya_pengiriman + biaya_tambahan + biaya_asuransi
                        ELSE 0
                    END
                ) as total_pendapatan"
            )
            ->groupBy('jenis_layanan')
            ->get()
            ->keyBy('jenis_layanan');

        $breakdownLayanan = collect($layananKeys)
            ->map(function (string $layanan) use ($breakdownRows): array {
                $row = $breakdownRows->get($layanan);

                $jumlahPengiriman = (int) ($row->jumlah_pengiriman ?? 0);
                $totalPendapatan = (float) ($row->total_pendapatan ?? 0);
                $rataRata = $jumlahPengiriman > 0
                    ? $totalPendapatan / $jumlahPengiriman
                    : 0;

                return [
                    'jenis_layanan' => $layanan,
                    'jumlah_pengiriman' => $jumlahPengiriman,
                    'total_pendapatan' => $totalPendapatan,
                    'rata_rata' => $rataRata,
                ];
            })
            ->values();

        return [
            'periodeLabel' => $periodData['periodeLabel'],
            'period' => $periodData['period'],
            'tab' => $tab,
            'mulai' => $periodData['mulai'],
            'akhir' => $periodData['akhir'],
            'rekapStatus' => $rekapStatus,
            'rekapLayanan' => $rekapLayanan,
            'totalPengiriman' => $totalPengiriman,
            'totalTerkirim' => $totalTerkirim,
            'totalBatal' => $totalBatal,
            'totalPendapatan' => $totalPendapatan,
            'rataBeratTagihan' => $rataBeratTagihan,
            'onTimeCount' => $onTimeCount,
            'lateCount' => $lateCount,
            'tabelPengiriman' => $tabelPengiriman,
            'breakdownLayanan' => $breakdownLayanan,
            'tanggalCetak' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function parsePeriod(Request $request): array
    {
        $period = (string) $request->string('period', 'bulan_ini');
        $allowedPeriods = ['hari_ini', 'minggu_ini', 'bulan_ini', 'bulan_lalu', 'tahun_ini', 'custom'];

        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'bulan_ini';
        }

        $mulai = (string) $request->string('mulai');
        $akhir = (string) $request->string('akhir');

        try {
            switch ($period) {
                case 'hari_ini':
                    $start = now()->startOfDay();
                    $end = now()->endOfDay();
                    break;

                case 'minggu_ini':
                    $start = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
                    $end = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                    break;

                case 'bulan_lalu':
                    $baseDate = now()->subMonthNoOverflow();
                    $start = $baseDate->copy()->startOfMonth()->startOfDay();
                    $end = $baseDate->copy()->endOfMonth()->endOfDay();
                    break;

                case 'tahun_ini':
                    $start = now()->startOfYear()->startOfDay();
                    $end = now()->endOfYear()->endOfDay();
                    break;

                case 'custom':
                    if ($mulai === '' || $akhir === '') {
                        $period = 'bulan_ini';
                        $start = now()->startOfMonth()->startOfDay();
                        $end = now()->endOfMonth()->endOfDay();
                    } else {
                        $start = Carbon::parse($mulai)->startOfDay();
                        $end = Carbon::parse($akhir)->endOfDay();

                        if ($start->gt($end)) {
                            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                        }
                    }
                    break;

                case 'bulan_ini':
                default:
                    $start = now()->startOfMonth()->startOfDay();
                    $end = now()->endOfMonth()->endOfDay();
                    break;
            }
        } catch (\Throwable) {
            $period = 'bulan_ini';
            $start = now()->startOfMonth()->startOfDay();
            $end = now()->endOfMonth()->endOfDay();
        }

        return [
            'period' => $period,
            'mulai' => $start->toDateString(),
            'akhir' => $end->toDateString(),
            'start' => $start,
            'end' => $end,
            'periodeLabel' => $start->translatedFormat('j M Y') . ' – ' . $end->translatedFormat('j M Y'),
        ];
    }
}
