<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'periodo_actual_id',
        'fecha_completado',
        'estado',
        'comprobante',
        'voucher_verificado',
        'voucher_verificado_at',
        'ficha_enviada_at',
        'programa_id',
        'ciclo_id',
        'condicion',
    ];

    protected $casts = [
        'fecha_completado' => 'datetime',
        'voucher_verificado' => 'boolean',
        'voucher_verificado_at' => 'datetime',
        'ficha_enviada_at' => 'datetime',
    ];

    /**
     * Foto de en qué programa/ciclo/condición estaba el alumno AL matricularse: users.ciclo_id
     * se sobrescribe cada periodo, así que es lo único que permite reconstruir su trayectoria
     * (p. ej. detectar que repitió un ciclo). Se fija al crear y no se toca en updates.
     */
    protected static function booted(): void
    {
        static::creating(function (Matricula $m) {
            $user = $m->alumno?->user;
            $m->ciclo_id ??= $user?->ciclo_id ?? $m->alumno?->ciclo_id;
            $m->programa_id ??= $user?->programa_id ?? $m->alumno?->programa_id;
            $m->condicion ??= $user?->condicion;
        });
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function periodoActual()
    {
        return $this->belongsTo(PeriodoActual::class);
    }
}
