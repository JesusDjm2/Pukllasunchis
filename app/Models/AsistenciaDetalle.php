<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaDetalle extends Model
{
    use HasFactory;

    protected $table = 'asistencia_detalles';

    protected $fillable = [
        'asistencia_id',
        'user_id',
        'alumno_id',
        'estado',
        'en_licencia',
        'es_beca',
        'tipo_beca',
        'origen_beca',
        'observacion',
    ];

    protected $casts = [
        'en_licencia' => 'boolean',
        'es_beca' => 'boolean',
    ];

    public function asistencia()
    {
        return $this->belongsTo(Asistencia::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    /** Nombre completo del estudiante con fallback */
    public function getNombreCompletoAttribute(): string
    {
        if ($this->alumno) {
            return trim($this->alumno->apellidos . ', ' . $this->alumno->nombres);
        }
        if ($this->user) {
            return trim(($this->user->apellidos ?? '') . ', ' . ($this->user->name ?? ''));
        }
        return 'Estudiante #' . $this->user_id;
    }

    /** Etiqueta formateada del estado para vistas */
    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'presente' => 'Presente',
            'tardanza' => 'Tardanza',
            'falta' => 'Falta',
            'justificado' => 'Justificado',
            'licencia' => 'Licencia',
            default => ucfirst($this->estado),
        };
    }

    /** Badge CSS para el estado */
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'presente' => 'badge-success',
            'tardanza' => 'badge-warning',
            'falta' => 'badge-danger',
            'justificado' => 'badge-info',
            'licencia' => 'badge-purple',
            default => 'badge-secondary',
        };
    }
}
