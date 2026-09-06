<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Orang Tua - Pemantauan Stunting Posyandu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-emerald-500 selection:text-slate-950">

    <!-- Top Navigation Header -->
    <header class="w-full border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-18 flex items-center justify-between py-3">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-300 p-0.5 shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-300">
                    <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <span class="block text-lg font-extrabold text-white tracking-tight leading-none">SiStunting <span class="text-emerald-400">SAW</span></span>
                    <span class="block text-[11px] font-medium text-slate-400 mt-0.5">Posyandu Digital Indonesia</span>
                </div>
            </a>

            <!-- Right Action: Login Petugas -->
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 py-2 px-4 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-semibold rounded-xl text-xs sm:text-sm transition-all duration-200 active:scale-95 min-h-[44px]">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span>Login Petugas</span>
            </a>
        </div>
    </header>

    <!-- Main Hero Section -->
    <main class="flex-grow flex items-center justify-center px-4 py-12 sm:py-20 relative overflow-hidden">
        <!-- Glowing Ambient Lights background -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-2xl w-full mx-auto text-center relative z-10">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Layanan Cek Tumbuh Kembang Balita</span>
            </div>

            <!-- Title -->
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight mb-4">
                Pantau Kesehatan Si Kecil Secara <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400">Mudah & Akurat</span>
            </h1>

            <p class="text-slate-300 text-sm sm:text-base mb-8 max-w-lg mx-auto leading-relaxed">
                Masukkan Kode/Token Unik Anak dari Kader Posyandu untuk melihat riwayat pertumbuhan tinggi, berat badan, serta status gizi buah hati Anda.
            </p>

            <!-- Search Token Box -->
            <div class="bg-slate-900/80 backdrop-blur-2xl border border-slate-800 p-4 sm:p-6 rounded-3xl shadow-2xl shadow-emerald-950/40 text-left">
                @if(session('error'))
                    <div class="mb-4 p-3.5 bg-rose-500/20 border border-rose-400/30 rounded-2xl text-rose-200 text-xs sm:text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('ortu.check') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="token" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Kode Token Unik Anak</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/>
                                </svg>
                            </div>
                            <input type="text" id="token" name="token" value="{{ old('token') }}" required
                                class="w-full pl-12 pr-4 py-4 bg-slate-950/80 border border-slate-700 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20 rounded-2xl text-white font-mono text-sm tracking-wider uppercase placeholder-slate-500 transition-all"
                                placeholder="Contoh: BALITA-RAIHAN-01">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-2xl shadow-lg shadow-emerald-500/20 active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2 min-h-[48px] text-sm sm:text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Cek Perkembangan Balita</span>
                    </button>
                </form>

                <!-- Quick Demo Token Buttons -->
                <div class="mt-5 pt-4 border-t border-slate-800">
                    <p class="text-xs font-semibold text-slate-400 mb-2">Coba Token Contoh (Klik untuk langsung tes):</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('ortu.show', ['token' => 'BALITA-RAIHAN-01']) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-emerald-500/20 hover:text-emerald-300 hover:border-emerald-500/30 border border-slate-700 rounded-xl text-xs text-slate-300 font-mono transition-all">
                            BALITA-RAIHAN-01
                        </a>
                        <a href="{{ route('ortu.show', ['token' => 'BALITA-AISYAH-02']) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-emerald-500/20 hover:text-emerald-300 hover:border-emerald-500/30 border border-slate-700 rounded-xl text-xs text-slate-300 font-mono transition-all">
                            BALITA-AISYAH-02
                        </a>
                        <a href="{{ route('ortu.show', ['token' => 'BALITA-BILAL-03']) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-emerald-500/20 hover:text-emerald-300 hover:border-emerald-500/30 border border-slate-700 rounded-xl text-xs text-slate-300 font-mono transition-all">
                            BALITA-BILAL-03
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-800/80 bg-slate-900/40 py-6 text-center text-xs text-slate-500">
        <p>© 2026 Posyandu Digital - Pemantauan Stunting & SPK SAW. Seluruh Hak Cipta Dilindungi.</p>
    </footer>
</body>
</html>
