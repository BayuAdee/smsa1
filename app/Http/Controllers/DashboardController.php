<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\HasilSaw;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $selectedBulan = (int) $request->input('bulan', now()->month);
        $selectedTahun = (int) $request->input('tahun', now()->year);

        // Count metrics within current scope (HasPosyanduScope auto applied)
        $totalBalita = Anak::where('status_aktif', true)->count();

        $totalRisikoTinggi = HasilSaw::where('bulan_ukur', $selectedBulan)
            ->where('tahun_ukur', $selectedTahun)
            ->whereIn('kategori_risiko', ['Tinggi', 'tinggi'])
            ->whereHas('anak', function ($q) {
                $q->where('status_aktif', true);
            })->count();

        $totalPengukuranBulanIni = Pengukuran::where('bulan_ukur', $selectedBulan)
            ->where('tahun_ukur', $selectedTahun)
            ->count();

        // Get Top 5 High Risk Balita (SAW Ranking for selected period)
        $topRisiko = HasilSaw::with(['anak.posyandu', 'pengukuran'])
            ->where('bulan_ukur', $selectedBulan)
            ->where('tahun_ukur', $selectedTahun)
            ->whereHas('anak', function ($q) {
                $q->where('status_aktif', true);
            })
            ->orderBy('nilai_v', 'asc')
            ->take(5)
            ->get();

        // Get Posyandus for Bidan dropdown filter
        $posyandus = $user->isBidan() ? Posyandu::all() : collect();

        // Generate Opsi Periode
        $periodeOptions = [];
        $current = now()->subMonths(10);
        for ($i = 0; $i < 13; $i++) {
            $m = (int) $current->format('n');
            $y = (int) $current->format('Y');
            $label = $current->translatedFormat('F Y');
            $periodeOptions[] = [
                'bulan' => $m,
                'tahun' => $y,
                'label' => $label,
            ];
            $current->addMonth();
        }

        return view('dashboard.index', compact(
            'totalBalita',
            'totalRisikoTinggi',
            'totalPengukuranBulanIni',
            'topRisiko',
            'posyandus',
            'selectedBulan',
            'selectedTahun',
            'periodeOptions'
        ));
    }
}
