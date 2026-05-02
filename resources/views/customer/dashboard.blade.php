@extends('layouts.customer')
@section('title','Dashboard')
@section('content')
<div class="mb-7">
    <h1 class="font-display font-bold text-2xl text-slate-900">Selamat datang kembali, {{ auth()->user()->name }}</h1>
    <p class="text-slate-400 text-sm mt-0.5">Berikut adalah ringkasan riwayat servis Anda.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-7">
    @foreach([['Total Booking',$stats['total_bookings'],'fas fa-calendar-check','slate'],['Aktif',$stats['active'],'fas fa-wrench','amber'],['Selesai',$stats['completed'],'fas fa-check-circle','emerald']] as [$l,$v,$i,$c])
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm">
        <div class="flex items-start justify-between mb-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $l }}</p>
            <div class="w-8 h-8 rounded-lg bg-{{ $c }}-50 flex items-center justify-center">
                <i class="{{ $i }} text-{{ $c }}-500 text-xs"></i>
            </div>
        </div>
        <p class="font-display font-bold text-3xl text-slate-900">{{ $v }}</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-sm text-slate-800">Booking Terbaru</h3>
            <a href="{{ route('customer.bookings.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-800 hover:underline">Lihat semua</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($bookings as $b)
            <div class="flex items-center gap-4 px-6 py-4">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-car text-slate-400 text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm text-slate-800 truncate">{{ $b->vehicle ? $b->vehicle->brand.' '.$b->vehicle->model : '—' }}</p>
                    <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }} · {{ $b->services->count() }} layanan</p>
                </div>
                <x-badges.status :status="$b->status" class="text-[10px] px-2 py-0.5" />
                <a href="{{ route('customer.bookings.show', $b) }}" class="text-slate-300 hover:text-slate-600 transition-colors flex-shrink-0">
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            </div>
            @empty
            <div class="text-center py-12 text-slate-400 text-sm">
                <i class="fas fa-calendar-times text-3xl block mb-3 text-slate-200"></i>
                Belum ada booking.
                <a href="{{ route('customer.bookings.create') }}" class="text-slate-700 font-semibold hover:underline ml-1">Booking sekarang →</a>
            </div>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Kendaraan Saya</h3>
                <a href="{{ route('customer.vehicles.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-800 hover:underline">Kelola</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($vehicles as $v)
                <div class="flex items-center gap-3 px-5 py-3.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-car text-slate-400 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-sm text-slate-800 truncate">{{ $v->brand }} {{ $v->model }}</p>
                        <p class="text-xs text-slate-400">{{ $v->license_plate }} · {{ $v->year }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-xs text-slate-400">
                    <a href="{{ route('customer.vehicles.index') }}" class="text-slate-700 font-semibold hover:underline">Tambah kendaraan pertama Anda →</a>
                </div>
                @endforelse
            </div>
        </div>
        <a href="{{ route('customer.bookings.create') }}"
           class="flex items-center justify-center gap-2 w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3.5 rounded-2xl transition-colors shadow-sm">
            <i class="fas fa-plus text-xs"></i> Booking Baru
        </a>
    </div>
</div>
@endsection
