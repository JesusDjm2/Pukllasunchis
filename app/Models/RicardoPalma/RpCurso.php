<?php

namespace App\Models\RicardoPalma;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RpCurso extends Model
{
    use HasFactory;

    protected $table = 'rp_cursos';

    protected $fillable = [
        'programa_id',
        'numero_semestre',
        'codigo',
        'nombre',
        'creditos',
        'horas',
    ];

    protected $casts = [
        'numero_semestre' => 'integer',
        'creditos' => 'integer',
        'horas' => 'integer',
    ];

    public function programa(): BelongsTo
    {
        return $this->belongsTo(RpPrograma::class, 'programa_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(RpCalificacion::class, 'curso_id');
    }
}
