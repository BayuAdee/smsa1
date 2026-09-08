<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Orang Tua - Pemantauan Stunting Posyandu</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi CDN Tailwind agar mendukung Dark Mode berbasis Class
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <!-- Script Anti-FOUC (Pencegah Kedip saat Di-refresh) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Alpine.js untuk Toggle Tema -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex flex-col justify-between selection:bg-emerald-500 selection:text-white dark:selection:text-slate-950 transition-colors duration-200"
      x-data="{
          isDark: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
          toggleTheme() {
              this.isDark = !this.isDark;
              if (this.isDark) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('theme', 'light');
              }
          }
      }">

    <!-- Top Navigation Header -->
    <header class="w-full border-b border-slate-200 dark:border-slate-800/80 bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between py-3">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-300 p-0.5 shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-300">
                    <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[10px] flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <span class="block text-lg font-extrabold text-slate-900 dark:text-white tracking-tight leading-none">SiStunting <span class="text-emerald-500 dark:text-emerald-400">SAW</span></span>
                    <span class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1">Posyandu Digital Indonesia</span>
                </div>
            </a>

            <!-- Right Actions: Toggle Theme + Login Petugas -->
            <div class="flex items-center gap-3">
                <!-- TOMBOL TOGGLE MODE TERANG / GELAP -->
                <button @click="toggleTheme()"
                        type="button"
                        class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-emerald-500 dark:hover:text-emerald-400 transition-all min-h-[44px] min-w-[44px] flex items-center justify-center shadow-sm"
                        title="Ganti Tema">
                    <!-- Icon Matahari (muncul saat Dark Mode aktif) -->
                    <svg x-show="isDark" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Icon Bulan (muncul saat Light Mode aktif) -->
                    <svg x-show="!isDark" x-cloak class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Login Petugas Button -->
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 py-2 px-4 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-500/30 font-semibold rounded-xl text-xs sm:text-sm transition-all duration-200 active:scale-95 min-h-[44px]">
                    <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Login Petugas</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        <!-- Hero & Search Token Section -->
        <section class="px-4 py-12 sm:py-20 relative overflow-hidden">
            <!-- Glowing Ambient Lights background -->
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-10 right-10 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-3xl w-full mx-auto text-center relative z-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-300 text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                    <span>Layanan Cek Tumbuh Kembang Balita</span>
                </div>

                <!-- Title -->
                <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-4">
                    Pantau Kesehatan Si Kecil Secara <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-500 to-cyan-500 dark:from-emerald-400 dark:via-teal-300 dark:to-cyan-400">Mudah & Akurat</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mb-8 max-w-lg mx-auto leading-relaxed">
                    Gunakan token akses dari kader untuk memantau grafik tumbuh kembang dan status gizi Si Kecil secara <span class="text-emerald-600 dark:text-emerald-400 font-semibold">real-time</span>.
                </p>

                <!-- Search Token Box -->
                <div class="bg-white/90 dark:bg-slate-900/80 backdrop-blur-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-8 rounded-3xl shadow-xl shadow-slate-200/50 dark:shadow-emerald-950/40 text-left relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-bl-full pointer-events-none"></div>

                    @if(session('error'))
                        <div class="mb-4 p-3.5 bg-rose-500/10 dark:bg-rose-500/20 border border-rose-500/30 rounded-2xl text-rose-800 dark:text-rose-200 text-xs sm:text-sm flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('ortu.check') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="token" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Kode Token Unik Anak</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/>
                                    </svg>
                                </div>
                                <input type="text" id="token" name="token" value="{{ old('token') }}" required
                                    class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 rounded-2xl text-slate-900 dark:text-white font-mono text-sm tracking-wider uppercase placeholder-slate-400 dark:placeholder-slate-500 transition-all"
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
                    {{-- <div class="mt-5 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">Coba Token Contoh (Klik untuk langsung tes):</p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('ortu.show', ['token' => 'BALITA-RAIHAN-01']) }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-500/10 dark:hover:bg-emerald-500/20 hover:text-emerald-600 dark:hover:text-emerald-300 hover:border-emerald-500/30 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 font-mono transition-all">
                                BALITA-RAIHAN-01
                            </a>
                            <a href="{{ route('ortu.show', ['token' => 'BALITA-AISYAH-02']) }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-500/10 dark:hover:bg-emerald-500/20 hover:text-emerald-600 dark:hover:text-emerald-300 hover:border-emerald-500/30 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 font-mono transition-all">
                                BALITA-AISYAH-02
                            </a>
                            <a href="{{ route('ortu.show', ['token' => 'BALITA-BILAL-03']) }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-500/10 dark:hover:bg-emerald-500/20 hover:text-emerald-600 dark:hover:text-emerald-300 hover:border-emerald-500/30 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 font-mono transition-all">
                                BALITA-BILAL-03
                            </a>
                        </div>
                    </div> --}}
                </div>
            </div>
        </section>

        <!-- KOMPONEN BARU 1: 3 Langkah Mudah Bagi Orang Tua -->
        <section class="py-12 bg-slate-100/60 dark:bg-slate-900/40 border-y border-slate-200 dark:border-slate-800/80 px-4 backdrop-blur-md">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">3 Langkah Praktis Cek Kesehatan Anak</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">Tanpa perlu pendaftaran akun atau mengunduh aplikasi</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Step 1 -->
                    <div class="p-6 bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200 dark:border-slate-800 relative shadow-sm">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-sm flex items-center justify-center mb-4 border border-emerald-500/20">
                            01
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base mb-2">Minta Kode Token</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
                            Dapatkan kode/token unik anak dari Kader Posyandu atau Bidan Desa saat penimbangan bulanan.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-6 bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200 dark:border-slate-800 relative shadow-sm">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-sm flex items-center justify-center mb-4 border border-emerald-500/20">
                            02
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base mb-2">Masukkan Kode Unik</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
                            Ketikkan kode token pada kolom pencarian di atas, lalu klik tombol "Cek Perkembangan Balita".
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-6 bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200 dark:border-slate-800 relative shadow-sm">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-sm flex items-center justify-center mb-4 border border-emerald-500/20">
                            03
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base mb-2">Pantau Grafik & Gizi</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
                            Lihat grafik pertumbuhan tinggi, berat badan, serta analisis status risiko stunting secara transparan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- KOMPONEN BARU 2: Fitur & Keunggulan Portal -->
        <section class="py-16 px-4">
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature 1 -->
                    <div class="p-6 bg-white/80 dark:bg-slate-900/60 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col items-start shadow-sm">
                        <div class="p-3 bg-emerald-500/10 rounded-2xl text-emerald-600 dark:text-emerald-400 mb-4 border border-emerald-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 00-2 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-base mb-1">Akurasi Standar WHO</h4>
                        <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
                            Kalkulasi Z-Score (TB/U & BB/U) terhitung otomatis mengikuti grafik baku baku kesehatan World Health Organization.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 bg-white/80 dark:bg-slate-900/60 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col items-start shadow-sm">
                        <div class="p-3 bg-teal-500/10 rounded-2xl text-teal-600 dark:text-teal-400 mb-4 border border-teal-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-base mb-1">Aman & Privat</h4>
                        <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
                            Akses rekam medis dilindungi dengan kode token acak agar informasi kesehatan anak Anda tetap aman.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 bg-white/80 dark:bg-slate-900/60 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col items-start shadow-sm">
                        <div class="p-3 bg-cyan-500/10 rounded-2xl text-cyan-600 dark:text-cyan-400 mb-4 border border-cyan-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-base mb-1">Deteksi Dini SPK SAW</h4>
                        <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
                            Sistem Pendukung Keputusan membantu mendeteksi risiko stunting lebih awal untuk penanganan tepat sasaran.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-200 dark:border-slate-800/80 bg-slate-100/50 dark:bg-slate-900/40 py-6 text-center text-xs text-slate-500 dark:text-slate-500">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p>© 2026 Posyandu Digital — Pemantauan Stunting & SPK SAW. Seluruh Hak Cipta Dilindungi.</p>
            <p class="text-slate-500">Aplikasi Sistem Petugas & Portal Orang Tua</p>
        </div>
    </footer>
</body>
</html>
