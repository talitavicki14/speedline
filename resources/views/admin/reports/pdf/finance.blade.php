<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - Speedline Automotive</title>
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
                    Laporan Keuangan & Analisa Penjualan
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
        <div class="report-title">LAPORAN KEUANGAN</div>
        <div class="report-period">
            Periode: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}
        </div>

        <table class="stats-table">
            <tr>
                <td style="padding-right: 5px;">
                    <div class="stats-card">
                        <span class="stats-label">Sparepart Terjual</span>
                        <div class="stats-value">{{ number_format($stats['total_spareparts_sold'], 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="padding-right: 5px;">
                    <div class="stats-card">
                        <span class="stats-label">Penjualan Sparepart</span>
                        <div class="stats-value">Rp {{ number_format($stats['total_sparepart_sales'], 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="padding-right: 5px;">
                    <div class="stats-card">
                        <span class="stats-label">Mobil Dilayani</span>
                        <div class="stats-value">{{ number_format($stats['total_cars_serviced'], 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="padding-right: 5px;">
                    <div class="stats-card" style="background: #ecfdf5; border: 1px solid #10b981;">
                        <span class="stats-label" style="color: #059669;">Estimasi Laba</span>
                        <div class="stats-value" style="color: #065f46;">Rp {{ number_format($stats['total_profit'], 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="stats-card" style="background: #0f172a;">
                        <span class="stats-label" style="color: #94a3b8;">Total Pendapatan</span>
                        <div class="stats-value" style="color: #ffffff;">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="charts-table">
            <tr>
                <td style="width: 40%; padding-right: 10px;">
                    <div class="chart-box">
                        <div style="font-size: 8px; font-weight: bold; margin-bottom: 12px; color: #94a3b8; text-transform: uppercase;">Tren Pendapatan</div>
                        @if($lineChartBase64)
                            <img src="{{ $lineChartBase64 }}" class="chart-img">
                        @endif
                    </div>
                </td>
                <td style="width: 30%; padding-right: 10px;">
                    <div class="chart-box">
                        <div style="font-size: 8px; font-weight: bold; margin-bottom: 12px; color: #94a3b8; text-transform: uppercase;">Top Layanan</div>
                        @if($pieChartBase64)
                            <img src="{{ $pieChartBase64 }}" class="chart-img pie-chart-img">
                        @endif
                    </div>
                </td>
                <td style="width: 30%;">
                    <div class="chart-box">
                        <div style="font-size: 8px; font-weight: bold; margin-bottom: 12px; color: #94a3b8; text-transform: uppercase;">Top Sparepart</div>
                        @if($sparepartChartBase64)
                            <img src="{{ $sparepartChartBase64 }}" class="chart-img pie-chart-img">
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table style="width: 100%; table-layout: fixed; margin-bottom: 25px;">
            <tr>
                <td style="vertical-align: top; padding-right: 15px;">
                    <div class="section-title">TOP 5 SPAREPART TERLARIS</div>
                    <table class="breakdown-table">
                        <thead>
                            <tr>
                                <th class="col-name">NAMA PRODUK</th>
                                <th class="col-qty">QTY</th>
                                <th class="col-pct">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['top_spareparts'] as $item)
                            <tr>
                                <td class="col-name">{{ $item->sparepart->name ?? 'Produk Tidak Diketahui' }}</td>
                                <td class="col-qty">{{ $item->total_qty }} item</td>
                                <td class="col-pct">
                                    {{ $stats['total_spareparts_sold'] > 0 ? round(($item->total_qty / $stats['total_spareparts_sold']) * 100) : 0 }}%
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
                <td style="vertical-align: top; padding-left: 15px;">
                    <div class="section-title">TOP 5 LAYANAN POPULER</div>
                    <table class="breakdown-table">
                        <thead>
                            <tr>
                                <th class="col-name">NAMA LAYANAN</th>
                                <th class="col-qty">JUMLAH</th>
                                <th class="col-pct">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['top_services'] as $item)
                            <tr>
                                <td class="col-name">{{ $item->service->service_name ?? 'Layanan Tidak Diketahui' }}</td>
                                <td class="col-qty">{{ $item->total_count }}x</td>
                                <td class="col-pct">
                                    {{ $stats['total_services_count'] > 0 ? round(($item->total_count / $stats['total_services_count']) * 100) : 0 }}%
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <div class="section-title">DETAIL TRANSAKSI PEMBAYARAN</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">TGL</th>
                    <th style="width: 20%;">NO. NOTA</th>
                    <th style="width: 28%;">PELANGGAN</th>
                    <th style="width: 10%;">METODE</th>
                    <th style="width: 15%; text-align: right;">TOTAL</th>
                    <th style="width: 15%; text-align: right;">DIBAYAR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->completed_at)->format('d/m/y') }}</td>
                    <td class="font-bold">#TRX-{{ str_pad($payment->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="font-bold">{{ $payment->customer_name }}</div>
                        @if($payment->vehicle_model)
                        <div style="font-size: 7px; color: #64748b;">{{ $payment->vehicle_model }} ({{ $payment->license_plate }})</div>
                        @endif
                    </td>
                    <td style="text-transform: uppercase;">{{ $payment->payment_method }}</td>
                    <td class="text-right">Rp {{ number_format($payment->transaction->grand_total, 0, ',', '.') }}</td>
                    <td class="text-right font-bold text-success">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada data transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
