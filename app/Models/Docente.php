<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'foto',
        'dni',
        'email',
        'telefono',
        'descripcion',
        'blog',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_docente');
    }
    public function competenciasPorCurso($cursoId)
    {
        return $this->belongsToMany(Competencia::class, 'docente_curso_competencia')
            ->where('curso_id', $cursoId);
    }
}
