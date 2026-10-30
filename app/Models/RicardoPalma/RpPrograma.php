<?php

namespace App\Models\RicardoPalma;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RpPrograma extends Model
{
    use HasFactory;

    protected $table = 'rp_programas';

    protected $fillable = [
        'codigo',
        'nombre',
        'nivel',
        'total_semestres',
        'activo',
    ];

    protected $casts = [
        'total_semestres' => 'integer',
        'activo' => 'boolean',
    ];

    public function cursos(): HasMany
    {
        return $this->hasMany(RpCurso::class, 'programa_id');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(RpEstudiante::class, 'programa_id');
    }

    public function actas(): HasMany
    {
        return $this->hasMany(RpActa::class, 'programa_id');
    }
}
