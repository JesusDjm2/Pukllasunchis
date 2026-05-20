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
    ];

    protected $casts = [
        'fecha_completado' => 'datetime',
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
