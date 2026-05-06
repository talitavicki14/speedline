@extends('layouts.admin')

@section('title', 'Laporan Pembelian')
@section('page-title', 'Riwayat Pengadaan Stok')

@section('content')
<div class="space-y-6">
    {{-- Unified Filter Card --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <form action="{{ route('admin.reports.purchases') }}" method="GET" class="flex flex-col md:flex-row items-end justify-between gap-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mulai</label>
                    <div data-datepicker data-name="start_date" data-placeholder="Mulai" data-value="{{ $startDate }}"></div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sampai</label>
                    <div data-datepicker data-name="end_date" data-placeholder="Selesai" data-value="{{ $endDate }}"></div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Distributor</label>
                    <div data-custom-select data-name="distributor_id" data-placeholder="Semua Distributor" data-value="{{ request('distributor_id') }}" data-search="true" data-options='@json($distributors->map(fn($d) => ["value" => $d->id, "label" => $d->name]))' class="w-48"></div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis</label>
                    <div data-custom-select data-name="type" data-placeholder="Semua Jenis" data-value="{{ request('type') }}" data-options='@json($types->map(fn($t) => ["value" => $t, "label" => $t]))' class="w-40"></div>
                </div>
                <button type="submit" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-800 transition-all flex items-center gap-2 h-[44px]">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="button" id="exportPdfBtn"
                   data-url="{{ route('admin.reports.purchases.pdf') }}"
                   data-filename="Laporan_Pembelian_{{ $startDate }}_{{ $endDate }}.pdf"
                   data-total-records="{{ $purchases->count() }}"
                   title="Export PDF"
                   class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2 h-[44px] text-sm font-semibold">
                    <i class="fas fa-file-pdf text-red-500"></i> PDF
                </button>
                <button type="button" id="exportExcelBtn"
                   data-url="{{ route('admin.reports.purchases.excel') }}"
                   data-filename="Laporan_Pembelian_{{ $startDate }}_{{ $endDate }}.xlsx"
                   data-total-records="{{ $purchases->count() }}"
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
                <i class="fas fa-truck-loading text-slate-600 text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Stok Masuk</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($totalQty) }} <span class="text-xs font-normal text-slate-400 ml-1">pcs</span></h3>
            </div>
        </div>

        <div class="bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-800 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-wallet text-white text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengeluaran</p>
                <h3 class="text-xl font-bold text-white mt-0.5">Rp {{ number_format($totalSpending, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i class="fas fa-store text-slate-400"></i> Top 3 Distributor
            </h4>
            <div class="space-y-2.5">
                @forelse($topDistributors->take(3) as $td)
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-medium text-slate-600 truncate max-w-[80px]">{{ $td->distributor->name }}</span>
                    <span class="text-[11px] font-bold text-slate-900">Rp{{ number_format($td->total_spent/1000000, 1) }}jt</span>
                </div>
                @empty
                <p class="text-[10px] text-slate-400 italic">No data</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i class="fas fa-box text-slate-400"></i> Top 3 Stok Masuk
            </h4>
            <div class="space-y-2.5">
                @forelse($topPurchased->take(3) as $tp)
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-medium text-slate-600 truncate max-w-[120px]">{{ $tp->sparepart->name }}</span>
                    <span class="text-[11px] font-bold text-slate-900">{{ $tp->total_qty }} pcs</span>
                </div>
                @empty
                <p class="text-[10px] text-slate-400 italic">No data</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Chart Section --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-bold text-sm text-slate-800 tracking-tight">Tren Pengeluaran Stok</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Statistik nilai pembelian barang 6 bulan terakhir</p>
            </div>
        </div>
        <div class="h-64">
            <canvas id="purchaseTrendChart"></canvas>
        </div>
    </div>

    {{-- Purchases Table (Standardized with Inventory Style) --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" data-table-pagination>
        <div class="px-6 py-4 border-b border-slate-50">
            <h3 class="font-bold text-sm text-slate-800 tracking-tight">Rincian Riwayat Pembelian</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Distributor</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center">Qty</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-right">Harga Beli</th>
                        <th class="px-6 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-slate-800">{{ $p->purchase_date->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $p->purchase_date->format('H:i') }} WIB</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-900">{{ $p->sparepart->name }}</div>
                            <div class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 uppercase inline-block mt-1">{{ $p->sparepart->type }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-slate-700 font-medium">{{ $p->distributor->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-bold text-emerald-600 font-mono">+{{ number_format($p->qty) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 text-right font-medium">
                            Rp {{ number_format($p->purchase_price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">
                            Rp {{ number_format($p->total_price, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="fas fa-truck-loading text-4xl mb-4 opacity-10"></i>
                                <p class="text-sm italic font-medium">Tidak ada riwayat pembelian pada periode ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-ui.table-footer :paginator="$purchases" />
    </div>
</div>
@push('scripts')
    <script>
        window.REPORT_DATA = {
            trendData: @json($sixMonthTrend)
        };
    </script>
    @vite(['resources/js/pages/admin/reports/index.js', 'resources/js/pages/admin/reports/purchases.js'])
@endpush
@endsection
