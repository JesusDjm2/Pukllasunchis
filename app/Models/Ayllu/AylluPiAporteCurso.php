<?php

namespace App\Models\Ayllu;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiAporteCurso extends Model
{
    protected $table = 'ayllu_pi_aportes_curso';

    protected $fillable = [
        'definicion_id',
        'ejecucion_id',
        'lote_importacion_id',
        'posicion_global',
        'posicion_ciclo',
        'anio_formativo',
        'ciclo_romano',
        'ciclo_orden',
        'agrupacion_programa_original',
        'agrupacion_programa_codigo',
        'es_aporte_comun',
        'curso_fuente_original',
        'curso_fuente_normalizado',
        'competencias_fuente_bruto',
        'competencias_fuente_mostrar',
        'competencias_formato_excel',
        'es_anomalia_competencia',
        'detalle_anomalia_competencia',
        'estrategias_fuente_original',
        'estrategias_mostrar',
        'producto_previsto_original',
        'producto_previsto_mostrar',
        'producto_integrador_fuente',
        'anotaciones_autor_historico',
        'observaciones_calidad',
        'archivo_fuente',
        'hoja_fuente',
        'celda_curso',
        'celda_competencias',
        'celda_estrategias',
        'celda_producto',
        'celda_programa',
        'curso_institucional_id',
        'correspondencia_sugerida_json',
        'estado_correspondencia',
        'ajuste_operativo_mesa',
        'estado_desarrollo',
        'version_integrada_id',
        'integrado_por_user_id',
        'integrado_at',
        'nota_integracion_anual',
    ];

    protected $casts = [
        'es_aporte_comun' => 'boolean',
        'es_anomalia_competencia' => 'boolean',
        'detalle_anomalia_competencia' => 'array',
        'anotaciones_autor_historico' => 'array',
        'observaciones_calidad' => 'array',
        'correspondencia_sugerida_json' => 'array',
        'integrado_at' => 'datetime',
    ];

    public const ESTADOS_DESARROLLO = [
        'sin_registrar' => [
            'label' => 'Sin registrar',
            'badge' => 'secondary',
            'quechua' => 'Manaraq qillqasqa',
        ],
        'en_desarrollo' => [
            'label' => 'En desarrollo',
            'badge' => 'info',
            'quechua' => 'Rurayllapiraq',
        ],
        'para_revision_compartida' => [
            'label' => 'Para revisión compartida',
            'badge' => 'warning',
            'quechua' => 'Kuska qhawarinapaq',
        ],
        'revisado' => [
            'label' => 'Revisado',
            'badge' => 'primary',
            'quechua' => 'Qhawarisqaña',
        ],
        'integrado_al_producto_anual' => [
            'label' => 'Integrado al producto anual',
            'badge' => 'success',
            'quechua' => 'Hatun rurayman tinkisqa',
        ],
    ];

    public function definicion()
    {
        return $this->belongsTo(AylluPiDefinicion::class, 'definicion_id');
    }

    public function ejecucion()
    {
        return $this->belongsTo(AylluPiEjecucion::class, 'ejecucion_id');
    }

    public function cursoInstitucional()
    {
        return $this->belongsTo(Curso::class, 'curso_institucional_id');
    }

    public function versionesEvidencia()
    {
        return $this->hasMany(AylluPiEvidenciaVersion::class, 'aporte_curso_id')->orderByDesc('numero_version');
    }

    public function versionIntegrada()
    {
        return $this->belongsTo(AylluPiEvidenciaVersion::class, 'version_integrada_id');
    }

    public function integradoPor()
    {
        return $this->belongsTo(User::class, 'integrado_por_user_id');
    }

    public function asignacionesActivas()
    {
        return $this->hasMany(AylluPiAsignacionMesa::class, 'aporte_curso_id')->where('activo', true);
    }

    public function articulacionesEntregadas()
    {
        return $this->hasMany(AylluPiArticulacion::class, 'aporte_origen_id');
    }

    public function articulacionesRecibidas()
    {
        return $this->hasMany(AylluPiArticulacion::class, 'aporte_destino_id');
    }

    public function bitacoras()
    {
        return $this->hasMany(AylluPiBitacoraMesa::class, 'aporte_curso_id')->latest();
    }
}
