<?php

namespace App\Models\CursosEspeciales;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CeProgresoAlumno extends Model
{
    public $timestamps = false;

    protected $table = 'ce_progreso_alumno';

    protected $fillable = [
        'user_id', 'ce_leccion_id', 'ce_ejercicio_id',
        'completado', 'puntaje', 'intentos', 'updated_at',
    ];

    protected $casts = [
        'completado'  => 'boolean',
        'updated_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leccion()
    {
        return $this->belongsTo(CeLeccion::class, 'ce_leccion_id');
    }

    public function ejercicio()
    {
        return $this->belongsTo(CeEjercicio::class, 'ce_ejercicio_id');
    }

    public static function marcarLeccionCompletada(int $userId, int $leccionId): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'ce_leccion_id' => $leccionId],
            ['completado' => true, 'updated_at' => now()]
        );
    }

    public static function registrarEjercicio(int $userId, int $ejercicioId, int $puntaje): void
    {
        $progreso = static::firstOrNew(
            ['user_id' => $userId, 'ce_ejercicio_id' => $ejercicioId]
        );
        $progreso->completado  = true;
        $progreso->puntaje     = $puntaje;
        $progreso->intentos    = ($progreso->intentos ?? 0) + 1;
        $progreso->updated_at  = now();
        $progreso->save();
    }
}
