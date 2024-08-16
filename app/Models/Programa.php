<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];
    public function ciclos()
    {
        return $this->hasMany(Ciclo::class);
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }
    public function postulantes()
    {
        return $this->hasMany(Alumno::class);
    }
}
