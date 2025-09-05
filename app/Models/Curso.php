<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Curso extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'sumilla', 'cc', 'horas', 'creditos', 'ciclo_id', 'silabo', 'classroom', 'clave'];

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
    public function periododos()
    {
        return $this->hasMany(PeriodoDos::class);
    }
    public function periodotres()
    {
        return $this->hasMany(PeriodoTres::class);
    }
    public function periodo()
    {
        return $this->hasMany(Periodo::class);
    }
    public function relacionsilabo()
    {
        return $this->hasOne(Silabo::class, 'curso_id');
    }
    public function enfoques()
    {
        return $this->hasMany(Enfoques::class);
    }
    //Calificaciones PPD
    public function calificacionesppd()
    {
        return $this->hasMany(Calificacionesppd::class);
    }
    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_cursos')->withTimestamps();
    }
    
    public function alumnosValidosIds(): \Illuminate\Support\Collection
    {
        $rel = $this->alumnos()
            ->whereHas('user.roles', fn($q) => $q->where('name', '!=', 'inhabilitado'))
            ->pluck('alumnos.id');
        $ciclo = $this->ciclo->alumnos()
            ->whereHas('user.roles', fn($q) => $q->where('name', '!=', 'inhabilitado'))
            ->pluck('alumnos.id');
        return $ciclo->merge($rel)->unique()->values(); 
    }

    public function porcentajePeriodo(int $periodo, array $camposClave = ['calificacion_curso']): float
    {
        $ids = $this->alumnosValidosIds();
        $total = $ids->count();
        if ($total === 0)
            return 0.0;
        $map = [1 => 'periodos', 2 => 'periododos', 3 => 'periodotres'];
        $relacion = $map[$periodo] ?? null;
        if (!$relacion)
            return 0.0;
        $q = $this->$relacion()->whereIn('alumno_id', $ids)->where('curso_id', $this->id);
        foreach ($camposClave as $campo) {
            $q->whereRaw("NULLIF($campo, '') IS NOT NULL");
        }
        $alumnosCompletos = $q->count(DB::raw('DISTINCT alumno_id'));

        return round(($alumnosCompletos / $total) * 100, 2);
    }
}
