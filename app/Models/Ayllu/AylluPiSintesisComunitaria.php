<?php

namespace App\Models\Ayllu;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiSintesisComunitaria extends Model
{
    protected $table = 'ayllu_pi_sintesis_comunitarias';

    protected $fillable = [
        'ejecucion_id',
        'aporte_curso_id',
        'evidencia_version_id',
        'ciclo_romano',
        'programa_codigo',
        'titulo',
        'resumen_sintesis',
        'autoria_reconocida',
        'audiencia_objetivo',
        'estado_editorial',
        'solicitado_por_user_id',
        'revisado_por_user_id',
        'revisado_at',
        'observacion_editorial',
        'ayllu_publicacion_id',
    ];

    protected $casts = [
        'revisado_at' => 'datetime',
    ];

    public function ejecucion()
    {
        return $this->belongsTo(AylluPiEjecucion::class, 'ejecucion_id');
    }

    public function aporte()
    {
        return $this->belongsTo(AylluPiAporteCurso::class, 'aporte_curso_id');
    }

    public function evidenciaVersion()
    {
        return $this->belongsTo(AylluPiEvidenciaVersion::class, 'evidencia_version_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitado_por_user_id');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisado_por_user_id');
    }

    public function publicacionComunitaria()
    {
        return $this->belongsTo(AylluPublicacion::class, 'ayllu_publicacion_id');
    }
}
