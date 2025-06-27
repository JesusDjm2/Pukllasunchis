<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'programa_id'];

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }
    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }
    public function alumnosB()
    {
        return $this->hasMany(ppd::class, 'ciclo_id');
    }
    public function cursos()
    {
        return $this->hasMany(Curso::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function estandares()
    {
        return $this->belongsToMany(Estandares::class, 'ciclo_competencia_estandar')
                    ->withPivot('competencia_id')
                    ->withTimestamps();
    }
}
