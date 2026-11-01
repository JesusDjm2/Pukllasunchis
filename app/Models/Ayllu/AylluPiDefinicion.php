<?php

namespace App\Models\Ayllu;

use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiDefinicion extends Model
{
    protected $table = 'ayllu_pi_definiciones';

    protected $fillable = [
        'proyecto_institucional_id',
        'lote_importacion_id',
        'anio_formativo',
        'ciclos_texto',
        'version_numero',
        'codigo_fuente',
        'es_version_base_matriz',
        'estado_aprobacion',
        'denominacion_original',
        'denominacion_ciclo_par',
        'proposito_original',
        'ambito_exploracion',
        'fundamentacion_pedagogica',
        'producto_integrador',
        'competencias_declaradas',
        'desempenos_especificos',
        'hitos_documentados',
        'diferencias_documentales',
        'archivo_origen',
        'referencia_ubicacion',
        'hash_contenido',
        'bloqueada_por_uso',
        'aprobado_por_user_id',
        'aprobado_at',
    ];

    protected $casts = [
        'es_version_base_matriz' => 'boolean',
        'bloqueada_por_uso' => 'boolean',
        'desempenos_especificos' => 'array',
        'hitos_documentados' => 'array',
        'diferencias_documentales' => 'array',
        'aprobado_at' => 'datetime',
    ];

    public function proyectoInstitucional()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_institucional_id');
    }

    public function lote()
    {
        return $this->belongsTo(AylluPiLoteImportacion::class, 'lote_importacion_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por_user_id');
    }

    public function ejecuciones()
    {
        return $this->hasMany(AylluPiEjecucion::class, 'definicion_id');
    }

    public function aportes()
    {
        return $this->hasMany(AylluPiAporteCurso::class, 'definicion_id')->orderBy('posicion_global');
    }
}
