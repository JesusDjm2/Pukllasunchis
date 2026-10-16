<?php

namespace App\Models;

use App\Enums\Modalidad;
use App\Enums\NivelPrograma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'modalidad',
        'nivel',
        'eib',
    ];

    protected $casts = [
        'modalidad' => Modalidad::class,
        'nivel' => NivelPrograma::class,
        'eib' => 'boolean',
    ];

    public function ciclos()
    {
        return $this->hasMany(Ciclo::class);
    }
    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }
    public function postulantes()
    {
        return $this->hasMany(Alumno::class);
    }

    // ── Clasificación (reemplaza a buscar "PPD"/"Inicial"/"EIB" en el nombre) ──

    public function scopeFid(Builder $query): void
    {
        $query->where('modalidad', Modalidad::FID->value);
    }

    public function scopePpd(Builder $query): void
    {
        $query->where('modalidad', Modalidad::PPD->value);
    }

    public function scopeNivel(Builder $query, NivelPrograma $nivel): void
    {
        $query->where('nivel', $nivel->value);
    }

    public function scopeEib(Builder $query, bool $eib = true): void
    {
        $query->where('eib', $eib);
    }

    /** Primer programa (por id) con esa combinación exacta, o null. */
    public static function buscar(Modalidad $modalidad, NivelPrograma $nivel, bool $eib): ?self
    {
        return static::where('modalidad', $modalidad->value)
            ->where('nivel', $nivel->value)
            ->where('eib', $eib)
            ->orderBy('id')
            ->first();
    }

    public function esFid(): bool
    {
        return $this->modalidad === Modalidad::FID;
    }

    public function esPpd(): bool
    {
        return $this->modalidad === Modalidad::PPD;
    }

    public function esInicial(): bool
    {
        return $this->nivel === NivelPrograma::INICIAL;
    }

    public function esPrimaria(): bool
    {
        return $this->nivel === NivelPrograma::PRIMARIA;
    }
}
