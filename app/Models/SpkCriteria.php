<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkCriteria extends Model
{
    use HasFactory;

    protected $table = 'spk_criterias';

    protected $fillable = [
        'kode',
        'nama',
        'bobot',
        'jenis',
        'tipe_sumber',
        'sub_kriteria',
        'keterangan',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'bobot' => 'float',
        'sub_kriteria' => 'array',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Dapatkan persentase bobot (contoh 0.45 -> 45)
     */
    public function getBobotPersenAttribute(): float
    {
        return round(((float) $this->bobot) * 100, 2);
    }
}
