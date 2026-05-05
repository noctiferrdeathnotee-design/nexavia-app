<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>PDF Resi - {{ $pengiriman->nomor_resi }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
        }

        .wrapper {
            width: 100%;
        }

        .header-table,
        .info-table,
        .barang-table,
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td,
        .info-table td,
        .two-col td {
            vertical-align: top;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .brand-title {
            font-size: 18px;
            font-weight: bold;
            color: #374151;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }

        .barcode-box {
            text-align: right;
        }

        .resi-box {
            margin-top: 10px;
            border: 1px solid #c7d2fe;
            background: #eef2ff;
            border-radius: 8px;
            padding: 10px;
        }

        .resi-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 4px;
        }

        .resi-number {
            font-size: 20px;
            font-weight: bold;
            color: #312e81;
            font-family: DejaVu Sans Mono, monospace;
            margin-bottom: 8px;
        }

        .qr-center {
            text-align: center;
        }

        .qr-center svg {
            width: 88px;
            height: 88px;
        }

        .tracking-url {
            margin-top: 4px;
            font-size: 8px;
            color: #6b7280;
            word-break: break-all;
        }

        .section {
            margin-top: 10px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 6px;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
        }

        .info-table td {
            width: 50%;
            padding-right: 6px;
        }

        .meta-line {
            margin-bottom: 4px;
            line-height: 1.45;
            color: #4b5563;
        }

        .meta-line strong {
            color: #374151;
        }

        .barang-table th,
        .barang-table td {
            border: 1px solid #e5e7eb;
            padding: 5px 6px;
            font-size: 9px;
            vertical-align: top;
        }

        .barang-table th {
            background: #f8fafc;
            text-align: left;
            color: #475569;
        }

        .barang-table tfoot td {
            background: #f8fafc;
            font-weight: bold;
        }

        .two-col td {
            width: 50%;
            padding-right: 6px;
        }

        .summary-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
            min-height: 120px;
        }

        .summary-title {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 6px;
        }

        .summary-line {
            margin-bottom: 5px;
            color: #4b5563;
        }

        .summary-line .label {
            display: inline-block;
            width: 42%;
        }

        .summary-line .value {
            display: inline-block;
            width: 56%;
            font-weight: bold;
            color: #374151;
        }

        .summary-total {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            font-weight: bold;
            color: #312e81;
        }

        .footer {
            margin-top: 10px;
            text-align: right;
            font-size: 9px;
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-pending {
            background: #e2e8f0;
            color: #475569;
        }

        .badge-diproses {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-dalam_perjalanan {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-tiba_di_kota_tujuan {
            background: #e0e7ff;
            color: #4338ca;
        }

        .badge-sedang_diantar {
            background: #f3e8ff;
            color: #7e22ce;
        }

        .badge-terkirim {
            background: #d1fae5;
            color: #047857;
        }

        .badge-gagal {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-dibatalkan {
            background: #f1f5f9;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    @php
        $badgeClass = 'badge-' . $pengiriman->status;
        $tarifPerKg =
            $pengiriman->total_berat_tagihan > 0
                ? round((float) $pengiriman->biaya_pengiriman / (float) $pengiriman->total_berat_tagihan, 2)
                : 0;
        $biayaTotal =
            (float) $pengiriman->biaya_pengiriman +
            (float) $pengiriman->biaya_asuransi +
            (float) $pengiriman->biaya_tambahan;
        $logoPath = public_path('images/logo-brand.png');
    @endphp

    <div class="wrapper">
        <table class="header-table">
            <tr>
                <td width="58%">
                    <table>
                        <tr>
                            <td width="52">
                                @if (file_exists($logoPath))
                                    <img src="{{ $logoPath }}" alt="Nexavia" class="brand-logo">
                                @endif
                            </td>
                            <td>
                                <div class="brand-title">SoftSend</div>
                                <div class="brand-subtitle">Sistem Manajemen Pengiriman</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="42%" class="barcode-box">
                    <img src="{{ $barcode }}" alt="Barcode" style="max-height: 42px; max-width: 100%;">
                    <div style="font-size:10px; margin-top:4px; color:#475569;">
                        {{ $pengiriman->nomor_resi }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="resi-box">
            <div class="resi-label">Nomor Resi</div>
            <div class="resi-number">{{ $pengiriman->nomor_resi }}</div>

            <div class="qr-center">
                <img src="{{ $qrCode }}" alt="QR Code" style="width: 88px; height: 88px;">
                <div class="tracking-url">{{ $trackingUrl }}</div>
            </div>
        </div>

        <div class="section">
            <table class="info-table">
                <tr>
                    <td>
                        <div class="card">
                            <div class="section-title">Pengirim</div>
                            <div class="meta-line"><strong>Nama:</strong> {{ $pengiriman->pengirim_nama }}</div>
                            <div class="meta-line"><strong>HP:</strong> {{ $pengiriman->pengirim_hp }}</div>
                            <div class="meta-line"><strong>Alamat:</strong> {{ $pengiriman->pengirim_alamat }}</div>
                            <div class="meta-line">
                                <strong>Kota:</strong>
                                {{ $pengiriman->pengirimKota?->nama_kota ?? '-' }},
                                {{ $pengiriman->pengirimKota?->provinsi ?? '-' }}
                                ({{ $pengiriman->pengirimKota?->kode_pos ?? '-' }})
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="card">
                            <div class="section-title">Penerima</div>
                            <div class="meta-line"><strong>Nama:</strong> {{ $pengiriman->penerima_nama }}</div>
                            <div class="meta-line"><strong>HP:</strong> {{ $pengiriman->penerima_hp }}</div>
                            <div class="meta-line"><strong>Alamat:</strong> {{ $pengiriman->penerima_alamat }}</div>
                            <div class="meta-line">
                                <strong>Kota:</strong>
                                {{ $pengiriman->penerimaKota?->nama_kota ?? '-' }},
                                {{ $pengiriman->penerimaKota?->provinsi ?? '-' }}
                                ({{ $pengiriman->penerimaKota?->kode_pos ?? '-' }})
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Daftar Barang</div>

            <table class="barang-table">
                <thead>
                    <tr>
                        <th width="4%">#</th>
                        <th width="22%">Nama</th>
                        <th width="12%">Asli</th>
                        <th width="13%">Volumetrik</th>
                        <th width="12%">Tagihan</th>
                        <th width="18%">Dimensi</th>
                        <th>Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengiriman->barang as $index => $barang)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $barang->nama_barang }}</td>
                            <td>{{ number_format((float) $barang->berat_asli_kg, 2, ',', '.') }} kg</td>
                            <td>{{ number_format((float) $barang->berat_volumetrik_kg, 2, ',', '.') }} kg</td>
                            <td>{{ number_format((float) $barang->berat_tagihan_kg, 2, ',', '.') }} kg</td>
                            <td>
                                {{ number_format((float) $barang->panjang_cm, 2, ',', '.') }}
                                ×
                                {{ number_format((float) $barang->lebar_cm, 2, ',', '.') }}
                                ×
                                {{ number_format((float) $barang->tinggi_cm, 2, ',', '.') }} cm
                            </td>
                            <td>{{ $barang->keterangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">Tidak ada data barang.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total</td>
                        <td>{{ number_format((float) $pengiriman->total_berat_asli, 2, ',', '.') }} kg</td>
                        <td>{{ number_format((float) $pengiriman->total_berat_volumetrik, 2, ',', '.') }} kg</td>
                        <td>{{ number_format((float) $pengiriman->total_berat_tagihan, 2, ',', '.') }} kg</td>
                        <td>-</td>
                        <td>{{ $pengiriman->jumlah_barang }} barang</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="section">
            <table class="two-col">
                <tr>
                    <td>
                        <div class="summary-box">
                            <div class="summary-title">Info Pengiriman</div>

                            <div class="summary-line">
                                <span class="label">Layanan</span>
                                <span
                                    class="value">{{ str_replace('_', ' ', ucfirst($pengiriman->jenis_layanan)) }}</span>
                            </div>

                            <div class="summary-line">
                                <span class="label">Estimasi</span>
                                <span class="value">{{ optional($pengiriman->estimasi_tiba)->format('d M Y') }}</span>
                            </div>

                            <div class="summary-line">
                                <span class="label">Metode</span>
                                <span
                                    class="value">{{ str_replace('_', ' ', ucfirst($pengiriman->metode_pembayaran)) }}</span>
                            </div>

                            <div class="summary-line">
                                <span class="label">Status</span>
                                <span class="value"><span
                                        class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', ucfirst($pengiriman->status)) }}</span></span>
                            </div>

                            <div class="summary-line">
                                <span class="label">Admin</span>
                                <span class="value">{{ $pengiriman->admin?->nama ?? '-' }}</span>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="summary-box">
                            <div class="summary-title">Biaya</div>

                            <div class="summary-line">
                                <span class="label">Tarif / kg</span>
                                <span class="value">Rp {{ number_format($tarifPerKg, 0, ',', '.') }}</span>
                            </div>

                            <div class="summary-line">
                                <span class="label">Ongkir</span>
                                <span class="value">Rp
                                    {{ number_format((float) $pengiriman->biaya_pengiriman, 0, ',', '.') }}</span>
                            </div>

                            <div class="summary-line">
                                <span class="label">Asuransi</span>
                                <span class="value">Rp
                                    {{ number_format((float) $pengiriman->biaya_asuransi, 0, ',', '.') }}</span>
                            </div>

                            <div class="summary-line">
                                <span class="label">Tambahan</span>
                                <span class="value">Rp
                                    {{ number_format((float) $pengiriman->biaya_tambahan, 0, ',', '.') }}</span>
                            </div>

                            <div class="summary-total">
                                Total: Rp {{ number_format($biayaTotal, 0, ',', '.') }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Dicetak oleh sistem Nexavia · {{ now()->format('d M Y H:i') }} WIB
        </div>
    </div>
</body>

</html>
