@extends('layouts.customer')
@section('title','Booking Baru')
@section('content')
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('customer.bookings.index') }}" class="text-slate-400 hover:text-slate-700"><i class="fas fa-arrow-left mr-1"></i> Booking</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Booking Baru</span>
</div>

<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="font-display font-bold text-2xl text-slate-900">Buat Jadwal Servis</h1>
        <p class="text-slate-400 text-sm mt-0.5">Jadwalkan perawatan kendaraan Anda di Speedline.</p>
    </div>

    <form action="{{ route('customer.bookings.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Vehicle --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-sm text-slate-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">1</span>
                Pilih Kendaraan
            </h3>
            <div class="space-y-2">
                @forelse($vehicles as $v)
                <label class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-slate-400 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50 transition-all">
                    <input type="radio" name="vehicle_id" value="{{ $v->id }}" class="accent-slate-900 w-4 h-4"
                           {{ old('vehicle_id') == $v->id ? 'checked' : '' }} required>
                    <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-car text-slate-500 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-slate-800">{{ $v->brand }} {{ $v->model }}</p>
                        <p class="text-xs text-slate-400">{{ $v->license_plate }} · {{ $v->color }} · {{ $v->year }}</p>
                    </div>
                </label>
                @empty
                <div class="text-center py-6 text-sm text-slate-400 border border-dashed border-slate-200 rounded-xl">
                    Belum ada kendaraan terdaftar.
                </div>
                @endforelse
            </div>
            <a href="{{ route('customer.vehicles.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 mt-3 transition-colors">
                <i class="fas fa-plus text-[10px]"></i> Tambah kendaraan baru
            </a>
        </div>

        {{-- Schedule --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-sm text-slate-800 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">2</span>
                Jadwal Servis
            </h3>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tanggal</label>
            <div class="grid grid-cols-2 gap-4">
                <div data-datepicker
                    data-name="booking_date"
                    data-value="{{ old('booking_date') }}"
                    data-min="{{ $minDate }}"
                    data-placeholder="Pilih tanggal"
                    data-hide-today="true"
                    class="w-full"></div>
                <div>
                    @php
                        $time_options = \App\Enums\BookingConfig::getOptions();
                    @endphp
                    <div data-custom-select
                        data-name="booking_time"
                        data-placeholder="Pilih jam..."
                        data-value="{{ old('booking_time') }}"
                        data-options='@json($time_options)'
                        data-current-day="{{ now()->format('Y-m-d') }}"
                        data-current-hour="{{ now()->format('H:i') }}"
                        data-base-options='@json($time_options)'
                        class="w-full"></div>
                </div>
            </div>
        </div>

        {{-- Services --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-sm text-slate-800 mb-1 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">3</span>
                Pilih Layanan
                <span class="text-slate-400 font-normal text-xs">(opsional)</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4 ml-8">Anda juga dapat menambahkan layanan nanti saat dikonfirmasi.</p>
            
            <div class="relative mb-3">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="serviceSearch" placeholder="Cari layanan..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 focus:border-slate-900 rounded-xl text-sm outline-none transition-colors placeholder-slate-400">
            </div>

            <div class="space-y-2 max-h-64 overflow-y-auto pr-2 custom-scrollbar" id="servicesList">
                @forelse($services as $svc)
                <label class="service-item flex items-center justify-between p-4 rounded-xl border border-slate-200 hover:border-slate-400 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50 cursor-pointer transition-all">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="service_ids[]" value="{{ $svc->id }}"
                               {{ in_array($svc->id, old('service_ids',[])) ? 'checked' : '' }}
                               class="w-4 h-4 accent-slate-900 rounded">
                        <div>
                            <p class="service-name font-medium text-sm text-slate-800">{{ $svc->service_name }}</p>
                            <p class="text-xs text-slate-400">Est. {{ $svc->estimated_time }} menit</p>
                        </div>
                    </div>
                    <span class="font-semibold text-sm text-slate-700 whitespace-nowrap">Rp {{ number_format($svc->price,0,',','.') }}</span>
                </label>
                @empty
                <div class="text-center py-6 text-sm text-slate-400 border border-dashed border-slate-200 rounded-xl">
                    Layanan tidak tersedia saat ini.
                </div>
                @endforelse
                
                <div id="noServiceFound" class="hidden text-center py-6 text-sm text-slate-400 border border-dashed border-slate-200 rounded-xl">
                    Layanan tidak ditemukan.
                </div>
            </div>
        </div>

        {{-- Complaint --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-sm text-slate-800 mb-1 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">4</span>
                Deskripsi Keluhan
                <span class="text-slate-400 font-normal text-xs">(opsional)</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4 ml-8">Membantu teknisi kami mempersiapkan pengecekan lebih awal.</p>
            <textarea name="complaint" rows="3"
                      placeholder="Contoh: Mesin terdengar bunyi kasar saat akselerasi di RPM rendah..."
                      class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors resize-none text-slate-700 placeholder-slate-300">{{ old('complaint') }}</textarea>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('customer.bookings.index') }}" class="flex-1 text-center py-3.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</a>
            <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3.5 rounded-xl transition-colors">
                <i class="fas fa-calendar-check mr-2"></i> Kirim Booking
            </button>
        </div>
    </form>
</div>

@push('scripts')
    @vite('resources/js/pages/customer/bookings/create.js')
@endpush
@endsection
