<?php

namespace App\Models\Ayllu;

use App\Models\Curso;
use App\Models\TaqeGrupo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiEvidenciaVersion extends Model
{
    protected $table = 'ayllu_pi_evidencia_versiones';

    protected $fillable = [
        'aporte_curso_id',
        'ejecucion_id',
        'curso_id',
        'taqe_grupo_id',
        'numero_version',
        'titulo_version',
        'descripcion_avance',
        'autoria_tipo',
        'autor_user_id',
        'docente_acompanante_user_id',
        'audiencia',
        'colaboradores_autorizados_json',
        'enlace_recurso',
        'archivo_privado_path',
        'archivo_nombre_original',
        'archivo_mime',
        'necesidades_articulacion',
        'reconocimiento_autoria_comunitaria',
        'restriccion_circulacion',
        'estado_version',
        'revisado_por_user_id',
        'revisado_at',
        'comentario_revision',
        'aportes_vinculados_json',
    ];

    protected $casts = [
        'colaboradores_autorizados_json' => 'array',
        'aportes_vinculados_json' => 'array',
        'revisado_at' => 'datetime',
    ];

    public const AUDIENCIAS = [
        'borrador_personal' => [
            'label' => 'Borrador personal',
            'descripcion' => 'Solo visible para la autoría y colaboradores expresamente elegidos.',
            'badge' => 'secondary',
        ],
        'mesa_docente_ciclo' => [
            'label' => 'Mesa docente del ciclo',
            'descripcion' => 'Docentes asignados al ciclo y coordinación autorizada (planificación, avances y acuerdos).',
            'badge' => 'info',
        ],
        'equipo_estudiantes' => [
            'label' => 'Equipo de estudiantes',
            'descripcion' => 'Integrantes del equipo y docentes acompañantes del curso/ciclo.',
            'badge' => 'primary',
        ],
        'ciclo_completo' => [
            'label' => 'Ciclo completo',
            'descripcion' => 'Orientaciones acordadas, recursos y avances publicados expresamente para el ciclo.',
            'badge' => 'success',
        ],
        'comunidad_ayllu' => [
            'label' => 'Comunidad Ayllu Puklla',
            'descripcion' => 'Síntesis y productos autorizados tras revisión editorial institucional.',
            'badge' => 'warning',
        ],
    ];

    public function aporte()
    {
        return $this->belongsTo(AylluPiAporteCurso::class, 'aporte_curso_id');
    }

    public function ejecucion()
    {
        return $this->belongsTo(AylluPiEjecucion::class, 'ejecucion_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function taqeGrupo()
    {
        return $this->belongsTo(TaqeGrupo::class, 'taqe_grupo_id');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_user_id');
    }

    public function docenteAcompanante()
    {
        return $this->belongsTo(User::class, 'docente_acompanante_user_id');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisado_por_user_id');
    }
}
