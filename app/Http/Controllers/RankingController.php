<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilSaw;
use App\Services\SawCalculatorService;
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
        $user = Auth::user();

        // Get SAW Rankings sorted ASCENDING by nilai_v (V terkecil = Risiko Stunting Tertinggi = Rank 1)
        $rankings = HasilSaw::with(['anak.posyandu', 'pengukuran'])
            ->whereHas('anak', function($q) {
                $q->where('status_aktif', true);
            })
            ->orderBy('nilai_v', 'asc')
            ->get();

        return view('ranking.index', compact('rankings'));
    }

    public function recalculate()
    {
        $user = Auth::user();
        $posyanduId = $user->isKader() ? $user->posyandu_id : session('selected_posyandu_id');
        if ($posyanduId === 'all') {
            $posyanduId = null;
        }

        $this->sawCalculatorService->calculateForPosyandu($posyanduId);

        return back()->with('success', 'Kalkulasi ulang SPK SAW berhasil dilakukan.');
    }
}
