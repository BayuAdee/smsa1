<?php

namespace App\Models;

use App\Models\Traits\HasPosyanduScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pengukuran extends Model
{
    use HasFactory, HasPosyanduScope;

    protected $fillable = [
        'anak_id',
        'tanggal_ukur',
        'bulan_ukur',
        'tahun_ukur',
        'usia_bulan',
        'tinggi_cm',
        'berat_kg',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_ukur' => 'date',
        'bulan_ukur' => 'integer',
        'tahun_ukur' => 'integer',
        'usia_bulan' => 'integer',
        'tinggi_cm' => 'float',
        'berat_kg' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (Pengukuran $pengukuran) {
            if ($pengukuran->tanggal_ukur) {
                $date = Carbon::parse($pengukuran->tanggal_ukur);
                if (is_null($pengukuran->bulan_ukur)) {
                    $pengukuran->bulan_ukur = (int) $date->month;
                }
                if (is_null($pengukuran->tahun_ukur)) {
                    $pengukuran->tahun_ukur = (int) $date->year;
                }
            }
        });
    }

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
