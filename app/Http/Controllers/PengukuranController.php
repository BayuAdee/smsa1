<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Services\SawCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengukuranController extends Controller
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

        // Ambil daftar balita aktif di posyandu user
        $anaks = Anak::with(['posyandu', 'pengukurans' => function ($q) use ($selectedBulan, $selectedTahun) {
            $q->where('bulan_ukur', $selectedBulan)->where('tahun_ukur', $selectedTahun);
        }])
            ->where('status_aktif', true)
            ->orderBy('nama', 'asc')
            ->get();

        // Attach pengukuranPeriode helper
        foreach ($anaks as $anak) {
            $anak->pengukuran_periode = $anak->pengukurans->first();
        }

        // Generate Opsi Periode (12 bulan ke belakang sampai bulan depan)
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

        // Support JSON response for background realtime polling
        if ($request->wantsJson()) {
            $sudahDiukurCount = $anaks->filter(fn($a) => !is_null($a->pengukuran_periode))->count();
            return response()->json([
                'success' => true,
                'totalBalita' => $anaks->count(),
                'sudahDiukur' => $sudahDiukurCount,
                'anaks' => $anaks->map(function ($anak) {
                    $p = $anak->pengukuran_periode;
                    return [
                        'id' => $anak->id,
                        'sudah' => !is_null($p),
                        'tinggi_cm' => $p ? $p->tinggi_cm : null,
                        'berat_kg'  => $p ? $p->berat_kg : null,
                    ];
                })->values(),
            ]);
        }

        return view('pengukuran.index', compact('anaks', 'selectedBulan', 'selectedTahun', 'periodeOptions'));
    }

    public function create(Request $request)
    {
        return redirect()->route('pengukuran.index', $request->only(['bulan', 'tahun', 'anak_id']));
    }

    public function store(Request $request)
    {
        // Sanitisasi input desimal: ubah koma (,) menjadi titik (.) dan hilangkan string kosong
        $tinggi = $request->input('tinggi_cm');
        $berat  = $request->input('berat_kg');

        if (is_numeric($tinggi) || (is_string($tinggi) && trim($tinggi) !== '')) {
            $tinggi = str_replace(',', '.', trim((string) $tinggi));
        } else {
            $tinggi = null;
        }

        if (is_numeric($berat) || (is_string($berat) && trim($berat) !== '')) {
            $berat = str_replace(',', '.', trim((string) $berat));
        } else {
            $berat = null;
        }

        $request->merge([
            'tinggi_cm' => $tinggi,
            'berat_kg'  => $berat,
        ]);

        $validated = $request->validate([
            'anak_id' => 'required|exists:anaks,id',
            'bulan_ukur' => 'required|integer|min:1|max:12',
            'tahun_ukur' => 'required|integer|min:2000|max:2099',
            'tinggi_cm' => 'nullable|numeric|min:30|max:150|required_without:berat_kg',
            'berat_kg' => 'nullable|numeric|min:1|max:40|required_without:tinggi_cm',
        ], [
            'tinggi_cm.required_without' => 'Isi minimal salah satu antara Tinggi Badan atau Berat Badan.',
            'berat_kg.required_without'  => 'Isi minimal salah satu antara Tinggi Badan atau Berat Badan.',
        ]);

        $anak = Anak::findOrFail($validated['anak_id']);

        $bulanUkur = (int) $validated['bulan_ukur'];
        $tahunUkur = (int) $validated['tahun_ukur'];

        // Tentukan tanggal ukur
        if ($bulanUkur === (int) now()->month && $tahunUkur === (int) now()->year) {
            $tanggalUkur = now()->format('Y-m-d');
        } else {
            $tanggalUkur = sprintf('%04d-%02d-01', $tahunUkur, $bulanUkur);
        }

        // Hitung usia bulan saat pengukuran
        $usiaBulan = (int) floor(Carbon::parse($anak->tanggal_lahir)->diffInMonths(Carbon::parse($tanggalUkur)));
        $usiaBulan = max(0, $usiaBulan);

        // Cari pengukuran yang sudah ada untuk periode ini (jika ada) agar data lama tidak tertimpa null saat diisi secara terpisah
        $existing = Pengukuran::where('anak_id', $anak->id)
            ->where('bulan_ukur', $bulanUkur)
            ->where('tahun_ukur', $tahunUkur)
            ->first();

        $tinggiSimpan = (array_key_exists('tinggi_cm', $validated) && !is_null($validated['tinggi_cm']))
            ? $validated['tinggi_cm']
            : ($existing ? $existing->tinggi_cm : null);

        $beratSimpan = (array_key_exists('berat_kg', $validated) && !is_null($validated['berat_kg']))
            ? $validated['berat_kg']
            : ($existing ? $existing->berat_kg : null);

        // Timpa Data (UPSERT) per balita & periode
        $pengukuran = Pengukuran::updateOrCreate(
            [
                'anak_id' => $anak->id,
                'bulan_ukur' => $bulanUkur,
                'tahun_ukur' => $tahunUkur,
            ],
            [
                'tanggal_ukur' => $tanggalUkur,
                'usia_bulan' => $usiaBulan,
                'tinggi_cm' => $tinggiSimpan,
                'berat_kg'  => $beratSimpan,
                'dibuat_oleh' => Auth::id(),
            ]
        );

        // Otomatis Hitung Ulang SPK SAW Periode Ini
        $this->sawCalculatorService->hitungUntukPosyanduPeriode($anak->posyandu_id, $bulanUkur, $tahunUkur);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pengukuran balita {$anak->nama} berhasil disimpan!",
                'pengukuran' => [
                    'id' => $pengukuran->id,
                    'anak_id' => $pengukuran->anak_id,
                    'tinggi_cm' => (float) $pengukuran->tinggi_cm,
                    'berat_kg' => (float) $pengukuran->berat_kg,
                    'usia_bulan' => $pengukuran->usia_bulan,
                    'bulan_ukur' => $pengukuran->bulan_ukur,
                    'tahun_ukur' => $pengukuran->tahun_ukur,
                ],
            ]);
        }

        return redirect()
            ->route('pengukuran.index', ['bulan' => $bulanUkur, 'tahun' => $tahunUkur])
            ->with('success', "Pengukuran bulanan balita {$anak->nama} berhasil disimpan!");
    }
}
