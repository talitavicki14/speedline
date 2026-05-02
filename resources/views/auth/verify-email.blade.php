<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <title>Verifikasi Email | Speedline Automotive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/pages/auth/verify-email.js'])
    
    @if(session('first_time'))
    <script>
        localStorage.setItem('speedline_auth_resend_cooldown', (Date.now() + 60000).toString());
    </script>
    @endif
</head>
<body class="bg-white text-slate-900 min-h-screen flex" 
      data-verification-sent="{{ session('status') == 'verification-link-sent' ? 'true' : 'false' }}"
      data-first-time="{{ session('first_time') ? 'true' : 'false' }}">

    {{-- Left: Visual Panel --}}
    <div class="hidden lg:flex flex-col justify-between w-5/12 bg-slate-950 p-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptNiA2djZoNnYtNmgtNnptLTEyIDB2Nmg2di02aC02em0tNiAwdjZoNnYtNmgtNnoiLz48L2c+PC9nPjwvc3ZnPg==')] opacity-40"></div>
        <a href="{{ route('landing') }}" class="inline-block relative z-10 transition-opacity hover:opacity-80">
            <img src="{{ asset('images/logo_white.png') }}" alt="Speedline Automotive" class="h-8 w-auto object-contain opacity-90">
        </a>
        <div class="relative z-10">
            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest mb-4">Verifikasi Diperlukan</p>
            <h2 class="font-display font-bold text-4xl text-white leading-snug mb-5">
                Satu langkah lagi<br>untuk keamanan<br>kendaraan Anda.
            </h2>
            <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                Kami telah mengirimkan tautan verifikasi ke email Anda. Pastikan email Anda aktif untuk menerima update status booking.
            </p>
        </div>
    </div>

    {{-- Right: Content --}}
    <div class="flex-1 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-sm text-center lg:text-left">
            <div class="lg:hidden mb-10 flex justify-center">
                <a href="{{ route('landing') }}" class="inline-block transition-opacity hover:opacity-80">
                    <img src="{{ asset('images/logo_black.png') }}" alt="Speedline" class="h-8 w-auto object-contain">
                </a>
            </div>
            
            <h1 class="font-display font-bold text-2xl text-slate-900 mb-2">Cek email Anda</h1>
            <p class="text-slate-400 text-sm mb-8 leading-relaxed">
                Tautan verifikasi telah dikirimkan. Silakan klik tautan tersebut untuk mengaktifkan akun Anda.
            </p>

            <div class="space-y-3">
                <form id="resendForm" method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" id="resendBtn"
                            class="w-full bg-slate-900 hover:bg-slate-800 disabled:bg-slate-400 text-white font-semibold text-sm py-3.5 rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane text-xs"></i>
                        <span id="btnText">Kirim Ulang Tautan</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-slate-500 hover:text-slate-900 font-semibold text-xs py-3 rounded-xl transition-colors">
                        Keluar Akun
                    </button>
                </form>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-50">
                <p class="text-xs text-slate-400 italic">
                    Tidak menemukan email? Periksa folder spam atau promosi Anda.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
