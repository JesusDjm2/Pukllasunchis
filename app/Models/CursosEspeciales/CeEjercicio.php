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
        'opciones', 'respuesta_correcta', 'audio_url', 'puntaje_max', 'orden',
    ];

    protected $casts = ['opciones' => 'array'];

    /**
     * URL relativa del audio, independiente de APP_URL.
     * Funciona en local y producción sin reconfiguración.
     */
    public function getAudioSrcAttribute(): ?string
    {
        if (!$this->audio_url) return null;
        if (str_starts_with($this->audio_url, 'gdrive:')) {
            return '/ce/audio/' . substr($this->audio_url, 7);
        }
        if (str_starts_with($this->audio_url, 'http')) {
            return parse_url($this->audio_url, PHP_URL_PATH);
        }
        return '/storage/' . $this->audio_url;
    }

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
