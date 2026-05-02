<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <title>Speedline Automotive | Servis Mobil Premium</title>
    @vite(['resources/css/app.css', 'resources/js/pages/landing/index.js'])
</head>
<body class="bg-white text-slate-900 overflow-x-hidden">
<div class="page-progress"></div>
@php
    $bookingRoute = route('register');
    $authRoute = route('login');
    $isLoggedIn = auth()->check();
    $role = $isLoggedIn ? auth()->user()->role : null;

    if ($isLoggedIn) {
        if ($role === 'customer') {
            $bookingRoute = route('customer.bookings.create');
            $authRoute = route('customer.dashboard');
        } else {
            $bookingRoute = route('admin.dashboard');
            $authRoute = route('admin.dashboard');
        }
    }
@endphp

{{-- Navbar --}}
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
        <a href="#" class="inline-block transition-opacity hover:opacity-80">
            <img src="{{ asset('images/logo_black.png') }}" alt="Speedline Automotive" class="h-8 w-auto object-contain">
        </a>
        <div class="hidden md:flex items-center gap-8">
            <a href="#services" class="text-sm text-slate-500 hover:text-slate-900 transition-colors font-medium">Layanan</a>
            <a href="#why" class="text-sm text-slate-500 hover:text-slate-900 transition-colors font-medium">Tentang Kami</a>
            <a href="#contact" class="text-sm text-slate-500 hover:text-slate-900 transition-colors font-medium">Kontak</a>
        </div>
        <div class="flex items-center gap-3">
            @guest
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                Masuk
            </a>
            <a href="{{ route('register') }}"
               class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                Booking Servis
            </a>
            @else
            <a href="{{ $authRoute }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                Dashboard
            </a>
            <a href="{{ $bookingRoute }}"
               class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                {{ $role === 'customer' ? 'Booking Saya' : 'Panel Admin' }}
            </a>
            @endguest
        </div>
    </div>
</nav>

{{-- Hero Carousel --}}
@php
    $finalHeroes = $heroes->count() > 0 ? $heroes : collect([
        (object)[
            'image_url' => 'images/carousel/carousel-1.webp',
            'title' => 'Perawatan ahli untuk kendaraan istimewa Anda.',
            'subtitle' => 'Spesialis mobil performa Eropa dan Jepang. Harga transparan, teknisi bersertifikat.',
            'is_default' => true
        ],
        (object)[
            'image_url' => 'images/carousel/carousel-2.webp',
            'title' => 'Diagnostik Presisi & Akurat.',
            'subtitle' => 'Menggunakan peralatan standar pabrikan untuk hasil terbaik.',
            'is_default' => true
        ]
    ]);
@endphp

