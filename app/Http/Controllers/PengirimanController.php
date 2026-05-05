<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengirimanRequest;
use App\Models\Kota;
use App\Models\Pengiriman;
use App\Models\Tarif;
use App\Services\ResiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PengirimanController extends Controller
{
    public function index(Request $request): Response
    {
        $today = today();

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => trim((string) $request->input('status', '')),
            'layanan' => trim((string) $request->input('layanan', '')),
            'tanggal_mulai' => trim((string) $request->input('tanggal_mulai', '')),
            'tanggal_akhir' => trim((string) $request->input('tanggal_akhir', '')),
            'sort' => trim((string) $request->input('sort', 'terbaru')),
        ];

        $query = Pengiriman::query()
            ->select([
                'id',
                'nomor_resi',
                'pengirim_nama',
                'penerima_nama',
                'penerima_kota_id',
                'jenis_layanan',
                'status',
                'estimasi_tiba',
                'created_at',
            ])
            ->with([
                'penerimaKota:id,nama_kota',
            ]);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($subQuery) use ($search): void {
                $subQuery
                    ->where('nomor_resi', 'like', "%{$search}%")
                    ->orWhere('pengirim_nama', 'like', "%{$search}%")
                    ->orWhere('penerima_nama', 'like', "%{$search}%");
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['layanan'] !== '') {
            $query->where('jenis_layanan', $filters['layanan']);
        }

        if ($filters['tanggal_mulai'] !== '') {
            $query->whereDate('created_at', '>=', $filters['tanggal_mulai']);
        }

        if ($filters['tanggal_akhir'] !== '') {
            $query->whereDate('created_at', '<=', $filters['tanggal_akhir']);
        }

        if ($filters['sort'] === 'terlama') {
            $query->oldest();
        } elseif ($filters['sort'] === 'terlambat') {
            $query
                ->orderByRaw(
                    "CASE
                        WHEN estimasi_tiba < ?
                        AND status NOT IN ('terkirim', 'dibatalkan', 'gagal')
                        THEN 0
                        ELSE 1
                    END ASC",
                    [$today->toDateString()]
                )
                ->latest();
        } else {
            $query->latest();
        }

        $pengiriman = $query
            ->paginate(20)
            ->withQueryString()
            ->through(function (Pengiriman $item) use ($today): array {
                $isTerlambat = $item->estimasi_tiba
                    && $item->estimasi_tiba->lt($today)
                    && ! in_array($item->status, ['terkirim', 'dibatalkan', 'gagal'], true);

                return [
                    'id' => $item->id,
                    'nomor_resi' => $item->nomor_resi,
                    'pengirim_nama' => $item->pengirim_nama,
                    'tujuan_kota' => $item->penerimaKota?->nama_kota,
                    'layanan' => $item->jenis_layanan,
                    'status' => $item->status,
                    'estimasi_tiba' => optional($item->estimasi_tiba)->format('Y-m-d'),
                    'is_terlambat' => $isTerlambat,
                    'created_at' => optional($item->created_at)->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Pengiriman/Index', [
            'title' => 'Data Pengiriman',
            'pengiriman' => $pengiriman,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): Response
    {
        $kotaList = Kota::query()
            ->where('status', 'aktif')
            ->select([
                'id',
                'nama_kota',
                'provinsi',
                'kode_pos',
                'latitude',
                'longitude',
            ])
            ->orderBy('nama_kota')
            ->get();

        return Inertia::render('Pengiriman/Create', [
            'title' => 'Input Pengiriman',
            'kotaList' => $kotaList,
            'prefill' => [
                'asal_id' => $request->query('asal_id'),
                'tujuan_id' => $request->query('tujuan_id'),
                'layanan' => $request->query('layanan'),
                'berat' => $request->query('berat'),
            ],
        ]);
    }

    public function store(PengirimanRequest $request, ResiService $resiService): RedirectResponse
    {
        $validated = $request->validated();

        $barangList = collect($validated['barang'])->map(function (array $barang): array {
            $beratAsli = round((float) $barang['berat_asli_kg'], 2);
            $panjang = round((float) $barang['panjang_cm'], 2);
            $lebar = round((float) $barang['lebar_cm'], 2);
            $tinggi = round((float) $barang['tinggi_cm'], 2);

            $beratVolumetrik = round(($panjang * $lebar * $tinggi) / 6000, 2);
            $beratTagihan = round(max($beratAsli, $beratVolumetrik), 2);

            return [
                'nama_barang' => $barang['nama_barang'],
                'berat_asli_kg' => $beratAsli,
                'panjang_cm' => $panjang,
                'lebar_cm' => $lebar,
                'tinggi_cm' => $tinggi,
                'keterangan' => $barang['keterangan'] ?? null,
                'berat_volumetrik_calc' => $beratVolumetrik,
                'berat_tagihan_calc' => $beratTagihan,
            ];
        });

        $totalBeratAsli = round((float) $barangList->sum('berat_asli_kg'), 2);
        $totalBeratVolumetrik = round((float) $barangList->sum('berat_volumetrik_calc'), 2);
        $totalBeratTagihan = round((float) $barangList->sum('berat_tagihan_calc'), 2);

        $tarif = Tarif::query()
            ->select(['id', 'harga_per_kg', 'estimasi_hari'])
            ->where('kota_asal_id', $validated['pengirim_kota_id'])
            ->where('kota_tujuan_id', $validated['penerima_kota_id'])
            ->where('jenis_layanan', $validated['jenis_layanan'])
            ->where('status', 'aktif')
            ->first();

        $hargaPerKg = (float) ($tarif?->harga_per_kg ?? 0);
        $estimasiHari = (int) ($tarif?->estimasi_hari ?? 3);

        $biayaPengiriman = (float) round($totalBeratTagihan * $hargaPerKg);
        $biayaTambahan = round((float) ($validated['biaya_tambahan'] ?? 0), 2);
        $biayaAsuransi = round((float) ($validated['biaya_asuransi'] ?? 0), 2);

        $kotaPengirim = Kota::query()
            ->select(['id', 'nama_kota'])
            ->find($validated['pengirim_kota_id']);

        $pengiriman = DB::transaction(function () use (
            $validated,
            $barangList,
            $totalBeratAsli,
            $totalBeratVolumetrik,
            $totalBeratTagihan,
            $biayaPengiriman,
            $biayaTambahan,
            $biayaAsuransi,
            $estimasiHari,
            $resiService,
            $request,
            $kotaPengirim
        ): Pengiriman {
            $pengiriman = Pengiriman::create([
                'nomor_resi' => $resiService->generate(),
                'pengirim_nama' => $validated['pengirim_nama'],
                'pengirim_hp' => $validated['pengirim_hp'],
                'pengirim_alamat' => $validated['pengirim_alamat'],
                'pengirim_kota_id' => $validated['pengirim_kota_id'],
                'penerima_nama' => $validated['penerima_nama'],
                'penerima_hp' => $validated['penerima_hp'],
                'penerima_alamat' => $validated['penerima_alamat'],
                'penerima_kota_id' => $validated['penerima_kota_id'],
                'total_berat_asli' => $totalBeratAsli,
                'total_berat_volumetrik' => $totalBeratVolumetrik,
                'total_berat_tagihan' => $totalBeratTagihan,
                'jumlah_barang' => $barangList->count(),
                'jenis_layanan' => $validated['jenis_layanan'],
                'biaya_pengiriman' => $biayaPengiriman,
                'biaya_tambahan' => $biayaTambahan,
                'biaya_asuransi' => $biayaAsuransi,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status' => 'pending',
                'estimasi_tiba' => now()->addDays($estimasiHari)->toDateString(),
                'tanggal_terkirim' => null,
                'catatan' => $validated['catatan'] ?? null,
                'alasan_batal' => null,
                'admin_id' => $request->user()->id,
            ]);

            foreach ($barangList as $barang) {
                $pengiriman->barang()->create([
                    'nama_barang' => $barang['nama_barang'],
                    'berat_asli_kg' => $barang['berat_asli_kg'],
                    'panjang_cm' => $barang['panjang_cm'],
                    'lebar_cm' => $barang['lebar_cm'],
                    'tinggi_cm' => $barang['tinggi_cm'],
                    'keterangan' => $barang['keterangan'],
                ]);
            }

            $pengiriman->trackingHistories()->create([
                'status_lama' => null,
                'status_baru' => 'pending',
                'lokasi' => $kotaPengirim?->nama_kota ?? 'Kota Asal',
                'keterangan' => 'Pengiriman dibuat dan menunggu diproses.',
                'admin_id' => $request->user()->id,
            ]);

            return $pengiriman;
        });

        return redirect()
            ->route('pengiriman.show', $pengiriman)
            ->with('success', 'Pengiriman berhasil disimpan.');
    }

    public function show(Pengiriman $pengiriman): Response
    {
        $pengiriman->load([
            'pengirimKota:id,nama_kota,provinsi,kode_pos',
            'penerimaKota:id,nama_kota,provinsi,kode_pos',
            'barang:id,pengiriman_id,nama_barang,berat_asli_kg,panjang_cm,lebar_cm,tinggi_cm,berat_volumetrik_kg,berat_tagihan_kg,keterangan',
            'trackingHistories' => fn($query) => $query
                ->select([
                    'id',
                    'pengiriman_id',
                    'status_lama',
                    'status_baru',
                    'lokasi',
                    'keterangan',
                    'admin_id',
                    'created_at',
                ])
                ->with('admin:id,nama')
                ->orderByDesc('created_at'),
            'admin:id,nama,email',
        ]);

        $tarifPerKg = (float) $pengiriman->total_berat_tagihan > 0
            ? round((float) $pengiriman->biaya_pengiriman / (float) $pengiriman->total_berat_tagihan, 2)
            : 0;

        return Inertia::render('Pengiriman/Show', [
            'title' => 'Detail Pengiriman',
            'pengiriman' => [
                'id' => $pengiriman->id,
                'nomor_resi' => $pengiriman->nomor_resi,
                'pengirim_nama' => $pengiriman->pengirim_nama,
                'pengirim_hp' => $pengiriman->pengirim_hp,
                'pengirim_alamat' => $pengiriman->pengirim_alamat,
                'pengirim_kota' => $pengiriman->pengirimKota ? [
                    'id' => $pengiriman->pengirimKota->id,
                    'nama_kota' => $pengiriman->pengirimKota->nama_kota,
                    'provinsi' => $pengiriman->pengirimKota->provinsi,
                    'kode_pos' => $pengiriman->pengirimKota->kode_pos,
                ] : null,
                'penerima_nama' => $pengiriman->penerima_nama,
                'penerima_hp' => $pengiriman->penerima_hp,
                'penerima_alamat' => $pengiriman->penerima_alamat,
                'penerima_kota' => $pengiriman->penerimaKota ? [
                    'id' => $pengiriman->penerimaKota->id,
                    'nama_kota' => $pengiriman->penerimaKota->nama_kota,
                    'provinsi' => $pengiriman->penerimaKota->provinsi,
                    'kode_pos' => $pengiriman->penerimaKota->kode_pos,
                ] : null,
                'total_berat_asli' => (float) $pengiriman->total_berat_asli,
                'total_berat_volumetrik' => (float) $pengiriman->total_berat_volumetrik,
                'total_berat_tagihan' => (float) $pengiriman->total_berat_tagihan,
                'jumlah_barang' => (int) $pengiriman->jumlah_barang,
                'jenis_layanan' => $pengiriman->jenis_layanan,
                'biaya_pengiriman' => (float) $pengiriman->biaya_pengiriman,
                'biaya_tambahan' => (float) $pengiriman->biaya_tambahan,
                'biaya_asuransi' => (float) $pengiriman->biaya_asuransi,
                'biaya_total' => (float) $pengiriman->biaya_pengiriman
                    + (float) $pengiriman->biaya_tambahan
                    + (float) $pengiriman->biaya_asuransi,
                'tarif_per_kg' => $tarifPerKg,
                'metode_pembayaran' => $pengiriman->metode_pembayaran,
                'status' => $pengiriman->status,
                'estimasi_tiba' => optional($pengiriman->estimasi_tiba)->format('Y-m-d'),
                'tanggal_terkirim' => optional($pengiriman->tanggal_terkirim)->format('Y-m-d H:i:s'),
                'catatan' => $pengiriman->catatan,
                'alasan_batal' => $pengiriman->alasan_batal,
                'created_at' => optional($pengiriman->created_at)->format('Y-m-d H:i:s'),
                'admin' => $pengiriman->admin ? [
                    'nama' => $pengiriman->admin->nama,
                    'email' => $pengiriman->admin->email,
                ] : null,
                'barang' => $pengiriman->barang
                    ->map(fn($barang) => [
                        'id' => $barang->id,
                        'nama_barang' => $barang->nama_barang,
                        'berat_asli_kg' => (float) $barang->berat_asli_kg,
                        'panjang_cm' => (float) $barang->panjang_cm,
                        'lebar_cm' => (float) $barang->lebar_cm,
                        'tinggi_cm' => (float) $barang->tinggi_cm,
                        'berat_volumetrik_kg' => (float) $barang->berat_volumetrik_kg,
                        'berat_tagihan_kg' => (float) $barang->berat_tagihan_kg,
                        'keterangan' => $barang->keterangan,
                    ])
                    ->values(),
                'tracking_histories' => $pengiriman->trackingHistories
                    ->map(fn($item) => [
                        'id' => $item->id,
                        'status_lama' => $item->status_lama,
                        'status_baru' => $item->status_baru,
                        'lokasi' => $item->lokasi,
                        'keterangan' => $item->keterangan,
                        'admin_nama' => $item->admin?->nama,
                        'created_at' => optional($item->created_at)->format('Y-m-d H:i:s'),
                    ])
                    ->values(),
            ],
        ]);
    }

    public function batal(Request $request, Pengiriman $pengiriman): RedirectResponse
    {
        $validated = $request->validate([
            'alasan_batal' => ['required', 'string', 'max:255'],
        ], [
            'alasan_batal.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        if (in_array($pengiriman->status, ['terkirim', 'dibatalkan', 'gagal'], true)) {
            return back()->with('error', 'Pengiriman ini tidak dapat dibatalkan.');
        }

        $pengiriman->loadMissing('penerimaKota:id,nama_kota');

        DB::transaction(function () use ($pengiriman, $validated, $request): void {
            $pengiriman->trackingHistories()->create([
                'status_lama' => $pengiriman->status,
                'status_baru' => 'dibatalkan',
                'lokasi' => $pengiriman->penerimaKota?->nama_kota ?? 'Tidak diketahui',
                'keterangan' => 'Pengiriman dibatalkan. Alasan: ' . $validated['alasan_batal'],
                'admin_id' => $request->user()->id,
            ]);

            $pengiriman->update([
                'status' => 'dibatalkan',
                'alasan_batal' => $validated['alasan_batal'],
            ]);
        });

        return redirect()
            ->route('pengiriman.show', $pengiriman)
            ->with('success', 'Pengiriman berhasil dibatalkan.');
    }
}
