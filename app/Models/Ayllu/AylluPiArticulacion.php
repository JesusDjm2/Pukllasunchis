<?php

namespace App\Models\Ayllu;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiArticulacion extends Model
{
    protected $table = 'ayllu_pi_articulaciones';

    protected $fillable = [
        'ejecucion_id',
        'aporte_origen_id',
        'aporte_destino_id',
        'insumo_requerido',
        'criterio_articulacion',
        'fecha_acordada',
        'estado_revision',
        'acordado_por_user_id',
        'validado_por_user_id',
        'validado_at',
        'observaciones_revision',
    ];

    protected $casts = [
        'fecha_acordada' => 'date',
        'validado_at' => 'datetime',
    ];

    public function ejecucion()
    {
        return $this->belongsTo(AylluPiEjecucion::class, 'ejecucion_id');
    }

    public function aporteOrigen()
    {
        return $this->belongsTo(AylluPiAporteCurso::class, 'aporte_origen_id');
    }

    public function aporteDestino()
    {
        return $this->belongsTo(AylluPiAporteCurso::class, 'aporte_destino_id');
    }

    public function acordadoPor()
    {
        return $this->belongsTo(User::class, 'acordado_por_user_id');
    }

    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por_user_id');
    }
}
