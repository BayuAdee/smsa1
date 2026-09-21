@extends('layouts.app')

@section('title', 'Kelola Data Kader')
@section('page-title', 'Manajemen Data Petugas Kader')

@section('content')

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 space-y-6 transition-colors duration-200">

    <!-- Top Action Bar & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Daftar Petugas Kader Posyandu</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola akun dan penugasan Kader Posyandu di seluruh wilayah desa.</p>
        </div>

        <a href="{{ route('kader.create') }}" class="py-2.5 px-4 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 transition-all min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Kader Baru</span>
        </a>
    </div>

    <!-- Filters & Search Form -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 dark:bg-slate-950/60 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800/80">
        <div class="sm:col-span-2 relative">
            <input type="text" id="search_kader" oninput="handleKaderSearchInput()" value="{{ $search }}" placeholder="Cari nama, username, atau email kader... (Min. 3 Karakter)"
                   class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700/80 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl pl-9 pr-4 py-2.5 text-xs focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-1 focus:ring-emerald-500 dark:focus:ring-emerald-400 min-h-[40px] transition-all">
            <div class="pointer-events-none absolute left-3 top-3">
                <svg id="search_icon" class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <svg id="search_spinner" class="hidden w-4 h-4 animate-spin text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        <div>
            <form action="{{ route('kader.index') }}" method="GET" id="posyandu_filter_form">
                <input type="hidden" name="search" id="hidden_search_param" value="{{ $search }}">
                <select name="posyandu_id" id="posyandu_filter" onchange="handlePosyanduFilterChange()" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700/80 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 text-xs focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-1 focus:ring-emerald-500 dark:focus:ring-emerald-400 min-h-[40px] font-semibold cursor-pointer transition-all">
                    <option value="all" {{ request('posyandu_id') == 'all' || !request('posyandu_id') ? 'selected' : '' }}>Semua Unit Posyandu</option>
                    @foreach($posyandus as $pos)
                        <option value="{{ $pos->id }}" {{ request('posyandu_id') == $pos->id ? 'selected' : '' }}>
                            {{ $pos->nama }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Table List Kader -->
    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-400 text-[11px] font-extrabold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <th class="py-3.5 px-4">Nama Kader</th>
                    <th class="py-3.5 px-4">Username</th>
                    <th class="py-3.5 px-4">Email</th>
                    <th class="py-3.5 px-4">Penugasan Posyandu</th>
                    <th class="py-3.5 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="kader_tbody" class="divide-y divide-slate-200 dark:divide-slate-800/60 text-xs font-medium">
                @forelse($kaders as $kader)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center font-bold text-emerald-700 dark:text-emerald-300 text-xs flex-shrink-0">
                                    {{ strtoupper(substr($kader->name, 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $kader->name }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-700 dark:text-slate-300">
                            {{ $kader->username }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                            {{ $kader->email ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold rounded-xl inline-block">
                                {{ $kader->posyandu->nama ?? 'Belum Ditugaskan' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kader.edit', $kader->id) }}" class="p-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl transition-all border border-slate-200 dark:border-slate-700/80" title="Edit Kader">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('kader.destroy', $kader->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun kader {{ $kader->name }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 rounded-xl transition-all border border-rose-500/30" title="Hapus Kader">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                            Tidak ada data Kader yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="kader_pagination" class="pt-2">
        {{ $kaders->links() }}
    </div>

</div>

<script>
let kaderSearchDebounceTimer = null;
let initialKaderTbodyHtml = '';
let isKaderAjaxActive = false;

document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('kader_tbody');
    if (tbody) {
        initialKaderTbodyHtml = tbody.innerHTML;
    }
});

function handlePosyanduFilterChange() {
    const searchVal = document.getElementById('search_kader').value.trim();
    document.getElementById('hidden_search_param').value = searchVal;
    document.getElementById('posyandu_filter_form').submit();
}

function handleKaderSearchInput() {
    const searchVal = document.getElementById('search_kader').value.trim();
    const searchIcon = document.getElementById('search_icon');
    const searchSpinner = document.getElementById('search_spinner');
    const pagination = document.getElementById('kader_pagination');

    if (kaderSearchDebounceTimer) clearTimeout(kaderSearchDebounceTimer);

    if (searchVal.length < 3) {
        if (searchIcon) searchIcon.classList.remove('hidden');
        if (searchSpinner) searchSpinner.classList.add('hidden');

        if (isKaderAjaxActive) {
            isKaderAjaxActive = false;
            const tbody = document.getElementById('kader_tbody');
            if (tbody && initialKaderTbodyHtml) {
                tbody.innerHTML = initialKaderTbodyHtml;
            }
            if (pagination) pagination.classList.remove('hidden');
        }
        return;
    }

    if (searchIcon) searchIcon.classList.add('hidden');
    if (searchSpinner) searchSpinner.classList.remove('hidden');

    kaderSearchDebounceTimer = setTimeout(() => {
        performKaderAjaxSearch(searchVal);
    }, 300);
}

async function performKaderAjaxSearch(query) {
    const posyanduId = document.getElementById('posyandu_filter').value;
    const searchIcon = document.getElementById('search_icon');
    const searchSpinner = document.getElementById('search_spinner');
    const pagination = document.getElementById('kader_pagination');

    try {
        const url = `{{ route('kader.search') }}?q=${encodeURIComponent(query)}&posyandu_id=${posyanduId}`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const json = await response.json();

        if (json.success) {
            isKaderAjaxActive = true;
            renderKaderTableResults(json.data);
            if (pagination) pagination.classList.add('hidden');
        }
    } catch (err) {
        console.error('Error fetching kader search:', err);
    } finally {
        if (searchIcon) searchIcon.classList.remove('hidden');
        if (searchSpinner) searchSpinner.classList.add('hidden');
    }
}

function renderKaderTableResults(items) {
    const tbody = document.getElementById('kader_tbody');
    if (!tbody) return;

    if (items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                    Tidak ada data Kader yang cocok dengan kata kunci pencarian.
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    const csrfToken = '{{ csrf_token() }}';

    items.forEach(kader => {
        const firstChar = kader.name.charAt(0).toUpperCase();
        const escapedName = kader.name.replace(/'/g, "\\'");

        html += `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                <td class="py-3.5 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center font-bold text-emerald-700 dark:text-emerald-300 text-xs flex-shrink-0">
                            ${firstChar}
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white">${kader.name}</span>
                    </div>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-700 dark:text-slate-300">
                    ${kader.username}
                </td>
                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                    ${kader.email}
                </td>
                <td class="py-3.5 px-4">
                    <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold rounded-xl inline-block">
                        ${kader.posyandu_nama}
                    </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="${kader.edit_url}" class="p-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl transition-all border border-slate-200 dark:border-slate-700/80" title="Edit Kader">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="${kader.destroy_url}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun kader ${escapedName}?')" class="inline">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 rounded-xl transition-all border border-rose-500/30" title="Hapus Kader">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}
</script>

@endsection
