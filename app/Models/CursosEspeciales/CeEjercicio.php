<?php

namespace App\Models\CursosEspeciales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CeEjercicio extends Model
{
    use HasFactory;

    protected $table = 'ce_ejercicios';

    protected $fillable = [
        'ce_unidad_id', 'tipo', 'pregunta',
        'opciones', 'respuesta_correcta', 'puntaje_max', 'orden',
    ];

    protected $casts = ['opciones' => 'array'];

    public function unidad()
    {
        return $this->belongsTo(CeUnidad::class, 'ce_unidad_id');
    }

    public function progresos()
    {
        return $this->hasMany(CeProgresoAlumno::class, 'ce_ejercicio_id');
    }

    public function esCorrecta(string $respuesta): bool
    {
        return trim(strtolower($respuesta)) === trim(strtolower($this->respuesta_correcta));
    }
}
