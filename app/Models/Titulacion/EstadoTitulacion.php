<?php

namespace App\Models\Titulacion;

use Illuminate\Database\Eloquent\Model;

class EstadoTitulacion extends Model
{
    protected $table = 'estados_titulacion';

    protected $fillable = ['nombre', 'descripcion', 'orden'];

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoTitulacion::class, 'estado_titulacion_id');
    }
}
