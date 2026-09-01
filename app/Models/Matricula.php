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
    ];

    protected $casts = [
        'fecha_completado' => 'datetime',
        'voucher_verificado' => 'boolean',
        'voucher_verificado_at' => 'datetime',
        'ficha_enviada_at' => 'datetime',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function periodoActual()
    {
        return $this->belongsTo(PeriodoActual::class);
    }
}
