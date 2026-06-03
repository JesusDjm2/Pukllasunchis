<?php

namespace App\Models\CursosEspeciales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CeLeccion extends Model
{
    use HasFactory;

    protected $table = 'ce_lecciones';

    protected $fillable = [
        'ce_unidad_id', 'nombre', 'tipo',
        'contenido_texto', 'archivo_url', 'duracion_min', 'orden',
    ];

    public function unidad()
    {
        return $this->belongsTo(CeUnidad::class, 'ce_unidad_id');
    }

    public function progresos()
    {
        return $this->hasMany(CeProgresoAlumno::class, 'ce_leccion_id');
    }

    public function completadoPor(int $userId): bool
    {
        return $this->progresos()->where('user_id', $userId)->where('completado', true)->exists();
    }
}
