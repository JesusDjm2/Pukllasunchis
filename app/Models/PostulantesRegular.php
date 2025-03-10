<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostulantesRegular extends Model
{
    use HasFactory;
    protected $table = 'postulantes_regulars';
    protected $fillable = [
        'email',
        'programa',
        'estudio_beca',
        'apellidos',
        'nombres',
        'dni',
        'genero',
        'direccion',
        'numero',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'distrito_nacimiento',
        'provincia_nacimiento',
        'departamento_nacimiento',
        'contacto',

        'colegio',
        'codigo_colegio',
        'gestion_colegio',
        'direccion_colegio',
        'distrito_colegio',
        'provincia_colegio',
        'departamento_colegio',
        'ano_termino_colegio',
        'promedio_colegio',
        'lengua_1',
        'lengua_2',
        'estado_civil',
        'num_hijos',
        'trabajas',
        'donde_trabajas',
        'cargo_trabajas',
        'describe_eespp',
        
        'dni_pdf',
        'partida_nacimiento_pdf',
        'certificado_secundaria_pdf',
        'foto',
        'declaracion_jurada_salud_pdf',
        'declaracion_jurada_documentos_pdf',
        'declaracion_jurada_conectividad_pdf',
        'voucher_pago',
        'declaracion_veracidad',

        'observaciones',
    ];
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'genero' => 'boolean',
        'trabajas' => 'boolean',
        'declaracion_veracidad' => 'boolean',
    ];
}
