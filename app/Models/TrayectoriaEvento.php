<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrayectoriaEvento extends Model
{
    use SoftDeletes;

    protected $table = 'trayectoria_eventos';

    /** Reglamento: las licencias de un alumno suman como máximo 2 años ACUMULADOS (aunque estén partidas). */
    public const LICENCIA_MAX_ANIOS = 2;

    /** El mismo límite en días, fijo (sin depender de años bisiestos). */
    public const LICENCIA_MAX_DIAS = 730;

    public const TIPOS = [
        'licencia' => 'Licencia',
        'retiro' => 'Retiro',
        'inhabilitacion' => 'Inhabilitación',
        'reincorporacion' => 'Reincorporación',
        'cambio_ciclo' => 'Cambio de ciclo',
        'cambio_programa' => 'Cambio de programa',
        'egreso' => 'Egreso',
        'beca' => 'Beca',
        'sancion' => 'Sanción',
        'observacion' => 'Observación',
        'estado_inicial' => 'Estado al iniciar el registro',
    ];

    protected $fillable = [
        'user_id', 'tipo', 'periodo_actual_id', 'periodo_actual_ppd_id', 'fecha_inicio', 'fecha_fin',
        'valor_anterior', 'valor_nuevo', 'ciclo_origen_id', 'ciclo_destino_id', 'motivo', 'resolucion',
        'documento', 'origen', 'registrado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function periodoActual()
    {
        return $this->belongsTo(PeriodoActual::class);
    }

    public function periodoActualPpd()
    {
        return $this->belongsTo(PeriodoActualPpd::class);
    }

    public function cicloOrigen()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_origen_id');
    }

    public function cicloDestino()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_destino_id');
    }

    public function getEtiquetaAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst(str_replace('_', ' ', $this->tipo));
    }

    /** Licencias todavía abiertas (sin reincorporación que las cierre). */
    public function scopeLicenciasActivas($query)
    {
        return $query->where('tipo', 'licencia')->whereNull('fecha_fin');
    }
}
