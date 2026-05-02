@extends('layouts.admin')
@section('title', 'Pembayaran')
@section('page-title', 'Pembayaran')

@section('content')
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-sm text-slate-800">Daftar Pembayaran</h3>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Filter --}}
                <form method="GET" class="flex flex-wrap items-center gap-2" data-auto-filter id="paymentsFilterForm">
                    @php
                        $export_status_options = collect([
                            ['value' => 'partial', 'label' => 'Cicilan'],
                            ['value' => 'paid', 'label' => 'Lunas'],
                            ['value' => 'expired', 'label' => 'Kedaluwarsa'],
                            ['value' => 'failed', 'label' => 'Gagal'],
                        ]);
                    @endphp
                    <div data-custom-select data-name="status" data-placeholder="Semua Status" data-value="{{ $status }}"
                        data-options='@json($export_status_options)' class="w-44"></div>

                    <div id="clear-container">
                        @if ($status)
                            <a href="{{ route('admin.payments.index') }}"
                                class="text-sm text-slate-500 hover:text-slate-800 px-3 py-2 border border-slate-200 rounded-lg transition-colors">Hapus</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div id="filter-container">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">
                                No. Nota</th>
                            <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">
                                Pelanggan</th>
                            <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">
                                Total</th>
                            <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">
                                Dibayar</th>
                            <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">
                                Metode</th>
                            <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">
                                Status</th>
                            <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">
                                Tanggal</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $p)
                            @php 
                                $c = match($p->payment_status) {
                                    'paid' => 'emerald',
                                    'partial' => 'amber',
                                    'expired' => 'slate',
                                    'failed' => 'red',
                                    default => 'red'
                                };
                                $payLabel = match($p->payment_status) {
                                    'paid' => 'Lunas',
                                    'partial' => 'Cicilan',
                                    'expired' => 'Kedaluwarsa',
                                    'failed' => 'Gagal',
                                    default => 'Belum Bayar'
                                };
                                $payMethod = match(strtolower($p->payment_method)) {
                                    'cash' => 'Tunai',
                                    'midtrans' => 'Midtrans',
                                    default => $p->payment_method
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-400 font-mono">
                                    #TRX-{{ str_pad($p->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 font-medium text-sm text-slate-800">
                                    {{ $p->transaction->booking->user->name ?? 'Pelanggan Umum (Retail)' }}</td>
                                <td class="px-6 py-4 font-semibold text-sm text-slate-900">Rp
                                    {{ number_format($p->transaction->grand_total, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">Rp {{ number_format($p->amount_paid, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $payMethod ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-{{ $c }}-100 text-{{ $c }}-700">{{ $payLabel }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->translatedFormat('d M Y') : '—' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.transactions.show', $p->transaction) }}"
                                        class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-20">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <i class="fas fa-credit-card text-5xl mb-4 text-slate-200"></i>
                                        <p class="text-sm text-slate-400 font-medium tracking-wide">Tidak ada pembayaran ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination footer --}}
            <x-ui.table-footer :paginator="$payments" />
        </div>
    </div>
@endsection
