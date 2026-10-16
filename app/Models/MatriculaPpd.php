<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriculaPpd extends Model
{
    use HasFactory;

    protected $table = 'matriculas_ppd';

    protected $fillable = [
        'ppd_id',
        'periodo_actual_ppd_id',
        'semestre',
        'fecha_completado',
        'estado',
        'comprobante',
        'voucher_imagen',
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

    /** Foto de programa/ciclo/condición al matricularse (ver Matricula::booted). */
    protected static function booted(): void
    {
        static::creating(function (MatriculaPpd $m) {
            // PPD se matricula por semestre dentro de un periodo anual: si no se indica, el que
            // tenga abierto el periodo (periodo_actual_ppds.semestre_matricula).
            $m->semestre ??= $m->periodoActualPpd?->semestre_matricula ?? 1;
            $user = $m->ppd?->user;
            $m->ciclo_id ??= $user?->ciclo_id ?? $m->ppd?->ciclo_id;
            $m->programa_id ??= $user?->programa_id ?? $m->ppd?->programa_id;
            $m->condicion ??= $user?->condicion;
        });
    }

    public function getSemestreEtiquetaAttribute(): string
    {
        return ($this->semestre ?? 1) == 2 ? '2.º semestre' : '1.er semestre';
    }

    public function ppd()
    {
        return $this->belongsTo(ppd::class, 'ppd_id');
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function periodoActualPpd()
    {
        return $this->belongsTo(PeriodoActualPpd::class);
    }
}
