<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Pengukuran;
use Illuminate\Contracts\Console\Kernel;

$sample = Pengukuran::withoutGlobalScopes()->whereNotNull('dibuat_oleh')->with('pembuat')->take(5)->get();

echo 'Count with dibuat_oleh: '.Pengukuran::withoutGlobalScopes()->whereNotNull('dibuat_oleh')->count().PHP_EOL;
foreach ($sample as $p) {
    echo "Pengukuran ID #{$p->id} | Anak ID: {$p->anak_id} | Periode: {$p->bulan_ukur}/{$p->tahun_ukur} | Dibuat Oleh: ".($p->pembuat->name ?? "User ID {$p->dibuat_oleh}").PHP_EOL;
}
