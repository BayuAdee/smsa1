<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

echo '=== USER LIST ==='.PHP_EOL;
foreach (User::all() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Username: {$u->username} | Email: {$u->email} | Role: {$u->role}".PHP_EOL;
}
