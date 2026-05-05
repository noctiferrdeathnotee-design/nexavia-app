<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackingRequest;
use App\Models\Pengiriman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TrackingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tracking/Index', [
            'title' => 'Tracking',
        ]);
    }

    public function show(string $resi): Response
    {
        $nomorResi = strtoupper(trim($resi));

        $pengiriman = Pengiriman::query()
            ->where('nomor_resi', $nomorResi)
            ->with([
                'pengirimKota:id,nama_kota,provinsi,kode_pos,latitude,longitude',
                'penerimaKota:id,nama_kota,provinsi,kode_pos,latitude,longitude',
                'trackingHistories' => fn($query) => $query->orderByDesc('created_at'),
            ])
            ->firstOrFail();

        return Inertia::render('Tracking/Show', [
            'title' => 'Detail Tracking',
            'pengiriman' => [
                'id' => $pengiriman->id,
                'nomor_resi' => $pengiriman->nomor_resi,
                'status' => $pengiriman->status,
                'jenis_layanan' => $pengiriman->jenis_layanan,
                'estimasi_tiba' => optional($pengiriman->estimasi_tiba)->format('Y-m-d'),
                'pengirim_nama' => $pengiriman->pengirim_nama,
                'penerima_nama' => $pengiriman->penerima_nama,
                'pengirim_kota' => $pengiriman->pengirimKota ? [
                    'nama_kota' => $pengiriman->pengirimKota->nama_kota,
                    'provinsi' => $pengiriman->pengirimKota->provinsi,
                    'kode_pos' => $pengiriman->pengirimKota->kode_pos,
                    'latitude' => (float) $pengiriman->pengirimKota->latitude,
                    'longitude' => (float) $pengiriman->pengirimKota->longitude,
                ] : null,
                'penerima_kota' => $pengiriman->penerimaKota ? [
                    'nama_kota' => $pengiriman->penerimaKota->nama_kota,
                    'provinsi' => $pengiriman->penerimaKota->provinsi,
                    'kode_pos' => $pengiriman->penerimaKota->kode_pos,
                    'latitude' => (float) $pengiriman->penerimaKota->latitude,
                    'longitude' => (float) $pengiriman->penerimaKota->longitude,
                ] : null,
                'tracking_histories' => $pengiriman->trackingHistories
                    ->map(fn($item) => [
                        'id' => $item->id,
                        'status_lama' => $item->status_lama,
                        'status_baru' => $item->status_baru,
                        'lokasi' => $item->lokasi,
                        'keterangan' => $item->keterangan,
                        'created_at' => optional($item->created_at)->format('Y-m-d H:i:s'),
                    ])
                    ->values(),
            ],
        ]);
    }

    public function cari(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'nomor_resi' => ['required', 'string', 'max:25'],
            ],
            [
                'nomor_resi.required' => 'Nomor resi wajib diisi.',
            ]
        );

        $nomorResi = strtoupper(trim($validated['nomor_resi']));

        $exists = Pengiriman::query()
            ->where('nomor_resi', $nomorResi)
            ->exists();

        if (! $exists) {
            return response()->json([
                'found' => false,
                'message' => 'Nomor resi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'found' => true,
            'redirect_url' => route('tracking.show', $nomorResi),
        ]);
    }

    public function store(TrackingRequest $request, Pengiriman $pengiriman): RedirectResponse
    {
        if (in_array($pengiriman->status, ['terkirim', 'gagal', 'dibatalkan'], true)) {
            return redirect()
                ->route('pengiriman.show', $pengiriman)
                ->with('error', 'Status pengiriman terminal dan tidak dapat diubah lagi.');
        }

        $validated = $request->validated();
        $allowedStatuses = $this->allowedNextStatusesFor($pengiriman->status);

        if (! in_array($validated['status_baru'], $allowedStatuses, true)) {
            return redirect()
                ->route('pengiriman.show', $pengiriman)
                ->with('error', 'Transisi status tidak valid untuk status saat ini.');
        }

        DB::transaction(function () use ($pengiriman, $validated, $request): void {
            $lokasi = trim($validated['lokasi']);
            $keterangan = trim($validated['keterangan']);

            $pengiriman->trackingHistories()->create([
                'status_lama' => $pengiriman->status,
                'status_baru' => $validated['status_baru'],
                'lokasi' => $lokasi,
                'keterangan' => $keterangan,
                'admin_id' => $request->user()->id,
            ]);

            $updateData = [
                'status' => $validated['status_baru'],
            ];

            if ($validated['status_baru'] === 'terkirim') {
                $updateData['tanggal_terkirim'] = now();
            }

            if ($validated['status_baru'] === 'dibatalkan') {
                $updateData['alasan_batal'] = $keterangan;
            }

            $pengiriman->update($updateData);
        });

        return redirect()
            ->route('pengiriman.show', $pengiriman)
            ->with('success', 'Status pengiriman berhasil diperbarui.');
    }

    private function allowedNextStatusesFor(string $currentStatus): array
    {
        return match ($currentStatus) {
            'pending' => ['diproses', 'gagal', 'dibatalkan'],
            'diproses' => ['dalam_perjalanan', 'gagal', 'dibatalkan'],
            'dalam_perjalanan' => ['tiba_di_kota_tujuan', 'gagal', 'dibatalkan'],
            'tiba_di_kota_tujuan' => ['sedang_diantar', 'gagal', 'dibatalkan'],
            'sedang_diantar' => ['terkirim', 'gagal', 'dibatalkan'],
            default => [],
        };
    }
}
