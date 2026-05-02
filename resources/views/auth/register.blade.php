<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <title>Daftar | Speedline Automotive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center p-6" data-flash-error="{{ $errors->first() }}">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}" class="inline-block mb-5">
                <img src="{{ asset('images/logo_black.png') }}" alt="Speedline" class="h-10 w-auto mx-auto object-contain">
            </a>
            <h1 class="font-display font-bold text-2xl text-slate-900">Buat Akun</h1>
            <p class="text-slate-400 text-sm mt-1">Bergabung dengan Speedline dan kelola jadwal servis Anda</p>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl p-8 shadow-sm">
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300"
                           placeholder="Nama lengkap Anda">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300"
                           placeholder="anda@contoh.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300"
                           placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kata Sandi</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300 pr-11"
                               placeholder="Minimal 6 karakter">
                        <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors placeholder-slate-300 pr-11"
                               placeholder="Ulangi kata sandi Anda">
                        <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
                <button type="submit"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3 rounded-xl transition-colors mt-1">
                    Buat Akun
                </button>
            </form>
        </div>
        <p class="text-center text-sm text-slate-400 mt-5">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-slate-900 font-semibold hover:underline">Masuk</a>
        </p>
    </div>
</body>
</html>
