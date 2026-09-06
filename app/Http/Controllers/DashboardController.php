<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\HasilSaw;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Count metrics within current scope (HasPosyanduScope auto applied)
        $totalBalita = Anak::where('status_aktif', true)->count();

        $totalRisikoTinggi = HasilSaw::whereIn('kategori_risiko', ['Sangat Tinggi', 'Tinggi'])
            ->whereHas('anak', function($q) {
                $q->where('status_aktif', true);
            })->count();

        $totalPengukuranBulanIni = Pengukuran::whereMonth('tanggal_ukur', now()->month)
            ->whereYear('tanggal_ukur', now()->year)
            ->count();

        // Get Top 5 High Risk Balita (SAW Ranking)
        $topRisiko = HasilSaw::with(['anak.posyandu', 'pengukuran'])
            ->whereHas('anak', function($q) {
                $q->where('status_aktif', true);
            })
            ->orderBy('nilai_v', 'asc')
            ->take(5)
            ->get();

        // Get Posyandus for Bidan dropdown filter
        $posyandus = $user->isBidan() ? Posyandu::all() : collect();

        return view('dashboard.index', compact(
            'totalBalita',
            'totalRisikoTinggi',
            'totalPengukuranBulanIni',
            'topRisiko',
            'posyandus'
        ));
    }
}
