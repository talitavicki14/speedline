@extends('layouts.customer')
@section('title','Pembayaran')
@section('content')
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('customer.bookings.show', $payment->transaction->booking) }}" class="text-slate-400 hover:text-slate-700">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail Booking
    </a>
</div>

<div class="max-w-xl">
    {{-- Invoice --}}
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6 mb-5">
        <div class="flex items-start justify-between mb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1">Nota</p>
                <h2 class="font-display font-bold text-3xl text-slate-900">#TRX-{{ str_pad($payment->transaction_id,4,'0',STR_PAD_LEFT) }}</h2>
                <p class="text-slate-400 text-sm mt-0.5">{{ $payment->transaction->booking->user->name ?? 'Pelanggan Umum (Retail)' }}</p>
            </div>
            @php
                $payStatusLabel = match($payment->payment_status) {
                    'paid' => 'Lunas',
                    'partial' => 'Cicilan',
                    'unpaid' => 'Belum Bayar',
                    'expired' => 'Kedaluwarsa',
                    'failed' => 'Gagal',
                    default => $payment->payment_status
                };
            @endphp
            <x-badges.status id="statusBadge" :status="$payment->payment_status" class="px-3 py-1.5" />
        </div>
        <div class="space-y-2 text-sm border-t border-slate-100 pt-4">
            <div class="flex justify-between text-slate-400"><span>Layanan</span><span>Rp {{ number_format($payment->transaction->total_service,0,',','.') }}</span></div>
            <div class="flex justify-between text-slate-400"><span>Sparepart</span><span>Rp {{ number_format($payment->transaction->total_sparepart,0,',','.') }}</span></div>
            <div class="flex justify-between font-semibold text-slate-900 border-t border-slate-100 pt-2">
                <span>Total</span>
                <span class="font-display font-bold text-xl">Rp {{ number_format($payment->transaction->grand_total,0,',','.') }}</span>
            </div>
        </div>
    </div>

    @if($payment->payment_status === 'paid')
    {{-- Already paid --}}
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-8 text-center">
        <i class="fas fa-check-circle text-emerald-500 text-4xl mb-3 block"></i>
        <h3 class="font-semibold text-lg text-emerald-700 mb-1">Pembayaran Selesai</h3>
        @php
            $payMethodLabel = match(strtolower($payment->payment_method)) {
                'cash' => 'Tunai',
                'midtrans' => 'Midtrans',
                default => $payment->payment_method
            };
        @endphp
        <p class="text-sm text-slate-500">Dibayar pada {{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d M Y') }} melalui {{ $payMethodLabel ?? '—' }}</p>
        @if($payment->midtrans_transaction_id)
        <p class="text-xs text-slate-400 mt-2 font-mono">{{ $payment->midtrans_transaction_id }}</p>
        @endif
        <a href="{{ route('customer.bookings.index') }}" class="inline-flex items-center gap-2 mt-5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
            Lihat Booking Saya
        </a>
    </div>

    @else
    {{-- Choose method --}}
    <div id="stepChooseMethod" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-sm text-slate-800">Pilih Metode Pembayaran</h3>
        </div>
        <div class="p-6 space-y-5">

            {{-- Cash --}}
            <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-money-bill-wave text-emerald-600 text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-sm text-slate-800">Tunai - Bayar di Bengkel</p>
                    <p class="text-xs text-slate-400">Bayar langsung saat Anda mengambil kendaraan</p>
                </div>
                <span class="text-xs font-medium text-slate-400 bg-white border border-slate-200 px-2.5 py-1 rounded-lg">Offline</span>
            </div>

            {{-- Bank Transfer --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Transfer Bank - Virtual Account</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([['bca','BCA'],['bni','BNI'],['bri','BRI'],['mandiri','Mandiri'],['permata','Permata']] as [$code,$name])
                    <a href="{{ route('customer.payments.pay', [$payment, 'type' => 'bank_transfer', 'bank' => $code]) }}"
                       class="flex items-center justify-center p-6 rounded-xl border border-slate-200 hover:border-slate-900 hover:bg-slate-50 transition-all h-24 group">
                        <img src="{{ asset('images/payment/' . $code . '.svg') }}" alt="{{ $name }}" class="h-10 w-auto max-w-full object-contain transition-transform group-hover:scale-110">
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- E-Wallet --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">E-Wallet & QRIS</p>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('customer.payments.pay', [$payment, 'type' => 'gopay']) }}"
                       class="flex items-center justify-center p-6 rounded-xl border border-slate-200 hover:border-slate-900 hover:bg-slate-50 transition-all h-24 group">
                        <img src="{{ asset('images/payment/gopay.svg') }}" alt="GoPay" class="h-10 w-auto max-w-full object-contain transition-transform group-hover:scale-110">
                    </a>
                    <a href="{{ route('customer.payments.pay', [$payment, 'type' => 'qris']) }}"
                       class="flex items-center justify-center p-6 rounded-xl border border-slate-200 hover:border-slate-900 hover:bg-slate-50 transition-all h-24 group">
                        <img src="{{ asset('images/payment/qris.svg') }}" alt="QRIS" class="h-10 w-auto max-w-full object-contain transition-transform group-hover:scale-110">
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
@push('scripts')
    <div id="payment-init-data" class="hidden"
        data-payment-id="{{ $payment->id }}"
        data-grand-total="{{ $payment->transaction->grand_total }}">
    </div>
    @vite('resources/js/pages/customer/payments/flow.js')
@endpush
