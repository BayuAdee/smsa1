<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Traits\HasPosyanduScope;

class HasilSaw extends Model
{
    use HasFactory, HasPosyanduScope;

    protected $table = 'hasil_saw';

    protected $fillable = [
        'anak_id',
        'pengukuran_id',
        'z_tbu',
        'z_bbu',
        'raw_c1',
        'raw_c2',
        'raw_c3',
        'raw_c4',
        'r_c1',
        'r_c2',
        'r_c3',
        'r_c4',
        'nilai_v',
        'kategori_risiko',
        'is_c2_estimasi',
        'dihitung_pada',
    ];

    protected $casts = [
        'z_tbu' => 'float',
        'z_bbu' => 'float',
        'raw_c1' => 'float',
        'raw_c2' => 'float',
        'raw_c3' => 'float',
        'raw_c4' => 'float',
        'r_c1' => 'float',
        'r_c2' => 'float',
        'r_c3' => 'float',
        'r_c4' => 'float',
        'nilai_v' => 'float',
        'is_c2_estimasi' => 'boolean',
        'dihitung_pada' => 'datetime',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function pengukuran(): BelongsTo
    {
        return $this->belongsTo(Pengukuran::class);
    }
}
