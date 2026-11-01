<?php

namespace App\Services;

use App\Models\Ayllu\AylluEntregaFormativa;
use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\Curso;
use App\Models\TaqeGrupo;
use App\Models\User;

class AylluAuthorizationService
{
    /**
     * Autoridades institucionales con alcance transversal.
     */
    public function esAutoridadInstitucional(User $user, array $ctx): bool
    {
        return !empty($ctx['esAdmin']) || $user->hasAnyRole(['super-admin', 'admin', 'adminB', 'direccion']);
    }

    /**
     * Verifica si el usuario es tutor asignado al ciclo indicado.
     */
    public function esTutorDelCiclo(User $user, ?int $cicloId): bool
    {
        if (!$cicloId || !$user->hasRole('tutor')) {
            return false;
        }

        return $user->tutorCiclos()->where('ciclos.id', $cicloId)->exists();
    }

    /**
     * Verifica si un estudiante o docente tiene acceso autorizado a la comunidad de un curso.
     * - Estudiantes: solo cursos en los que tienen matrícula/asignación vigente ($ctx['cursos']).
     * - Docentes: solo cursos asignados en curso_docente ($ctx['cursos']), salvo tutores del ciclo o autoridades.
     */
    public function puedeAccederCurso(User $user, Curso $curso, array $ctx): bool
    {
        if ($this->esAutoridadInstitucional($user, $ctx)) {
            return true;
        }

        if ($this->esTutorDelCiclo($user, $curso->ciclo_id)) {
            return true;
        }

        if (isset($ctx['cursos']) && $ctx['cursos']->contains('id', $curso->id)) {
            return true;
        }

        if (!empty($ctx['docente'])) {
            return $curso->docentes()->where('docentes.id', $ctx['docente']->id)->exists();
        }

        return false;
    }

    /**
     * Verifica si un docente o autoridad puede ver entregas de todos los estudiantes
     * de un curso y emitir retroalimentación formativa oficial en dicho curso.
     */
    public function puedeEvaluarEnCurso(User $user, Curso $curso, array $ctx): bool
    {
        if ($this->esAutoridadInstitucional($user, $ctx)) {
            return true;
        }

        if ($this->esTutorDelCiclo($user, $curso->ciclo_id)) {
            return true;
        }

        if (!empty($ctx['docente'])) {
            return $curso->docentes()->where('docentes.id', $ctx['docente']->id)->exists();
        }

        return false;
    }

    /**
     * Verifica si el usuario pertenece o tiene autorización sobre un ciclo académico.
     */
    public function puedeAccederCiclo(User $user, ?int $cicloId, array $ctx): bool
    {
        if (!$cicloId) {
            return false;
        }

        if ($this->esAutoridadInstitucional($user, $ctx) || $this->esTutorDelCiclo($user, $cicloId)) {
            return true;
        }

        if (!empty($ctx['ciclo']) && (int) $ctx['ciclo']->id === (int) $cicloId) {
            return true;
        }

        if (isset($ctx['cursos']) && $ctx['cursos']->pluck('ciclo_id')->contains($cicloId)) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si el usuario es integrante registrado de un grupo comunitario Ayllu.
     */
    public function esMiembroGrupoAyllu(User $user, AylluGrupo $grupo): bool
    {
        if ((int) $grupo->creado_por === (int) $user->id) {
            return true;
        }

        return $grupo->miembros()->where('users.id', $user->id)->exists();
    }

    /**
     * Verifica si el usuario puede publicar o enviar mensajes en un grupo Ayllu.
     * Requiere que el grupo esté activo y que el usuario sea miembro integrado o autoridad.
     */
    public function puedeParticiparEnGrupo(User $user, AylluGrupo $grupo, array $ctx): bool
    {
        if (!$grupo->activo) {
            return false;
        }

        if ($this->esAutoridadInstitucional($user, $ctx)) {
            return true;
        }

        return $this->esMiembroGrupoAyllu($user, $grupo);
    }

    /**
     * Verifica si el estudiante puede vincular una entrega formativa a un equipo Taq'e específico.
     * Exige pertenencia validada al grupo Taq'e y correspondencia con el curso o proyecto anual.
     */
    public function puedeVincularGrupoTaqe(User $user, TaqeGrupo $grupo, Curso $curso, array $ctx): bool
    {
        $proyectoIdCurso = $curso->ciclo?->proyecto_id ?? $ctx['proyectoIntegrador']?->id;

        $correspondeCursoOProyecto = ((int) $grupo->curso_id === (int) $curso->id)
            || ($proyectoIdCurso && (int) $grupo->proyecto_id === (int) $proyectoIdCurso);

        if (!$correspondeCursoOProyecto) {
            return false;
        }

        if ($this->puedeEvaluarEnCurso($user, $curso, $ctx)) {
            return true;
        }

        if (!empty($ctx['alumno'])) {
            return $grupo->alumnosAceptados()->where('alumnos.id', $ctx['alumno']->id)->exists();
        }

        return false;
    }

    /**
     * Verifica si el usuario puede ver e interactuar (comentar / reaccionar) con una publicación.
     */
    public function puedeInteractuarConPublicacion(User $user, AylluPublicacion $publicacion, array $ctx): bool
    {
        if (!$publicacion->visible && (int) $publicacion->user_id !== (int) $user->id && !$this->esAutoridadInstitucional($user, $ctx)) {
            return false;
        }

        return match ($publicacion->ambito) {
            'institucional' => true,
            'curso' => $publicacion->curso
                ? $this->puedeAccederCurso($user, $publicacion->curso, $ctx)
                : false,
            'ciclo' => $this->puedeAccederCiclo($user, $publicacion->ciclo_id, $ctx),
            'grupo' => $publicacion->grupo
                ? $this->puedeParticiparEnGrupo($user, $publicacion->grupo, $ctx)
                : false,
            default => true,
        };
    }

    /**
     * Verifica si el usuario puede editar o eliminar una publicación.
     */
    public function puedeModificarPublicacion(User $user, AylluPublicacion $publicacion, array $ctx): bool
    {
        if ((int) $publicacion->user_id === (int) $user->id) {
            return true;
        }

        if ($this->esAutoridadInstitucional($user, $ctx)) {
            return true;
        }

        if (!empty($ctx['puedeModerar'])) {
            if ($publicacion->ambito === 'curso' && $publicacion->curso) {
                return $this->puedeEvaluarEnCurso($user, $publicacion->curso, $ctx);
            }
            if ($publicacion->ambito === 'ciclo') {
                return $this->puedeAccederCiclo($user, $publicacion->ciclo_id, $ctx);
            }
            if ($publicacion->ambito === 'institucional') {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si el usuario puede ver y emitir retroalimentación sobre una entrega formativa.
     */
    public function puedeRetroalimentarEntrega(User $user, AylluEntregaFormativa $entrega, array $ctx): bool
    {
        if ((int) $entrega->user_id === (int) $user->id) {
            return true;
        }

        $curso = $entrega->curso ?? Curso::find($entrega->curso_id);
        if (!$curso) {
            return false;
        }

        return $this->puedeEvaluarEnCurso($user, $curso, $ctx);
    }
}
