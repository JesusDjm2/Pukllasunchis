<?php

namespace App\Models\RicardoPalma;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RpEstudiante extends Model
{
    use HasFactory;

    protected $table = 'rp_estudiantes';

    protected $fillable = [
        'programa_id',
        'numero_matricula',
        'dni',
        'apellidos',
        'nombres',
        'sexo',
        'telefono',
        'anio_ingreso',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'anio_ingreso' => 'integer',
    ];

    public function programa(): BelongsTo
    {
        return $this->belongsTo(RpPrograma::class, 'programa_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(RpCalificacion::class, 'estudiante_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->apellidos}, {$this->nombres}");
    }
}
