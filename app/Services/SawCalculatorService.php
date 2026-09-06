<?php

namespace App\Services;

use App\Models\Anak;
use App\Models\HasilSaw;
use App\Models\Pengukuran;
use Illuminate\Support\Collection;

class SawCalculatorService
{
    protected ZscoreService $zscoreService;

    protected GrowthFalteringService $growthFalteringService;

    public function __construct(ZscoreService $zscoreService, GrowthFalteringService $growthFalteringService)
    {
        $this->zscoreService = $zscoreService;
        $this->growthFalteringService = $growthFalteringService;
    }

    /**
     * Hitung SPK SAW untuk anak yang memiliki pengukuran di posyandu tertentu pada periode (bulan/tahun) tertentu.
     */
    public function hitungUntukPosyanduPeriode(?int $posyanduId, int $bulanUkur, int $tahunUkur): Collection
    {
        $query = Anak::where('status_aktif', true);
        if ($posyanduId) {
            $query->where('posyandu_id', $posyanduId);
        }

        $anaks = $query->get();

        // 1. Kumpulkan raw matrix untuk anak yang memiliki pengukuran pada periode ini
        $rawMatrix = [];
        foreach ($anaks as $anak) {
            $pengukuran = Pengukuran::where('anak_id', $anak->id)
                ->where('bulan_ukur', $bulanUkur)
                ->where('tahun_ukur', $tahunUkur)
                ->first();

            if (! $pengukuran) {
                continue;
            }

            // C1: TB/U Z-score
            $zTbu = $this->zscoreService->calculate('tbu', $anak->jenis_kelamin, $pengukuran->usia_bulan, (float) $pengukuran->tinggi_cm);
            $rawC1 = 10.0 - $zTbu; // Kontinu: makin kerdil (Z < 0), raw cost makin tinggi

            // C2: Growth Faltering
            $c2Data = $this->growthFalteringService->evaluate($anak, $pengukuran);
            $rawC2 = (float) $c2Data['skor']; // 1, 2, 3, atau 4

            // C3: BB/U Z-score
            $zBbu = $this->zscoreService->calculate('bbu', $anak->jenis_kelamin, $pengukuran->usia_bulan, (float) $pengukuran->berat_kg);
            $rawC3 = 10.0 - $zBbu; // Kontinu: makin kurus/underweight, raw cost makin tinggi

            // C4: Riwayat BBLR
            if ($anak->status_bblr === 'bblr' || $anak->berat_lahir_gram < 2500) {
                $rawC4 = 4.0;
            } elseif ($anak->status_bblr === 'tidak_diketahui') {
                $rawC4 = 2.0;
            } else {
                $rawC4 = 1.0;
            }

            $rawMatrix[] = [
                'anak' => $anak,
                'pengukuran' => $pengukuran,
                'z_tbu' => $zTbu,
                'z_bbu' => $zBbu,
                'c1' => $rawC1,
                'c2' => $rawC2,
                'c3' => $rawC3,
                'c4' => $rawC4,
                'is_c2_estimasi' => $c2Data['is_estimasi'],
            ];
        }

        if (empty($rawMatrix)) {
            return collect();
        }

        // 2. Cari nilai minimum untuk setiap kriteria COST
        $minC1 = min(array_column($rawMatrix, 'c1'));
        $minC2 = min(array_column($rawMatrix, 'c2'));
        $minC3 = min(array_column($rawMatrix, 'c3'));
        $minC4 = min(array_column($rawMatrix, 'c4'));

        // Bobot kriteria COST:
        $w1 = 0.40;
        $w2 = 0.25;
        $w3 = 0.25;
        $w4 = 0.10;

        $results = collect();

        // 3. Hitung Normalisasi Cost: r_ij = min / raw_ij & Nilai V_i
        foreach ($rawMatrix as $item) {
            $r1 = $minC1 / max(0.0001, $item['c1']);
            $r2 = $minC2 / max(0.0001, $item['c2']);
            $r3 = $minC3 / max(0.0001, $item['c3']);
            $r4 = $minC4 / max(0.0001, $item['c4']);

            $nilaiV = round(($w1 * $r1) + ($w2 * $r2) + ($w3 * $r3) + ($w4 * $r4), 4);

            // Kategori Risiko berdasarkan Nilai V (semakin kecil Nilai V = risiko stunting semakin tinggi)
            if ($nilaiV < 0.78) {
                $kategori = 'Sangat Tinggi';
            } elseif ($nilaiV < 0.86) {
                $kategori = 'Tinggi';
            } elseif ($nilaiV < 0.93) {
                $kategori = 'Sedang';
            } else {
                $kategori = 'Rendah';
            }

            // Simpan atau update ke database (Upsert per anak & periode)
            $hasil = HasilSaw::updateOrCreate(
                [
                    'anak_id' => $item['anak']->id,
                    'bulan_ukur' => $bulanUkur,
                    'tahun_ukur' => $tahunUkur,
                ],
                [
                    'posyandu_id' => $item['anak']->posyandu_id,
                    'pengukuran_id' => $item['pengukuran']->id,
                    'z_tbu' => $item['z_tbu'],
                    'z_bbu' => $item['z_bbu'],
                    'raw_c1' => $item['c1'],
                    'raw_c2' => $item['c2'],
                    'raw_c3' => $item['c3'],
                    'raw_c4' => $item['c4'],
                    'r_c1' => round($r1, 4),
                    'r_c2' => round($r2, 4),
                    'r_c3' => round($r3, 4),
                    'r_c4' => round($r4, 4),
                    'nilai_v' => $nilaiV,
                    'kategori_risiko' => $kategori,
                    'is_c2_estimasi' => $item['is_c2_estimasi'],
                    'dihitung_pada' => now(),
                ]
            );

            $results->push($hasil);
        }

        // Urutkan ASCENDING berdasarkan nilai_v (V terkecil = Risiko Tertinggi = Ranking 1)
        return $results->sortBy('nilai_v')->values();
    }

    /**
     * Compatibility wrapper for calculateForPosyandu
     */
    public function calculateForPosyandu(?int $posyanduId = null, ?int $bulanUkur = null, ?int $tahunUkur = null): Collection
    {
        $bulan = $bulanUkur ?? now()->month;
        $tahun = $tahunUkur ?? now()->year;

        return $this->hitungUntukPosyanduPeriode($posyanduId, (int) $bulan, (int) $tahun);
    }
}
