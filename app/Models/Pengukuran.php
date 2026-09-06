<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\Traits\HasPosyanduScope;

class Pengukuran extends Model
{
    use HasFactory, HasPosyanduScope;

    protected $fillable = [
        'anak_id',
        'tanggal_ukur',
        'usia_bulan',
        'tinggi_cm',
        'berat_kg',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_ukur' => 'date',
        'usia_bulan' => 'integer',
        'tinggi_cm' => 'float',
        'berat_kg' => 'float',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function hasilSaw(): HasOne
    {
        return $this->hasOne(HasilSaw::class);
    }
}
