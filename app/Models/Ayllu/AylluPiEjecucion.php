<?php

namespace App\Models\Ayllu;

use App\Models\PeriodoActual;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiEjecucion extends Model
{
    protected $table = 'ayllu_pi_ejecuciones';

    protected $fillable = [
        'definicion_id',
        'proyecto_institucional_id',
        'anio_lectivo',
        'periodo_actual_id',
        'cohorte',
        'anio_formativo',
        'ciclo_impar_romano',
        'ciclo_par_romano',
        'estado',
        'fechas_hitos_operativos',
        'coordinador_user_id',
        'adoptado_por_user_id',
        'adoptado_at',
    ];

    protected $casts = [
        'fechas_hitos_operativos' => 'array',
        'adoptado_at' => 'datetime',
    ];

    public function definicion()
    {
        return $this->belongsTo(AylluPiDefinicion::class, 'definicion_id');
    }

    public function proyectoInstitucional()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_institucional_id');
    }

    public function periodoActual()
    {
        return $this->belongsTo(PeriodoActual::class, 'periodo_actual_id');
    }

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_user_id');
    }

    public function adoptadoPor()
    {
        return $this->belongsTo(User::class, 'adoptado_por_user_id');
    }

    public function aportes()
    {
        return $this->hasMany(AylluPiAporteCurso::class, 'ejecucion_id')->orderBy('posicion_global');
    }

    public function bitacoras()
    {
        return $this->hasMany(AylluPiBitacoraMesa::class, 'ejecucion_id')->latest();
    }

    public function articulaciones()
    {
        return $this->hasMany(AylluPiArticulacion::class, 'ejecucion_id')->latest();
    }
}
