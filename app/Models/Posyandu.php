<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Posyandu extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'wilayah',
    ];

    public function users(): HasMany
    {
        return $table = $this->hasMany(User::class);
    }

    public function anaks(): HasMany
    {
        return $this->hasMany(Anak::class);
    }
}
