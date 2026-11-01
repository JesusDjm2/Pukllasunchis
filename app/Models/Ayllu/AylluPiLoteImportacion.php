<?php

namespace App\Models\Ayllu;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AylluPiLoteImportacion extends Model
{
    protected $table = 'ayllu_pi_lotes_importacion';

    protected $fillable = [
        'codigo_lote',
        'version_manifiesto',
        'estado',
        'total_entradas',
        'total_anomalias',
        'archivos_fuente',
        'resumen_conciliacion',
        'importado_por_user_id',
        'confirmado_at',
    ];

    protected $casts = [
        'archivos_fuente' => 'array',
        'resumen_conciliacion' => 'array',
        'confirmado_at' => 'datetime',
    ];

    public function importador()
    {
        return $this->belongsTo(User::class, 'importado_por_user_id');
    }

    public function definiciones()
    {
        return $this->hasMany(AylluPiDefinicion::class, 'lote_importacion_id');
    }

    public function aportes()
    {
        return $this->hasMany(AylluPiAporteCurso::class, 'lote_importacion_id');
    }
}
