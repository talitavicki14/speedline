@extends('layouts.admin')
@section('title','Sparepart')
@section('page-title','Sparepart')
@section('content')
@can('manage-data')
<div class="flex justify-end mb-5">
    <button onclick="openModal('addModal')" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus text-xs"></i> Tambah Sparepart
    </button>
</div>
@endcan
<div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-6 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-sm text-slate-800">Inventaris</h3>
        <form method="GET" class="flex items-center gap-2 flex-wrap w-full sm:w-auto" data-auto-filter>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari sparepart..."
                   class="border border-slate-200 focus:border-slate-400 rounded-lg px-3 py-2 text-sm outline-none w-44 placeholder-slate-300 text-slate-700">
            
            @php 
                $stock_opts = [
                    ['value' => 'in',  'label' => 'Tersedia'],
                    ['value' => 'low', 'label' => 'Stok Menipis'],
                    ['value' => 'out', 'label' => 'Habis']
                ];
                $type_opts = $types->map(fn($t) => ['value' => $t, 'label' => $t]);
            @endphp
            <div data-custom-select
                  data-name="type"
                  data-value="{{ request('type') }}"
                  data-placeholder="Semua Jenis"
                  data-options='@json($type_opts)'
                  class="w-40"></div>
            <div data-custom-select
                  data-name="stock_status"
                  data-value="{{ request('stock_status') }}"
                  data-placeholder="Semua Stok"
                  data-options='@json($stock_opts)'
                  class="w-40"></div>

            <div data-custom-select
                  data-name="distributor_id"
                  data-value="{{ request('distributor_id') }}"
                  data-placeholder="Semua Distributor"
                  data-search="true"
                  data-options='@json($distributors->map(fn($d) => ["value" => $d->id, "label" => $d->name]))'
                  class="w-48"></div>

            <div id="clear-container" class="flex items-center">
                @if(request('search') || request('stock_status') || request('distributor_id') || request('type'))
                <a href="{{ route('admin.spareparts.index') }}" class="text-sm text-slate-500 hover:text-slate-800 px-3 py-2 border border-slate-200 rounded-lg transition-colors">Hapus</a>
                @endif
            </div>
        </form>
    </div>
    <div id="filter-container">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="bg-slate-50">
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Nama & Distributor</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Jenis</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Merek</th>
                <th class="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Stok</th>
                <th class="text-right text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Harga Beli</th>
                <th class="text-right text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Harga Jual</th>
                <th class="px-6 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($spareparts as $s)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-medium text-sm text-slate-800">{{ $s->name }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1">
                            <i class="fas fa-truck text-[9px]"></i> {{ $s->distributor->name ?? 'Tanpa Distributor' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $s->type }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $s->brand }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $s->stock <= 5 ? 'bg-red-100 text-red-700' : ($s->stock <= 15 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                            {{ $s->stock }} pcs
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm text-slate-600">Rp {{ number_format($s->purchase_price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-right font-semibold text-sm text-slate-900">Rp {{ number_format($s->price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-right">
                        @can('manage-data')
                        <div class="flex items-center justify-end gap-2">
                            <button onclick='openAddStock(@json($s))' class="text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                                <i class="fas fa-plus"></i> Stok
                            </button>
                            <button onclick='openEdit(@json($s), "sparepart")' class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Edit</button>
                            <form action="{{ route('admin.spareparts.destroy',$s) }}" method="POST" class="inline" data-confirm="Hapus sparepart ini?">
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
                    <td colspan="7" class="px-6 py-20">
                        <div class="flex flex-col items-center justify-center text-center">
                            <i class="fas fa-box-open text-5xl mb-4 text-slate-200"></i>
                            <p class="text-sm text-slate-400 font-medium tracking-wide">Tidak ada sparepart ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination footer --}}
    <x-ui.table-footer :paginator="$spareparts" />
</div>

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Tambah Sparepart</h3>
        <form action="{{ route('admin.spareparts.store') }}" method="POST" class="space-y-4">
            @csrf
            <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Barang</label>
            <input type="text" name="name" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jenis</label>
                <select name="type" 
                        required 
                        data-placeholder="Pilih Jenis"
                        class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none bg-white text-slate-700">
                    <option value="" disabled selected>Pilih Jenis</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Merek</label>
                <input type="text" name="brand" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Harga Beli (Rp)</label>
                <input type="text" name="purchase_price" required class="input-currency w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="0"></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Harga Jual (Rp)</label>
                <input type="text" name="price" required class="input-currency w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="0"></div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Distributor <span class="text-red-500">*</span></label>
                <select name="distributor_id" 
                        required 
                        data-placeholder="Pilih Distributor"
                        data-search="true"
                        class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none bg-white text-slate-700">
                    <option value="" disabled selected>Pilih Distributor</option>
                    @foreach($distributors as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Stok Awal</label>
                <input type="number" name="stock" min="0" value="0" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tanggal Beli</label>
                    <div data-datepicker 
                         data-name="purchase_date" 
                         data-value="{{ date('Y-m-d') }}" 
                         data-placeholder="Pilih Tanggal"
                         class="[&>button]:!rounded-xl [&>button]:!py-3 [&>button]:!px-4"></div>
                </div>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal('addModal')" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Edit Sparepart</h3>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Barang</label>
            <input type="text" name="name" id="edit_name" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jenis</label>
                <select name="type" 
                        id="edit_type"
                        required 
                        data-placeholder="Pilih Jenis"
                        class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none bg-white text-slate-700">
                    @foreach($types as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Merek</label>
                <input type="text" name="brand" id="edit_brand" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Harga Beli (Rp)</label>
                <input type="text" name="purchase_price" id="edit_purchase_price" required class="input-currency w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="0"></div>
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Harga Jual (Rp)</label>
                <input type="text" name="price" id="edit_price" required class="input-currency w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="0"></div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Distributor <span class="text-red-500">*</span></label>
                <div id="edit_distributor_container">
                    <select name="distributor_id" 
                            id="edit_distributor_id"
                            required 
                            data-placeholder="Pilih Distributor"
                            data-search="true"
                            class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none bg-white text-slate-700">
                        @foreach($distributors as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1">
                <div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Stok</label>
                <input type="number" name="stock" id="edit_stock" min="0" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"></div>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal('editModal')" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Stock Modal --}}
<div id="addStockModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <div class="mb-5">
            <h3 class="font-display font-bold text-lg text-slate-900 mb-2">Tambah Stok</h3>
            <input type="text" id="stock_item_name" disabled class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm text-slate-500 font-medium">
        </div>
        <form id="addStockForm" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jumlah (Qty)</label>
                    <input type="number" name="qty" required min="1" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="0">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tanggal Beli</label>
                    <div data-datepicker 
                         data-name="purchase_date" 
                         data-value="{{ date('Y-m-d') }}" 
                         data-placeholder="Pilih Tanggal"
                         class="[&>button]:!rounded-xl [&>button]:!py-3 [&>button]:!px-4"></div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Harga Beli Baru (Rp)</label>
                <input type="text" name="purchase_price" id="stock_purchase_price" required class="input-currency w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="0">
                <p class="text-[10px] text-slate-400 mt-1 italic">* Harga beli ini akan mengupdate harga beli sparepart saat ini.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Distributor</label>
                <div id="stock_distributor_container">
                    <select name="distributor_id" 
                            id="stock_distributor_id"
                            required 
                            data-placeholder="Pilih Distributor"
                            data-search="true"
                            class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none bg-white text-slate-700">
                        @foreach($distributors as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addStockModal')" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">Simpan Stok</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
    @vite('resources/js/pages/admin/inventory/index.js')
@endpush
