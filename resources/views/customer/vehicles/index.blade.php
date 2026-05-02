@extends('layouts.customer')
@section('title','Kendaraan Saya')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl text-slate-900">Kendaraan Saya</h1>
        <p class="text-slate-400 text-sm mt-0.5">Kelola kendaraan Anda yang terdaftar</p>
    </div>
    <button onclick="openModal('addModal')" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus text-xs"></i> Tambah Kendaraan
    </button>
</div>

@if($vehicles->isEmpty())
<div class="bg-white border border-slate-100 rounded-2xl shadow-sm text-center py-20">
    <i class="fas fa-car text-5xl text-slate-200 mb-4 block"></i>
    <p class="text-slate-500 text-sm mb-5">Belum ada kendaraan yang terdaftar.</p>
    <button onclick="openModal('addModal')" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        Daftarkan Kendaraan Pertama Anda
    </button>
</div>
@else
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($vehicles as $v)
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden hover:border-slate-300 transition-colors">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                <i class="fas fa-car text-slate-500 text-sm"></i>
            </div>
            <div class="flex gap-1.5">
                <button onclick='openEdit(@json($v))' class="w-8 h-8 rounded-lg border border-slate-200 hover:border-slate-400 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-colors">
                    <i class="fas fa-pen text-xs"></i>
                </button>
                <form action="{{ route('customer.vehicles.destroy',$v) }}" method="POST" data-confirm="Hapus kendaraan ini?" class="inline">
                    @csrf @method('DELETE')
                    <button class="w-8 h-8 rounded-lg border border-red-200 hover:border-red-400 flex items-center justify-center text-red-400 hover:text-red-600 transition-colors">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="p-5">
            <h3 class="font-semibold text-base text-slate-900 mb-3">{{ $v->brand }} {{ $v->model }}</h3>
            <div class="space-y-1.5 text-sm text-slate-500">
                <p><i class="fas fa-calendar-alt w-4 mr-2 text-slate-400"></i>{{ $v->year }}</p>
                <p><i class="fas fa-id-card w-4 mr-2 text-slate-400"></i>{{ $v->license_plate }}</p>
                <p><i class="fas fa-circle w-4 mr-2 text-slate-400"></i>{{ $v->color }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Tambah Kendaraan</h3>
        <form action="{{ route('customer.vehicles.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Merek</label>
                <input type="text" name="brand" value="{{ old('brand') }}" required placeholder="Contoh: Toyota" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300"></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Model</label>
                <input type="text" name="model" value="{{ old('model') }}" required placeholder="Contoh: Avanza" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tahun</label>
                <input type="number" name="year" value="{{ old('year') }}" min="1990" max="{{ date('Y')+1 }}" required placeholder="{{ date('Y') }}" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300"></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Warna</label>
                <input type="text" name="color" value="{{ old('color') }}" required placeholder="Contoh: Putih" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300"></div>
            </div>
            <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nomor Plat</label>
            <input type="text" name="license_plate" value="{{ old('license_plate') }}" required placeholder="Contoh: B 1234 ABC" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300"></div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal('addModal')" class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3 rounded-xl transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Edit Kendaraan</h3>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Merek</label>
                <input type="text" name="brand" id="e_brand" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Model</label>
                <input type="text" name="model" id="e_model" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tahun</label>
                <input type="number" name="year" id="e_year" min="1990" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Warna</label>
                <input type="text" name="color" id="e_color" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            </div>
            <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nomor Plat</label>
            <input type="text" name="license_plate" id="e_plate" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal('editModal')" class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3 rounded-xl transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    @vite('resources/js/pages/customer/vehicles/index.js')
@endpush
@endsection
