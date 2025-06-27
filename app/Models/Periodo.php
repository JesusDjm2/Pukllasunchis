<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'valoracion_curso',
        'calificacion_curso',
        'calificacion_sistema',
        'alumno_id',
        'curso_id',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
