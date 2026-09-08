<?php

namespace App\Services;

use App\Models\Anak;
use App\Models\KbmReference;
use App\Models\Pengukuran;

class GrowthFalteringService
{
    /**
     * Evaluasi C2 (Growth Faltering) berdasarkan tren Kenaikan Berat Badan bulanan (KBM KMS)
     *
     * @return array ['skor' => float (1.0..4.0), 'is_estimasi' => bool]
     */
    public function evaluate(Anak $anak, ?Pengukuran $pengukuranTerbaru = null): array
    {
        // 1. Tentukan pengukuran aktif (p0)
        $p0 = $pengukuranTerbaru;
        if (! $p0) {
            $p0 = $anak->pengukurans()
                ->withoutGlobalScope('posyandu_scope')
                ->orderByDesc('tahun_ukur')
                ->orderByDesc('bulan_ukur')
                ->first();
        }

        // Jika tidak ada pengukuran sama sekali
        if (! $p0) {
            return [
                'skor' => 2.0,
                'is_estimasi' => true,
            ];
        }

        $b0 = (int) $p0->bulan_ukur;
        $t0 = (int) $p0->tahun_ukur;

        // 2. Hitung periode 1 bulan sebelumnya (T1, B1) dan 2 bulan sebelumnya (T2, B2)
        $b1 = ($b0 === 1) ? 12 : $b0 - 1;
        $t1 = ($b0 === 1) ? $t0 - 1 : $t0;

        $b2 = ($b1 === 1) ? 12 : $b1 - 1;
        $t2 = ($b1 === 1) ? $t1 - 1 : $t1;

        // 3. Ambil data pengukuran yang benar-benar berasal dari bulan berurutan
        $p1 = $anak->pengukurans()
            ->withoutGlobalScope('posyandu_scope')
            ->where('tahun_ukur', $t1)
            ->where('bulan_ukur', $b1)
            ->first();

        // Jika tidak memiliki data pengukuran tepat 1 bulan sebelumnya
        if (! $p1) {
            return [
                'skor' => 2.0, // Skor netral pemantauan awal
                'is_estimasi' => true,
            ];
        }

        // 4. Hitung kenaikan BB bulan ini vs target KBM
        $deltaGram0 = ($p0->berat_kg - $p1->berat_kg) * 1000;
        $usiaKbm0 = max(1, min(60, (int) $p0->usia_bulan));
        $kbmRef0 = KbmReference::where('usia_bulan', $usiaKbm0)->first();
        $targetKbm0 = $kbmRef0 ? $kbmRef0->kbm_gram : 300;

        $isWeightLoss0 = $deltaGram0 < 0;
        $isFaltering0 = $deltaGram0 < $targetKbm0;

        // 5. Cek apakah ada data bulan ke-2 sebelumnya (p2) untuk evaluasi 2T (2 bulan tidak naik)
        $p2 = $anak->pengukurans()
            ->withoutGlobalScope('posyandu_scope')
            ->where('tahun_ukur', $t2)
            ->where('bulan_ukur', $b2)
            ->first();

        if ($p2) {
            $deltaGram1 = ($p1->berat_kg - $p2->berat_kg) * 1000;
            $usiaKbm1 = max(1, min(60, (int) $p1->usia_bulan));
            $kbmRef1 = KbmReference::where('usia_bulan', $usiaKbm1)->first();
            $targetKbm1 = $kbmRef1 ? $kbmRef1->kbm_gram : 300;

            $isFaltering1 = $deltaGram1 < $targetKbm1;

            if ($isWeightLoss0 || ($isFaltering0 && $isFaltering1)) {
                $skor = 4.0; // Berat badan turun atau 2 bulan berturut-turut tidak naik (2T)
            } elseif ($isFaltering0) {
                $skor = 3.0; // 1 bulan tidak mencapai KBM (1T)
            } else {
                $skor = 1.0; // Kenaikan berat badan memenuhi target KBM (N)
            }

            return [
                'skor' => $skor,
                'is_estimasi' => false,
            ];
        }

        // Hanya ada data 2 bulan berurutan (p0 dan p1)
        if ($isWeightLoss0) {
            $skor = 4.0; // BB Turun
        } elseif ($isFaltering0) {
            $skor = 3.0; // Tidak Capai KBM (1T)
        } else {
            $skor = 1.0; // Naik Adekuat Sesuai KBM (N)
        }

        return [
            'skor' => $skor,
            'is_estimasi' => false,
        ];
    }
}
