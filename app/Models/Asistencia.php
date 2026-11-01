<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'curso_id',
        'docente_id',
        'periodo_actual_id',
        'fecha',
        'tema',
        'observaciones',
        'created_by',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    public function periodoActual()
    {
        return $this->belongsTo(PeriodoActual::class, 'periodo_actual_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function detalles()
    {
        return $this->hasMany(AsistenciaDetalle::class, 'asistencia_id');
    }

    /* ── Métricas y contadores de la sesión ── */

    public function getTotalAlumnosAttribute(): int
    {
        return $this->detalles->count();
    }

    public function getPresentesCountAttribute(): int
    {
        return $this->detalles->where('estado', 'presente')->count();
    }

    public function getTardanzasCountAttribute(): int
    {
        return $this->detalles->where('estado', 'tardanza')->count();
    }

    public function getFaltasCountAttribute(): int
    {
        return $this->detalles->where('estado', 'falta')->count();
    }

    public function getJustificadosCountAttribute(): int
    {
        return $this->detalles->where('estado', 'justificado')->count();
    }

    public function getLicenciasCountAttribute(): int
    {
        return $this->detalles->where('estado', 'licencia')->count();
    }

    public function getPorcentajeAsistenciaAttribute(): float
    {
        $total = $this->total_alumnos;
        if ($total === 0) {
            return 0.0;
        }

        // Presentes + tardanzas se consideran asistencia efectiva
        $asistieron = $this->presentes_count + $this->tardanzas_count;
        return round(($asistieron / $total) * 100, 1);
    }
}
