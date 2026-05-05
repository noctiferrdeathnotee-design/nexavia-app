<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pengiriman</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 12px;
        }

        .header-table,
        .rekap-table,
        .status-table,
        .layanan-table,
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }

        .brand-title {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 9px;
            color: #6b7280;
            margin-top: 3px;
        }

        .report-title {
            text-align: right;
            font-size: 15px;
            font-weight: bold;
            color: #111827;
        }

        .report-meta {
            text-align: right;
            font-size: 9px;
            color: #6b7280;
            margin-top: 4px;
        }

        .section {
            margin-top: 14px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 6px;
            color: #374151;
        }

        .rekap-table td,
        .status-table th,
        .status-table td,
        .layanan-table th,
        .layanan-table td,
        .detail-table th,
        .detail-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 7px;
            vertical-align: top;
        }

        .status-table th,
        .layanan-table th,
        .detail-table th {
            background: #f8fafc;
            text-align: left;
            color: #475569;
            font-weight: bold;
        }

        .label-cell {
            width: 42%;
            background: #f8fafc;
            font-weight: bold;
            color: #374151;
        }

        .footer-fixed {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #6b7280;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            padding-top: 8px;
        }

        .small-muted {
            color: #6b7280;
        }
    </style>
</head>

<body>
    @php
        $logoPath = public_path('images/logo-brand.png');
        $successRate = $totalPengiriman > 0 ? ($totalTerkirim / $totalPengiriman) * 100 : 0;
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td width="55%">
                    <table>
                        <tr>
                            <td width="52">
                                @if (file_exists($logoPath))
                                    <img src="{{ $logoPath }}" alt="Nexavia" class="logo">
                                @endif
                            </td>
                            <td>
                                <div class="brand-title">Nexavia</div>
                                <div class="brand-subtitle">Sistem Manajemen Pengiriman</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="45%">
                    <div class="report-title">LAPORAN PENGIRIMAN</div>
                    <div class="report-meta">Periode: {{ $periodeLabel }}</div>
                    <div class="report-meta">Dicetak:
                        {{ \Carbon\Carbon::parse($tanggalCetak)->translatedFormat('d M Y H:i') }} WIB</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Rekap Utama</div>

        <table class="rekap-table">
            <tr>
                <td class="label-cell">Total Pengiriman</td>
                <td>{{ number_format($totalPengiriman, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label-cell">Terkirim</td>
                <td>{{ number_format($totalTerkirim, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label-cell">Batal / Gagal</td>
                <td>{{ number_format($totalBatal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label-cell">Sukses Rate</td>
                <td>{{ number_format($successRate, 2, ',', '.') }}%</td>
            </tr>
            <tr>
                <td class="label-cell">Pendapatan Total</td>
                <td>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Rekap Per Status</div>

        <table class="status-table">
            <thead>
                <tr>
                    <th>Pending</th>
                    <th>Diproses</th>
                    <th>Perjalanan</th>
                    <th>Tiba Kota</th>
                    <th>Diantar</th>
                    <th>Terkirim</th>
                    <th>Gagal</th>
                    <th>Dibatalkan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($rekapStatus['pending'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($rekapStatus['diproses'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($rekapStatus['dalam_perjalanan'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($rekapStatus['tiba_di_kota_tujuan'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($rekapStatus['sedang_diantar'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($rekapStatus['terkirim'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($rekapStatus['gagal'] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($rekapStatus['dibatalkan'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Rekap Per Layanan</div>

        <table class="layanan-table">
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Jumlah</th>
                    <th>Pendapatan</th>
                    <th>Avg Biaya</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($breakdownLayanan as $item)
                    <tr>
                        <td>{{ str_replace('_', ' ', ucfirst($item['jenis_layanan'])) }}</td>
                        <td>{{ number_format($item['jumlah_pengiriman'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item['total_pendapatan'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item['rata_rata'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Daftar Pengiriman</div>

        <table class="detail-table">
            <thead>
                <tr>
                    <th>No Resi</th>
                    <th>Tanggal</th>
                    <th>Pengirim</th>
                    <th>Kota Asal → Tujuan</th>
                    <th>Layanan</th>
                    <th>B. Tagihan</th>
                    <th>Status</th>
                    <th>Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tabelPengiriman as $item)
                    <tr>
                        <td>{{ $item['nomor_resi'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['tanggal'])->translatedFormat('d M Y H:i') }}</td>
                        <td>{{ $item['pengirim'] }}</td>
                        <td>{{ $item['kota_asal'] ?: '-' }} → {{ $item['tujuan'] ?: '-' }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($item['layanan'])) }}</td>
                        <td>{{ number_format($item['berat_tagihan'], 2, ',', '.') }} kg</td>
                        <td>{{ str_replace('_', ' ', ucfirst($item['status'])) }}</td>
                        <td>Rp {{ number_format($item['biaya'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="small-muted" style="text-align:center;">Tidak ada data pengiriman pada
                            periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer-fixed">
        <table class="footer-table">
            <tr>
                <td align="left">Dicetak oleh sistem Nexavia</td>
                <td align="right"></td>
            </tr>
        </table>
    </div>
</body>

</html>
