<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Speedline Automotive</title>
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
                    Detail Penjualan Sparepart
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
        <div class="report-title">LAPORAN PENJUALAN</div>
        <div class="report-period">
            Periode: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}
        </div>

        <table class="stats-table">
            <tr>
                <td style="padding-right: 10px; width: 25%;">
                    <div class="stats-card">
                        <span class="stats-label">Total Unit</span>
                        <div class="stats-value" style="font-size: 14px;">{{ number_format($totalQty, 0, ',', '.') }} pcs</div>
                    </div>
                </td>
                <td style="padding-right: 10px; width: 35%;">
                    <div class="stats-card" style="border: 1px solid #e2e8f0; background: #f8fafc;">
                        <span class="stats-label">Penjualan Layanan</span>
                        <div class="stats-value" style="font-size: 14px;">Rp {{ number_format($totalServiceSales, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 40%;">
                    <div class="stats-card" style="background: #0f172a;">
                        <span class="stats-label" style="color: #94a3b8;">Penjualan Sparepart</span>
                        <div class="stats-value" style="color: #ffffff; font-size: 14px;">Rp {{ number_format($totalSparepartSales, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        @if($chartBase64)
        <div class="chart-box" style="margin-bottom: 25px;">
            <div style="font-size: 8px; font-weight: bold; margin-bottom: 12px; color: #94a3b8; text-transform: uppercase;">Tren Volume Penjualan (6 Bulan Terakhir)</div>
            <img src="{{ $chartBase64 }}" class="chart-img">
        </div>
        @endif

        <div style="margin-bottom: 20px;">
            <div style="width: 48%; display: inline-block; vertical-align: top; margin-right: 2%;">
                <div class="section-title">TOP 5 SPAREPART TERLARIS</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">RANK</th>
                            <th style="width: 50%;">NAMA</th>
                            <th style="width: 20%; text-align: center;">QTY</th>
                            <th style="width: 15%; text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sumQty = $topSpareparts->sum('total_qty'); @endphp
                        @foreach($topSpareparts as $index => $top)
                        <tr>
                            <td style="text-align: center;">#{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $top->sparepart->name }}</td>
                            <td style="text-align: center;">{{ $top->total_qty }}</td>
                            <td style="text-align: right;">{{ $sumQty > 0 ? number_format(($top->total_qty / $sumQty) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="width: 48%; display: inline-block; vertical-align: top;">
                <div class="section-title">TOP 5 LAYANAN TERLARIS</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">RANK</th>
                            <th style="width: 50%;">NAMA</th>
                            <th style="width: 20%; text-align: center;">FREQ</th>
                            <th style="width: 15%; text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sumServ = $topServices->sum('total_count'); @endphp
                        @foreach($topServices as $index => $top)
                        <tr>
                            <td style="text-align: center;">#{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $top->service->service_name }}</td>
                            <td style="text-align: center;">{{ $top->total_count }}x</td>
                            <td style="text-align: right;">{{ $sumServ > 0 ? number_format(($top->total_count / $sumServ) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-title">DETAIL PENJUALAN SPAREPART</div>
        <table class="data-table" style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th style="width: 12%;">TGL</th>
                    <th style="width: 33%;">NAMA BARANG</th>
                    <th style="width: 15%;">KATEGORI</th>
                    <th style="width: 10%; text-align: center;">QTY</th>
                    <th style="width: 15%; text-align: right;">HARGA</th>
                    <th style="width: 15%; text-align: right;">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sparepartSales as $sale)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/y') }}</td>
                    <td>
                        <div class="font-bold">{{ $sale->sparepart->name }}</div>
                        <div style="font-size: 7px; color: #64748b; text-transform: uppercase;">Pembeli: {{ $sale->customer_name }}</div>
                    </td>
                    <td style="text-transform: uppercase; font-size: 8px;">{{ $sale->sparepart->type }}</td>
                    <td style="text-align: center;">{{ $sale->qty }} pcs</td>
                    <td class="text-right">Rp {{ number_format($sale->price, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada data penjualan barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">DETAIL PENJUALAN LAYANAN</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">TGL</th>
                    <th style="width: 60%;">NAMA LAYANAN</th>
                    <th style="width: 25%; text-align: right;">HARGA LAYANAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($serviceSales as $service)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($service->sale_date)->format('d/m/y') }}</td>
                    <td>
                        <div class="font-bold">{{ $service->service->service_name }}</div>
                        <div style="font-size: 7px; color: #64748b; text-transform: uppercase;">Pelanggan: {{ $service->customer_name }}</div>
                    </td>
                    <td class="text-right font-bold">Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada data penjualan layanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
