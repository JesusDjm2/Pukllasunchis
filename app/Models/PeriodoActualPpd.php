<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoActualPpd extends Model
{
    use HasFactory;

    protected $table = 'periodo_actual_ppds';

    protected $fillable = [
        'nombre',
        'calendario',
        'fecha_inicio',
        'fecha_cierre',
        'actual',
    ];

    // Añade esta relación
    public function periodosPpd()
    {
        return $this->hasMany(PeriodoPpd::class, 'periodo_actual_ppd_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->actual) {
                // Desactivar otros períodos actuales
                static::where('id', '!=', $model->id)
                    ->where('actual', true)
                    ->update(['actual' => false]);
            }
        });

        // El período marcado como "actual" sigue en calificación: no debe tener snapshot en periodo_ppds.
        static::updating(function (PeriodoActualPpd $model) {
            if ($model->isDirty('actual') && (int) $model->actual === 1) {
                PeriodoPpd::where('periodo_actual_ppd_id', $model->id)->delete();
            }
        });

        static::created(function (PeriodoActualPpd $model) {
            if ((int) $model->actual === 1) {
                PeriodoPpd::where('periodo_actual_ppd_id', $model->id)->delete();
            }
        });
    }
}
