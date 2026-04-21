<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    protected $fillable = [
        'docente_id', 'nombre_docente', 'alumno_id', 'programa_id', 'ciclo_id',
        'fecha', 'reporte', 'imagen',
    ];

    protected $casts = ['fecha' => 'date'];

    public function docente()  { return $this->belongsTo(Docente::class); }
    public function alumno()   { return $this->belongsTo(Alumno::class); }
    public function programa() { return $this->belongsTo(Programa::class); }
    public function ciclo()    { return $this->belongsTo(Ciclo::class); }
}
