@extends('layouts.admin')
@section('title','Transaksi')
@section('page-title','Daftar Transaksi')

@section('content')
<div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-sm text-slate-800">Semua Transaksi</h3>
        <form method="GET" class="flex items-center gap-2" data-auto-filter>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pelanggan..."
                   class="border border-slate-200 focus:border-slate-400 rounded-lg px-3 py-2 text-sm outline-none w-44 placeholder-slate-300 text-slate-700">
            
            <div id="clear-container" class="flex items-center">
            @if(request('search'))
            <a href="{{ route('admin.transactions.index') }}"
               class="text-sm text-slate-500 hover:text-slate-800 px-3 py-2 border border-slate-200 rounded-lg transition-colors">Hapus</a>
            @endif
            </div>
        </form>
    </div>
    <div id="filter-container">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">#</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Pelanggan</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Mekanik</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Kasir</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Total Akhir</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Pembayaran</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $t)
                @php 
                    $payStatus = $t->payment->payment_status ?? 'unpaid';
                    $pc = ($payStatus === 'paid' ? 'emerald' : ($payStatus === 'partial' ? 'amber' : 'red')); 
                    $payLabel = match($payStatus) {
                        'paid' => 'Lunas',
                        'partial' => 'Cicilan',
                        'unpaid' => 'Belum Bayar',
                        default => $payStatus
                    };
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-400">#{{ $t->id }}</td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-sm text-slate-800">{{ $t->booking->user->name ?? 'Pelanggan Umum (Retail)' }}</p>
                        <p class="text-xs text-slate-400">
                            @if($t->booking)
                                {{ $t->booking->vehicle->brand ?? '' }} {{ $t->booking->vehicle->model ?? '' }} ({{ $t->booking->vehicle->license_plate ?? '' }})
                            @else
                                <span class="italic">Penjualan Langsung</span>
                            @endif
                        </p>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $t->mekanik->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $t->kasir->name ?? '-' }}</td>
                    <td class="px-6 py-4 font-semibold text-sm text-slate-900">Rp {{ number_format($t->grand_total,0,',','.') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-{{ $pc }}-100 text-{{ $pc }}-700">
                            {{ $payLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $t->created_at->translatedFormat('d M Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.transactions.show', $t) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Lihat</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-20">
                        <div class="flex flex-col items-center justify-center text-center">
                            <i class="fas fa-receipt text-5xl mb-4 text-slate-200"></i>
                            <p class="text-sm text-slate-400 font-medium tracking-wide">Belum ada transaksi</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination footer --}}
    <x-ui.table-footer :paginator="$transactions" />
    </div>
</div>
@endsection
