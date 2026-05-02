@extends('layouts.admin')
@section('title','Transaksi #'.$transaction->id)
@section('page-title','Detail Transaksi')

@section('content')
@php
    $amountPaid = $transaction->payment->amount_paid ?? 0;
    $balanceDue = $transaction->grand_total - $amountPaid;
    $transactionSpareparts = $transaction->transactionSpareparts;
@endphp

<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.transactions.index') }}" class="text-slate-400 hover:text-slate-700">
        <i class="fas fa-arrow-left mr-1"></i> Transaksi
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">

        {{-- Invoice Header --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1">Nota</p>
                    <h2 class="font-display font-bold text-2xl text-slate-900">#TRX-{{ str_pad($transaction->id,4,'0',STR_PAD_LEFT) }}</h2>
                    <p class="text-slate-400 text-sm mt-1">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($transaction->payment && in_array($transaction->payment->payment_status, ['paid', 'partial']))
                    <button onclick="printReceipt()"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-2 rounded-lg transition-colors">
                        <i class="fas fa-print text-[10px]"></i> Cetak Struk
                    </button>
                    @endif
                    <x-badges.status :status="$transaction->payment->payment_status ?? 'unpaid'" />
                </div>
            </div>
            <div class="grid grid-cols-4 gap-5 text-sm">
                <div>
                    <p class="text-xs text-slate-400 mb-1">Pelanggan</p>
                    <p class="font-medium text-slate-800">{{ $transaction->booking->user->name ?? 'Pelanggan Umum (Retail)' }}</p>
                    <p class="text-slate-500">{{ $transaction->booking->user->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-1">Kendaraan</p>
                    @if($transaction->booking)
                        <p class="font-medium text-slate-800">{{ $transaction->booking->vehicle->brand ?? '' }} {{ $transaction->booking->vehicle->model ?? '' }}</p>
                        <p class="text-slate-500">{{ $transaction->booking->vehicle->license_plate ?? '' }}</p>
                    @else
                        <p class="font-medium text-slate-800">-</p>
                        <p class="text-slate-500">Tanpa Servis</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-1">Mekanik</p>
                    <p class="font-medium text-slate-800">{{ $transaction->mekanik->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-1">Kasir</p>
                    <p class="font-medium text-slate-800">{{ $transaction->kasir->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Services --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Layanan</h3>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Layanan</th>
                        <th class="text-right text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $bookingServices = $transaction->booking->bookingServices ?? collect(); @endphp
                    @forelse($bookingServices as $bs)
                    <tr>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $bs->service->service_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-slate-900">
                            Rp {{ number_format($bs->price,0,',','.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-6 py-4 text-sm text-slate-400 text-center">Tidak ada layanan (Penjualan Sparepart)</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50">
                        <td class="px-6 py-3 text-sm font-semibold text-slate-500">Subtotal</td>
                        <td class="px-6 py-3 text-right font-semibold text-slate-900">
                            Rp {{ number_format($transaction->total_service,0,',','.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Spareparts (from transaction_spareparts) --}}
        @if($transactionSpareparts->count())
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Sparepart</h3>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Barang</th>
                        <th class="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Jumlah</th>
                        <th class="text-right text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Harga</th>
                        <th class="text-right text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($transactionSpareparts as $bsp)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-700">{{ $bsp->sparepart->name ?? '-' }}</p>
                            <p class="text-xs text-slate-400">{{ $bsp->sparepart->brand ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-center text-slate-600">{{ $bsp->qty }}</td>
                        <td class="px-6 py-4 text-sm text-right text-slate-600">Rp {{ number_format($bsp->price,0,',','.') }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-slate-900">Rp {{ number_format($bsp->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50">
                        <td colspan="3" class="px-6 py-3 text-sm font-semibold text-slate-500">Subtotal Sparepart</td>
                        <td class="px-6 py-3 text-right font-semibold text-slate-900">
                            Rp {{ number_format($transaction->total_sparepart,0,',','.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- Grand Total --}}
        <div class="bg-slate-900 text-white rounded-2xl p-6 flex items-center justify-between">
            <span class="font-semibold text-lg">Total Keseluruhan</span>
            <span class="font-display font-bold text-2xl">Rp {{ number_format($transaction->grand_total,0,',','.') }}</span>
        </div>

    </div>

    {{-- Right sidebar --}}
    <div class="space-y-4">
        @can('manage-data')
        @if($transaction->payment && $transaction->payment->payment_status !== 'paid')
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Catat Pembayaran Tunai</h3>
            </div>
            <div class="p-5">
                <form action="{{ route('admin.payments.cash', $transaction->payment) }}" 
                      method="POST"
                      data-confirm="Apakah Anda yakin ingin menandai transaksi ini telah dibayar secara Tunai?">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            Jumlah Diterima (Rp)
                        </label>
                        <input type="text" name="amount_paid" value="{{ $balanceDue }}" required
                               class="input-currency w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors text-slate-700 font-semibold">
                    </div>
                    <button type="submit"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">
                        <i class="fas fa-money-bill-wave mr-2"></i> Dibayar (Tunai)
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endcan

        @if($transaction->payment)
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5 space-y-3">
            <h3 class="font-semibold text-sm text-slate-800 mb-3">Detail Pembayaran</h3>
            <div class="flex justify-between text-sm">
                <span class="text-slate-400">Metode</span>
                <span class="font-medium capitalize text-slate-700">{{ $transaction->payment->payment_method === 'cash' ? 'Tunai' : ($transaction->payment->payment_method ?? '-') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-400">Jumlah Dibayar</span>
                <span class="font-medium text-slate-700">Rp {{ number_format($amountPaid,0,',','.') }}</span>
            </div>
            @if($balanceDue > 0)
            <div class="flex justify-between text-sm pt-2 border-t border-slate-100">
                <span class="font-semibold text-slate-600">Sisa Tagihan</span>
                <span class="font-bold text-rose-600">Rp {{ number_format($balanceDue,0,',','.') }}</span>
            </div>
            @endif
            <div class="flex justify-between text-sm">
                <span class="text-slate-400">Tanggal</span>
                <span class="font-medium text-slate-700">{{ $transaction->payment->payment_date ?? '-' }}</span>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Print Receipt --}}
@include('admin.transactions.receipt.print')
@endsection

@push('scripts')
    @vite('resources/js/pages/admin/transactions/show.js')
@endpush
