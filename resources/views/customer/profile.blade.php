@extends('layouts.customer')
@section('title', 'Profil Saya')
@section('content')
<div class="mb-7">
    <h1 class="font-display font-bold text-2xl text-slate-900">Pengaturan Profil</h1>
    <p class="text-slate-400 text-sm mt-0.5">Kelola informasi akun dan keamanan kata sandi Anda.</p>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    {{-- Main Profile Info --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                <h3 class="font-semibold text-sm text-slate-800">Informasi Pribadi</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf @method('PUT')
                    
                    {{-- Photo Section --}}
                    <div class="flex items-center gap-6 pb-6 border-b border-slate-50">
                        <div class="w-24 h-24 rounded-2xl bg-slate-50 border border-slate-100 overflow-hidden flex items-center justify-center transition-all group-hover:bg-slate-100 cursor-pointer" 
                             onclick="const src = document.getElementById('photoPreview')?.src; if(src && !src.includes('hidden')) showImageFull(src)">
                            @if($user->photo)
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
                                @if($user->photo)
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
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor WhatsApp / Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                               class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors"
                               placeholder="Contoh: 08123456789">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Lengkap</label>
                        <textarea name="address" rows="3" 
                                  class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors resize-none">{{ old('address', $user->address) }}</textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-8 py-3 rounded-xl transition-all shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Security/Password Sidebar --}}
    <div class="space-y-5">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                <h3 class="font-semibold text-sm text-slate-800">Keamanan</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('customer.profile.password') }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Kata Sandi Saat Ini</label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password" required 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 pr-12 text-sm outline-none transition-colors">
                            <button type="button" onclick="togglePassword('current_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 outline-none">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Kata Sandi Baru</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 pr-12 text-sm outline-none transition-colors">
                            <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 outline-none">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Konfirmasi Sandi Baru</label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required 
                                   class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 pr-12 text-sm outline-none transition-colors">
                            <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 outline-none">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-slate-50 hover:bg-slate-100 text-slate-800 font-semibold text-sm py-3 rounded-xl transition-colors">
                            Ubah Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<form id="deletePhotoForm" action="{{ route('customer.profile.photo.destroy') }}" method="POST" class="hidden">
    @csrf @method('DELETE')
</form>

@push('scripts')
    @vite('resources/js/pages/customer/profile.js')
@endpush
@endsection
