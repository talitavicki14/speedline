<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <title>Masuk | Speedline Automotive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-900 min-h-screen flex" data-flash-error="{{ $errors->first() }}">

    {{-- Left: Visual Panel --}}
    <div class="hidden lg:flex flex-col justify-between w-5/12 bg-slate-950 p-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptNiA2djZoNnYtNmgtNnptLTEyIDB2Nmg2di02aC02em0tNiAwdjZoNnYtNmgtNnoiLz48L2c+PC9nPjwvc3ZnPg==')] opacity-40"></div>
        <a href="{{ route('landing') }}" class="inline-block relative z-10 transition-opacity hover:opacity-80">
            <img src="{{ asset('images/logo_white.png') }}" alt="Speedline Automotive" class="h-8 w-auto object-contain opacity-90">
        </a>
        <div class="relative z-10">
            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest mb-4">Layanan Otomotif Premium</p>
            <h2 class="font-display font-bold text-4xl text-white leading-snug mb-5">
                Perawatan presisi<br>untuk kendaraan<br>istimewa.
            </h2>
            <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                Dipercaya oleh pemilik Mercedes, BMW, Porsche, dan banyak lagi. Layanan ahli, harga transparan.
            </p>
        </div>
    </div>

    {{-- Right: Form --}}
    <div class="flex-1 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-sm">
            <div class="lg:hidden mb-10 text-left">
                <a href="{{ route('landing') }}" class="inline-block transition-opacity hover:opacity-80">
                    <img src="{{ asset('images/logo_black.png') }}" alt="Speedline" class="h-8 w-auto object-contain">
                </a>
            </div>
            <h1 class="font-display font-bold text-2xl text-slate-900 mb-1">Selamat datang kembali</h1>
            <p class="text-slate-400 text-sm mb-8">Masuk ke akun Anda untuk melanjutkan.</p>

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           class="w-full border border-slate-200 focus:border-slate-900 focus:ring-0 rounded-xl px-4 py-3 text-sm text-slate-900 outline-none transition-colors placeholder-slate-300 bg-white"
                           placeholder="anda@contoh.com">
                </div>
                <div class="relative">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kata Sandi</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full border border-slate-200 focus:border-slate-900 focus:ring-0 rounded-xl px-4 py-3 text-sm text-slate-900 outline-none transition-colors placeholder-slate-300 bg-white pr-11"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300 text-slate-900 accent-slate-900">
                    <label for="remember" class="text-sm text-slate-500">Ingat saya</label>
                </div>
                <button type="submit"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3 rounded-xl transition-colors mt-1">
                    Masuk
                </button>
            </form>

            <p class="text-center text-sm text-slate-400 mt-6">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-slate-900 font-semibold hover:underline">Daftar</a>
            </p>
        </div>
    </div>
</body>
</html>
