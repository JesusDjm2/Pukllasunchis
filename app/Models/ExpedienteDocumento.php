<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Archivo del expediente de un estudiante. Vive en el disco local (storage/app/expedientes,
 * fuera de public/): solo se descarga por ExpedienteController con sesión de admin.
 */
class ExpedienteDocumento extends Model
{
    use SoftDeletes;

    protected $table = 'expediente_documentos';

    protected $fillable = [
        'user_id', 'trayectoria_evento_id', 'seguimiento_accion_id', 'titulo', 'archivo',
        'nombre_original', 'mime', 'tamano', 'subido_por',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evento()
    {
        return $this->belongsTo(TrayectoriaEvento::class, 'trayectoria_evento_id');
    }

    public function accion()
    {
        return $this->belongsTo(SeguimientoAccion::class, 'seguimiento_accion_id');
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function getTamanoLegibleAttribute(): string
    {
        $b = (int) $this->tamano;

        return $b >= 1048576 ? round($b / 1048576, 1) . ' MB' : max(1, round($b / 1024)) . ' KB';
    }

    public function getEsImagenAttribute(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
