@extends('layouts.app')

@section('title', 'Data Posyandu')
@section('page-title', 'Manajemen Data Posyandu')

@section('content')

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-extrabold text-white">Daftar Unit Posyandu Wilayah</h2>
            <p class="text-xs text-slate-400 mt-0.5">Daftar Posyandu terdaftar dan jumlah kader/balita terpantau.</p>
        </div>

        @if(Auth::user()->isBidan())
            <button onclick="document.getElementById('modal-tambah-posyandu').classList.remove('hidden')" class="py-2.5 px-4 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Posyandu Baru</span>
            </button>
        @endif
    </div>

    <!-- Posyandu Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($posyandus as $pos)
            <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-5 space-y-4 relative overflow-hidden group hover:border-emerald-500/30 transition-all">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0v-4a1 1 0 011-1h2a1 1 0 011 1v4m-4 0h4"/>
                        </svg>
                    </div>
                    <span class="px-2.5 py-1 bg-slate-900 border border-slate-800 text-[11px] font-mono text-emerald-400 font-bold rounded-xl">ID #{{ $pos->id }}</span>
                </div>

                <div>
                    <h3 class="text-base font-extrabold text-white">{{ $pos->nama }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ $pos->wilayah ?? 'Wilayah belum diatur' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-800 text-xs">
                    <div class="bg-slate-900/60 p-2.5 rounded-xl border border-slate-800/60">
                        <span class="text-slate-400 block text-[10px]">Total Balita</span>
                        <span class="font-black text-white text-base mt-0.5 block">{{ number_format($pos->anaks_count ?? 0) }}</span>
                    </div>
                    <div class="bg-slate-900/60 p-2.5 rounded-xl border border-slate-800/60">
                        <span class="text-slate-400 block text-[10px]">Kader Terdaftar</span>
                        <span class="font-black text-emerald-400 text-base mt-0.5 block">{{ number_format($pos->users_count ?? 0) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>

<!-- Modal Tambah Posyandu (Khusus Bidan) -->
@if(Auth::user()->isBidan())
<div id="modal-tambah-posyandu" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-white">Tambah Posyandu Baru</h3>
            <button onclick="document.getElementById('modal-tambah-posyandu').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('posyandu.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="nama" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nama Posyandu</label>
                <input type="text" id="nama" name="nama" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3 text-sm" placeholder="Contoh: Posyandu Dahlia 04">
            </div>

            <div>
                <label for="wilayah" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Wilayah / Alamat</label>
                <input type="text" id="wilayah" name="wilayah" class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3 text-sm" placeholder="Contoh: Kelurahan Sukamaju RW 04">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-tambah-posyandu').classList.add('hidden')" class="px-4 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-xl text-xs shadow-md">Simpan Posyandu</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
