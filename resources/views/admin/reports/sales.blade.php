@extends('layouts.admin')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan Sparepart')

@section('content')
    <div class="space-y-6">
        {{-- Unified Filter Card --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <form action="{{ route('admin.reports.sales') }}" method="GET"
                class="flex flex-col md:flex-row items-end justify-between gap-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mulai</label>
                        <div data-datepicker data-name="start_date" data-placeholder="Mulai" data-value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sampai</label>
                        <div data-datepicker data-name="end_date" data-placeholder="Selesai"
                            data-value="{{ $endDate }}"></div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis</label>
                        <div data-custom-select data-name="type" data-placeholder="Semua Jenis"
                            data-value="{{ request('type') }}" data-options='@json($types->map(fn($t) => ['value' => $t, 'label' => $t]))' class="w-40">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Merek</label>
                        <div data-custom-select data-name="brand" data-placeholder="Semua Merek"
                            data-value="{{ request('brand') }}" data-options='@json($brands->map(fn($b) => ['value' => $b, 'label' => $b]))' class="w-40">
                        </div>
                    </div>
                    <button type="submit"
                        class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-800 transition-all flex items-center gap-2 h-[44px]">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="exportPdfBtn"
                        data-url="{{ route('admin.reports.sales.pdf') }}"
                        data-filename="Laporan_Penjualan_{{ $startDate }}_{{ $endDate }}.pdf"
                        data-total-records="{{ $totalQty + $totalServicesCount }}"
                        title="Export PDF"
                        class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2 h-[44px] text-sm font-semibold">
                        <i class="fas fa-file-pdf text-red-500"></i> PDF
                    </button>
                    <button type="button" id="exportExcelBtn"
                        data-url="{{ route('admin.reports.sales.excel') }}"
                        data-filename="Laporan_Penjualan_{{ $startDate }}_{{ $endDate }}.xlsx"
                        data-total-records="{{ $totalQty + $totalServicesCount }}"
                        title="Export Excel"
                        class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2 h-[44px] text-sm font-semibold">
                        <i class="fas fa-file-excel text-emerald-500"></i> Excel
                    </button>
                </div>
            </form>
        </div>

        {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-box-open text-slate-600 text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Unit Terjual</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($totalQty) }} <span class="text-xs font-normal text-slate-400 ml-1">pcs</span></h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-tools text-slate-600 text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Penjualan Layanan</p>
                <h3 class="text-xl font-bold text-slate-900 mt-0.5">Rp {{ number_format($totalServiceSales, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-wallet text-white text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Penjualan Sparepart</p>
                <h3 class="text-xl font-bold text-white mt-0.5">Rp {{ number_format($totalSparepartSales, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-check text-emerald-600 text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Layanan</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($serviceSales->count()) }} <span class="text-xs font-normal text-slate-400 ml-1">servis</span></h3>
            </div>
        </div>
    </div>

    {{-- Analytical Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
        {{-- Left: Chart Section --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col h-full">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-sm text-slate-800 tracking-tight">Tren Unit Terjual</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Statistik volume penjualan 6 bulan terakhir</p>
                </div>
            </div>
            <div class="flex-grow relative mt-2">
                <div class="absolute inset-0">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Right: Top Performers Column --}}
        <div class="flex flex-col gap-6">
            {{-- Top Spareparts --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex-1">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-[11px] font-bold text-slate-800 flex items-center gap-2 uppercase tracking-wider">
                        <i class="fas fa-trophy text-amber-500"></i> Top 5 Sparepart
                    </h4>
                </div>
                <div class="space-y-3">
                    @php $sumQty = $topSpareparts->sum('total_qty'); @endphp
                    @forelse($topSpareparts as $top)
                    <div class="flex items-center justify-between group">
                        <span class="text-[11px] font-medium text-slate-600 truncate max-w-[140px]">{{ $top->sparepart->name }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-[11px] font-bold text-slate-900">{{ $top->total_qty }} pcs</span>
                            <span class="text-[10px] font-bold text-slate-300 w-10 text-right">{{ $sumQty > 0 ? number_format(($top->total_qty / $sumQty) * 100, 0) : 0 }}%</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-[10px] text-slate-400 italic">No data</p>
                    @endforelse
                </div>
            </div>

            {{-- Top Services --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex-1">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-[11px] font-bold text-slate-800 flex items-center gap-2 uppercase tracking-wider">
                        <i class="fas fa-medal text-blue-500"></i> Top 5 Layanan
                    </h4>
                </div>
                <div class="space-y-3">
                    @php $sumServ = $topServices->sum('total_count'); @endphp
                    @forelse($topServices as $top)
                    <div class="flex items-center justify-between group">
                        <span class="text-[11px] font-medium text-slate-600 truncate max-w-[140px]">{{ $top->service->service_name }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-[11px] font-bold text-slate-900">{{ $top->total_count }}x</span>
                            <span class="text-[10px] font-bold text-slate-300 w-10 text-right">{{ $sumServ > 0 ? number_format(($top->total_count / $sumServ) * 100, 0) : 0 }}%</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-[10px] text-slate-400 italic">No data</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Tables Row --}}
    <div class="space-y-6 mt-6">
        {{-- Spareparts Table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800 tracking-tight">Rincian Penjualan Sparepart</h3>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-500">{{ $sparepartSales->count() }} transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tanggal & Pelanggan</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center">Qty</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-right">Harga Satuan</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sparepartSales as $sale)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-slate-800">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $sale->customer_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-slate-900">{{ $sale->sparepart->name }}</div>
                            <div class="text-[10px] text-slate-400 uppercase font-medium">{{ $sale->sparepart->brand }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $sale->sparepart->type }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-bold text-slate-900">{{ $sale->qty }} pcs</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 text-right font-medium">
                            Rp {{ number_format($sale->price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">
                            Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic text-sm">Tidak ada penjualan barang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Services Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800 tracking-tight">Rincian Penjualan Layanan</h3>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-500">{{ $serviceSales->count() }} layanan</span>
        </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tanggal
                                & Pelanggan</th>
                            <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nama
                                Layanan</th>
                            <th
                                class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-right">
                                Harga Layanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($serviceSales as $service)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-800">
                                        {{ \Carbon\Carbon::parse($service->sale_date)->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">
                                        {{ $service->customer_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                    {{ $service->service->service_name }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">
                                    Rp {{ number_format($service->price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400 italic text-sm">Tidak ada
                                    penjualan layanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        window.REPORT_DATA = {
            salesTrend: {
                labels: @json($sixMonthTrend->pluck('month_label')),
                spareparts: @json($sixMonthTrend->pluck('spareparts')),
                services: @json($sixMonthTrend->pluck('services'))
            }
        };
    </script>
    @vite(['resources/js/pages/admin/reports/index.js', 'resources/js/pages/admin/reports/sales.js'])
    @endpush
@endsection
