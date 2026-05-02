@extends('layouts.customer')
@section('title','Instruksi Pembayaran')
@section('content')
<div class="flex items-center gap-2 mb-6 text-sm">
    <button onclick="cancelPaymentSession()" class="text-slate-400 hover:text-slate-700 transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Ubah Metode Pembayaran
    </button>
</div>

<div class="max-w-xl">
    {{-- Invoice Summary (Mini) --}}
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6 mb-5 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Bayar</p>
            <p class="font-display font-bold text-2xl text-slate-900">Rp {{ number_format($payment->transaction->grand_total,0,',','.') }}</p>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Nota</p>
            <p class="font-semibold text-sm text-slate-600">#TRX-{{ str_pad($payment->transaction_id,4,'0',STR_PAD_LEFT) }}</p>
        </div>
    </div>

    {{-- Active Payment --}}
    <div id="stepActivePayment">
        {{-- Timer bar --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden mb-4">
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-sm text-slate-800" id="activeMethodLabel">Memproses...</p>
                    <p class="text-xs text-slate-400 mt-0.5">Selesaikan pembayaran sebelum waktu habis</p>
                </div>
                <div class="text-right">
                    <div id="countdownDisplay" class="font-display font-bold text-2xl text-slate-900">--:--</div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">tersisa</p>
                </div>
            </div>
            <div class="h-1.5 bg-slate-100">
                <div id="timerBar" class="h-full bg-slate-900 transition-all duration-1000" style="width:100%"></div>
            </div>
        </div>

        {{-- Instructions --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden mb-4">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-sm text-slate-800">Instruksi</h3>
                <div id="pollingIndicator" class="flex items-center gap-2 text-xs text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Menunggu pembayaran...
                </div>
            </div>
            <div id="paymentInstructionsContent" class="p-6">
                {{-- Loading skeleton --}}
                <div class="animate-pulse space-y-4">
                    <div class="h-16 bg-slate-50 rounded-xl"></div>
                    <div class="h-4 bg-slate-50 rounded w-3/4"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending State --}}
    <div id="stepPending" class="hidden bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-8 text-center text-slate-800">
            <h3 class="font-semibold text-lg mb-1">Menunggu Pembayaran</h3>
            <p class="text-sm text-slate-500 mb-6">Silakan selesaikan pembayaran Anda. Setelah selesai, Anda dapat memeriksa status di bawah ini.</p>
            <button onclick="PaymentFlow.resumePolling()"
                    class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-8 py-3 rounded-xl transition-colors">
                <i class="fas fa-sync-alt text-xs"></i> Cek Pembayaran
            </button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <div id="payment-init-data" class="hidden"
        data-payment-id="{{ $payment->id }}"
        data-grand-total="{{ $payment->transaction->grand_total }}"
        data-type="{{ $type }}"
        data-bank="{{ $bank }}">
    </div>
    @vite('resources/js/pages/customer/payments/flow.js')
@endpush
