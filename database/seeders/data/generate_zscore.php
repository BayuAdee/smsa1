<?php

$headers = ['indicator', 'jenis_kelamin', 'usia_bulan', 'l', 'm', 's', 'sd3neg', 'sd2neg', 'sd1neg', 'sd0', 'sd1', 'sd2', 'sd3'];
$rows = [$headers];

// Generate 0-60 months
for ($m = 0; $m <= 60; $m++) {
    // 1. TBU Laki-laki
    $l_tbu_l = 1.0;
    $m_tbu_l = round(49.8842 + 3.15 * sqrt($m) + 0.64 * $m - 0.0032 * pow($m, 1.8) / 10, 2);
    $s_tbu_l = round(0.038 - 0.0001 * $m, 4);
    
    // 2. TBU Perempuan
    $l_tbu_p = 1.0;
    $m_tbu_p = round(49.1477 + 3.08 * sqrt($m) + 0.63 * $m - 0.0032 * pow($m, 1.8) / 10, 2);
    $s_tbu_p = round(0.0385 - 0.0001 * $m, 4);

    // 3. BBU Laki-laki
    $l_bbu_l = -0.15;
    $m_bbu_l = round(3.346 + 1.25 * sqrt($m) + 0.18 * $m - 0.0008 * pow($m, 1.8) / 5, 2);
    $s_bbu_l = round(0.12 + 0.0003 * $m, 4);

    // 4. BBU Perempuan
    $l_bbu_p = -0.15;
    $m_bbu_p = round(3.232 + 1.20 * sqrt($m) + 0.17 * $m - 0.0008 * pow($m, 1.8) / 5, 2);
    $s_bbu_p = round(0.125 + 0.0003 * $m, 4);

    $configs = [
        ['tbu', 'L', $m, $l_tbu_l, $m_tbu_l, $s_tbu_l],
        ['tbu', 'P', $m, $l_tbu_p, $m_tbu_p, $s_tbu_p],
        ['bbu', 'L', $m, $l_bbu_l, $m_bbu_l, $s_bbu_l],
        ['bbu', 'P', $m, $l_bbu_p, $m_bbu_p, $s_bbu_p],
    ];

    foreach ($configs as $cfg) {
        list($ind, $jk, $age, $l, $med, $s) = $cfg;
        $calcSd = function($z) use ($l, $med, $s) {
            if ($l != 0) {
                return round($med * pow(1 + $z * $l * $s, 1 / $l), 2);
            }
            return round($med * exp($z * $s), 2);
        };

        $rows[] = [
            $ind, $jk, $age, $l, $med, $s,
            $calcSd(-3), $calcSd(-2), $calcSd(-1), $med, $calcSd(1), $calcSd(2), $calcSd(3)
        ];
    }
}

$fp = fopen(__DIR__ . '/zscore_references.csv', 'w');
foreach ($rows as $row) {
    fputcsv($fp, $row);
}
fclose($fp);
echo "Successfully generated zscore_references.csv with " . (count($rows) - 1) . " rows.\n";
