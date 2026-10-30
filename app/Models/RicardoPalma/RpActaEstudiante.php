<?php

namespace App\Models\RicardoPalma;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Condición y observación de un estudiante en un acta (lo que no son notas). */
class RpActaEstudiante extends Model
{
    protected $table = 'rp_acta_estudiante';

    protected $fillable = [
        'acta_id',
        'estudiante_id',
        'condicion',
        'observacion',
    ];

    public function acta(): BelongsTo
    {
        return $this->belongsTo(RpActa::class, 'acta_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(RpEstudiante::class, 'estudiante_id');
    }
}
