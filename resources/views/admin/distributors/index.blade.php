@extends('layouts.admin')
@section('title', 'Distributor')
@section('page-title', 'Daftar Distributor')

@section('content')
@can('manage-data')
<div class="flex justify-end mb-5">
    <button onclick="openModal('addModal')" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus text-xs"></i> Tambah Distributor
    </button>
</div>
@endcan

<div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-semibold text-sm text-slate-800">Data Distributor</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Nama</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Kontak Person</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Telepon</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Email</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($distributors as $d)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-sm text-slate-800">
                        <div>{{ $d->name }}</div>
                        <div class="text-[11px] text-slate-400 font-normal mt-0.5">{{ $d->address ?: '—' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $d->contact_person ?: '—' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $d->phone ?: '—' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $d->email ?: '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        @can('manage-data')
                        <div class="flex items-center justify-end gap-2">
                            <button onclick='openEditDistributor(@json($d))' class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Edit</button>
                            <form action="{{ route('admin.distributors.destroy', $d) }}" method="POST" class="inline" data-confirm="Hapus distributor ini?">
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
                    <td colspan="5" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-truck text-5xl mb-4 text-slate-200"></i>
                            <p class="text-sm text-slate-400 font-medium">Belum ada data distributor</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Tambah Distributor</h3>
        <form action="{{ route('admin.distributors.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Perusahaan / Distributor</label>
                <input type="text" name="name" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Kontak Person</label>
                <input type="text" name="contact_person" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="Nama PIC">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Telepon</label>
                    <input type="text" name="phone" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Email</label>
                    <input type="email" name="email" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Alamat</label>
                <textarea name="address" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors h-24"></textarea>
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
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Edit Distributor</h3>
        <form id="editDistributorForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nama Perusahaan / Distributor</label>
                <input type="text" name="name" id="edit_name" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Kontak Person</label>
                <input type="text" name="contact_person" id="edit_contact_person" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Telepon</label>
                    <input type="text" name="phone" id="edit_phone" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Email</label>
                    <input type="email" name="email" id="edit_email" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Alamat</label>
                <textarea name="address" id="edit_address" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors h-24"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal('editModal')" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.openEditDistributor = function(distributor) {
        const form = document.getElementById('editDistributorForm');
        form.action = `/admin/distributors/${distributor.id}`;
        
        document.getElementById('edit_name').value = distributor.name;
        document.getElementById('edit_contact_person').value = distributor.contact_person || '';
        document.getElementById('edit_phone').value = distributor.phone || '';
        document.getElementById('edit_email').value = distributor.email || '';
        document.getElementById('edit_address').value = distributor.address || '';
        
        openModal('editModal');
    }
</script>
@endpush
