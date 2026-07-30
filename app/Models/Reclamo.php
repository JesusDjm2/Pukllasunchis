<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reclamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_reclamo',
        'nombre',
        'dni',
        'domicilio',
        'telefono',
        'correo',
        'condicion_reclamante',
        'programa',
        'ciclo',
        'codigo_estudiante',
        'tipo_servicio',
        'descripcion_servicio',
        'area_involucrada',
        'servicio_contratado',
        'tipo_reclamacion',
        'descripcion_hechos',
        'pedido',
        'adjunto',
        'declara_informacion_verdadera',
        'autoriza_tratamiento_datos',
        'confirma_lectura_libro',
    ];

    protected $casts = [
        'declara_informacion_verdadera' => 'boolean',
        'autoriza_tratamiento_datos' => 'boolean',
        'confirma_lectura_libro' => 'boolean',
    ];
}
