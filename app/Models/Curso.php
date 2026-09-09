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
        // Un curso puede acumular varios sílabos (uno por periodo, más los que queden
        // de "reusar"), así que sin orden explícito hasOne() podía devolver uno viejo
        // -y con eso, el link de Ver/Editar mostraba el "Semestre Académico" de otro
        // periodo. Aquí siempre se toma el más reciente (el del periodo vigente).
        return $this->hasOne(Silabo::class, 'curso_id')->latestOfMany('id');
    }

    public function silabos()
    {
        return $this->hasMany(Silabo::class, 'curso_id');
    }

    public function enfoques()
    {
        return $this->hasMany(Enfoques::class);
    }

    //Relación con sialbo en PDF
    public function silabosPdf()
    {
        return $this->hasMany(SilaboPdf::class, 'curso_id');
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
            ->whereHas('user.roles', fn ($q) => $q->where('name', '!=', 'inhabilitado'))
            ->pluck('alumnos.id');
        // La rama por ciclo trae a TODOS los del ciclo del curso, incluso a quien nunca se
        // matriculó en este curso puntual — por eso hay que excluir a los "Egresado": cada
        // promoción que termina conserva su Ciclo X real (para no perder el vínculo con sus
        // cursos/calificaciones), pero mezclarla aquí contaría cohortes distintas que comparten
        // el mismo curso_id (una graduada, con notas viejas; otra cursando, sin notas aún).
        $ciclo = $this->ciclo->alumnos()
            ->whereHas('user.roles', fn ($q) => $q->where('name', '!=', 'inhabilitado'))
            ->whereHas('user', fn ($q) => $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado'))
            ->pluck('alumnos.id');

        return $ciclo->merge($rel)->unique()->values();
    }

    /**
     * El avance se mide por CASILLA llena (Participación, Actividad, Autoevaluación, Evaluación x
     * competencia), no por alumno 100% completo: un docente suele ir llenando por partes (p.ej.
     * primero todo Producto Final y después Producto de Proceso), y contar solo alumnos totalmente
     * terminados dejaba el indicador en 0% aunque ya hubiera bastante trabajo real guardado.
     */
    public function porcentajePPD(): float
    {
        if (! $this->ciclo_id) {
            return 0.0;
        }

        // El universo son los alumnos del mismo PROGRAMA, no del mismo Ciclo exacto: en PPD
        // un alumno cursa Ciclo I y Ciclo II en paralelo dentro del mismo año (a diferencia de
        // FID, donde el ciclo es secuencial), así que el "a qué Ciclo está asignado" no debe
        // limitar en qué cursos puede ser calificado.
        $programaId = $this->ciclo?->programa_id;
        if (! $programaId) {
            return 0.0;
        }

        $idsUniverso = \App\Models\User::where('programa_id', $programaId)
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
                  ->orWhere(function ($q2) {
                      $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                          ->where('perfil', '!=', 'Retirado');
                  });
            })
            // Los egresados conservan su Ciclo II real (para no perder el vínculo con sus
            // cursos/calificaciones), pero no forman parte del periodo actual: no deben
            // contarse en el porcentaje de la cohorte que está cursando ahora.
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            })
            ->pluck('id');

        $total = $idsUniverso->count();
        if ($total === 0) {
            return 0.0;
        }

        $numCompetencias = $this->numeroCompetenciasPpd();
        $camposClave = $this->camposManualesPpd($numCompetencias);
        $totalCasillas = $total * count($camposClave);

        $campos = array_merge(['ppd_id', 'user_id'], $camposClave);
        $ppdIds = $this->calificacionesppd()->pluck('ppd_id')->filter()->unique();
        $ppdUserMap = \App\Models\ppd::whereIn('id', $ppdIds)->pluck('user_id', 'id');

        $casillasLlenas = $this->calificacionesppd()
            ->get($campos)
            ->sum(function ($fila) use ($camposClave, $idsUniverso, $ppdUserMap) {
                $uid = $fila->user_id ?? ($ppdUserMap[$fila->ppd_id] ?? null);
                if (! $uid || ! $idsUniverso->contains($uid)) {
                    return 0;
                }

                $llenas = 0;
                foreach ($camposClave as $campo) {
                    if ($fila->{$campo} !== null) {
                        $llenas++;
                    }
                }

                return $llenas;
            });

        return round(($casillasLlenas / $totalCasillas) * 100, 2);
    }

    /**
     * IDs de usuario (alumnos) con TODOS los campos manuales de Proceso y Final llenos para este
     * curso PPD — la señal real de "ya calificado". No usa calificacion_curso: ese campo lo
     * recalcula el JS de la pantalla de calificar cada 200ms para TODAS las filas visibles,
     * incluidas las que el docente nunca tocó, así que puede quedar con una nota derivada (p.ej.
     * "1.10") aunque casi todos los campos reales sigan vacíos.
     */
    public function alumnosPpdCalificadosCompletos(): \Illuminate\Support\Collection
    {
        $camposClave = $this->camposManualesPpd($this->numeroCompetenciasPpd());
        $campos = array_merge(['ppd_id', 'user_id'], $camposClave);

        $ppdIds = $this->calificacionesppd()->pluck('ppd_id')->filter()->unique();
        $ppdUserMap = \App\Models\ppd::whereIn('id', $ppdIds)->pluck('user_id', 'id');

        return $this->calificacionesppd()
            ->get($campos)
            ->filter(function ($fila) use ($camposClave) {
                foreach ($camposClave as $campo) {
                    if ($fila->{$campo} === null) {
                        return false;
                    }
                }

                return true;
            })
            ->map(fn ($fila) => $fila->user_id ?? ($ppdUserMap[$fila->ppd_id] ?? null))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Cuántas competencias (1 a 3) se califican realmente en este curso PPD — mismo criterio que
     * PpdController/DocenteCOntroller al armar la pantalla de calificar.
     */
    private function numeroCompetenciasPpd(): int
    {
        $totalCompetencias = $this->competencias()->count();
        $n = ($totalCompetencias > 0 && $totalCompetencias <= 3)
            ? $totalCompetencias
            : $this->competenciasSeleccionadas()->count();

        return $n > 0 ? min(3, $n) : 3;
    }

    /**
     * Nombres de las columnas que el docente llena a mano en calificacionesppds — Participación y
     * Actividad (Proceso), Autoevaluación y Evaluación (Final) — por cada competencia evaluada.
     * No incluye los "Promedio" (pp_c{n}_4, pf_c{n}_3): esos los calcula solo el JS de la pantalla.
     */
    private function camposManualesPpd(int $numCompetencias): array
    {
        $campos = [];
        for ($i = 1; $i <= $numCompetencias; $i++) {
            $campos[] = "pp_c{$i}_1";
            $campos[] = "pp_c{$i}_2";
            $campos[] = "pf_c{$i}_1";
            $campos[] = "pf_c{$i}_2";
        }

        return $campos;
    }

    public function porcentajePeriodo(int $periodo, array $camposClave = ['calificacion_curso']): float
    {
        $ids = $this->alumnosValidosIds();
        $total = $ids->count();
        if ($total === 0) {
            return 0.0;
        }
        $map = [1 => 'periodos', 2 => 'periododos', 3 => 'periodotres'];
        $relacion = $map[$periodo] ?? null;
        if (! $relacion) {
            return 0.0;
        }
        $q = $this->$relacion()->whereIn('alumno_id', $ids)->where('curso_id', $this->id);
        foreach ($camposClave as $campo) {
            $q->whereRaw("NULLIF($campo, '') IS NOT NULL");
        }
        $alumnosCompletos = $q->count(DB::raw('DISTINCT alumno_id'));

        return round(($alumnosCompletos / $total) * 100, 2);
    }

    /* public function alumnosValidosPpdIds(): \Illuminate\Support\Collection
    {
        return $this->calificacionesppd()
            ->whereHas('ppd.user.roles', fn ($q) => $q->where('name', 'alumnoB'))
            ->pluck('ppd_id')
            ->unique()
            ->values();
    }

    public function porcentajePPD(
        array $camposClave = ['calificacion_curso']
    ): float {
        $ids = $this->alumnosValidosPpdIds();
        $total = $ids->count();

        if ($total === 0) {
            return 0.0;
        }

        $q = $this->calificacionesppd()
            ->whereIn('ppd_id', $ids);

        foreach ($camposClave as $campo) {
            $q->whereRaw("NULLIF($campo, '') IS NOT NULL");
        }

        $alumnosCompletos = $q->count(DB::raw('DISTINCT ppd_id'));

        return round(($alumnosCompletos / $total) * 100, 2);
    } */
}
