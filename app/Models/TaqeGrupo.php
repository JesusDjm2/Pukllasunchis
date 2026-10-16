<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaqeGrupo extends Model
{
    use HasFactory;

    protected $table = 'taqe_grupos';

    protected $fillable = [
        'nombre',
        'curso_id',
        'ciclo_id',
        'programa_id',
        'periodo_actual_id',
        'creador_alumno_id',
        'proyecto_id',
        'estado',
        'entregado_at',
    ];

    protected $casts = ['entregado_at' => 'datetime'];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    /** Proyecto Integrador del ciclo del curso (el mismo del sílabo), fijado al crear el grupo. */
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function periodoActual()
    {
        return $this->belongsTo(PeriodoActual::class, 'periodo_actual_id');
    }

    public function creador()
    {
        return $this->belongsTo(Alumno::class, 'creador_alumno_id');
    }

    /** Todos los integrantes invitados, con su estado (pendiente/aceptado/rechazado) en el pivot. */
    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'taqe_grupo_alumno')
            ->withPivot(['estado', 'respondido_at'])
            ->withTimestamps();
    }

    /** Solo los integrantes que ya aceptaron — el equipo "real" hoy. */
    public function alumnosAceptados()
    {
        return $this->alumnos()->wherePivot('estado', 'aceptado');
    }

    public function trabajos()
    {
        return $this->hasMany(TaqeTrabajo::class);
    }

    /** Avances subidos durante el proceso. */
    public function evidencias()
    {
        return $this->hasMany(TaqeTrabajo::class)->where('categoria', 'evidencia');
    }

    /**
     * Archivos y enlaces del trabajo final (pueden ser varios y de distintos formatos). Se arman
     * subiendo directo o pasando evidencias; el organizador lo entrega con entregar().
     */
    public function finales()
    {
        return $this->hasMany(TaqeTrabajo::class)->where('categoria', 'final');
    }

    public function estaEntregado(): bool
    {
        return $this->estado === 'finalizado';
    }

    public function solicitudesAyuda()
    {
        return $this->hasMany(TaqeSolicitudAyuda::class);
    }
}
