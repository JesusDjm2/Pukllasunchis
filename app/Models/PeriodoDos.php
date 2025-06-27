<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoDos extends Model
{
    protected $table = 'periodo_dos';
    use HasFactory;
    protected $fillable = [
        'nombre',
        'fecha',
        'comp1',
        'comp2',
        'comp3',
        'valoracion_1',
        'valoracion_2',
        'valoracion_3',
        'valoracion_curso',
        'calificacion_curso',
        'calificacion_sistema',
        'observaciones',
        'alumno_id',
        'curso_id',
        'calificacion_id',
    ];
    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
    public function calificacion()
    {
        return $this->belongsTo(Calificacion::class, 'calificacion_id');
    }
}
