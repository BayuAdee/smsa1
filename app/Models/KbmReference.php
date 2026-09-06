<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbmReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'usia_bulan',
        'kbm_gram',
    ];

    protected $casts = [
        'usia_bulan' => 'integer',
        'kbm_gram' => 'integer',
    ];
}
