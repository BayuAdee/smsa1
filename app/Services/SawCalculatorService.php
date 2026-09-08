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
        $query = Anak::withoutGlobalScope('posyandu_scope')->where('status_aktif', true);
        if ($posyanduId) {
            $query->where('posyandu_id', $posyanduId);
        }

        $anaks = $query->get();

        // 1. Kumpulkan raw matrix untuk anak yang memiliki pengukuran pada periode ini
        $rawMatrix = [];
        foreach ($anaks as $anak) {
            $pengukuran = Pengukuran::withoutGlobalScope('posyandu_scope')
                ->where('anak_id', $anak->id)
                ->where('bulan_ukur', $bulanUkur)
                ->where('tahun_ukur', $tahunUkur)
                ->first();

            if (! $pengukuran) {
                continue;
            }

            // C1: TB/U Z-score terstandarisasi ke skala risiko [1.0, 4.0] (Standar WHO)
            $zTbu = $this->zscoreService->calculate('tbu', $anak->jenis_kelamin, (int) $pengukuran->usia_bulan, (float) $pengukuran->tinggi_cm);
            if ($zTbu >= -2.0) {
                $rawC1 = 1.0; // Normal / Tinggi (Tidak Stunting, Ideal)
            } elseif ($zTbu >= -3.0) {
                $rawC1 = round(2.0 + (-2.0 - $zTbu), 4); // Pendek / Stunted (Rentang 2.0 - 3.0)
            } else {
                $rawC1 = min(4.0, round(3.0 + (-3.0 - $zTbu), 4)); // Sangat Pendek / Severely Stunted (Rentang 3.0 - 4.0)
            }

            // C2: Growth Faltering (skor 1.0 - 4.0 dari kenaikan BB vs KBM KMS)
            $c2Data = $this->growthFalteringService->evaluate($anak, $pengukuran);
            $rawC2 = (float) $c2Data['skor'];

            // C3: BB/U Z-score terstandarisasi ke skala risiko [1.0, 4.0] (Standar WHO)
            $zBbu = $this->zscoreService->calculate('bbu', $anak->jenis_kelamin, (int) $pengukuran->usia_bulan, (float) $pengukuran->berat_kg);
            if ($zBbu >= -2.0 && $zBbu <= 1.0) {
                $rawC3 = 1.0; // Normal / Gizi Baik (Ideal)
            } elseif ($zBbu >= -3.0 && $zBbu < -2.0) {
                $rawC3 = round(2.0 + (-2.0 - $zBbu), 4); // Gizi Kurang / Underweight (Rentang 2.0 - 3.0)
            } elseif ($zBbu < -3.0) {
                $rawC3 = min(4.0, round(3.0 + (-3.0 - $zBbu), 4)); // Gizi Buruk / Severely Underweight (Rentang 3.0 - 4.0)
            } else {
                // $zBbu > 1.0 (Risiko Gizi Lebih / Overweight)
                $rawC3 = min(2.0, round(1.0 + (($zBbu - 1.0) * 0.5), 4));
            }

            // C4: Riwayat BBLR ke skala risiko [1.0, 4.0]
            if ($anak->status_bblr === 'bblr' || ($anak->berat_lahir_gram && $anak->berat_lahir_gram < 2500)) {
                $rawC4 = 4.0;
            } elseif ($anak->status_bblr === 'tidak_diketahui' || empty($anak->berat_lahir_gram)) {
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

        // Bobot kriteria COST:
        $w1 = 0.40;
        $w2 = 0.25;
        $w3 = 0.25;
        $w4 = 0.10;

        $results = collect();

        // 2. Hitung Normalisasi Cost: r_ij = 1.0 / raw_ij & Nilai V_i
        // (Seluruh kriteria terstandarisasi dengan nilai ideal terbaik = 1.0)
        foreach ($rawMatrix as $item) {
            $r1 = round(1.0 / max(1.0, $item['c1']), 4);
            $r2 = round(1.0 / max(1.0, $item['c2']), 4);
            $r3 = round(1.0 / max(1.0, $item['c3']), 4);
            $r4 = round(1.0 / max(1.0, $item['c4']), 4);

            $nilaiV = round(($w1 * $r1) + ($w2 * $r2) + ($w3 * $r3) + ($w4 * $r4), 4);

            // Kategori Risiko berdasarkan Nilai V (semakin kecil Nilai V = risiko stunting semakin tinggi)
            if ($nilaiV < 0.78) {
                $kategori = 'Tinggi';
            } elseif ($nilaiV < 0.93) {
                $kategori = 'Sedang';
            } else {
                $kategori = 'Rendah';
            }

            // Simpan atau update ke database (Upsert per anak & periode)
            $hasil = HasilSaw::withoutGlobalScope('posyandu_scope')->updateOrCreate(
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
                    'r_c1' => $r1,
                    'r_c2' => $r2,
                    'r_c3' => $r3,
                    'r_c4' => $r4,
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
