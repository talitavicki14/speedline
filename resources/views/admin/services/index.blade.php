@extends('layouts.admin')
@section('title','Layanan')
@section('page-title','Layanan')

@section('content')
@can('manage-data')
<div class="flex justify-end mb-5">
    <button onclick="openModal('addModal')" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus text-xs"></i> Tambah Layanan
    </button>
</div>
@endcan
<div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-6 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-sm text-slate-800">Katalog Layanan</h3>
        <form method="GET" class="flex items-center gap-2 flex-wrap w-full sm:w-auto" data-auto-filter>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari layanan..."
                   class="border border-slate-200 focus:border-slate-400 rounded-lg px-3 py-2 text-sm outline-none w-44 placeholder-slate-300 text-slate-700">
            <div id="clear-container" class="flex items-center">
            @if(request('search'))
            <a href="{{ route('admin.services.index') }}" class="text-sm text-slate-500 hover:text-slate-800 px-3 py-2 border border-slate-200 rounded-lg transition-colors">Hapus</a>
            @endif
            </div>
        </form>
    </div>
    <div id="filter-container">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="bg-slate-50">
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Nama Layanan</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Deskripsi</th>
                <th class="text-right text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Harga</th>
                <th class="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Est. Waktu</th>
                <th class="px-6 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($services as $s)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-sm text-slate-800">{{ $s->service_name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">{{ $s->description ?? '—' }}</td>
                    <td class="px-6 py-4 text-right font-semibold text-sm text-slate-900">Rp {{ number_format($s->price,0,',','.') }}</td>
                    <td class="px-6 py-4 text-center text-sm text-slate-500">{{ $s->estimated_time }} menit</td>
                    <td class="px-6 py-4 text-right">
                        @can('manage-data')
                        <div class="flex items-center justify-end gap-2">
                            <button onclick='openEdit(@json($s), "service")' class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Edit</button>
                            <form action="{{ route('admin.services.destroy',$s) }}" method="POST" class="inline" data-confirm="Hapus layanan ini?">
                                @csrf @method('DELETE')
                                <button class="text-xs font-semibold text-red-500 hover:text-red-700 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition-colors">Hapus</button>
                            </form>
                        </div>
                        @else
                        <span class="text-[10px] text-slate-300 italic">Hanya Baca</span>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-20">
                        <div class="flex flex-col items-center justify-center text-center">
                            <i class="fas fa-tools text-5xl mb-4 text-slate-200"></i>
                            <p class="text-sm text-slate-400 font-medium tracking-wide">Belum ada layanan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination footer --}}
    <x-ui.table-footer :paginator="$services" />
</div>

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Tambah Layanan</h3>
        <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-4">
            @csrf
            <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Layanan</label>
            <input type="text" name="service_name" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors resize-none"></textarea></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Harga (Rp)</label>
                <input type="text" name="price" required class="input-currency w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="0"></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Est. Waktu (menit)</label>
                <input type="number" name="estimated_time" min="1" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal('addModal')" class="flex-1 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold py-2.5 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Edit Layanan</h3>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Layanan</label>
            <input type="text" name="service_name" id="edit_service_name" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Deskripsi</label>
            <textarea name="description" id="edit_description" rows="2" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors resize-none"></textarea></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Harga (Rp)</label>
                <input type="text" name="price" id="edit_price" required class="input-currency w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="0"></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Est. Waktu (menit)</label>
                <input type="number" name="estimated_time" id="edit_estimated_time" min="1" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal('editModal')" class="flex-1 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold py-2.5 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/admin/inventory/index.js')
@endpush
