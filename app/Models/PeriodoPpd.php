<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoPpd extends Model
{
    use HasFactory;

    protected $table = 'periodo_ppds';

    protected $fillable = [
        'periodo_actual_ppd_id',
        'alumno_id',
        'curso_id',

        'calificacion_curso',
        'calificacion_sistema',
        'nivel_desempeno',
    ];

    public function periodoActualPpd()
    {
        return $this->belongsTo(PeriodoActualPpd::class);
    }

    public function alumno()
    {
        return $this->belongsTo(ppd::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
