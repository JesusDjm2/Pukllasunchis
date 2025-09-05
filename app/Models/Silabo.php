<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Silabo extends Model
{
    use HasFactory;
    protected $table = 'silabos';
    protected $fillable = [
        'curso_id',
        'nombre',
        'sumilla',
        'periodo',

        'proyecto_integrador',
        'producto_proyecto_integrador',
        'descripcion_proyecto_integrador',
        'vinculacion_pi',
        'producto_curso',

        'capacidad1',
        'desempeno1',
        'criterio1',
        'evidencia1',
        'instrumento1',
        'capacidad2',
        'desempeno2',
        'criterio2',
        'evidencia2',
        'instrumento2',
        'capacidad3',
        'desempeno3',
        'criterio3',
        'evidencia3',
        'instrumento3',

        /* 'enfoques',
        'enfoques_observables',
        'enfoques_concretos', */
        'organizacion',
        'modelos_metodologicos',
        'recursos',
        'referencias',
        'fecha1',
        'fecha2',
    ];
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
    public function enfoques()
    {
        return $this->hasMany(EnfoqueSilabo::class);
    }
    public function unidades()
    {
        return $this->hasMany(Unidades::class);
    }
    public function rubricas()
    {
        return $this->hasMany(Rubricas::class);
    }
}
