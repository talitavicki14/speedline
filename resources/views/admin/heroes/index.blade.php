@extends('layouts.admin')
@section('title', 'Banner Promosi')
@section('page-title', 'Banner Landing Page')

@section('content')
@can('manage-data')
<div class="flex justify-end mb-5">
    <button onclick="openModal('addModal')" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus text-xs"></i> Tambah Slide
    </button>
</div>
@endcan

<div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-sm text-slate-800">List Banner</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3 w-40">Preview</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Judul & Subjudul</th>
                    <th class="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Urutan</th>
                    <th class="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($heroes as $h)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="w-32 h-20 rounded-lg overflow-hidden bg-slate-100 border border-slate-100 cursor-zoom-in" 
                             onclick="showImageFull('{{ asset('storage/'.$h->image_url) }}')">
                            <img src="{{ asset('storage/'.$h->image_url) }}" alt="Preview" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-sm text-slate-800">{{ $h->title }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $h->subtitle ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-4 text-center text-sm font-medium text-slate-600">{{ $h->order }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide {{ $h->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $h->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        @can('manage-data')
                        <div class="flex items-center justify-end gap-2">
                            <button onclick='openEditHero(@json($h))' class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Edit</button>
                            <form action="{{ route('admin.heroes.destroy', $h) }}" method="POST" class="inline" data-confirm="Hapus slide ini?">
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
                        <div class="flex flex-col items-center">
                            <i class="fas fa-images text-5xl text-slate-200 mb-4"></i>
                            <p class="text-sm text-slate-400">Belum ada slide banner yang ditambahkan</p>
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
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Tambah Slide</h3>
        <form action="{{ route('admin.heroes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Gambar Banner (Max 2MB)</label>
                <div class="mb-3 w-full h-32 rounded-xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden group relative">
                    <img id="add_preview" class="hidden w-full h-full object-cover">
                    <div id="add_placeholder" class="text-slate-300 flex flex-col items-center">
                        <i class="fas fa-image text-2xl mb-1"></i>
                        <span class="text-[10px]">Pratinjau Gambar</span>
                    </div>
                </div>
                <input type="file" name="image" required accept="image/*" onchange="previewImage(this, 'add_preview', 'add_placeholder')" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Judul Utama</label>
                <input type="text" name="title" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors" placeholder="Contoh: Servis Motor Professional">
            </div>
            <div class="relative">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Subjudul (Optional)</label>
                <textarea name="subtitle" id="add_hero_subtitle" maxlength="200" oninput="updateCharCount(this, 'add_char_count')" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors min-h-[80px] max-h-[120px]" placeholder="Contoh: Harga terjangkau dengan teknisi ahli"></textarea>
                <div class="absolute right-3 bottom-2 text-[9px] font-bold text-slate-300" id="add_char_count">200</div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Urutan Tampil</label>
                    <input type="number" name="order" value="{{ $suggestedOrder }}" min="1" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                </div>
                <div class="flex items-end pb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" checked class="w-4 h-4 rounded text-slate-900 focus:ring-slate-900">
                        <span class="text-sm font-semibold text-slate-700">Aktifkan</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addModal')" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 w-full max-w-md">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-5">Edit Slide</h3>
        <form id="editHeroForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Gambar Banner</label>
                <div class="mb-3 w-full h-32 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden">
                    <img id="edit_preview" class="w-full h-full object-cover">
                </div>
                <input type="file" name="image" accept="image/*" onchange="previewImage(this, 'edit_preview')" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-2 italic">* Biarkan kosong jika tidak ingin mengganti gambar</p>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Judul Utama</label>
                <input type="text" name="title" id="edit_hero_title" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
            </div>
            <div class="relative">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Subjudul (Optional)</label>
                <textarea name="subtitle" id="edit_hero_subtitle" maxlength="200" oninput="updateCharCount(this, 'edit_char_count')" class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors min-h-[80px] max-h-[120px]"></textarea>
                <div class="absolute right-3 bottom-2 text-[9px] font-bold text-slate-300" id="edit_char_count">200</div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Urutan Tampil</label>
                    <input type="number" name="order" id="edit_hero_order" min="1" required class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                </div>
                <div class="flex items-end pb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="edit_hero_active" class="w-4 h-4 rounded text-slate-900 focus:ring-slate-900">
                        <span class="text-sm font-semibold text-slate-700">Aktifkan</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editModal')" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/pages/admin/heroes/index.js')
@endpush
