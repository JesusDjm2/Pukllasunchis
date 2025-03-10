<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estandares extends Model
{
    use HasFactory;
    protected $table = 'estandares';
    protected $fillable = ['descripcion'];

    // Relación con Ciclos (Muchos a Muchos)
    public function ciclos()
    {
        return $this->belongsToMany(Ciclo::class, 'ciclo_competencia_estandar', 'estandar_id', 'ciclo_id')
            ->withPivot('competencia_id')
            ->withTimestamps();
    }

    public function competencias()
    {
        return $this->belongsToMany(Competencia::class, 'ciclo_competencia_estandar', 'estandar_id', 'competencia_id')
            ->withPivot('ciclo_id')
            ->withTimestamps();
    }
}
