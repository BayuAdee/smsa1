<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anak;
use App\Models\Pengukuran;
use App\Services\SawCalculatorService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PengukuranController extends Controller
{
    protected SawCalculatorService $sawCalculatorService;

    public function __construct(SawCalculatorService $sawCalculatorService)
    {
        $this->sawCalculatorService = $sawCalculatorService;
    }

    public function index()
    {
        $pengukurans = Pengukuran::with(['anak.posyandu', 'pembuat', 'hasilSaw'])
            ->orderBy('tanggal_ukur', 'desc')
            ->paginate(15);

        return view('pengukuran.index', compact('pengukurans'));
    }

    public function create(Request $request)
    {
        $selectedAnakId = $request->input('anak_id');
        $anaks = Anak::where('status_aktif', true)->orderBy('nama', 'asc')->get();

        return view('pengukuran.create', compact('anaks', 'selectedAnakId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anak_id' => 'required|exists:anaks,id',
            'tanggal_ukur' => 'required|date|before_or_equal:today',
            'tinggi_cm' => 'required|numeric|min:30|max:150',
            'berat_kg' => 'required|numeric|min:1|max:40',
        ]);

        $anak = Anak::findOrFail($validated['anak_id']);

        // Calculate age in months at measurement date
        $tanggalUkur = Carbon::parse($validated['tanggal_ukur']);
        $usiaBulan = (int) floor(Carbon::parse($anak->tanggal_lahir)->diffInMonths($tanggalUkur));
        $validated['usia_bulan'] = max(0, $usiaBulan);
        $validated['dibuat_oleh'] = Auth::id();

        $pengukuran = Pengukuran::create($validated);

        // Auto recalculate SAW rankings for the Posyandu
        $this->sawCalculatorService->calculateForPosyandu($anak->posyandu_id);

        return redirect()->route('ranking.index')->with('success', "Pengukuran fisik balita {$anak->nama} berhasil disimpan & ranking SAW otomatis ter-update!");
    }
}
