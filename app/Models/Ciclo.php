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
    public function cursos()
    {
        return $this->hasMany(Curso::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
