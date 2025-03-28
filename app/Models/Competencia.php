<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'descripcion',
        'capacidades',
    ];
    public function cursos()
    {
        return $this->belongsToMany(Curso::class);
    }
    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'competencia_docente');
    }
    public function cursosSeleccionados()
    {
        return $this->belongsToMany(Curso::class, 'curso_competencia_seleccionada');
    }
    public function capacidad()
    {
        return $this->hasMany(Capacidades::class, 'competencia_id');
    }
    
    public function estandares()
    {
        return $this->belongsToMany(Estandares::class, 'ciclo_competencia_estandar', 'competencia_id', 'estandar_id')
                    ->withPivot('ciclo_id')
                    ->withTimestamps();
    }
    
}
