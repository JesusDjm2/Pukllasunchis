<?php

namespace App\Models\Titulacion;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{
    protected $table = 'titulacion_importaciones';

    protected $fillable = [
        'lote_importacion', 'origen', 'archivo_nombre', 'fila_num',
        'dni', 'nombre_estudiante', 'nombre_normalizado', 'user_id',
        'estado_match', 'datos_originales', 'datos_normalizados', 'importado_por',
    ];

    protected $casts = [
        'datos_originales' => 'array',
        'datos_normalizados' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function importadoPor()
    {
        return $this->belongsTo(User::class, 'importado_por');
    }
}
