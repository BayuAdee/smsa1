<?php

namespace App\Services;

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\KbmReference;

class GrowthFalteringService
{
    /**
     * Evaluasi C2 (Growth Faltering) berdasarkan tren Kenaikan Berat Badan 2 bulan terakhir
     *
     * @param Anak $anak
     * @param Pengukuran|null $pengukuranTerbaru
     * @return array ['skor' => int (1..4), 'is_estimasi' => bool]
     */
    public function evaluate(Anak $anak, ?Pengukuran $pengukuranTerbaru = null): array
    {
        // Ambil 2 pengukuran terakhir (atau 3 jika dibandingkan 2 interval)
        $pengukurans = $anak->pengukurans()
            ->orderBy('tanggal_ukur', 'desc')
            ->take(3)
            ->get();

        if ($pengukurans->count() < 2) {
            return [
                'skor' => 2,
                'is_estimasi' => true,
            ];
        }

        // $p0 = terbaru, $p1 = 1 bulan lalu, $p2 = 2 bulan lalu (bila ada)
        $p0 = $pengukurans->get(0);
        $p1 = $pengukurans->get(1);

        // Kenaikan BB bulan ini = p0.berat - p1.berat (dalam gram)
        $deltaGram0 = ($p0->berat_kg - $p1->berat_kg) * 1000;

        // Ambil acuan KBM untuk usia bulan p0
        $kbmRef0 = KbmReference::where('usia_bulan', max(1, $p0->usia_bulan))->first();
        $targetKbm0 = $kbmRef0 ? $kbmRef0->kbm_gram : 300;

        $falteringBulan0 = $deltaGram0 < $targetKbm0;
        $weightLossBulan0 = $deltaGram0 < 0;

        if ($pengukurans->count() >= 3) {
            $p2 = $pengukurans->get(2);
            $deltaGram1 = ($p1->berat_kg - $p2->berat_kg) * 1000;
            $kbmRef1 = KbmReference::where('usia_bulan', max(1, $p1->usia_bulan))->first();
            $targetKbm1 = $kbmRef1 ? $kbmRef1->kbm_gram : 300;

            $falteringBulan1 = $deltaGram1 < $targetKbm1;

            if ($weightLossBulan0 || ($falteringBulan0 && $deltaGram0 <= 0)) {
                $skor = 4; // Berat badan turun / faltering parah
            } elseif ($falteringBulan0 && $falteringBulan1) {
                $skor = 3; // 2 bulan berturut-turut tidak mencapai KBM
            } elseif ($falteringBulan0 || $falteringBulan1) {
                $skor = 2; // 1 bulan faltering
            } else {
                $skor = 1; // 2 bulan berturut-turut memenuhi KBM
            }

            return [
                'skor' => $skor,
                'is_estimasi' => false,
            ];
        }

        // Hanya ada 2 data pengukuran (1 interval)
        if ($weightLossBulan0) {
            $skor = 4;
        } elseif ($falteringBulan0) {
            $skor = 3;
        } else {
            $skor = 1;
        }

        return [
            'skor' => $skor,
            'is_estimasi' => true,
        ];
    }
}
