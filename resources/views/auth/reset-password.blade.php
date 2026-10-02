<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SiStunting Bidan Desa</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <!-- Script Anti-FOUC -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Alpine.js untuk Toggle Tema & Password -->
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
          isDark: localStorage.getItem('theme') === 'dark',
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

    <!-- Floating Toggle Theme Button -->
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
                    <span class="block text-xs font-medium text-emerald-700 dark:text-emerald-200/70">Pemulihan Akun Bidan Desa</span>
                </div>
            </a>
        </div>

        <!-- Glassmorphism Card -->
        <div class="bg-white/90 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-300/40 dark:shadow-black/50">

            @if(empty($valid))
                <!-- State Error: Token Invalid / Kedaluwarsa -->
                <div class="text-center py-4">
                    <div class="w-16 h-16 rounded-3xl bg-rose-500/10 text-rose-500 dark:text-rose-400 flex items-center justify-center mx-auto mb-4 border border-rose-500/20">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Tautan Tidak Valid</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                        {{ $error ?? 'Link reset password sudah kedaluwarsa (lebih dari 30 menit) atau telah digunakan sebelumnya.' }}
                    </p>

                    <a href="{{ route('password.request') }}" class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-2xl shadow-lg shadow-emerald-500/25 active:scale-[0.98] transition-all duration-200 inline-flex items-center justify-center gap-2">
                        <span>Minta Tautan Baru</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </a>
                </div>
            @else
                <!-- State Form: Token Valid -->
                <div class="mb-6">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-xs font-semibold mb-3">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Verifikasi Berhasil
                    </div>
                    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Buat Password Baru</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Masukkan kata sandi baru untuk akun Bidan Anda.</p>
                </div>

                @if($errors->any())
                    <div class="mb-5 p-4 bg-rose-500/10 dark:bg-rose-500/20 border border-rose-500/30 rounded-2xl text-rose-800 dark:text-rose-200 text-sm">
                        <div class="font-semibold mb-1">Periksa Input Anda:</div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST" class="space-y-5"
                      x-data="{ showPass: false, showConfirm: false }">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    @if(!empty($email))
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Akun Email</label>
                            <input type="text" value="{{ $email }}" disabled
                                class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-500 dark:text-slate-400 text-sm cursor-not-allowed">
                        </div>
                    @endif

                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Kata Sandi Baru</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" id="password" name="password" required autofocus
                                class="w-full pl-11 pr-11 py-3.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 rounded-2xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all duration-200"
                                placeholder="Minimal 8 karakter">
                            <button type="button" @click="showPass = !showPass"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 focus:outline-none transition-colors"
                                tabindex="-1"
                                title="Tampilkan / Sembunyikan Kata Sandi">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.02 10.02 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Ulangi Kata Sandi Baru</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input :type="showConfirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required
                                class="w-full pl-11 pr-11 py-3.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 rounded-2xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm transition-all duration-200"
                                placeholder="Ketik ulang kata sandi">
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 focus:outline-none transition-colors"
                                tabindex="-1"
                                title="Tampilkan / Sembunyikan Konfirmasi Kata Sandi">
                                <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.02 10.02 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-2xl shadow-lg shadow-emerald-500/25 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 min-h-[48px]">
                        <span>Simpan Password Baru</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </form>
            @endif

            <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800 text-center">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Halaman Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
