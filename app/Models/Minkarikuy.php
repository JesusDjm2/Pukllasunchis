<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Minkarikuy extends Model
{
    protected $fillable = ['nombre', 'fecha', 'hora', 'imagen', 'activo'];

    protected $casts = [
        'fecha'  => 'date',
        'activo' => 'boolean',
    ];

    public static function activo(): ?self
    {
        return self::where('activo', true)->latest()->first();
    }
}
