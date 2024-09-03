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
    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }
    public function competencias()
    {
        return $this->belongsToMany(Competencia::class);
    }
}
