<?php

namespace App\Models\Ayllu;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiAsignacionMesa extends Model
{
    protected $table = 'ayllu_pi_asignaciones_mesa';

    protected $fillable = [
        'ejecucion_id',
        'aporte_curso_id',
        'curso_id',
        'user_id',
        'docente_id',
        'rol_mesa',
        'programa_codigo',
        'ciclo_romano',
        'activo',
        'asignado_por_user_id',
        'revocado_por_user_id',
        'revocado_at',
        'motivo_revocacion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'revocado_at' => 'datetime',
    ];

    public function ejecucion()
    {
        return $this->belongsTo(AylluPiEjecucion::class, 'ejecucion_id');
    }

    public function aporte()
    {
        return $this->belongsTo(AylluPiAporteCurso::class, 'aporte_curso_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }
}
