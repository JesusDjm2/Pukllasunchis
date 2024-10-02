<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;
    protected $fillable = [
        'alumno_id',
        'curso_id',
        'valoracion_1',
        'valoracion_2',
        'valoracion_3',
        'valoracion_curso',
        'calificacion_curso',
        'calificacion_sistema',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }
    public function periodos()
    {
        return $this->hasMany(PeriodoUno::class);
    }
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

}
