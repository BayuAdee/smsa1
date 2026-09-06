<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Perkembangan - {{ $anak->nama }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-12 selection:bg-emerald-500 selection:text-slate-950">

    <!-- Simple Topbar for Ortu View -->
    <header class="w-full border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-300 p-0.5 shadow-md shadow-emerald-500/20">
                    <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <span class="block text-base font-extrabold text-white tracking-tight">SiStunting <span class="text-emerald-400">SAW</span></span>
                    <span class="block text-[10px] text-slate-400">Laporan Tumbuh Kembang</span>
                </div>
            </a>

            <a href="/" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition-all">
                Cek Token Lain
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 pt-6 space-y-6">

        <!-- Child Profile Card -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-900/90 border border-slate-800 rounded-3xl p-5 sm:p-7 shadow-xl relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-2xl shadow-lg shadow-emerald-500/20">
                        {{ strtoupper(substr($anak->nama, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $anak->nama }}</h1>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $anak->jenis_kelamin === 'L' ? 'bg-sky-500/20 text-sky-300 border border-sky-500/30' : 'bg-pink-500/20 text-pink-300 border border-pink-500/30' }}">
                                {{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1">
                            Orang Tua: <span class="text-slate-200 font-medium">{{ $anak->nama_orang_tua ?? '-' }}</span> | 
                            Posyandu: <span class="text-emerald-400 font-semibold">{{ $anak->posyandu->nama ?? '-' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Token Code Badge -->
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl px-4 py-2.5 text-left sm:text-right">
                    <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Token Akses Anak</span>
                    <div class="inline-flex items-center gap-1.5 mt-0.5">
                        <span class="font-mono text-xs sm:text-sm font-bold text-emerald-400 tracking-wider">{{ $anak->token_akses }}</span>
                        <button type="button" onclick="copyToClipboard('{{ $anak->token_akses }}', this)" title="Salin Token Akses" class="text-slate-400 hover:text-emerald-400 transition-colors p-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Key Info Pills -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-800/80 text-xs">
                <div class="bg-slate-950/40 p-3 rounded-2xl border border-slate-800/60">
                    <span class="block text-slate-400 text-[11px]">Tanggal Lahir</span>
                    <span class="font-bold text-white mt-0.5 block">{{ $anak->tanggal_lahir->translatedFormat('d M Y') }}</span>
                </div>
                <div class="bg-slate-950/40 p-3 rounded-2xl border border-slate-800/60">
                    <span class="block text-slate-400 text-[11px]">Usia Saat Ini</span>
                    <span class="font-bold text-emerald-300 mt-0.5 block">{{ $anak->usia_bulan }} Bulan</span>
                </div>
                <div class="bg-slate-950/40 p-3 rounded-2xl border border-slate-800/60">
                    <span class="block text-slate-400 text-[11px]">Berat Lahir</span>
                    <span class="font-bold text-white mt-0.5 block">{{ number_format($anak->berat_lahir_gram) }} gram</span>
                </div>
                <div class="bg-slate-950/40 p-3 rounded-2xl border border-slate-800/60">
                    <span class="block text-slate-400 text-[11px]">Riwayat BBLR</span>
                    <span class="font-bold text-white mt-0.5 block">
                        @if($anak->status_bblr === 'bblr')
                            <span class="text-rose-400">Ya (&lt;2500g)</span>
                        @elseif($anak->status_bblr === 'tidak')
                            <span class="text-emerald-400">Tidak (Normal)</span>
                        @else
                            <span class="text-slate-400">Tidak Diketahui</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Layman Status & Advice Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl relative">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 {{ $laymanStatus['badge_color'] }} border">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold border mb-2 {{ $laymanStatus['badge_color'] }}">
                        {{ $laymanStatus['badge_text'] ?? 'Status Tumbuh Kembang' }}
                    </span>
                    <h2 class="text-lg font-extrabold text-white mb-1.5">{{ $laymanStatus['title'] }}</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">{{ $laymanStatus['description'] }}</p>
                </div>
            </div>

            <!-- Tips for Parents -->
            <div class="mt-5 pt-4 border-t border-slate-800/80">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Saran & Anjuran Gizi Kesehatan:
                </h3>
                <ul class="space-y-2">
                    @foreach($laymanStatus['tips'] as $tip)
                        <li class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-2 flex-shrink-0"></span>
                            <span>{{ $tip }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Growth Charts Section -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-6">
            <div>
                <h3 class="text-base font-extrabold text-white">Grafik Perkembangan Pertumbuhan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Riwayat pengukuran tinggi badan (cm) dan berat badan (kg) di Posyandu</p>
            </div>

            @if($pengukurans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Chart Tinggi Badan -->
                    <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                        <h4 class="text-xs font-bold text-emerald-400 mb-3 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            Tinggi Badan (cm)
                        </h4>
                        <div class="relative h-56">
                            <canvas id="chartTinggi"></canvas>
                        </div>
                    </div>

                    <!-- Chart Berat Badan -->
                    <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800">
                        <h4 class="text-xs font-bold text-teal-400 mb-3 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span>
                            Berat Badan (kg)
                        </h4>
                        <div class="relative h-56">
                            <canvas id="chartBerat"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Measurement History Table (Mobile Responsive Cards/Table) -->
                <div class="pt-4 border-t border-slate-800">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Riwayat Catatan Posyandu</h4>
                    
                    <!-- Table Desktop -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-3 rounded-l-xl">Tanggal Ukur</th>
                                    <th class="py-2.5 px-3">Usia (Bulan)</th>
                                    <th class="py-2.5 px-3">Tinggi (cm)</th>
                                    <th class="py-2.5 px-3">Berat (kg)</th>
                                    <th class="py-2.5 px-3 rounded-r-xl">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 text-slate-200">
                                @foreach($pengukurans as $p)
                                    <tr>
                                        <td class="py-3 px-3 font-medium">{{ \Carbon\Carbon::parse($p->tanggal_ukur)->translatedFormat('d M Y') }}</td>
                                        <td class="py-3 px-3">{{ $p->usia_bulan }} bln</td>
                                        <td class="py-3 px-3 font-bold text-emerald-400">{{ number_format($p->tinggi_cm, 1) }} cm</td>
                                        <td class="py-3 px-3 font-bold text-teal-400">{{ number_format($p->berat_kg, 1) }} kg</td>
                                        <td class="py-3 px-3 text-slate-400">Pemeriksaan rutin</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Cards Mobile -->
                    <div class="sm:hidden space-y-2.5">
                        @foreach($pengukurans as $p)
                            <div class="bg-slate-950/70 p-3.5 rounded-2xl border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <span class="block font-bold text-white">{{ \Carbon\Carbon::parse($p->tanggal_ukur)->translatedFormat('d M Y') }}</span>
                                    <span class="text-[11px] text-slate-400">Usia: {{ $p->usia_bulan }} bulan</span>
                                </div>
                                <div class="text-right">
                                    <span class="block font-bold text-emerald-400">TB: {{ number_format($p->tinggi_cm, 1) }} cm</span>
                                    <span class="block font-bold text-teal-400">BB: {{ number_format($p->berat_kg, 1) }} kg</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-slate-400 text-xs">
                    Belum ada data riwayat pengukuran untuk balita ini.
                </div>
            @endif
        </div>
    </main>

    @if($pengukurans->count() > 0)
    <script>
        const labels = {!! json_encode($pengukurans->map(fn($p) => \Carbon\Carbon::parse($p->tanggal_ukur)->translatedFormat('M Y'))->toArray()) !!};
        const dataTinggi = {!! json_encode($pengukurans->pluck('tinggi_cm')->toArray()) !!};
        const dataBerat = {!! json_encode($pengukurans->pluck('berat_kg')->toArray()) !!};

        // Chart Tinggi
        new Chart(document.getElementById('chartTinggi'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tinggi (cm)',
                    data: dataTinggi,
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52, 211, 153, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 3,
                    pointBackgroundColor: '#34d399',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 } } }
                }
            }
        });

        // Chart Berat
        new Chart(document.getElementById('chartBerat'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Berat (kg)',
                    data: dataBerat,
                    borderColor: '#2dd4bf',
                    backgroundColor: 'rgba(45, 212, 191, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 3,
                    pointBackgroundColor: '#2dd4bf',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 } } }
                }
            }
        });
    </script>
    @endif
    <script>
    window.copyToClipboard = function(text, btn) {
        const doFeedback = () => {
            const btnEl = btn || event?.target;
            let svg = btnEl ? btnEl.querySelector('svg') : null;
            if (!svg && btnEl && btnEl.tagName === 'svg') svg = btnEl;

            if (svg) {
                svg.outerHTML = `<svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
            }

            let toast = document.getElementById('global-copy-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'global-copy-toast';
                toast.className = 'fixed bottom-5 right-5 z-50 bg-emerald-500 text-slate-950 px-4 py-2.5 rounded-2xl font-black text-xs shadow-2xl flex items-center gap-2 transition-all duration-300 transform translate-y-0 opacity-100 border border-emerald-400';
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<svg class="w-4 h-4 text-slate-950 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg> <span>Token "${text}" berhasil disalin!</span>`;
            toast.style.display = 'flex';
            toast.style.opacity = '1';

            setTimeout(() => {
                if (btnEl) {
                    btnEl.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>`;
                }
                if (toast) {
                    toast.style.opacity = '0';
                    setTimeout(() => { toast.style.display = 'none'; }, 300);
                }
            }, 1800);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(doFeedback).catch(() => {
                fallbackCopy(text);
                doFeedback();
            });
        } else {
            fallbackCopy(text);
            doFeedback();
        }

        function fallbackCopy(str) {
            const el = document.createElement('textarea');
            el.value = str;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        }
    };
    </script>
</body>
</html>
