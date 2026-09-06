@extends('layouts.app')

@section('title', 'Detail Balita - ' . $anak->nama)
@section('page-title', 'Detail & Riwayat Balita')

@section('content')

<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-7 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-2xl shadow-lg">
                {{ strtoupper(substr($anak->nama, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $anak->nama }}</h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    Posyandu: <span class="text-emerald-400 font-semibold">{{ $anak->posyandu->nama ?? '-' }}</span> | 
                    Ibu: <span class="text-slate-200">{{ $anak->nama_orang_tua ?? '-' }}</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('ortu.show', ['token' => $anak->token_akses]) }}" target="_blank" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-slate-700 font-bold rounded-2xl text-xs flex items-center gap-1.5 min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span>Lihat Tampilan Ortu</span>
            </a>
            <a href="{{ route('pengukuran.create', ['anak_id' => $anak->id]) }}" class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-2xl text-xs flex items-center gap-1.5 min-h-[44px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Pengukuran</span>
            </a>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Informasi Kelahiran & Token</h3>
            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Token Akses Ortu:</span>
                    <span class="font-mono text-emerald-400 font-bold">{{ $anak->token_akses }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Tanggal Lahir:</span>
                    <span class="font-bold text-white">{{ $anak->tanggal_lahir->translatedFormat('d M Y') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Usia Sekarang:</span>
                    <span class="font-bold text-emerald-300">{{ $anak->usia_bulan }} Bulan</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Jenis Kelamin:</span>
                    <span class="font-bold text-white">{{ $anak->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Berat Lahir:</span>
                    <span class="font-bold text-white">{{ number_format($anak->berat_lahir_gram) }} gram</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Status BBLR:</span>
                    <span class="font-bold {{ $anak->status_bblr === 'bblr' ? 'text-rose-400' : 'text-emerald-400' }}">
                        {{ ucfirst($anak->status_bblr) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- History Measurement List -->
        <div class="md:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Riwayat Pengukuran Fisik ({{ $anak->pengukurans->count() }})</h3>

            @if($anak->pengukurans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3 rounded-l-xl">Tanggal Ukur</th>
                                <th class="py-2.5 px-3">Usia</th>
                                <th class="py-2.5 px-3">Tinggi (cm)</th>
                                <th class="py-2.5 px-3">Berat (kg)</th>
                                <th class="py-2.5 px-3 rounded-r-xl">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-200">
                            @foreach($anak->pengukurans as $p)
                                <tr>
                                    <td class="py-3 px-3 font-medium">{{ \Carbon\Carbon::parse($p->tanggal_ukur)->translatedFormat('d M Y') }}</td>
                                    <td class="py-3 px-3">{{ $p->usia_bulan }} bln</td>
                                    <td class="py-3 px-3 font-bold text-emerald-400">{{ number_format($p->tinggi_cm, 1) }} cm</td>
                                    <td class="py-3 px-3 font-bold text-teal-400">{{ number_format($p->berat_kg, 1) }} kg</td>
                                    <td class="py-3 px-3 text-slate-400">{{ $p->pembuat->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-slate-400 text-xs">
                    Belum ada riwayat pengukuran fisik.
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
