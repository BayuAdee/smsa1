<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Anak;
use Illuminate\Contracts\Console\Kernel;

$anaks = Anak::withoutGlobalScopes()->where('nama', 'like', '%INAZ%')->get();
echo 'Found '.$anaks->count().' children for INAZ:'.PHP_EOL;
foreach ($anaks as $a) {
    echo "ID: {$a->id} | Nama: {$a->nama} | Posyandu ID: {$a->posyandu_id} (".($a->posyandu->nama ?? '').')'.PHP_EOL;
}
