<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkSetting extends Model
{
    use HasFactory;

    protected $table = 'spk_settings';

    protected $fillable = [
        'key',
        'value',
        'deskripsi',
    ];

    /**
     * Ambil nilai setting berdasarkan key dengan fallback default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Simpan atau update nilai setting
     */
    public static function set(string $key, mixed $value, ?string $deskripsi = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'deskripsi' => $deskripsi,
            ]
        );
    }
}
