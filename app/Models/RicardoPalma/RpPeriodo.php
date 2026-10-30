<?php

namespace App\Models\RicardoPalma;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RpPeriodo extends Model
{
    use HasFactory;

    protected $table = 'rp_periodos';

    protected $fillable = [
        'codigo',
        'anio',
        'semestre',
        'activo',
    ];

    protected $casts = [
        'anio' => 'integer',
        'activo' => 'boolean',
    ];

    public function actas(): HasMany
    {
        return $this->hasMany(RpActa::class, 'periodo_id');
    }
}
