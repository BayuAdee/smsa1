<?php

namespace App\Models;

use App\Models\Traits\HasPosyanduScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Anak extends Model
{
    use HasFactory, HasPosyanduScope;

    protected static function booted(): void
    {
        static::creating(function (Anak $anak) {
            if (empty($anak->token_akses)) {
                $anak->token_akses = static::generateTokenAkses();
            }
        });
    }

    public static function generateTokenAkses(): string
    {
        return 'BALITA-'.strtoupper((string) Str::uuid());
    }

    protected $fillable = [
        'posyandu_id',
        'nama',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'berat_lahir_gram',
        'status_bblr',
        'token_akses',
        'status_aktif',
        'nama_orang_tua',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'status_aktif' => 'boolean',
        'berat_lahir_gram' => 'integer',
    ];

    public function posyandu(): BelongsTo
    {
        return $this->belongsTo(Posyandu::class);
    }

    public function pengukurans(): HasMany
    {
        return $this->hasMany(Pengukuran::class)->orderBy('tanggal_ukur', 'asc');
    }

    public function pengukuranTerakhir(): HasOne
    {
        return $this->hasOne(Pengukuran::class)->latestOfMany('tanggal_ukur');
    }

    public function hasilSaws(): HasMany
    {
        return $this->hasMany(HasilSaw::class);
    }

    public function hasilSawTerakhir(): HasOne
    {
        return $this->hasOne(HasilSaw::class)->latestOfMany('dihitung_pada');
    }

    public function getUsiaBulanAttribute(): int
    {
        return (int) floor(Carbon::parse($this->tanggal_lahir)->diffInMonths(now()));
    }
}
