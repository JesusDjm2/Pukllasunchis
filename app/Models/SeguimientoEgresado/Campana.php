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
        'es_ensayo',
    ];

    protected $casts = [
        'es_ensayo' => 'boolean',
    ];

    public function actualizaciones()
    {
        return $this->hasMany(Actualizacion::class, 'campana_id');
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionCampana::class, 'campana_id');
    }

    public function llamadas()
    {
        return $this->hasMany(Llamada::class, 'campana_id');
    }

    public function scopeEnsayo($query)
    {
        return $query->where('es_ensayo', true);
    }

    public function scopeReales($query)
    {
        return $query->where('es_ensayo', false);
    }
}
