<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - SiStunting SAW Posyandu</title>

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
<body class="bg-gradient-to-br from-emerald-50 via-teal-50 to-slate-100 dark:from-emerald-950 dark:via-teal-950 dark:to-slate-950 min-h-screen flex items-center justify-center p-4 transition-colors duration-200 relative"
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

    <!-- Floating Toggle Theme Button (Pojok Kanan Atas) -->
    <div class="absolute top-4 right-4 z-50">
        <button @click="toggleTheme()"
                type="button"
                class="p-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:text-emerald-500 dark:hover:text-emerald-400 backdrop-blur-md shadow-md transition-all min-h-[44px] min-w-[44px] flex items-center justify-center"
                title="Ganti Tema">
            <svg x-show="isDark" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg x-show="!isDark" x-cloak class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
    </div>

    <div class="w-full max-w-md my-8">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-400 to-teal-300 p-0.5 shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-300">
                    <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[14px] flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-left">
                    <span class="block text-xl font-bold text-slate-900 dark:text-white tracking-tight">SiStunting <span class="text-emerald-500 dark:text-emerald-400">SAW</span></span>
                    <span class="block text-xs font-medium text-emerald-700 dark:text-emerald-200/70">Sistem Petugas Posyandu</span>
                </div>
            </a>
        </div>

        <!-- Glassmorphism Card -->
        <div class="bg-white/90 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-300/40 dark:shadow-black/50">
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Login Petugas</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Masuk sebagai Kader Posyandu atau Bidan Desa</p>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3.5 bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 rounded-2xl text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3.5 bg-rose-500/10 dark:bg-rose-500/20 border border-rose-500/30 rounded-2xl text-rose-800 dark:text-rose-200 text-sm">
                    <div class="font-semibold mb-1">Gagal Masuk:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Username / Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 rounded-2xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all duration-200"
                            placeholder="masukan username atau email">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 rounded-2xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all duration-200"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-emerald-500 focus:ring-emerald-400 bg-slate-50 dark:bg-slate-950/80">
                        <span class="ml-2 text-xs text-slate-600 dark:text-slate-300">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-2xl shadow-lg shadow-emerald-500/25 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 min-h-[48px]">
                    <span>Masuk ke Dashboard</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
            <a href="/" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors inline-flex items-center gap-1 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Halaman Depan Ortu
            </a>
        </div>
    </div>
</body>
</html>
