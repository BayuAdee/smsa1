<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\SpkCriteria;
use App\Models\SpkSetting;
use App\Services\SawCalculatorService;
use Database\Seeders\SpkSettingSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingSpkController extends Controller
{
    protected SawCalculatorService $sawCalculatorService;

    public function __construct(SawCalculatorService $sawCalculatorService)
    {
        $this->sawCalculatorService = $sawCalculatorService;
    }

    /**
     * Tampilkan form pengaturan parameter SPK SAW (C1-C4, Bobot, Threshold)
     */
    public function index(): View
    {
        $this->authorizeBidan();

        $criterias = SpkCriteria::orderBy('urutan')->get();
        if ($criterias->isEmpty()) {
            (new SpkSettingSeeder)->run();
            $criterias = SpkCriteria::orderBy('urutan')->get();
        }

        $thresholdTinggi = (float) SpkSetting::get('threshold_tinggi', 0.6374);
        $thresholdSedang = (float) SpkSetting::get('threshold_sedang', 0.7857);

        $availableSources = [
            'otomatis_tbu' => [
                'label' => 'TB/U Z-Score (Standar WHO)',
                'desc' => 'Tinggi Badan menurut Umur untuk mendeteksi Stunting / Sangat Pendek',
                'type' => 'Otomatis Medis',
            ],
            'otomatis_growth_faltering' => [
                'label' => 'Growth Faltering (KMS)',
                'desc' => 'Kenaikan Berat Badan bulanan vs Standar KBM KMS (T/T1/T2/T3)',
                'type' => 'Otomatis Medis',
            ],
            'otomatis_bbu' => [
                'label' => 'BB/U Z-Score (Standar WHO)',
                'desc' => 'Berat Badan menurut Umur untuk mendeteksi Gizi Kurang / Buruk',
                'type' => 'Otomatis Medis',
            ],
            'otomatis_bblr' => [
                'label' => 'Riwayat BBLR',
                'desc' => 'Berat Lahir Rendah (< 2500 gram) atau berat lahir normal',
                'type' => 'Otomatis Medis',
            ],
            'kustom' => [
                'label' => 'Kriteria Kustom / Variabel Bebas',
                'desc' => 'Variabel mandiri dengan skala skor risiko 1.0 (Aman) hingga 4.0 (Berisiko)',
                'type' => 'Kustom Bebas',
            ],
        ];

        return view('settings.spk', compact('criterias', 'thresholdTinggi', 'thresholdSedang', 'availableSources'));
    }

    /**
     * Simpan perubahan parameter kriteria C1-C4, bobot, dan batas threshold
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeBidan();

        $request->validate([
            'criterias' => ['required', 'array', 'size:4'],
            'criterias.*.id' => ['required', 'exists:spk_criterias,id'],
            'criterias.*.nama' => ['required', 'string', 'max:255'],
            'criterias.*.bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'criterias.*.jenis' => ['required', 'in:cost,benefit'],
            'criterias.*.tipe_sumber' => ['required', 'string'],
            'threshold_tinggi' => ['required', 'numeric', 'min:0.0001', 'max:1'],
            'threshold_sedang' => ['required', 'numeric', 'min:0.0001', 'max:1'],
        ]);

        $thresholdTinggi = (float) $request->input('threshold_tinggi');
        $thresholdSedang = (float) $request->input('threshold_sedang');

        if ($thresholdTinggi >= $thresholdSedang) {
            return back()->withInput()->withErrors([
                'threshold_tinggi' => 'Ambang batas Risiko Tinggi harus lebih kecil dari ambang batas Risiko Sedang.',
            ]);
        }

        $inputCriterias = $request->input('criterias');
        $totalBobotPersen = 0.0;

        foreach ($inputCriterias as $item) {
            $totalBobotPersen += (float) $item['bobot'];
        }

        // Toleransi rounding 0.1%
        if (abs($totalBobotPersen - 100.0) > 0.1) {
            return back()->withInput()->withErrors([
                'total_bobot' => "Total persentase bobot C1–C4 harus tepat 100%. Saat ini: {$totalBobotPersen}%.",
            ]);
        }

        // Update Kriteria C1 - C4
        foreach ($inputCriterias as $item) {
            $criteria = SpkCriteria::findOrFail($item['id']);
            $bobotDesimal = round(((float) $item['bobot']) / 100, 4);

            $subKriteria = null;
            if ($item['tipe_sumber'] === 'kustom' && ! empty($item['sub_kriteria']) && is_array($item['sub_kriteria'])) {
                $subKriteria = [];
                foreach ($item['sub_kriteria'] as $sub) {
                    if (! empty($sub['label'])) {
                        $subKriteria[] = [
                            'label' => trim($sub['label']),
                            'skor' => (float) ($sub['skor'] ?? 1.0),
                            'keterangan' => trim($sub['keterangan'] ?? ''),
                        ];
                    }
                }
            }

            $criteria->update([
                'nama' => trim($item['nama']),
                'bobot' => $bobotDesimal,
                'jenis' => $item['jenis'],
                'tipe_sumber' => $item['tipe_sumber'],
                'sub_kriteria' => $subKriteria,
            ]);
        }

        // Update Thresholds
        SpkSetting::set('threshold_tinggi', number_format($thresholdTinggi, 4, '.', ''));
        SpkSetting::set('threshold_sedang', number_format($thresholdSedang, 4, '.', ''));

        // Jika opsi recalculate dicentang
        if ($request->boolean('recalculate_now')) {
            $bulan = (int) now()->month;
            $tahun = (int) now()->year;
            $posyandus = Posyandu::all();
            foreach ($posyandus as $posyandu) {
                $this->sawCalculatorService->hitungUntukPosyanduPeriode($posyandu->id, $bulan, $tahun);
            }
        }

        return redirect()->route('settings.spk.index')->with('success', 'Pengaturan parameter SPK SAW (C1–C4, Bobot, & Threshold) berhasil disimpan!');
    }

    /**
     * Kembalikan konfigurasi parameter ke default standar
     */
    public function resetDefault(): RedirectResponse
    {
        $this->authorizeBidan();

        (new SpkSettingSeeder)->run();

        // Hitung ulang ranking periode aktif
        $bulan = (int) now()->month;
        $tahun = (int) now()->year;
        $posyandus = Posyandu::all();
        foreach ($posyandus as $posyandu) {
            $this->sawCalculatorService->hitungUntukPosyanduPeriode($posyandu->id, $bulan, $tahun);
        }

        return redirect()->route('settings.spk.index')->with('success', 'Pengaturan parameter SPK SAW berhasil dikembalikan ke nilai default standar.');
    }

    /**
     * Pastikan hanya Bidan Desa yang memiliki hak akses
     */
    protected function authorizeBidan(): void
    {
        if (! Auth::check() || ! Auth::user()->isBidan()) {
            abort(403, 'Akses ditolak. Pengaturan parameter SPK SAW hanya dapat diakses oleh Bidan Desa.');
        }
    }
}
