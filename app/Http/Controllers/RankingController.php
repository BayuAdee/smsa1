<?php

namespace App\Http\Controllers;

use App\Models\HasilSaw;
use App\Services\SawCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    protected SawCalculatorService $sawCalculatorService;

    public function __construct(SawCalculatorService $sawCalculatorService)
    {
        $this->sawCalculatorService = $sawCalculatorService;
    }

    public function index(Request $request)
    {
        $selectedBulan = (int) $request->input('bulan', now()->month);
        $selectedTahun = (int) $request->input('tahun', now()->year);
        $selectedKategori = strtolower((string) $request->input('kategori', 'all'));

        // Get SAW Rankings sorted ASCENDING by nilai_v (V terkecil = Risiko Stunting Tertinggi = Rank 1)
        // Filtered by selected periode & kategori
        $query = HasilSaw::with(['anak.posyandu', 'pengukuran'])
            ->where('bulan_ukur', $selectedBulan)
            ->where('tahun_ukur', $selectedTahun)
            ->whereHas('anak', function ($q) {
                $q->where('status_aktif', true);
            });

        if ($selectedKategori !== 'all') {
            $query->whereRaw('LOWER(kategori_risiko) = ?', [$selectedKategori]);
        }

        $rankings = $query->orderBy('nilai_v', 'asc')->get();

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

        return view('ranking.index', compact('rankings', 'selectedBulan', 'selectedTahun', 'selectedKategori', 'periodeOptions'));
    }

    public function recalculate(Request $request)
    {
        $selectedBulan = (int) $request->input('bulan', now()->month);
        $selectedTahun = (int) $request->input('tahun', now()->year);

        $user = Auth::user();
        $posyanduId = $user->isKader() ? $user->posyandu_id : session('selected_posyandu_id');
        if ($posyanduId === 'all') {
            $posyanduId = null;
        }

        $this->sawCalculatorService->hitungUntukPosyanduPeriode($posyanduId, $selectedBulan, $selectedTahun);

        return redirect()->route('ranking.index', ['bulan' => $selectedBulan, 'tahun' => $selectedTahun])
            ->with('success', "Kalkulasi ulang SPK SAW periode {$selectedBulan}/{$selectedTahun} berhasil dilakukan.");
    }
}
