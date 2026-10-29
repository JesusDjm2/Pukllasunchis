<?php

namespace App\Models\SeguimientoEgresado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campana extends Model
{
    use HasFactory;

    protected $table = 'egresado_campanas';

    protected $fillable = [
        'nombre',
        'codigo',
        'anio',
        'semestre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'descripcion',
    ];

    public function actualizaciones()
    {
        return $this->hasMany(Actualizacion::class, 'campana_id');
    }
}
