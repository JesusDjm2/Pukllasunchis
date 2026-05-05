<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comunicado extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'archivo',
        'archivo_tipo',
        'fecha_publicacion',
        'anio',
        'mes',
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'anio' => 'integer',
        'mes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Comunicado $model) {
            if ($model->fecha_publicacion) {
                $d = Carbon::parse($model->fecha_publicacion);
                $model->anio = (int) $d->year;
                $model->mes = (int) $d->month;
            }
        });
    }

    public function esImagen(): bool
    {
        return $this->archivo_tipo === 'imagen';
    }

    public function esPdf(): bool
    {
        return $this->archivo_tipo === 'pdf';
    }
}
