<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BolsaTrabajoOferta extends Model
{
    use HasFactory;

    protected $table = 'bolsa_trabajo_ofertas';

    protected $fillable = [
        'nombre',
        'detalles',
        'imagen',
        'fecha_inicio',
        'fecha_fin',
        'anio',
        'mes',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'anio' => 'integer',
        'mes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (BolsaTrabajoOferta $model) {
            if ($model->fecha_inicio) {
                $d = Carbon::parse($model->fecha_inicio);
                $model->anio = (int) $d->year;
                $model->mes = (int) $d->month;
            }
        });
    }
}
