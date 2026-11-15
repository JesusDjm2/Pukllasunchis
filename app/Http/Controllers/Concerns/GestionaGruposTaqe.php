<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\PeriodoActual;
use App\Models\TaqeGrupo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;

/**
 * Reglas de elegibilidad de integrantes para grupos de Investigaciones, compartidas entre
 * el autoservicio del alumno (TaqeController) y la gestión institucional del
 * Super Admin (TaqeSuperAdminController) — deben mantenerse en un solo lugar
 * porque son las que garantizan que nadie quede en dos grupos aceptados en el
 * mismo periodo (un alumno = un grupo por periodo), ni que un inhabilitado pueda sumarse, ni que alguien trabaje en el
 * Proyecto Integrador de otro ciclo.
 */
trait GestionaGruposTaqe
{
    /**
     * Alumnos "disponibles" para armar/ampliar un grupo de Investigaciones de este curso:
     * matriculados (o del mismo ciclo si no hay asignación explícita),
     * excluyendo a quien ya esté aceptado en algún grupo del periodo y a
     * cualquier alumno inhabilitado. $excluirAlumnoId saca opcionalmente a un
     * alumno puntual de la lista (el propio usuario en el autoservicio del
     * alumno; se deja null en la gestión del Super Admin, que no es alumno).
     */
    private function companerosDisponibles(Curso $curso, PeriodoActual $periodoActual, ?int $excluirAlumnoId = null, ?int $proyectoId = null): Collection
    {
        $query = $curso->alumnos()->wherePivot('periodo_actual_id', $periodoActual->id);
        if ($excluirAlumnoId) {
            $query->where('alumnos.id', '!=', $excluirAlumnoId);
        }
        $compañeros = $query->orderBy('apellidos')
            ->get(['alumnos.id', 'alumnos.user_id', 'alumnos.ciclo_id', 'alumnos.nombres', 'alumnos.apellidos', 'alumnos.dni']);

        // Sin asignaciones explícitas en alumno_cursos, el curso se toma por
        // ciclo — los compañeros son el resto de alumnos de ese mismo ciclo.
        if ($compañeros->isEmpty() && $curso->ciclo_id) {
            $compañeros = Alumno::where('ciclo_id', $curso->ciclo_id)
                ->when($excluirAlumnoId, fn ($q) => $q->where('id', '!=', $excluirAlumnoId))
                ->orderBy('apellidos')
                ->get(['id', 'user_id', 'ciclo_id', 'nombres', 'apellidos', 'dni']);
        }

        // Un alumno pertenece a un solo grupo de Investigaciones por periodo: quien ya está aceptado en uno
        // (de cualquier curso) no aparece como compañero disponible.
        $comprometidos = $this->alumnoIdsConGrupoEnPeriodo($periodoActual->id);
        $compañeros = $compañeros->reject(fn ($a) => $comprometidos->contains($a->id));

        // Ningún alumno inhabilitado (retirado, licencia, deuda, etc.) puede
        // aparecer como compañero disponible para armar un grupo de Investigaciones.
        $compañeros = $compañeros->load('user.roles', 'ciclo')
            ->reject(fn ($a) => (bool) $a->user?->roles->contains('name', 'inhabilitado'));

        // Solo compañeros del mismo Proyecto Integrador (el de su propio ciclo): quien lleva el curso
        // desde otro ciclo (p. ej. un curso a cargo) no puede trabajar en el proyecto de ese ciclo.
        if ($proyectoId) {
            $compañeros = $compañeros->filter(fn ($a) => (int) $a->ciclo?->proyecto_id === $proyectoId);
        }

        return $compañeros->values();
    }

    /** Un grupo de un periodo que ya no es el actual queda como archivo: se ve, pero no se modifica. */
    private function grupoDePeriodoCerrado(TaqeGrupo $grupo): bool
    {
        return (int) $grupo->periodo_actual_id !== (int) PeriodoActual::where('actual', true)->value('id');
    }

    /** Corta cualquier cambio del alumno sobre un grupo de un periodo anterior. */
    private function bloquearSiPeriodoCerrado(TaqeGrupo $grupo): void
    {
        if ($this->grupoDePeriodoCerrado($grupo)) {
            throw new HttpResponseException(back()->with('error', "Ese grupo de Investigaciones es de un periodo anterior: queda como archivo de solo lectura."));
        }
    }

    /** Rechaza las invitaciones pendientes del alumno en el periodo (al crear o unirse a un grupo). */
    private function rechazarInvitacionesDelPeriodo(Alumno $alumno, int $periodoActualId, int $exceptoGrupoId): int
    {
        $pendientes = $alumno->taqeGrupos()
            ->wherePivot('estado', 'pendiente')
            ->where('periodo_actual_id', $periodoActualId)
            ->where('taqe_grupos.id', '!=', $exceptoGrupoId)
            ->pluck('taqe_grupos.id');
        foreach ($pendientes as $id) {
            $alumno->taqeGrupos()->updateExistingPivot($id, ['estado' => 'rechazado', 'respondido_at' => now()]);
        }

        return $pendientes->count();
    }

    /** Proyecto Integrador de un alumno: el de SU ciclo (ciclos.proyecto_id), no el del curso que lleve. */
    private function proyectoDelAlumno(Alumno $alumno): ?int
    {
        return $alumno->ciclo?->proyecto_id;
    }

    /** De estos alumnos, los que NO pertenecen al Proyecto Integrador indicado. */
    private function alumnoIdsDeOtroProyecto(Collection $alumnoIds, ?int $proyectoId): Collection
    {
        if (! $proyectoId || $alumnoIds->isEmpty()) {
            return collect();
        }

        return Alumno::whereIn('alumnos.id', $alumnoIds)
            ->leftJoin('ciclos', 'ciclos.id', '=', 'alumnos.ciclo_id')
            ->where(fn ($q) => $q->whereNull('ciclos.proyecto_id')->orWhere('ciclos.proyecto_id', '!=', $proyectoId))
            ->pluck('alumnos.id');
    }

    /** IDs de alumnos ya aceptados en algún grupo de Investigaciones del periodo (regla: un grupo por alumno y periodo). */
    private function alumnoIdsConGrupoEnPeriodo(int $periodoActualId): Collection
    {
        return TaqeGrupo::where('periodo_actual_id', $periodoActualId)
            ->join('taqe_grupo_alumno', 'taqe_grupo_alumno.taqe_grupo_id', '=', 'taqe_grupos.id')
            ->where('taqe_grupo_alumno.estado', 'aceptado')
            ->pluck('taqe_grupo_alumno.alumno_id');
    }
}
