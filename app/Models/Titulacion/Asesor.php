<?php

namespace App\Models\Titulacion;

use Illuminate\Database\Eloquent\Model;

/**
 * Asesor/jurado registrado solo para Titulación (externo o docente sin cuenta
 * en el sistema). No inicia sesión: es un nombre más en los selects, junto a
 * los usuarios con rol docente. En los formularios su valor va como "a{id}"
 * para distinguirlo del id de un docente (ver TieneAsesor).
 */
class Asesor extends Model
{
    protected $table = 'titulacion_asesores';

    protected $fillable = ['nombres', 'apellidos', 'dni', 'email', 'telefono', 'institucion', 'activo', 'created_by', 'updated_by'];

    protected $casts = ['activo' => 'boolean'];

    public function nombreCompleto(): string
    {
        return trim($this->nombres.' '.$this->apellidos);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
