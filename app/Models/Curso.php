<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'cc', 'horas', 'creditos', 'ciclo_id', 'silabo', 'classroom', 'clave'];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'curso_docente');
    }
    public function competencias()
    {
        return $this->belongsToMany(Competencia::class);
    }
    public function competenciasSeleccionadas()
    {
        return $this->belongsToMany(Competencia::class, 'curso_competencia_seleccionada');
    }
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }
    public function periodos()
    {
        return $this->hasMany(PeriodoUno::class);
    }
    public function periodo()
    {
        return $this->hasMany(Periodo::class);
    }
}
