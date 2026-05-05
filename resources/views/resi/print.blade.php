<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resi - {{ $pengiriman->nomor_resi }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        .page {
            max-width: 700px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 10mm;
        }

        .top-actions {
            max-width: 700px;
            margin: 16px auto 0;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #6366f1;
            color: #ffffff;
            border-color: #6366f1;
        }

        .header-table,
        .info-table,
        .barang-table,
        .biaya-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 4px;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 700;
            color: #334155;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .barcode-box {
            text-align: right;
        }

        .barcode-box svg {
            max-width: 100%;
            height: auto;
        }

        .resi-box {
            margin-top: 14px;
            border: 1px solid #c7d2fe;
            background: #eef2ff;
            border-radius: 12px;
            padding: 14px;
        }

        .resi-label {
            font-size: 11px;
            letter-spacing: 1.4px;
            color: #6366f1;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .resi-number {
            font-size: 26px;
            font-weight: 700;
            color: #312e81;
            font-family: "Courier New", Courier, monospace;
            margin-bottom: 10px;
        }

        .qr-center {
            text-align: center;
        }

        .qr-center svg {
            width: 100px;
            height: 100px;
        }

        .tracking-url {
            margin-top: 6px;
            font-size: 10px;
            color: #64748b;
            word-break: break-all;
        }

        .section {
            margin-top: 14px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin: 0 0 8px;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
            background: #ffffff;
        }

        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 6px 0 0;
        }

        .meta-line {
            margin-bottom: 6px;
            font-size: 12px;
            line-height: 1.5;
            color: #475569;
        }

        .meta-line strong {
            color: #334155;
        }

        .barang-table th,
        .barang-table td,
        .biaya-table td {
            border: 1px solid #e2e8f0;
            padding: 7px 8px;
            font-size: 11px;
            vertical-align: top;
        }

        .barang-table th {
            background: #f8fafc;
            color: #475569;
            text-align: left;
            font-weight: 700;
        }

        .barang-table tfoot td {
            background: #f8fafc;
            font-weight: 700;
        }

        .two-col {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .two-col td {
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }

        .summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
            min-height: 145px;
        }

        .summary-title {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #334155;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            margin-bottom: 6px;
            color: #475569;
        }

        .summary-line strong {
            color: #334155;
        }

        .summary-total {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            font-weight: 700;
            color: #312e81;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
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

        .footer {
            margin-top: 14px;
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .top-actions,
            .no-print {
                display: none !important;
            }

            .page {
                margin: 0;
                border: none;
                max-width: none;
                padding: 0;
            }

            @page {
                margin: 8mm;
                size: auto;
            }
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
        $logoWebPath = '/images/logo-brand.png';
    @endphp

    <div class="top-actions no-print">
        <a href="{{ route('pengiriman.show', $pengiriman) }}" class="btn">Kembali</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Resi</button>
    </div>

    <div class="page">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-wrap">
                        <img src="{{ $logoWebPath }}" alt="Nexavia" class="brand-logo">
                        <div>
                            <p class="brand-title">Nexavia</p>
                            <div class="brand-subtitle">Sistem Manajemen Pengiriman</div>
                        </div>
                    </div>
                </td>
                <td width="42%" class="barcode-box">
                    <img src="{{ $barcode }}" alt="Barcode" style="max-height: 48px; max-width: 100%;">
                    <div
                        style="font-size:11px; color:#475569; margin-top:4px; font-family:'Courier New', Courier, monospace;">
                        {{ $pengiriman->nomor_resi }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="resi-box">
            <div class="resi-label">Nomor Resi</div>
            <div class="resi-number">{{ $pengiriman->nomor_resi }}</div>

            <div class="qr-center">
                <img src="{{ $qrCode }}" alt="QR Code" style="width: 100px; height: 100px;">
                <div class="tracking-url">{{ $trackingUrl }}</div>
            </div>
        </div>

        <div class="section">
            <table class="info-table">
                <tr>
                    <td>
                        <div class="card">
                            <h3 class="section-title">Pengirim</h3>
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
                            <h3 class="section-title">Penerima</h3>
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
            <h3 class="section-title">Daftar Barang</h3>

            <table class="barang-table">
                <thead>
                    <tr>
                        <th width="4%">#</th>
                        <th width="20%">Nama</th>
                        <th width="12%">Berat Asli</th>
                        <th width="13%">Volumetrik</th>
                        <th width="12%">Tagihan</th>
                        <th width="19%">Dimensi</th>
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
                                <span>Layanan</span>
                                <strong>{{ str_replace('_', ' ', ucfirst($pengiriman->jenis_layanan)) }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Estimasi</span>
                                <strong>{{ optional($pengiriman->estimasi_tiba)->format('d M Y') }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Metode</span>
                                <strong>{{ str_replace('_', ' ', ucfirst($pengiriman->metode_pembayaran)) }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Status</span>
                                <strong><span
                                        class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', ucfirst($pengiriman->status)) }}</span></strong>
                            </div>
                            <div class="summary-line">
                                <span>Admin</span>
                                <strong>{{ $pengiriman->admin?->nama ?? '-' }}</strong>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="summary-box">
                            <div class="summary-title">Biaya</div>
                            <div class="summary-line">
                                <span>Tarif / kg</span>
                                <strong>Rp {{ number_format($tarifPerKg, 0, ',', '.') }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Ongkir</span>
                                <strong>Rp
                                    {{ number_format((float) $pengiriman->biaya_pengiriman, 0, ',', '.') }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Asuransi</span>
                                <strong>Rp
                                    {{ number_format((float) $pengiriman->biaya_asuransi, 0, ',', '.') }}</strong>
                            </div>
                            <div class="summary-line">
                                <span>Tambahan</span>
                                <strong>Rp
                                    {{ number_format((float) $pengiriman->biaya_tambahan, 0, ',', '.') }}</strong>
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
            Dicetak pada {{ now()->format('d M Y H:i') }} WIB
        </div>
    </div>
</body>

</html>
