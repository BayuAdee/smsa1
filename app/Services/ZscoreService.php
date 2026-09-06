<?php

namespace App\Services;

use App\Models\ZscoreReference;
use InvalidArgumentException;

class ZscoreService
{
    /**
     * Hitung Z-Score WHO LMS untuk TB/U atau BB/U
     *
     * @param string $indicator 'tbu' atau 'bbu'
     * @param string $jenisKelamin 'L' atau 'P'
     * @param int $usiaBulan 0 - 60
     * @param float $val Nilai aktual tinggi (cm) atau berat (kg)
     * @return float
     */
    public function calculate(string $indicator, string $jenisKelamin, int $usiaBulan, float $val): float
    {
        $usiaBulan = max(0, min(60, $usiaBulan));

        $ref = ZscoreReference::where('indicator', strtolower($indicator))
            ->where('jenis_kelamin', strtoupper($jenisKelamin))
            ->where('usia_bulan', $usiaBulan)
            ->first();

        if (!$ref) {
            // Fallback default calculation if reference not populated
            $m = ($indicator === 'tbu') ? 50 + $usiaBulan * 0.75 : 3.5 + $usiaBulan * 0.25;
            $s = ($indicator === 'tbu') ? 0.038 : 0.12;
            $l = 1.0;
            return round((pow($val / $m, $l) - 1) / ($l * $s), 2);
        }

        $l = $ref->l;
        $m = $ref->m;
        $s = $ref->s;

        if ($l != 0) {
            $z = (pow($val / $m, $l) - 1) / ($l * $s);
        } else {
            $z = log($val / $m) / $s;
        }

        return round($z, 2);
    }
}
