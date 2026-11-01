<?php

namespace App\Models\Ayllu;

use App\Models\TaqeGrupo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiBitacoraMesa extends Model
{
    protected $table = 'ayllu_pi_bitacora_mesa';

    protected $fillable = [
        'ejecucion_id',
        'aporte_curso_id',
        'evidencia_version_id',
        'ciclo_romano',
        'programa_codigo',
        'tipo_entrada',
        'audiencia',
        'titulo',
        'contenido',
        'autor_user_id',
        'taqe_grupo_id',
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

    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_user_id');
    }

    public function taqeGrupo()
    {
        return $this->belongsTo(TaqeGrupo::class, 'taqe_grupo_id');
    }
}
