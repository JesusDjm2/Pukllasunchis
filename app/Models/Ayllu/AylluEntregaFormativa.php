<?php

namespace App\Models\Ayllu;

use App\Models\Curso;
use App\Models\Proyecto;
use App\Models\Silabo;
use App\Models\TaqeGrupo;
use App\Models\TaqeTrabajo;
use App\Models\Unidades;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Plano 2: Evaluación y Retroalimentación Formativa.
 * Estrictamente separada de las actas oficiales de calificación (calificacions / periodouno / etc.).
 */
class AylluEntregaFormativa extends Model
{
    use HasFactory;

    protected $table = 'ayllu_entregas_formativas';

    protected $fillable = [
        'curso_id',
        'silabo_id',
        'unidad_id',
        'proyecto_id',
        'taqe_grupo_id',
        'taqe_trabajo_id',
        'user_id',
        'titulo',
        'descripcion_reflexion',
        'enlace_evidencia',
        'autoevaluacion_nivel',
        'autoevaluacion_comentario',
        'es_aporte_proyecto_integrador',
        'en_portafolio',
        'reflexion_portafolio',
        'estado',
    ];

    protected $casts = [
        'es_aporte_proyecto_integrador' => 'boolean',
        'en_portafolio' => 'boolean',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function silabo()
    {
        return $this->belongsTo(Silabo::class);
    }

    public function unidad()
    {
        return $this->belongsTo(Unidades::class, 'unidad_id');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function taqeGrupo()
    {
        return $this->belongsTo(TaqeGrupo::class, 'taqe_grupo_id');
    }

    public function taqeTrabajo()
    {
        return $this->belongsTo(TaqeTrabajo::class, 'taqe_trabajo_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function retroalimentaciones()
    {
        return $this->hasMany(AylluRetroalimentacion::class, 'ayllu_entrega_formativa_id')->orderBy('created_at');
    }

    public static function nivelesFormativos(): array
    {
        return [
            'en_inicio'   => ['nombre' => 'En inicio · Qallariyninpi', 'badge' => 'warning'],
            'en_proceso'  => ['nombre' => 'En proceso · Puririyninpi', 'badge' => 'info'],
            'logrado'     => ['nombre' => 'Logrado · Aypasqa', 'badge' => 'success'],
            'destacado'   => ['nombre' => 'Destacado · Ñawpaqman puriq', 'badge' => 'primary'],
        ];
    }
}
