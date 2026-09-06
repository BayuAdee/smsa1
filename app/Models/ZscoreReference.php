<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZscoreReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator',
        'jenis_kelamin',
        'usia_bulan',
        'l',
        'm',
        's',
        'sd3neg',
        'sd2neg',
        'sd1neg',
        'sd0',
        'sd1',
        'sd2',
        'sd3',
    ];

    protected $casts = [
        'l' => 'float',
        'm' => 'float',
        's' => 'float',
        'sd3neg' => 'float',
        'sd2neg' => 'float',
        'sd1neg' => 'float',
        'sd0' => 'float',
        'sd1' => 'float',
        'sd2' => 'float',
        'sd3' => 'float',
        'usia_bulan' => 'integer',
    ];
}
