@extends('layouts.app')

@section('title', 'Data Balita')
@section('page-title', 'Manajemen Data Balita (Anak)')

@section('content')

<div x-data="{
    searchQuery: '{{ $search ?? '' }}',
    isLoading: false,
    isSearching: false,
    searchResults: [],
    debounceTimer: null,

    onInput() {
        const query = this.searchQuery.trim();

        if (query.length < 3) {
            this.isSearching = false;
            this.searchResults = [];
            this.isLoading = false;
            if (this.debounceTimer) clearTimeout(this.debounceTimer);
            return;
        }

        this.isLoading = true;
        if (this.debounceTimer) clearTimeout(this.debounceTimer);

        this.debounceTimer = setTimeout(() => {
            this.performSearch(query);
        }, 300);
    },

    async performSearch(query) {
        try {
            const response = await fetch('{{ route('balita.search') }}?q=' + encodeURIComponent(query), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            if (data.success) {
                this.searchResults = data.data;
                this.isSearching = true;
            }
        } catch (err) {
            console.error('Error searching balita:', err);
        } finally {
            this.isLoading = false;
        }
    }
}"
class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-5">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Search Box (AJAX Search with Debounce & Loading Spinner) -->
        <div class="w-full sm:w-80">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg x-show="!isLoading" class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <svg x-show="isLoading" x-cloak class="w-4 h-4 animate-spin text-emerald-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <input type="text" 
                       x-model="searchQuery" 
                       @input="onInput()"
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-700 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 rounded-2xl text-white text-xs placeholder-slate-500 shadow-inner"
                       placeholder="Cari nama, NIK, token, ortu... (Min. 3 Karakter)">
            </div>
        </div>

        <!-- Create Button -->
        <a href="{{ route('balita.create') }}" class="py-2.5 px-4 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-2xl shadow-md active:scale-95 transition-all text-xs flex items-center justify-center gap-2 min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Balita Baru</span>
        </a>
    </div>

    <!-- 1. DEFAULT LIST VIEW (When search length < 3 or search not active) -->
    <div x-show="!isSearching">
        @if($anaks->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 rounded-l-xl">Nama Balita</th>
                            <th class="py-3 px-4">Posyandu</th>
                            <th class="py-3 px-4">Tgl Lahir / Usia</th>
                            <th class="py-3 px-4">J.K / BBLR</th>
                            <th class="py-3 px-4">Orang Tua</th>
                            <th class="py-3 px-4">Token Ortu</th>
                            <th class="py-3 px-4 text-right rounded-r-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-200">
                        @foreach($anaks as $anak)
                            <tr class="balita-row hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-white">
                                    <a href="{{ route('balita.show', $anak->id) }}" class="hover:text-emerald-400">
                                        {{ $anak->nama }}
                                    </a>
                                    @if($anak->nik)
                                        <span class="block text-[11px] text-slate-400 font-normal">NIK: {{ $anak->nik }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-300">{{ $anak->posyandu->nama ?? '-' }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="block font-medium">{{ $anak->tanggal_lahir ? $anak->tanggal_lahir->translatedFormat('d M Y') : '-' }}</span>
                                    <span class="text-emerald-400 font-bold text-[11px]">{{ $anak->usia_bulan }} Bulan</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $anak->jenis_kelamin === 'L' ? 'bg-sky-500/20 text-sky-300' : 'bg-pink-500/20 text-pink-300' }}">
                                        {{ $anak->jenis_kelamin }}
                                    </span>
                                    @if($anak->status_bblr === 'bblr')
                                        <span class="block text-rose-400 text-[10px] font-semibold mt-1">BBLR (&lt;2500g)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-300">{{ $anak->nama_orang_tua ?? '-' }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-xl">
                                        <span class="font-mono text-emerald-400 text-[11px] font-bold">{{ $anak->token_akses }}</span>
                                        <button type="button" onclick="copyToClipboard('{{ $anak->token_akses }}', this)" title="Salin Token Akses" class="text-slate-400 hover:text-emerald-400 transition-colors p-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('pengukuran.create', ['anak_id' => $anak->id]) }}" title="Input Ukur" class="p-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('balita.edit', $anak->id) }}" title="Edit" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List View -->
            <div class="md:hidden space-y-3">
                @foreach($anaks as $anak)
                    <div class="balita-card bg-slate-950/80 p-4 rounded-2xl border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <a href="{{ route('balita.show', $anak->id) }}" class="font-bold text-white text-sm hover:text-emerald-400">
                                    {{ $anak->nama }}
                                </a>
                                <span class="block text-[11px] text-slate-400">Usia: <strong class="text-emerald-400">{{ $anak->usia_bulan }} bln</strong></span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-900 border border-slate-700 rounded-xl">
                                <span class="font-mono text-emerald-400 text-[11px] font-bold">{{ $anak->token_akses }}</span>
                                <button type="button" onclick="copyToClipboard('{{ $anak->token_akses }}', this)" title="Salin Token Akses" class="text-slate-400 hover:text-emerald-400 transition-colors p-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-800">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Posyandu:</span>
                                <span class="font-semibold text-slate-200">{{ $anak->posyandu->nama ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Orang Tua:</span>
                                <span class="font-semibold text-slate-200">{{ $anak->nama_orang_tua ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                            <a href="{{ route('pengukuran.create', ['anak_id' => $anak->id]) }}" class="px-3 py-1.5 bg-emerald-500/20 text-emerald-300 font-extrabold rounded-xl text-xs flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Input Ukur</span>
                            </a>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('balita.edit', $anak->id) }}" class="p-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-semibold">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-4 border-t border-slate-800">
                {{ $anaks->links() }}
            </div>
        @else
            <div class="text-center py-12 text-slate-400 text-xs">
                Belum ada data balita yang ditemukan.
            </div>
        @endif
    </div>

    <!-- 2. DYNAMIC AJAX SEARCH RESULTS VIEW -->
    <div x-show="isSearching" x-cloak>
        <template x-if="searchResults.length > 0">
            <div>
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4 rounded-l-xl">Nama Balita</th>
                                <th class="py-3 px-4">Posyandu</th>
                                <th class="py-3 px-4">Tgl Lahir / Usia</th>
                                <th class="py-3 px-4">J.K / BBLR</th>
                                <th class="py-3 px-4">Orang Tua</th>
                                <th class="py-3 px-4">Token Ortu</th>
                                <th class="py-3 px-4 text-right rounded-r-xl">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-200">
                            <template x-for="item in searchResults" :key="item.id">
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-white">
                                        <a :href="item.show_url" class="hover:text-emerald-400" x-text="item.nama"></a>
                                        <template x-if="item.nik">
                                            <span class="block text-[11px] text-slate-400 font-normal" x-text="'NIK: ' + item.nik"></span>
                                        </template>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-300" x-text="item.posyandu_nama"></td>
                                    <td class="py-3.5 px-4">
                                        <span class="block font-medium" x-text="item.tanggal_lahir_formatted"></span>
                                        <span class="text-emerald-400 font-bold text-[11px]" x-text="item.usia_bulan + ' Bulan'"></span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                              :class="item.jenis_kelamin === 'L' ? 'bg-sky-500/20 text-sky-300' : 'bg-pink-500/20 text-pink-300'"
                                              x-text="item.jenis_kelamin">
                                        </span>
                                        <template x-if="item.status_bblr === 'bblr'">
                                            <span class="block text-rose-400 text-[10px] font-semibold mt-1">BBLR (&lt;2500g)</span>
                                        </template>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-300" x-text="item.nama_orang_tua || '-'"></td>
                                    <td class="py-3.5 px-4">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-xl">
                                            <span class="font-mono text-emerald-400 text-[11px] font-bold" x-text="item.token_akses"></span>
                                            <button type="button" @click="copyToClipboard(item.token_akses, $el)" title="Salin Token Akses" class="text-slate-400 hover:text-emerald-400 transition-colors p-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a :href="item.create_pengukuran_url" title="Input Ukur" class="p-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 rounded-xl transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </a>
                                            <a :href="item.edit_url" title="Edit" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View -->
                <div class="md:hidden space-y-3">
                    <template x-for="item in searchResults" :key="item.id">
                        <div class="bg-slate-950/80 p-4 rounded-2xl border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <a :href="item.show_url" class="font-bold text-white text-sm hover:text-emerald-400" x-text="item.nama"></a>
                                    <span class="block text-[11px] text-slate-400">Usia: <strong class="text-emerald-400" x-text="item.usia_bulan + ' bln'"></strong></span>
                                </div>
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-900 border border-slate-700 rounded-xl">
                                    <span class="font-mono text-emerald-400 text-[11px] font-bold" x-text="item.token_akses"></span>
                                    <button type="button" @click="copyToClipboard(item.token_akses, $el)" title="Salin Token Akses" class="text-slate-400 hover:text-emerald-400 transition-colors p-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-800">
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Posyandu:</span>
                                    <span class="font-semibold text-slate-200" x-text="item.posyandu_nama"></span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Orang Tua:</span>
                                    <span class="font-semibold text-slate-200" x-text="item.nama_orang_tua || '-'"></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                                <a :href="item.create_pengukuran_url" class="px-3 py-1.5 bg-emerald-500/20 text-emerald-300 font-extrabold rounded-xl text-xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Input Ukur</span>
                                </a>
                                <div class="flex items-center gap-1">
                                    <a :href="item.edit_url" class="p-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-semibold">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Empty Search State -->
        <template x-if="searchResults.length === 0 && !isLoading">
            <div class="text-center py-12 bg-slate-950/60 rounded-2xl border border-slate-800 text-slate-400 text-xs space-y-2">
                <svg class="w-8 h-8 mx-auto text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="font-semibold text-white">Data balita tidak ditemukan</p>
                <p class="text-[11px] text-slate-500">Tidak ada balita yang cocok dengan kata kunci "<span x-text="searchQuery" class="text-emerald-400 font-bold"></span>".</p>
            </div>
        </template>
    </div>
</div>

@endsection
