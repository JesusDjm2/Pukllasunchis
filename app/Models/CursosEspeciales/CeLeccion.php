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
        'contenido_texto', 'archivo_url', 'video_url', 'duracion_min', 'orden',
    ];

    /** URL resuelta del audio (Drive, local o externa). */
    public function getArchivoSrcAttribute(): ?string
    {
        if (!$this->archivo_url) return null;
        if (str_starts_with($this->archivo_url, 'gdrive:')) {
            return '/ce/audio/' . substr($this->archivo_url, 7);
        }
        if (str_starts_with($this->archivo_url, 'http')) {
            return $this->archivo_url;
        }
        return '/storage/' . $this->archivo_url;
    }

    /** Tipo inferido del contenido presente (texto | audio | video | mixto). */
    public function getTipoDisplayAttribute(): string
    {
        $t = $this->tipo;
        if ($t) return $t;
        $has = array_filter([
            $this->contenido_texto ? 'texto' : null,
            $this->archivo_url    ? 'audio' : null,
            $this->video_url      ? 'video' : null,
        ]);
        return count($has) > 1 ? 'mixto' : (reset($has) ?: 'texto');
    }

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
