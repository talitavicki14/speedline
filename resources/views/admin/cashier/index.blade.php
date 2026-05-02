@extends('layouts.admin')
@section('title', 'Kasir Retail')
@section('page-title', 'Kasir Retail')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-7">
    {{-- Product List --}}
    <div class="lg:col-span-7 space-y-5">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3 mb-5">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="sparepartSearch" placeholder="Cari nama atau merek sparepart..." 
                           class="w-full bg-slate-50 border-none focus:ring-2 focus:ring-slate-900 rounded-xl pl-10 pr-4 py-3 text-sm outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[calc(100vh-16rem)] overflow-y-auto pr-1" id="sparepartList">
                @foreach($spareparts as $s)
                <div class="sparepart-item group bg-white border border-slate-100 rounded-2xl p-4 hover:border-slate-900 hover:shadow-md transition-all cursor-pointer"
                     data-id="{{ $s->id }}" 
                     data-name="{{ $s->name }}" 
                     data-price="{{ $s->price }}" 
                     data-stock="{{ $s->stock }}"
                     data-brand="{{ $s->brand }}"
                     data-type="{{ $s->type }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900 group-hover:text-slate-900 transition-colors mb-1">{{ $s->name }}</h4>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ $s->type }} · {{ $s->brand }}</p>
                        </div>
                        <span class="text-xs font-bold text-slate-900">Rp {{ number_format($s->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $s->stock <= 5 ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-600' }}">
                            Stok: {{ $s->stock }}
                        </span>
                        <button class="w-7 h-7 rounded-full bg-slate-900 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all transform scale-75 group-hover:scale-100">
                            <i class="fas fa-plus text-[10px]"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Cart / Checkout --}}
    <div class="lg:col-span-5">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm flex flex-col h-full sticky top-24">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900">Keranjang</h3>
                <button id="clearCart" class="text-[10px] font-bold text-red-500 uppercase tracking-widest hover:text-red-700 transition-colors">Kosongkan</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 min-h-[12rem] space-y-4" id="cartItems">
                {{-- Cart items will be injected here --}}
                <div class="empty-cart flex flex-col items-center justify-center h-full text-center py-10">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-shopping-basket text-slate-200 text-2xl"></i>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Keranjang masih kosong</p>
                </div>
            </div>

            <div class="p-5 bg-slate-50 border-t border-slate-100 rounded-b-2xl space-y-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-medium">Total Item</span>
                        <span class="text-slate-900 font-bold" id="totalQty">0</span>
                    </div>
                    <div class="flex justify-between text-lg">
                        <span class="text-slate-900 font-bold">Total Harga</span>
                        <span class="text-slate-900 font-black" id="totalAmount">Rp 0</span>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Jumlah Bayar (Tunai)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                            <input type="text" id="amountPaid" class="input-currency w-full bg-white border border-slate-200 focus:border-slate-900 rounded-xl pl-11 pr-4 py-3 text-sm font-bold outline-none transition-all" placeholder="0">
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-sm px-1">
                        <span class="text-slate-500 font-medium">Kembalian</span>
                        <span class="text-emerald-600 font-black" id="changeAmount">Rp 0</span>
                    </div>

                    <button id="btnCheckout" disabled class="w-full py-4 rounded-xl transition-all flex items-center justify-center gap-2 font-bold 
                        bg-slate-100 text-slate-400 cursor-not-allowed shadow-none
                        enabled:bg-slate-900 enabled:text-white enabled:shadow-lg enabled:shadow-slate-900/10 enabled:cursor-pointer 
                        hover:enabled:shadow-xl hover:enabled:shadow-slate-900/20 active:enabled:scale-[0.98]">
                        <i class="fas fa-check-circle"></i> Selesaikan Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Template for Cart Item (Hidden) --}}
<template id="cartItemTemplate">
    <div class="cart-item flex items-center gap-4 bg-white border border-slate-100 p-3 rounded-xl">
        <div class="flex-1 min-w-0">
            <h5 class="text-xs font-bold text-slate-900 truncate item-name"></h5>
            <p class="text-[10px] font-bold text-slate-400 item-price"></p>
        </div>
        <div class="flex items-center gap-2">
            <button class="btn-qty-minus w-6 h-6 rounded-lg bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-900 transition-colors flex items-center justify-center">
                <i class="fas fa-minus text-[8px]"></i>
            </button>
            <span class="text-xs font-black text-slate-900 w-4 text-center item-qty">1</span>
            <button class="btn-qty-plus w-6 h-6 rounded-lg bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-900 transition-colors flex items-center justify-center">
                <i class="fas fa-plus text-[8px]"></i>
            </button>
        </div>
        <div class="text-right min-w-[5rem]">
            <p class="text-xs font-black text-slate-900 item-subtotal"></p>
            <button class="btn-remove text-[9px] font-bold text-red-400 hover:text-red-600 transition-colors">Hapus</button>
        </div>
    </div>
</template>

<div id="receiptContainer" style="display:none"></div>
@endsection

@push('scripts')
    <script>
        const STORE_URL = "{{ route('admin.cashier.store') }}";
    </script>
    @vite('resources/js/pages/admin/cashier/index.js')
@endpush
