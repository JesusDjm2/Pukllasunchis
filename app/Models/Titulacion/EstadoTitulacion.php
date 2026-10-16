<?php

namespace App\Models\Titulacion;

use Illuminate\Database\Eloquent\Model;

class EstadoTitulacion extends Model
{
    protected $table = 'estados_titulacion';

    protected $fillable = ['clave', 'nombre', 'descripcion', 'orden'];

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoTitulacion::class, 'estado_titulacion_id');
    }

    /** id del estado por su clave (ver AvanceTitulacion::ESTADOS), cacheado por request. */
    public static function idPorClave(string $clave): ?int
    {
        static $ids = null;
        $ids ??= static::pluck('id', 'clave')->all();

        return $ids[$clave] ?? null;
    }
}
