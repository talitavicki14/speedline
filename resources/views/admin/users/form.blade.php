@extends('layouts.admin')
@section('title', $user ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page-title', $user ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-700">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Pengguna
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    {{-- Main Form Info --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="font-semibold text-sm text-slate-800">{{ $user ? 'Edit: '.$user->name : 'Pengguna Baru' }}</h3>
                </div>
            </div>
            <div class="p-6">
                <form id="userForm" action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @if($user) @method('PUT') @endif

                    @php
                        $roleLabels = [
                            'admin' => 'Administrator',
                            'mekanik' => 'Mekanik',
                            'kasir' => 'Kasir',
                            'customer' => 'Pelanggan',
                            'owner' => 'Pemilik'
                        ];
                        $availableRoles = collect(['admin', 'mekanik', 'kasir', 'customer']);
                        if(auth()->user()->role === 'owner') {
                            $availableRoles->push('owner');
                        }
                    @endphp

                    {{-- Photo Section --}}
                    <div class="flex items-center gap-6 pb-6 border-b border-slate-50">
                        <div class="w-24 h-24 rounded-2xl bg-slate-50 border border-slate-100 overflow-hidden flex items-center justify-center transition-all group-hover:bg-slate-100 cursor-pointer" 
                             onclick="const src = document.getElementById('photoPreview')?.src; if(src && !src.includes('hidden')) showImageFull(src)">
                            @if($user && $user->photo)
                                <img id="photoPreview" src="{{ asset('storage/' . $user->photo) }}" class="w-full h-full object-cover">
                            @else
                                <div id="photoPlaceholder" class="flex flex-col items-center text-slate-400">
                                    <i class="fas fa-camera text-xl mb-1"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-widest">Foto</span>
                                </div>
                                <img id="photoPreview" class="w-full h-full object-cover hidden">
                            @endif
                        </div>
                        
                        <div class="flex-1">
                            <h4 class="font-bold text-slate-800 text-sm mb-2">Foto Profil</h4>
                            <div class="flex flex-wrap gap-2">
                                <input type="file" name="photo" id="photoInput" onchange="previewImage(this, 'photoPreview', 'photoPlaceholder')" class="hidden" accept="image/*">
                                <button type="button" onclick="document.getElementById('photoInput').click()" class="text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg px-4 py-2 hover:bg-slate-50 transition-colors">
                                    Pilih Foto
                                </button>
                                @if($user && $user->photo)
                                <button type="button" onclick="confirmDeletePhoto()" class="text-xs font-semibold text-red-500 border border-red-100 rounded-lg px-4 py-2 hover:bg-red-50 transition-colors">
                                    Hapus Foto
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"
                                   placeholder="Contoh: 08123456789">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Peran / Hak Akses</label>
                            <select name="role" required 
                                    data-placeholder="Pilih Peran"
                                    data-hide-placeholder="true"
                                    class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none bg-white text-slate-700">
                                <option value="" disabled {{ !old('role', $user->role ?? '') ? 'selected' : '' }}>Pilih Peran</option>
                                @foreach($availableRoles as $r)
                                <option value="{{ $r }}" {{ old('role', $user->role ?? '') == $r ? 'selected' : '' }}>{{ $roleLabels[$r] ?? ucfirst($r) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat</label>
                        <textarea name="address" rows="3" 
                                  class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors resize-none">{{ old('address', $user->address ?? '') }}</textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <a href="{{ route('admin.users.index') }}" class="flex-1 text-center py-3 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">Batal</a>
                        <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3 rounded-xl transition-colors shadow-sm">
                            {{ $user ? 'Simpan Perubahan' : 'Buat Pengguna' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Password Sidebar --}}
    <div class="space-y-5">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                <h3 class="font-semibold text-sm text-slate-800">Keamanan</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <div class="flex flex-col gap-1">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Kata Sandi Baru</label>
                            @if($user)
                                <span class="text-[10px] text-slate-400">Kosongkan jika tidak ingin diubah</span>
                            @endif
                        </div>
                        <div class="relative">
                            <input type="password" form="userForm" id="password" name="password" {{ $user ? '' : 'required' }} 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 pr-12 text-sm outline-none transition-colors"
                                   placeholder="Min. 6 karakter">
                            <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 outline-none">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Konfirmasi Sandi</label>
                        <div class="relative">
                            <input type="password" form="userForm" id="password_confirmation" name="password_confirmation" 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 pr-12 text-sm outline-none transition-colors">
                            <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 outline-none">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($user)
<form id="deletePhotoForm" action="{{ route('admin.users.photo.destroy', $user) }}" method="POST" class="hidden">
    @csrf @method('DELETE')
</form>
@endif

@endsection

@push('scripts')
    @vite('resources/js/pages/admin/users/form.js')
@endpush
