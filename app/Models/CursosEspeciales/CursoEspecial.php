<?php

namespace App\Models\CursosEspeciales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CursoEspecial extends Model
{
    use HasFactory;

    protected $table = 'cursos_especiales';

    protected $fillable = ['nombre', 'descripcion', 'imagen', 'activo', 'orden', 'docente_id'];

    protected $casts = ['activo' => 'boolean'];

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? Storage::disk('uploads')->url($this->imagen) : null;
    }

    public function docente()
    {
        return $this->belongsTo(\App\Models\Docente::class, 'docente_id');
    }

    public function niveles()
    {
        return $this->hasMany(CeNivel::class, 'curso_especial_id')->orderBy('orden');
    }

    public function inscripciones()
    {
        return $this->hasMany(CeInscripcion::class, 'curso_especial_id');
    }
}
