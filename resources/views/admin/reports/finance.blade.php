@extends('layouts.admin')

@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan Keuangan & Analisa')

@section('content')
<div class="space-y-6">
    {{-- Unified Filter Card--}}
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <form action="{{ route('admin.reports.finance') }}" method="GET" class="flex flex-col md:flex-row items-end justify-between gap-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mulai</label>
                    <div data-datepicker data-name="start_date" data-placeholder="Mulai" data-value="{{ $startDate }}"></div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sampai</label>
                    <div data-datepicker data-name="end_date" data-placeholder="Selesai" data-value="{{ $endDate }}"></div>
                </div>
                <button type="submit" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-800 transition-all flex items-center gap-2 h-[44px]">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            
            <div class="flex items-center gap-2">
                <button id="exportPdfBtn"
                   data-url="{{ route('admin.reports.finance.pdf') }}"
                   data-filename="Laporan_Keuangan_{{ $startDate }}_{{ $endDate }}.pdf"
                   data-total-records="{{ $payments->count() }}"
                   title="Export PDF"
                   class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-all flex items-center justify-center gap-2 h-[44px]">
                    <i class="fas fa-file-pdf text-red-500"></i> PDF
                </button>
                <button id="exportExcelBtn"
                   data-url="{{ route('admin.reports.finance.excel') }}"
                   data-filename="Laporan_Keuangan_{{ $startDate }}_{{ $endDate }}.xlsx"
                   data-total-records="{{ $payments->count() }}"
                   title="Export Excel"
                   class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-all flex items-center justify-center gap-2 h-[44px]">
                    <i class="fas fa-file-excel text-emerald-500"></i> Excel
                </button>
            </div>
        </form>
    </div>

    {{-- Stats Grid (Neutralized Colors) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-box text-slate-500 text-sm"></i>
            </div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Item Terjual</p>
            <h3 class="text-xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_spareparts_sold'], 0, ',', '.') }}</h3>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-wallet text-slate-500 text-sm"></i>
            </div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</p>
            <h3 class="text-xl font-bold text-slate-900 mt-1">Rp{{ number_format($stats['total_revenue'], 0, ',', '.') }}</h3>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-car text-slate-500 text-sm"></i>
            </div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mobil Dilayani</p>
            <h3 class="text-xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_cars_serviced'], 0, ',', '.') }}</h3>
        </div>

        <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-100 shadow-sm">
            <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-chart-line text-white text-sm"></i>
            </div>
            <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Estimasi Laba</p>
            <h3 class="text-xl font-bold text-emerald-700 mt-1">Rp{{ number_format($stats['total_profit'], 0, ',', '.') }}</h3>
        </div>

        <div class="bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-800">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center mb-3">
                <i class="fas fa-percentage text-white text-sm"></i>
            </div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Margin Laba</p>
            <h3 class="text-xl font-bold text-white mt-1">
                {{ $stats['total_revenue'] > 0 ? round(($stats['total_profit'] / $stats['total_revenue']) * 100) : 0 }}%
            </h3>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-sm text-slate-800 tracking-tight">Tren Pendapatan</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Statistik pendapatan 6 bulan terakhir</p>
                </div>
            </div>
            <div class="h-64">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <div class="mb-6">
                    <h3 class="font-bold text-sm text-slate-800 tracking-tight">Top Layanan</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Distribusi kategori jasa</p>
                </div>
                <div class="h-64">
                    <canvas id="servicesPieChart"></canvas>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <div class="mb-6">
                    <h3 class="font-bold text-sm text-slate-800 tracking-tight">Top Sparepart</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Barang paling laris</p>
                </div>
                <div class="h-64">
                    <canvas id="sparepartsPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction List (Standardized with Inventory Style) --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" data-table-pagination>
        <div class="px-6 py-4 border-b border-slate-50">
            <h3 class="font-bold text-sm text-slate-800 tracking-tight">Detail Transaksi Pembayaran</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">No. Transaksi</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-right">Total</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-right">Dibayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-slate-600">
                            {{ \Carbon\Carbon::parse($payment->completed_at)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-mono font-semibold text-slate-400">#TRX-{{ str_pad($payment->transaction_id, 4, '0', STR_PAD_LEFT) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-slate-800">{{ $payment->customer_name }}</p>
                            @if($payment->vehicle_model)
                                <p class="text-[10px] text-slate-400">{{ $payment->vehicle_model }} ({{ $payment->license_plate }})</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wider {{ $payment->payment_method === 'cash' ? 'bg-slate-100 text-slate-600' : 'bg-slate-200 text-slate-800' }}">
                                {{ $payment->payment_method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900 text-right">
                            Rp {{ number_format($payment->transaction->grand_total, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">
                            Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="fas fa-file-invoice-dollar text-4xl mb-4 opacity-10"></i>
                                <p class="text-sm italic font-medium">Tidak ada data transaksi pada periode ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <x-ui.table-footer :paginator="$payments" />
    </div>
</div>

@push('scripts')
    <script>
        window.REPORT_DATA = {
            revenueTrend: {
                labels: @json($stats['six_month_revenue']->pluck('month_label')),
                values: @json($stats['six_month_revenue']->pluck('total'))
            },
            topServices: {
                labels: @json($stats['top_services']->map(fn($s) => $s->service->service_name ?? 'Lainnya')),
                values: @json($stats['top_services']->pluck('total_count'))
            },
            topSpareparts: {
                labels: @json($stats['top_spareparts']->map(fn($s) => $s->sparepart->name ?? 'Lainnya')),
                values: @json($stats['top_spareparts']->pluck('total_qty'))
            }
        };
    </script>
    @vite(['resources/js/pages/admin/reports/index.js', 'resources/js/pages/admin/reports/finance.js'])
@endpush
@endsection
