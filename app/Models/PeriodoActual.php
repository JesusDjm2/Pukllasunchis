<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoActual extends Model
{
    use HasFactory;
    protected $table = 'periodo_actual';

    protected $fillable = [
        'nombre',
        'horario',
        'fecha_inicio',
        'fecha_cierre',
        'actual',
        'formulario_habilitado',
        'calificaciones_parcial1_inicio',
        'calificaciones_parcial1_cierre',
        'calificaciones_parcial2_inicio',
        'calificaciones_parcial2_cierre',
    ];

    protected $casts = [
        'actual'               => 'boolean',
        'formulario_habilitado' => 'boolean',
        'fecha_inicio'         => 'date',
        'fecha_cierre'         => 'date',
        'calificaciones_parcial1_inicio' => 'date',
        'calificaciones_parcial1_cierre' => 'date',
        'calificaciones_parcial2_inicio' => 'date',
        'calificaciones_parcial2_cierre' => 'date',
    ];

    /**
     * Los sílabos de este periodo se habilitan para los alumnos recién en la
     * 3ª semana desde que inicia (le da tiempo al docente de prepararlos).
     * Sin fecha_inicio configurada, no se bloquea nada (fail-open).
     */
    public function fechaSilabosVisibles(): ?\Carbon\Carbon
    {
        return $this->fecha_inicio?->copy()->addWeeks(3);
    }

    public function silabosVisibles(): bool
    {
        $fecha = $this->fechaSilabosVisibles();

        return $fecha === null || now()->greaterThanOrEqualTo($fecha);
    }

    /**
     * El docente registra cada parcial desde el inicio de su ventana hasta el CIERRE DEL
     * PERÍODO: el cierre de la ventana ya no lo bloquea, solo marca cuándo se publican las
     * notas al alumno (fechaNotasVisibles). Un campo en null no restringe ese lado (fail-open).
     */
    public function parcial1Habilitado(): bool
    {
        return $this->dentroDeVentana($this->calificaciones_parcial1_inicio, $this->fecha_cierre);
    }

    public function parcial2DesempenoHabilitado(): bool
    {
        return $this->dentroDeVentana($this->calificaciones_parcial2_inicio, $this->fecha_cierre);
    }

    /** Ya pasó el cierre de la ventana: las notas del parcial están publicadas al alumno. */
    public function notasPublicadas(int $parcial): bool
    {
        $desde = $this->fechaNotasVisibles($parcial);

        return $desde !== null && now()->greaterThanOrEqualTo($desde);
    }

    /**
     * Visibilidad de notas para el alumno: las ve recién al CERRARSE la ventana en la que el
     * docente las registra (mientras está abierta todavía puede corregirlas). Sin fecha de
     * cierre se usa la de inicio; sin ninguna fecha, se ven siempre (fail-open).
     */
    public function parcial1Visible(): bool
    {
        $desde = $this->fechaNotasVisibles(1);

        return ! $desde || now()->greaterThanOrEqualTo($desde);
    }

    public function parcial2DesempenoVisible(): bool
    {
        $desde = $this->fechaNotasVisibles(2);

        return ! $desde || now()->greaterThanOrEqualTo($desde);
    }

    /** Desde cuándo el alumno ve las notas: el día siguiente al cierre de la ventana. */
    public function fechaNotasVisibles(int $parcial): ?\Carbon\Carbon
    {
        [$inicio, $cierre] = $parcial === 1
            ? [$this->calificaciones_parcial1_inicio, $this->calificaciones_parcial1_cierre]
            : [$this->calificaciones_parcial2_inicio, $this->calificaciones_parcial2_cierre];

        return $cierre ? $cierre->copy()->addDay()->startOfDay() : $inicio?->copy();
    }

    /**
     * Estado de una ventana de calificación para mostrarlo en listados: 'libre' (sin fechas),
     * 'proximo', 'abierto', 'publicado' (notas visibles al alumno, el docente aún corrige)
     * o 'cerrado' (cerró el período). Misma regla que parcial1Habilitado().
     */
    public function estadoVentanaCalificacion(int $parcial): string
    {
        [$inicio, $cierre] = $parcial === 1
            ? [$this->calificaciones_parcial1_inicio, $this->calificaciones_parcial1_cierre]
            : [$this->calificaciones_parcial2_inicio, $this->calificaciones_parcial2_cierre];

        if (! $inicio && ! $cierre) {
            return 'libre';
        }
        if ($inicio && now()->lt($inicio)) {
            return 'proximo';
        }

        if (! $this->dentroDeVentana($inicio, $this->fecha_cierre)) {
            return 'cerrado';
        }

        return $this->notasPublicadas($parcial) ? 'publicado' : 'abierto';
    }

    private function dentroDeVentana(?\Carbon\Carbon $inicio, ?\Carbon\Carbon $cierre): bool
    {
        $ahora = now();

        if ($inicio && $ahora->lt($inicio)) {
            return false;
        }

        if ($cierre && $ahora->gt($cierre->copy()->endOfDay())) {
            return false;
        }

        return true;
    }

    public function periodos()
    {
        return $this->hasMany(Periodo::class);
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class);
    }

    /**
     * Obtener el periodo actual con caché
     */
    public static function actual(): ?self
    {
        return cache()->remember('periodo_actual_fid', 3600, function () {
            return self::where('actual', true)->first();
        });
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($model) {
            cache()->forget('periodo_actual_fid');
        });
        static::saving(function ($model) {
            if ($model->actual) {
                $exists = static::where('id', '!=', $model->id)
                    ->where('actual', true)
                    ->exists();
                if ($exists) {
                    static::where('id', '!=', $model->id)
                        ->update(['actual' => false, 'formulario_habilitado' => false]);
                    $model->wasReplacing = true;
                }
            }
        });
    }
}
