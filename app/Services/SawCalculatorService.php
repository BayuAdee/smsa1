<?php

namespace App\Services;

use App\Models\Anak;
use App\Models\HasilSaw;
use App\Models\Pengukuran;
use App\Models\SpkCriteria;
use App\Models\SpkSetting;
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
     * Ambil konfigurasi kriteria aktif dari database atau fallback ke default
     */
    public function getActiveCriterias(): Collection
    {
        $criterias = SpkCriteria::where('is_active', true)->orderBy('urutan')->get();

        if ($criterias->isEmpty()) {
            return collect([
                new SpkCriteria(['kode' => 'C1', 'nama' => 'TB/U Z-Score (Standar WHO)', 'bobot' => 0.4500, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_tbu', 'urutan' => 1]),
                new SpkCriteria(['kode' => 'C2', 'nama' => 'Growth Faltering (KMS)', 'bobot' => 0.2500, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_growth_faltering', 'urutan' => 2]),
                new SpkCriteria(['kode' => 'C3', 'nama' => 'BB/U Z-Score (Standar WHO)', 'bobot' => 0.2000, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_bbu', 'urutan' => 3]),
                new SpkCriteria(['kode' => 'C4', 'nama' => 'Riwayat BBLR', 'bobot' => 0.1000, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_bblr', 'urutan' => 4]),
            ]);
        }

        return $criterias;
    }

    /**
     * Hitung nilai mentah (raw score 1.0 - 4.0) untuk suatu kriteria berdasarkan tipe sumbernya
     */
    public function calculateRawScore(SpkCriteria $criteria, Anak $anak, Pengukuran $pengukuran, array &$context): float
    {
        $tipe = $criteria->tipe_sumber;

        switch ($tipe) {
            case 'otomatis_tbu':
                if ($pengukuran->tinggi_cm !== null && (float) $pengukuran->tinggi_cm > 0) {
                    $zTbu = $this->zscoreService->calculate('tbu', $anak->jenis_kelamin, (int) $pengukuran->usia_bulan, (float) $pengukuran->tinggi_cm);
                    $context['z_tbu'] = $zTbu;
                    if ($zTbu >= -2.0) {
                        return 1.0; // Normal / Tinggi (Tidak Stunting)
                    } elseif ($zTbu >= -3.0) {
                        return round(2.0 + (-2.0 - $zTbu), 4); // Pendek / Stunted (2.0 - 3.0)
                    } else {
                        return min(4.0, round(3.0 + (-3.0 - $zTbu), 4)); // Sangat Pendek (3.0 - 4.0)
                    }
                }
                $context['z_tbu'] = 0.0;

                return 1.0;

            case 'otomatis_growth_faltering':
                $c2Data = $this->growthFalteringService->evaluate($anak, $pengukuran);
                $context['is_c2_estimasi'] = $c2Data['is_estimasi'];

                return (float) $c2Data['skor'];

            case 'otomatis_bbu':
                if ($pengukuran->berat_kg !== null && (float) $pengukuran->berat_kg > 0) {
                    $zBbu = $this->zscoreService->calculate('bbu', $anak->jenis_kelamin, (int) $pengukuran->usia_bulan, (float) $pengukuran->berat_kg);
                    $context['z_bbu'] = $zBbu;
                    if ($zBbu >= -2.0 && $zBbu <= 1.0) {
                        return 1.0; // Normal / Gizi Baik (Ideal)
                    } elseif ($zBbu >= -3.0 && $zBbu < -2.0) {
                        return round(2.0 + (-2.0 - $zBbu), 4); // Gizi Kurang (2.0 - 3.0)
                    } elseif ($zBbu < -3.0) {
                        return min(4.0, round(3.0 + (-3.0 - $zBbu), 4)); // Gizi Buruk (3.0 - 4.0)
                    } else {
                        return min(2.0, round(1.0 + (($zBbu - 1.0) * 0.5), 4));
                    }
                }
                $context['z_bbu'] = 0.0;

                return 1.0;

            case 'otomatis_bblr':
                if ($anak->status_bblr === 'bblr' || ($anak->berat_lahir_gram && $anak->berat_lahir_gram < 2500)) {
                    return 4.0;
                } elseif ($anak->status_bblr === 'tidak_diketahui' || empty($anak->berat_lahir_gram)) {
                    return 2.0;
                } else {
                    return 1.0;
                }

            case 'kustom':
            default:
                // Kriteria kustom berbasis skala 1.0 - 4.0 yang diinput kader / bidan
                if (! empty($pengukuran->nilai_kustom) && isset($pengukuran->nilai_kustom[$criteria->kode])) {
                    $val = (float) $pengukuran->nilai_kustom[$criteria->kode];

                    return max(1.0, min(4.0, $val));
                }

                return 1.0;
        }
    }

    /**
     * Hitung SPK SAW untuk anak yang memiliki pengukuran di posyandu tertentu pada periode (bulan/tahun) tertentu.
     */
    public function hitungUntukPosyanduPeriode(?int $posyanduId, int $bulanUkur, int $tahunUkur): Collection
    {
        $criterias = $this->getActiveCriterias();
        $thresholdTinggi = (float) SpkSetting::get('threshold_tinggi', 0.6374);
        $thresholdSedang = (float) SpkSetting::get('threshold_sedang', 0.7857);

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

            $context = [
                'z_tbu' => 0.0,
                'z_bbu' => 0.0,
                'is_c2_estimasi' => false,
            ];

            // Hitung nilai mentah untuk masing-masing kriteria C1 - C4
            $rawScores = [];
            foreach ($criterias as $crit) {
                $rawScores[$crit->kode] = $this->calculateRawScore($crit, $anak, $pengukuran, $context);
            }

            // Fallback backward-compatible untuk Z-scores jika belum dihitung oleh kriteria
            if (! isset($context['z_tbu']) || $context['z_tbu'] === 0.0) {
                if ($pengukuran->tinggi_cm !== null && (float) $pengukuran->tinggi_cm > 0) {
                    $context['z_tbu'] = $this->zscoreService->calculate('tbu', $anak->jenis_kelamin, (int) $pengukuran->usia_bulan, (float) $pengukuran->tinggi_cm);
                }
            }
            if (! isset($context['z_bbu']) || $context['z_bbu'] === 0.0) {
                if ($pengukuran->berat_kg !== null && (float) $pengukuran->berat_kg > 0) {
                    $context['z_bbu'] = $this->zscoreService->calculate('bbu', $anak->jenis_kelamin, (int) $pengukuran->usia_bulan, (float) $pengukuran->berat_kg);
                }
            }

            $rawMatrix[] = [
                'anak' => $anak,
                'pengukuran' => $pengukuran,
                'z_tbu' => $context['z_tbu'] ?? 0.0,
                'z_bbu' => $context['z_bbu'] ?? 0.0,
                'raw_scores' => $rawScores,
                'c1' => $rawScores['C1'] ?? 1.0,
                'c2' => $rawScores['C2'] ?? 1.0,
                'c3' => $rawScores['C3'] ?? 1.0,
                'c4' => $rawScores['C4'] ?? 1.0,
                'is_c2_estimasi' => $context['is_c2_estimasi'] ?? false,
            ];
        }

        if (empty($rawMatrix)) {
            return collect();
        }

        $results = collect();

        // 2. Hitung Normalisasi & Nilai Preferensi V_i
        foreach ($rawMatrix as $item) {
            $rScores = [];
            $nilaiV = 0.0;

            foreach ($criterias as $crit) {
                $raw = $item['raw_scores'][$crit->kode] ?? 1.0;
                $bobot = (float) $crit->bobot;

                if ($crit->jenis === 'benefit') {
                    // Benefit: r_ij = raw_ij / max(raw) (standar skala 4.0)
                    $r = round(min(1.0, max(0.0, $raw / 4.0)), 4);
                } else {
                    // Cost: r_ij = min(raw) / raw_ij (standar skala ideal = 1.0)
                    $r = round(1.0 / max(1.0, $raw), 4);
                }

                $rScores[$crit->kode] = $r;
                $nilaiV += ($bobot * $r);
            }

            $nilaiV = round($nilaiV, 4);

            $r1 = $rScores['C1'] ?? round(1.0 / max(1.0, $item['c1']), 4);
            $r2 = $rScores['C2'] ?? round(1.0 / max(1.0, $item['c2']), 4);
            $r3 = $rScores['C3'] ?? round(1.0 / max(1.0, $item['c3']), 4);
            $r4 = $rScores['C4'] ?? round(1.0 / max(1.0, $item['c4']), 4);

            // Kategori Risiko berdasarkan Nilai V (semakin kecil Nilai V = risiko stunting semakin tinggi)
            if ($nilaiV < $thresholdTinggi) {
                $kategori = 'Tinggi';
            } elseif ($nilaiV < $thresholdSedang) {
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
