<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacionesppd extends Model
{
    use HasFactory;
    protected $table = 'calificacionesppds';

    protected $fillable = [ 
        'nombre',
        'fecha',
        
        //Relaciones
        'ppd_id',
        'curso_id',
        
        'comp1',
        'comp2',
        'comp3', 
        // Productos de Proceso - Competencia 1
        'pp_c1_1',
        'pp_c1_2',
        'pp_c1_3',
        'pp_c1_4',

        // Productos de Proceso - Competencia 2
        'pp_c2_1',
        'pp_c2_2',
        'pp_c2_3',
        'pp_c2_4',

        // Productos de Proceso - Competencia 3
        'pp_c3_1',
        'pp_c3_2',
        'pp_c3_3',
        'pp_c3_4',

        // Producto Final - Competencia 1
        'pf_c1_1',
        'pf_c1_2',
        'pf_c1_3',

        // Producto Final - Competencia 2
        'pf_c2_1',
        'pf_c2_2',
        'pf_c2_3',

        // Producto Final - Competencia 3
        'pf_c3_1',
        'pf_c3_2',
        'pf_c3_3',

        // Promedios Generales - Competencia 1
        'pg_c1_1',
        'pg_c1_2',
        'pg_c1_3',

        // Promedios Generales - Competencia 2
        'pg_c2_1',
        'pg_c2_2',
        'pg_c2_3',

        // Promedios Generales - Competencia 3
        'pg_c3_1',
        'pg_c3_2',
        'pg_c3_3',

        // Evaluaciones finales
        'nivel_desempeno',
        'calificacion_curso',
        'calificacion_sistema',

        'observaciones',
    ];

    // Relaciones
    public function ppd()
    {
        return $this->belongsTo(ppd::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
