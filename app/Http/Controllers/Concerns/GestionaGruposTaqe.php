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
     * los del mismo ciclo del curso en cualquier programa con el mismo Proyecto Integrador,
     * excluyendo a quien ya esté aceptado en algún grupo del periodo y a
     * cualquier alumno inhabilitado. $excluirAlumnoId saca opcionalmente a un
     * alumno puntual de la lista (el propio usuario en el autoservicio del
     * alumno; se deja null en la gestión del Super Admin, que no es alumno).
     */
    private function companerosDisponibles(Curso $curso, PeriodoActual $periodoActual, ?int $excluirAlumnoId = null, ?int $proyectoId = null): Collection
    {
        // Compañeros = alumnos del MISMO ciclo que el curso, de cualquier programa que comparta su
        // Proyecto Integrador (Inicial II con Primaria EIB II, etc.). No se mezclan ciclos distintos
        // aunque compartan proyecto (I con II). Ver ciclosHermanos().
        $compañeros = $curso->ciclo_id
            ? Alumno::whereIn('ciclo_id', $this->ciclosHermanos((int) $curso->ciclo_id))
                ->when($excluirAlumnoId, fn ($q) => $q->where('id', '!=', $excluirAlumnoId))
                ->orderBy('apellidos')->orderBy('nombres')
                ->get(['id', 'user_id', 'ciclo_id', 'nombres', 'apellidos', 'dni'])
            : collect();

        // Un alumno pertenece a un solo grupo de Investigaciones por periodo: quien ya está aceptado en uno
        // (de cualquier curso) no aparece como compañero disponible.
        $comprometidos = $this->alumnoIdsConGrupoEnPeriodo($periodoActual->id);
        $compañeros = $compañeros->reject(fn ($a) => $comprometidos->contains($a->id));

        // Ningún alumno inhabilitado (retirado, licencia, deuda, etc.) puede
        // aparecer como compañero disponible para armar un grupo de Investigaciones.
        $compañeros = $compañeros->load('user.roles', 'ciclo.programa')
            ->reject(fn ($a) => (bool) $a->user?->roles->contains('name', 'inhabilitado'));

        // Solo compañeros del mismo Proyecto Integrador (el de su propio ciclo): quien lleva el curso
        // desde otro ciclo (p. ej. un curso a cargo) no puede trabajar en el proyecto de ese ciclo.
        if ($proyectoId) {
            $compañeros = $compañeros->filter(fn ($a) => (int) $a->ciclo?->proyecto_id === $proyectoId);
        }

        // Texto para listas (vista y JSON): incluye el programa porque el grupo puede mezclarlos.
        $compañeros->each(fn ($a) => $a->setAttribute('etiqueta_taqe', $this->etiquetaCompanero($a)));

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

    /**
     * Ciclos con los que se puede formar grupo: el mismo número de ciclo (II, IV…) en cualquier
     * programa que comparta el Proyecto Integrador (hoy Inicial y Primaria EIB). Un ciclo sin
     * proyecto solo se empareja consigo mismo.
     */
    private function ciclosHermanos(?int $cicloId): Collection
    {
        $ciclo = $cicloId ? \App\Models\Ciclo::find($cicloId) : null;
        if (! $ciclo) {
            return collect();
        }
        if (! $ciclo->proyecto_id) {
            return collect([$ciclo->id]);
        }

        return \App\Models\Ciclo::where('proyecto_id', $ciclo->proyecto_id)->get()
            ->filter(fn ($c) => $c->numero_romano === $ciclo->numero_romano)
            ->pluck('id')
            ->values();
    }

    /** De estos alumnos, los que NO son del mismo ciclo (o ciclo hermano de otro programa) que el indicado. */
    private function alumnoIdsDeOtroCiclo(Collection $alumnoIds, ?int $cicloId): Collection
    {
        if (! $cicloId || $alumnoIds->isEmpty()) {
            return collect();
        }

        return Alumno::whereIn('id', $alumnoIds)
            ->where(fn ($q) => $q->whereNull('ciclo_id')->orWhereNotIn('ciclo_id', $this->ciclosHermanos($cicloId)))
            ->pluck('id');
    }

    /** «Apellidos, Nombres · Primaria EIB»: el programa se muestra porque los grupos pueden mezclar programas. */
    private function etiquetaCompanero(Alumno $a): string
    {
        $programa = trim(preg_replace('/^Programa\s+/i', '', (string) $a->ciclo?->programa?->nombre));

        return $a->apellidos.', '.$a->nombres.($programa !== '' ? ' · '.$programa : '');
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
