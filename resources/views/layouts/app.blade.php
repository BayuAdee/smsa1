<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - SiStunting SAW Posyandu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen selection:bg-emerald-500 selection:text-slate-950" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- Mobile Sidebar Backdrop overlay -->
        <div x-show="sidebarOpen" 
             x-cloak 
             @click="sidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar Container (Drawer on Mobile, Fixed/Pinned on lg Desktop) -->
        <aside class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-slate-900 border-r border-slate-800/80 flex flex-col justify-between transform transition-transform duration-300 ease-in-out lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <!-- Sidebar Header Brand -->
            <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-300 p-0.5 shadow-md shadow-emerald-500/20">
                        <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <span class="block text-lg font-extrabold text-white tracking-tight leading-none">SiStunting <span class="text-emerald-400">SAW</span></span>
                        <span class="block text-[10px] font-medium text-slate-400 mt-1">Sistem Pemantauan Balita</span>
                    </div>
                </a>

                <!-- Close Button Mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider px-3 mb-2">Menu Utama</div>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold transition-all duration-200 min-h-[44px] {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/10 text-emerald-300 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard Utama</span>
                </a>

                <a href="{{ route('pengukuran.create') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold transition-all duration-200 min-h-[44px] {{ request()->routeIs('pengukuran.create') ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/10 text-emerald-300 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('pengukuran.create') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Input Pengukuran Bulanan</span>
                </a>

                <a href="{{ route('balita.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold transition-all duration-200 min-h-[44px] {{ request()->routeIs('balita.*') ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/10 text-emerald-300 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('balita.*') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Data Balita (Anak)</span>
                </a>

                <a href="{{ route('ranking.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold transition-all duration-200 min-h-[44px] {{ request()->routeIs('ranking.*') ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/10 text-emerald-300 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('ranking.*') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Ranking Prioritas SAW</span>
                </a>

                <a href="{{ route('posyandu.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold transition-all duration-200 min-h-[44px] {{ request()->routeIs('posyandu.*') ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/10 text-emerald-300 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('posyandu.*') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0v-4a1 1 0 011-1h2a1 1 0 011 1v4m-4 0h4"/>
                    </svg>
                    <span>Data Posyandu</span>
                </a>

                @if(Auth::user()->isBidan())
                <a href="{{ route('kader.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold transition-all duration-200 min-h-[44px] {{ request()->routeIs('kader.*') ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/10 text-emerald-300 border border-emerald-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('kader.*') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Kelola Data Kader</span>
                </a>
                @endif
            </nav>

            <!-- User Info & Logout at bottom -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center font-bold text-emerald-300 text-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <span class="block text-xs font-bold text-white truncate">{{ Auth::user()->name }}</span>
                        <span class="block text-[10px] text-emerald-400 font-semibold uppercase tracking-wider">
                            {{ Auth::user()->role === 'bidan' ? 'Bidan Desa (Global)' : 'Kader Posyandu' }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 px-3 bg-slate-800 hover:bg-rose-500/20 hover:text-rose-300 hover:border-rose-500/30 border border-slate-700 rounded-xl text-xs font-bold text-slate-300 flex items-center justify-center gap-2 transition-all min-h-[44px]">
                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Topbar Header -->
            <header class="h-16 bg-slate-900/90 border-b border-slate-800/80 backdrop-blur-md px-4 sm:px-6 flex items-center justify-between z-30">
                <div class="flex items-center gap-3">
                    <!-- Hamburger Toggle Button for Mobile -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800 min-h-[44px] min-w-[44px] flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <h1 class="text-base sm:text-lg font-extrabold text-white tracking-tight truncate">
                        @yield('page-title', 'Dashboard')
                    </h1>
                </div>

                <!-- Right Header Actions (Posyandu Scope Filter for Bidan or Kader Scope Badge) -->
                <div class="flex items-center gap-3">
                    @if(Auth::user()->isBidan())
                        <!-- Bidan Posyandu Selector Dropdown -->
                        <form action="{{ route('select-posyandu') }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <label for="posyandu_id" class="hidden sm:inline text-xs font-semibold text-slate-400">Scope Posyandu:</label>
                            <select name="posyandu_id" onchange="this.form.submit()" 
                                    class="bg-slate-950 border border-slate-700 text-white text-xs rounded-xl px-3 py-2 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 min-h-[40px] font-semibold cursor-pointer">
                                <option value="all" {{ session('selected_posyandu_id', 'all') == 'all' ? 'selected' : '' }}>Semua Posyandu (Gabungan)</option>
                                @foreach(\App\Models\Posyandu::all() as $pos)
                                    <option value="{{ $pos->id }}" {{ session('selected_posyandu_id') == $pos->id ? 'selected' : '' }}>
                                        {{ $pos->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <!-- Kader Posyandu Scope Badge -->
                        <div class="px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-xs font-bold text-emerald-300 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="truncate max-w-[140px] sm:max-w-none">{{ Auth::user()->posyandu->nama ?? 'Posyandu Kader' }}</span>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Scrollable Content Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
                <!-- Session Flash Messages -->
                @if(session('success'))
                    <div class="p-4 bg-emerald-500/15 border border-emerald-500/30 rounded-2xl text-emerald-200 text-sm flex items-center justify-between gap-3 shadow-lg">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-rose-500/15 border border-rose-500/30 rounded-2xl text-rose-200 text-sm">
                        <div class="font-bold mb-1">Periksa kembali data inputan:</div>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
