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
    ];

    protected $casts = [
        'actual'               => 'boolean',
        'formulario_habilitado' => 'boolean',
        'fecha_inicio'         => 'date',
        'fecha_cierre'         => 'date',
    ];

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
                        ->update(['actual' => false]);
                    $model->wasReplacing = true;
                }
            }
        });
    }
}