<section class="relative w-full overflow-hidden" style="height: 92vh; min-height: 560px; margin-top: 64px;">
    @foreach($finalHeroes as $i => $hero)
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out {{ $i === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}">
        <img src="{{ isset($hero->is_default) ? asset($hero->image_url) : asset('storage/'.$hero->image_url) }}" 
             alt="{{ $hero->title }}"
             class="w-full h-full object-cover object-center">
        <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(2,6,23,0.80) 0%, rgba(2,6,23,0.40) 50%, rgba(2,6,23,0.30) 100%);"></div>
        
        {{-- Text Overlay --}}
        <div class="absolute inset-0 z-20 flex items-center px-6 sm:px-12 md:px-24">
            <div class="max-w-6xl mx-auto w-full flex flex-col items-center md:items-start text-center md:text-left mt-[-2rem] md:mt-0">
                <div class="max-w-3xl w-full">
                    <h1 class="font-display font-bold text-4xl sm:text-5xl lg:text-7xl text-white leading-tight mb-4 md:mb-6">
                        {!! nl2br(e($hero->title)) !!}
                    </h1>
                    <p class="text-white/70 text-base md:text-lg leading-relaxed mb-6 md:mb-8 max-w-sm sm:max-w-md mx-auto md:mx-0 md:max-w-lg">
                        {{ $hero->subtitle }}
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center md:justify-start gap-3 md:gap-4 mb-6 md:mb-8">
                        <a href="{{ $bookingRoute }}"
                           class="w-full sm:w-auto flex items-center justify-center gap-2 bg-white hover:bg-slate-100 text-slate-900 font-semibold text-sm px-7 py-4 rounded-xl transition-colors shadow-lg">
                            {{ $isLoggedIn && $role === 'customer' ? 'Buat Booking Baru' : 'Jadwalkan Servis' }} <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                        <a href="#services"
                           class="w-full sm:w-auto flex items-center justify-center gap-2 text-white hover:text-white/80 font-semibold text-sm px-7 py-4 border border-white/30 hover:border-white/50 rounded-xl transition-colors bg-white/5 backdrop-blur-sm">
                            Lihat Layanan
                        </a>
                    </div>
                    
                    <div class="inline-flex items-center justify-center gap-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-5 py-3 shadow-lg w-full sm:w-auto">
                        <i class="fas fa-check-circle text-emerald-400 text-lg"></i>
                        <p class="text-sm font-medium text-white">
                            Buka Setiap Hari · {{ \App\Enums\BookingConfig::formatRange() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <button onclick="heroCarouselMove(-1)"
            class="group absolute top-auto bottom-6 md:top-1/2 md:-translate-y-1/2 left-6 md:left-8 z-30 w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/10 hover:bg-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all">
        <i class="fas fa-chevron-left text-white group-hover:text-black transition-colors text-sm md:text-base"></i>
    </button>

    <button onclick="heroCarouselMove(1)"
            class="group absolute top-auto bottom-6 md:top-1/2 md:-translate-y-1/2 right-6 md:right-8 z-30 w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/10 hover:bg-white backdrop-blur-md border border-white/20 flex items-center justify-center transition-all">
        <i class="fas fa-chevron-right text-white group-hover:text-black transition-colors text-sm md:text-base"></i>
    </button>

    <div class="absolute bottom-8 left-0 right-0 z-20 flex justify-center gap-2.5">
        @foreach($finalHeroes as $i => $hero)
        <button onclick="heroCarouselGoTo({{ $i }})"
                class="hero-dot transition-all duration-300 rounded-full {{ $i === 0 ? 'w-8 h-2.5 bg-white' : 'w-2.5 h-2.5 bg-white/40 hover:bg-white/60' }}"
                data-index="{{ $i }}"></button>
        @endforeach
    </div>
</section>

{{-- Stats --}}
<section class="border-y border-slate-100 bg-slate-50">
    <div class="max-w-6xl mx-auto px-6 py-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        @foreach([['500+','Kendaraan Diservis'],['15+','Tahun Pengalaman'],['8','Teknisi Bersertifikat'],['99%','Tingkat Kepuasan']] as [$v,$l])
        <div>
            <div class="font-display font-bold text-3xl text-slate-900 mb-1">{{ $v }}</div>
            <div class="text-sm text-slate-500">{{ $l }}</div>
        </div>
        @endforeach
    </div>
</section>

{{-- Services --}}
<section id="services" class="py-20 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="mb-12">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-400 mb-3">Apa yang Kami Tawarkan</p>
            <h2 class="font-display font-bold text-4xl text-slate-900">Layanan Kami</h2>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($services as $service)
            <div class="card-lift bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mb-5">
                    <i class="fas fa-cog text-slate-600 text-sm"></i>
                </div>
                <h3 class="font-semibold text-base text-slate-900 mb-2">{{ $service->service_name }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-5">{{ $service->description ?: 'Layanan profesional yang dilakukan oleh teknisi bersertifikat kami.' }}</p>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div>
                        <div class="font-display font-bold text-lg text-slate-900">Rp {{ number_format($service->price,0,',','.') }}</div>
                        <div class="text-slate-400 text-xs">Est. {{ $service->estimated_time }} menit</div>
                    </div>
                    <a href="{{ $bookingRoute }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-400 rounded-lg px-3 py-2 transition-colors">
                        {{ $isLoggedIn && $role === 'customer' ? 'Booking Sekarang' : 'Daftar untuk Booking' }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Why Us --}}
<section id="why" class="py-20 px-6 bg-slate-950 text-white">
    <div class="max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <p class="text-xs font-semibold tracking-widest uppercase text-slate-400 mb-4">Mengapa Speedline</p>
                <h2 class="font-display font-bold text-4xl leading-tight mb-6">
                    Standar untuk servis mobil premium.
                </h2>
                <p class="text-slate-400 leading-relaxed mb-8">
                    Kami tidak mengerjakan segalanya. Speedline berspesialisasi dalam kendaraan performa tinggi dan mewah — karena mobil Anda pantas ditangani oleh orang yang benar-benar memahaminya.
                </p>
                <a href="{{ $bookingRoute }}"
                   class="inline-flex items-center gap-2 bg-white text-slate-900 hover:bg-slate-100 font-semibold px-6 py-3.5 rounded-xl transition-colors text-sm">
                    {{ $isLoggedIn ? 'Buka Dashboard' : 'Booking Servis' }} <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="space-y-4">
                @foreach([
                    ['fas fa-microscope','Diagnostik Standar Pabrik','OBD-II, osiloskop, dan alat pemindai pabrikan untuk setiap merek.'],
                    ['fas fa-certificate','Teknisi Bersertifikat','Semua mekanik memiliki sertifikasi pabrikan dan menjalani pelatihan rutin.'],
                    ['fas fa-shield-alt','Hanya Sparepart Asli','Kami mengambil dari distributor resmi — tanpa kompromi pada kualitas.'],
                    ['fas fa-clock','Tepat Waktu','Estimasi akurat dan penyelesaian tepat waktu, setiap saat.'],
                ] as [$icon,$title,$desc])
                <div class="flex gap-4 bg-white/[0.05] border border-white/[0.08] rounded-xl p-5">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                        <i class="{{ $icon }} text-white/70 text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-sm text-white mb-1">{{ $title }}</h4>
                        <p class="text-slate-400 text-xs leading-relaxed">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-2xl mx-auto text-center">
        <p class="text-xs font-semibold tracking-widest uppercase text-slate-400 mb-4">Siap untuk Booking?</p>
        <h2 class="font-display font-bold text-4xl text-slate-900 mb-5">Mobil Anda.<br>Keahlian Kami.</h2>
        <p class="text-slate-500 mb-8">Booking slot servis Anda dalam hitungan menit. Tim kami akan mengonfirmasi dalam waktu 24 jam.</p>
        <div class="flex items-center gap-3 justify-center flex-wrap">
            <a href="{{ $bookingRoute }}"
               class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-7 py-3.5 rounded-xl transition-colors">
                {{ $isLoggedIn ? 'Dashboard Saya' : 'Daftar Sekarang' }} <i class="fas fa-arrow-right text-xs"></i>
            </a>
            @guest
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-semibold text-sm px-6 py-3.5 border border-slate-200 hover:border-slate-400 rounded-xl transition-colors">
                Masuk
            </a>
            @endguest
        </div>
    </div>
</section>

{{-- Footer --}}
<footer id="contact" class="bg-slate-950 text-white py-16 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="grid md:grid-cols-3 gap-12 mb-12">
            <div>
                <img src="{{ asset('images/logo_white.png') }}" alt="Speedline" class="h-8 w-auto object-contain mb-6 opacity-90">
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">Perawatan otomotif premium untuk kendaraan performa tinggi dan mewah di Jakarta. Layanan presisi sejak 2008.</p>
            </div>
            <div>
                <h4 class="font-display font-bold text-sm uppercase tracking-widest text-white mb-6">Tautan Cepat</h4>
                <div class="space-y-3">
                    <a href="#services" class="block text-slate-400 text-sm hover:text-white transition-colors">Layanan Kami</a>
                    <a href="#why" class="block text-slate-400 text-sm hover:text-white transition-colors">Tentang Kami</a>
                    @guest
                    <a href="{{ route('login') }}" class="block text-slate-400 text-sm hover:text-white transition-colors">Masuk</a>
                    <a href="{{ route('register') }}" class="block text-slate-400 text-sm hover:text-white transition-colors">Daftar</a>
                    @else
                    <a href="{{ $authRoute }}" class="block text-slate-400 text-sm hover:text-white transition-colors">Dashboard Saya</a>
                    <a href="{{ $bookingRoute }}" class="block text-slate-400 text-sm hover:text-white transition-colors">{{ $role === 'customer' ? 'Booking Saya' : 'Kelola' }}</a>
                    @endguest
                </div>
            </div>
            <div>
                <h4 class="font-display font-bold text-sm uppercase tracking-widest text-white mb-6">Hubungi Kami</h4>
                <div class="space-y-3 text-sm text-slate-400">
                    <p class="flex items-start gap-3"><i class="fas fa-map-marker-alt mt-1 text-slate-500"></i>Jl. Raya Otomotif No. 88, Jakarta</p>
                    <p class="flex items-center gap-3"><i class="fas fa-phone text-slate-500"></i>+62 21 8888 0000</p>
                    <p class="flex items-center gap-3"><i class="fas fa-envelope text-slate-500"></i>service@speedline.id</p>
                    <p class="flex items-center gap-3"><i class="fas fa-clock text-slate-500"></i>Buka Setiap Hari · {{ \App\Enums\BookingConfig::formatRange() }}</p>
                </div>
            </div>
        </div>
        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <span>&copy; {{ date('Y') }} Speedline Automotive. Hak cipta dilindungi undang-undang.</span>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                <a href="#" class="hover:text-white transition-colors">Syarat Layanan</a>
            </div>
        </div>
    </div>
</body>
</html>