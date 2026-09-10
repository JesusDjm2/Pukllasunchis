<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BolsaTrabajoOferta extends Model
{
    use HasFactory;

    /**
     * Días que una oferta se considera vigente después de su fecha de publicación.
     */
    public const DIAS_VIGENCIA = 20;

    protected $table = 'bolsa_trabajo_ofertas';

    protected $fillable = [
        'nombre',
        'detalles',
        'imagen',
        'fecha_publicacion',
        'nombre_publicador',
        'telefono_publicador',
        'relacion_publicador',
        'numero_correo',
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
        static::creating(function (BolsaTrabajoOferta $model) {
            $now = Carbon::now();
            $model->fecha_publicacion = $model->fecha_publicacion ?? $now->copy()->startOfDay();
            $model->anio = $model->anio ?? (int) $now->year;
            $model->mes = $model->mes ?? (int) $now->month;
        });
    }

    public function fechaLimiteVigencia(): ?Carbon
    {
        return $this->fecha_publicacion?->copy()->addDays(self::DIAS_VIGENCIA);
    }

    /**
     * Vigente = hoy está entre la fecha de publicación y los 20 días posteriores.
     * Ya no es un campo manual: se calcula siempre a partir de fecha_publicacion.
     */
    protected function vigente(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fecha_publicacion !== null
                && now()->startOfDay()->between(
                    $this->fecha_publicacion->copy()->startOfDay(),
                    $this->fecha_publicacion->copy()->addDays(self::DIAS_VIGENCIA)->endOfDay()
                ),
        );
    }
}
