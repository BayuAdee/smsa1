<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

$bidans = [
    [
        'name' => 'Ny. Tonik Purwanto, A.Md.Keb',
        'username' => 'ny_tonik',
        'email' => 'tonik@posyandu.id',
        'password' => 'bidan123',
    ],
    [
        'name' => 'Bidan Desa Bentak',
        'username' => 'bidan_desa',
        'email' => 'bidan_desa@posyandu.id', // Diubah agar unique (karena bidan@posyandu.id sudah dipakai akun bawaan)
        'password' => 'password123',
    ],
    [
        'name' => 'Bidan 1 Desa Bentak',
        'username' => 'bidan_bentak',
        'email' => 'bentak@posyandu.id',
        'password' => 'desabentak123',
    ],
];

foreach ($bidans as $data) {
    $user = User::updateOrCreate(
        ['username' => $data['username']],
        [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'bidan',
            'posyandu_id' => null,
        ]
    );
    echo "BERHASIL: Akun '{$user->name}' (Username: {$user->username}, Email: {$user->email}) berhasil dibuat/diperbarui.".PHP_EOL;
}
