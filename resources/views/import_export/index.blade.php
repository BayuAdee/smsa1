@extends('layouts.app')

@section('title', 'Import & Export Data')
@section('page-title', '📥 Import & Export Data')

@section('content')
<div x-data="{ activeTab: '{{ $activeTab }}' }" class="space-y-6">

    <!-- Header Summary Card -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 relative overflow-hidden shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-400 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Modul Management Data Massal
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Import & Export Data System</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl">
                    @if(Auth::user()->isBidan())
                        Fasilitas unggah massal data balita via CSV/Excel serta pengunduhan laporan berkala untuk seluruh Posyandu wilayah kerja.
                    @else
                        Fasilitas pengunduhan laporan berkala data balita & pengukuran untuk {{ Auth::user()->posyandu->nama ?? 'Posyandu Anda' }}.
                    @endif
                </p>
            </div>

            <!-- Role Badge -->
            <div class="shrink-0">
                <div class="px-4 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-700/80 text-right">
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Akses Sebagai</div>
                    <div class="text-sm font-extrabold {{ Auth::user()->isBidan() ? 'text-emerald-400' : 'text-teal-400' }}">
                        {{ Auth::user()->isBidan() ? 'Bidan Desa (Full Access)' : 'Kader Posyandu (Export Only)' }}
                    </div>
                </div>
            </div>
        </div>

        @if(Auth::user()->isBidan())
        <!-- Navigation Tabs (Bidan Only) -->
        <div class="flex items-center gap-2 mt-6 border-t border-slate-800/80 pt-6">
            <button @click="activeTab = 'import'"
                    :class="activeTab === 'import' ? 'bg-emerald-500 text-slate-950 shadow-lg shadow-emerald-500/20 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 font-bold'"
                    class="px-5 py-2.5 rounded-2xl text-xs transition-all flex items-center gap-2 min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Import Data Balita</span>
            </button>

            <button @click="activeTab = 'export'"
                    :class="activeTab === 'export' ? 'bg-emerald-500 text-slate-950 shadow-lg shadow-emerald-500/20 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 font-bold'"
                    class="px-5 py-2.5 rounded-2xl text-xs transition-all flex items-center gap-2 min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Export Laporan Posyandu</span>
            </button>
        </div>
        @endif
    </div>

    @if(Auth::user()->isBidan())
    <!-- TAB 1: IMPORT DATA BALITA (BIDAN ONLY) -->
    <div x-show="activeTab === 'import'" x-cloak class="space-y-6">

        <!-- Card Form Upload & Action Header -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800/80">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        Langkah 1: Unduh Template & Unggah File CSV
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Unduh format template CSV acuan, isi data balita, lalu unggah untuk pratinjau validasi 2-step.</p>
                </div>

                <!-- Download Template Button -->
                <a href="{{ route('import-export.template') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-slate-700 text-xs font-bold transition-all min-h-[44px] shrink-0 shadow-md">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Download Template (.csv)</span>
                </a>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('import-export.preview') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Pilih File CSV Data Balita</label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <input type="file" name="file_import" accept=".csv, .txt, .xlsx, .xls" required
                               class="block w-full text-xs text-slate-400 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-500/10 file:text-emerald-300 hover:file:bg-emerald-500/20 bg-slate-950 border border-slate-800 rounded-2xl p-2 cursor-pointer focus:outline-none focus:border-emerald-500">
                        <button type="submit" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs rounded-2xl transition-all shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 shrink-0 min-h-[44px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span>Unggah & Validasi File</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">Mendukung format file .csv dengan delimiter koma (,) atau titik koma (;). Ukuran maksimal 2MB.</p>
                </div>
            </form>
        </div>

        <!-- Card Preview Validasi 2-Step -->
        @if($importPreview)
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-teal-500/10 text-teal-400 border border-teal-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </span>
                        Langkah 2: Pratinjau Validasi & Konfirmasi Import
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Sistem menyaring baris data. Baris valid ditandai hijau dan baris bermasalah ditandai merah.</p>
                </div>

                <!-- Execute Button Top -->
                @if($importPreview['valid_count'] > 0)
                <form action="{{ route('import-export.execute') }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memproses {{ $importPreview['valid_count'] }} data balita yang valid ke database?')"
                            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2 min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Eksekusi Import ({{ $importPreview['valid_count'] }} Baris Valid)</span>
                    </button>
                </form>
                @endif
            </div>

            <!-- Summary Chips -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800">
                    <span class="block text-xs font-semibold text-slate-400">Total Baris File</span>
                    <span class="text-xl font-extrabold text-white mt-1 block">{{ $importPreview['total'] }} Baris</span>
                </div>
                <div class="bg-emerald-500/10 p-4 rounded-2xl border border-emerald-500/30">
                    <span class="block text-xs font-semibold text-emerald-400">Siap Diimpor (Valid)</span>
                    <span class="text-xl font-extrabold text-emerald-300 mt-1 block">{{ $importPreview['valid_count'] }} Baris</span>
                </div>
                <div class="{{ $importPreview['error_count'] > 0 ? 'bg-rose-500/10 border-rose-500/30' : 'bg-slate-950 border-slate-800' }} p-4 rounded-2xl border">
                    <span class="block text-xs font-semibold {{ $importPreview['error_count'] > 0 ? 'text-rose-400' : 'text-slate-400' }}">Bermasalah (Dilewati)</span>
                    <span class="text-xl font-extrabold {{ $importPreview['error_count'] > 0 ? 'text-rose-300' : 'text-slate-400' }} mt-1 block">{{ $importPreview['error_count'] }} Baris</span>
                </div>
            </div>

            <!-- Table Preview -->
            <div class="overflow-x-auto rounded-2xl border border-slate-800">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider font-extrabold border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3.5">Baris</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Posyandu</th>
                            <th class="px-4 py-3.5">Nama Balita</th>
                            <th class="px-4 py-3.5">NIK</th>
                            <th class="px-4 py-3.5">Tgl Lahir</th>
                            <th class="px-4 py-3.5">JK</th>
                            <th class="px-4 py-3.5">BB Lahir</th>
                            <th class="px-4 py-3.5">BBLR</th>
                            <th class="px-4 py-3.5">Nama Ortu</th>
                            <th class="px-4 py-3.5">Catatan / Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @foreach($importPreview['rows'] as $row)
                        <tr class="{{ $row['is_valid'] ? 'bg-emerald-950/20 hover:bg-emerald-950/30' : 'bg-rose-950/20 hover:bg-rose-950/30' }} transition-colors">
                            <td class="px-4 py-3.5 font-bold text-slate-400">#{{ $row['row_number'] }}</td>
                            <td class="px-4 py-3.5">
                                @if($row['is_valid'])
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-extrabold text-[10px] inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Valid
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-rose-500/20 border border-rose-500/40 text-rose-300 font-extrabold text-[10px] inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Error
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-white">{{ $row['data']['posyandu_nama'] ?? $row['data']['posyandu_input'] ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-bold text-white">{{ $row['data']['nama'] ?: '-' }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-400">{{ $row['data']['nik'] ?: '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-300">{{ $row['data']['tanggal_lahir'] ?: '-' }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-300">{{ $row['data']['jenis_kelamin'] ?: '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-300">{{ $row['data']['berat_lahir_gram'] ? number_format($row['data']['berat_lahir_gram']).' g' : '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-300 font-semibold uppercase">{{ $row['data']['status_bblr'] ?: '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-300">{{ $row['data']['nama_orang_tua'] ?: '-' }}</td>
                            <td class="px-4 py-3.5 max-w-xs">
                                @if($row['is_valid'])
                                    <span class="text-emerald-400 text-[11px] font-semibold">Siap diimpor</span>
                                @else
                                    <ul class="list-disc list-inside text-rose-300 text-[11px] space-y-0.5 font-medium">
                                        @foreach($row['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Bottom Execute Button -->
            @if($importPreview['valid_count'] > 0)
            <div class="pt-4 flex justify-end">
                <form action="{{ route('import-export.execute') }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memproses {{ $importPreview['valid_count'] }} data balita yang valid ke database?')"
                            class="px-8 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-sm rounded-2xl shadow-xl shadow-emerald-500/20 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Eksekusi Import ({{ $importPreview['valid_count'] }} Data Valid)</span>
                    </button>
                </form>
            </div>
            @endif
        </div>
        @endif

    </div>
    @endif

    <!-- TAB 2 / SECTION: EXPORT DATA LAPORAN (BIDAN & KADER) -->
    <div x-show="activeTab === 'export'" x-cloak class="space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
            <div class="pb-6 border-b border-slate-800/80">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    Form Filter Export Laporan
                </h3>
                <p class="text-xs text-slate-400 mt-1">Pilih kriteria wilayah, periode, jenis laporan, dan format output yang diinginkan.</p>
            </div>

            <form action="{{ route('import-export.export') }}" method="GET" target="_blank" class="mt-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- Filter 1: Scope Posyandu -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Scope Posyandu</label>
                        @if(Auth::user()->isBidan())
                            <select name="posyandu_id" class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 text-xs font-semibold text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 min-h-[44px]">
                                <option value="all">Semua Posyandu (Gabungan)</option>
                                @foreach($posyandus as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->nama }}</option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-slate-500 mt-1.5 block">Bidan dapat memilih laporan gabungan atau per Posyandu.</span>
                        @else
                            <!-- Locked for Kader -->
                            <input type="hidden" name="posyandu_id" value="{{ Auth::user()->posyandu_id }}">
                            <div class="w-full bg-slate-950/80 border border-emerald-500/30 rounded-2xl px-4 py-3 text-xs font-bold text-emerald-300 flex items-center gap-2 min-h-[44px]">
                                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>{{ Auth::user()->posyandu->nama ?? 'Posyandu Kader' }} (Terkunci)</span>
                            </div>
                            <span class="text-[10px] text-slate-500 mt-1.5 block">Kader otomatis terikat pada Posyandu wilayahnya.</span>
                        @endif
                    </div>

                    <!-- Filter 2: Periode Bulan & Tahun -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Bulan</label>
                            <select name="bulan" class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-3 py-3 text-xs font-semibold text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 min-h-[44px]">
                                @foreach(range(1, 12) as $b)
                                    <option value="{{ $b }}" {{ date('n') == $b ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Tahun</label>
                            <select name="tahun" class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-3 py-3 text-xs font-semibold text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 min-h-[44px]">
                                @foreach(range(2024, 2030) as $t)
                                    <option value="{{ $t }}" {{ date('Y') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Filter 3: Jenis Data Laporan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Jenis Data Laporan</label>
                        <select name="jenis_data" class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 text-xs font-semibold text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 min-h-[44px]">
                            <option value="profil_balita">Data Profil Balita (Anak)</option>
                            <option value="pengukuran_bulanan">Data Catatan Pengukuran Bulanan</option>
                            <option value="ranking_saw">Data Hasil Ranking Prioritas SAW</option>
                        </select>
                    </div>

                    <!-- Filter 4: Format Output -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Format Output</label>
                        <select name="format" class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 text-xs font-bold text-emerald-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 min-h-[44px]">
                            <option value="excel">Excel Spreadsheet (.xlsx / .csv)</option>
                            <option value="pdf">PDF / Cetak Dokumen Resmi (.pdf)</option>
                        </select>
                    </div>

                </div>

                <!-- Submit Action -->
                <div class="pt-4 border-t border-slate-800/80 flex items-center justify-end">
                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs rounded-2xl transition-all shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Unduh Laporan SEKARANG</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
