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
     * Ventanas de fecha para que el docente registre calificaciones. Un campo
     * en null no restringe ese lado de la ventana (fail-open).
     */
    public function parcial1Habilitado(): bool
    {
        return $this->dentroDeVentana($this->calificaciones_parcial1_inicio, $this->calificaciones_parcial1_cierre);
    }

    public function parcial2DesempenoHabilitado(): bool
    {
        return $this->dentroDeVentana($this->calificaciones_parcial2_inicio, $this->calificaciones_parcial2_cierre);
    }

    /**
     * Visibilidad de notas para el alumno: solo mira la fecha de inicio, no la
     * de cierre — una vez que empieza a verse una nota, se queda visible aunque
     * ya haya cerrado la ventana en la que el docente puede editarla.
     */
    public function parcial1Visible(): bool
    {
        return ! $this->calificaciones_parcial1_inicio || now()->greaterThanOrEqualTo($this->calificaciones_parcial1_inicio);
    }

    public function parcial2DesempenoVisible(): bool
    {
        return ! $this->calificaciones_parcial2_inicio || now()->greaterThanOrEqualTo($this->calificaciones_parcial2_inicio);
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
