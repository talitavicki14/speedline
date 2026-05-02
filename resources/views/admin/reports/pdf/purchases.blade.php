<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian - Speedline Automotive</title>
    <style>
        {!! file_get_contents(resource_path('css/pdf/export.css')) !!}
    </style>
</head>
<body>
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <img src="{{ public_path('images/logo_black.png') }}" class="logo-img">
                </td>
                <td class="header-subtitle">
                    Riwayat Pengadaan Stok
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <table class="footer-table">
            <tr>
                <td style="text-align: left;">
                    Laporan ini dibuat secara otomatis pada {{ date('d/m/Y H:i') }}
                </td>
                <td style="text-align: right;">
                    <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </footer>

    <div class="content">
        <div class="report-title">LAPORAN PEMBELIAN</div>
        <div class="report-period">
            Periode: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}
        </div>

        <table class="stats-table">
            <tr>
                <td style="padding-right: 10px; width: 33%;">
                    <div class="stats-card">
                        <span class="stats-label">Total Stok Masuk</span>
                        <div class="stats-value">{{ number_format($totalQty, 0, ',', '.') }} pcs</div>
                    </div>
                </td>
                <td style="padding-right: 10px; width: 33%;">
                    <div class="stats-card" style="background: #0f172a;">
                        <span class="stats-label" style="color: #94a3b8;">Total Pengeluaran</span>
                        <div class="stats-value" style="color: #ffffff;">Rp {{ number_format($totalSpending, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 34%;">
                    <div class="stats-card" style="border: 1px solid #e2e8f0; background: #f8fafc;">
                        <span class="stats-label">Main Distributor</span>
                        <div class="stats-value" style="font-size: 10px;">{{ $purchases->first()->distributor->name ?? '—' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        @if($chartBase64)
        <div class="chart-box" style="margin-bottom: 25px;">
            <div style="font-size: 8px; font-weight: bold; margin-bottom: 12px; color: #94a3b8; text-transform: uppercase;">Tren Nilai Pengeluaran (6 Bulan Terakhir)</div>
            <img src="{{ $chartBase64 }}" class="chart-img">
        </div>
        @endif

        <div style="margin-bottom: 25px;">
            <div style="width: 48%; display: inline-block; vertical-align: top; margin-right: 2%;">
                <div class="section-title">TOP 5 PENGADAAN BARANG</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">RANK</th>
                            <th style="width: 50%;">NAMA</th>
                            <th style="width: 20%; text-align: center;">UNIT</th>
                            <th style="width: 15%; text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topPurchased as $index => $top)
                        <tr>
                            <td style="text-align: center;">#{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $top->sparepart->name }}</td>
                            <td style="text-align: center;">{{ $top->total_qty }}</td>
                            <td style="text-align: right;">{{ $totalQty > 0 ? number_format(($top->total_qty / $totalQty) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="width: 48%; display: inline-block; vertical-align: top;">
                <div class="section-title">TOP 5 DISTRIBUTOR</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">RANK</th>
                            <th style="width: 50%;">NAMA</th>
                            <th style="width: 20%; text-align: center;">TOTAL</th>
                            <th style="width: 15%; text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topDistributors as $index => $top)
                        <tr>
                            <td style="text-align: center;">#{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $top->distributor->name }}</td>
                            <td style="text-align: center;">{{ number_format($top->total_spent / 1000000, 1) }}jt</td>
                            <td style="text-align: right;">{{ $totalSpending > 0 ? number_format(($top->total_spent / $totalSpending) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-title">DETAIL RIWAYAT PEMBELIAN</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">TGL</th>
                    <th style="width: 28%;">NAMA BARANG</th>
                    <th style="width: 20%;">DISTRIBUTOR</th>
                    <th style="width: 10%; text-align: center;">QTY</th>
                    <th style="width: 15%; text-align: right;">HARGA BELI</th>
                    <th style="width: 15%; text-align: right;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                <tr>
                    <td>{{ $p->purchase_date->format('d/m/y') }}</td>
                    <td class="font-bold">
                        {{ $p->sparepart->name }}
                        <div style="font-size: 7px; font-weight: normal; color: #64748b; text-transform: uppercase;">{{ $p->sparepart->type }}</div>
                    </td>
                    <td>{{ $p->distributor->name }}</td>
                    <td style="text-align: center;">+{{ $p->qty }}</td>
                    <td class="text-right">Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($p->total_price, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada riwayat pembelian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
