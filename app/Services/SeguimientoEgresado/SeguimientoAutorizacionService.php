<?php

namespace App\Services\SeguimientoEgresado;

use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\GrupoEntrevistador;
use App\Models\User;

class SeguimientoAutorizacionService
{
    public const PERMISOS_DELEGABLES = [
        'seguimiento-egresados.ver-dashboard',
        'seguimiento-egresados.ver-perfiles-autorizados',
        'seguimiento-egresados.actualizar-perfiles-autorizados',
        'seguimiento-egresados.registrar-entrevistas',
        'seguimiento-egresados.gestionar-campanas',
        'seguimiento-egresados.gestionar-grupos',
        'seguimiento-egresados.ver-analitica',
        'seguimiento-egresados.exportar-analitica',
        'seguimiento-egresados.gestionar-casos',
        'seguimiento-egresados.ver-contactos-autorizados',
    ];

    /**
     * Verifica si el usuario ejerce funciones de Dirección o Coordinación de Empleabilidad.
     */
    public function esPersonalGestion(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        // Misma jerarquía institucional que main. Admin Bolsa no administra expedientes.
        return $user->hasAnyRole(['admin', 'super-admin'])
            || ($user->can('seguimiento-egresados.gestionar-campanas') && $user->can('seguimiento-egresados.gestionar-grupos'));
    }

    /**
     * Verifica si el usuario pertenece al área de Bienestar / Trabajo Social.
     */
    public function esBienestarSocial(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        return $user->hasRole('bienestar');
    }

    /**
     * Verifica si el usuario es egresado o alumno (acceso exclusivo a su propia información).
     */
    public function esEgresadoOAlumno(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        return $user->condicion === 'Egresado' || $user->hasAnyRole(['egresado', 'alumno', 'alumnoB']);
    }

    /**
     * Verifica si el usuario tiene rol docente, tutor, encuestador o seguimiento de egresados.
     */
    public function esDocenteOEntrevistador(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        return $user->hasRole('seguimiento-egresados')
            || $user->can('seguimiento-egresados.ver-perfiles-autorizados')
            || ($user->hasAnyRole(['docente', 'tutor', 'encuestador-seguimiento', 'encuestador'])
                && method_exists(AsignacionCampana::class, 'scopeActivos')
                && AsignacionCampana::activos()->where('docente_user_id', $user->id)->exists());
    }

    /**
     * Comprueba autorización para consultar el expediente o historial de un egresado específico.
     * REGLA ESTRICTA (Fase 1D):
     * - Un egresado solo puede consultar su propio expediente.
     * - Personal directivo y coordinación pueden consultar el padrón institucional.
     * - Un docente, tutor o encuestador SOLO puede consultar egresados que tenga asignados en grupos activos.
     * - Un docente sin asignación vigente NO tiene acceso a expedientes personales (HTTP 403).
     */
    public function puedeConsultarEgresado(int $targetUserId, ?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        if ((int)$user->id === (int)$targetUserId) {
            return true;
        }

        if ($this->esPersonalGestion($user)) {
            return true;
        }

        if ($this->esDocenteOEntrevistador($user)) {
            return AsignacionCampana::esDocenteAsignado($user->id, $targetUserId)
                || GrupoEntrevistador::esEntrevistadorAsignado($user->id, $targetUserId);
        }

        return false;
    }

    /**
     * Comprueba autorización para mutar o actualizar datos de un egresado específico.
     */
    public function puedeModificarEgresado(int $targetUserId, ?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;
        if ((int) $user->id === $targetUserId || $this->esPersonalGestion($user)) return true;
        return ($this->esDocenteOEntrevistador($user) || $user->can('seguimiento-egresados.actualizar-perfiles-autorizados'))
            && $this->puedeConsultarEgresado($targetUserId, $user);
    }

    /**
     * Comprueba autorización para registrar llamadas y contactos.
     * Los egresados no pueden registrar llamadas; los docentes solo a sus asignados.
     */
    public function puedeRegistrarLlamada(int $targetUserId, ?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return false;
        }

        if ($this->esPersonalGestion($user)) {
            return true;
        }

        if ($this->esDocenteOEntrevistador($user)) {
            return AsignacionCampana::esDocenteAsignado($user->id, $targetUserId)
                || GrupoEntrevistador::esEntrevistadorAsignado($user->id, $targetUserId);
        }

        return false;
    }

    /**
     * Comprueba autorización para administrar grupos de entrevistadores.
     * Exclusivo para Coordinación y Dirección.
     */
    public function puedeGestionarGrupos(?User $user = null): bool
    {
        return $this->esPersonalGestion($user);
    }

    /**
     * Comprueba autorización para crear o avanzar casos institucionales.
     */
    public function puedeGestionarCasos(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        return $this->esPersonalGestion($user) || $this->esBienestarSocial($user)
            || $user->can('seguimiento-egresados.gestionar-casos');
    }

    /**
     * Comprueba si el usuario tiene permiso para leer notas confidenciales de Bienestar.
     */
    public function puedeVerNotasConfidencialesBienestar(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        return $this->esPersonalGestion($user) || $this->esBienestarSocial($user);
    }

    /**
     * Comprueba autorización para acceder al Laboratorio de Inteligencia y Observatorio Territorial.
     * Requiere permiso explícito de analítica o pertenecer a roles estratégicos directivos.
     */
    public function puedeAccederLaboratorio(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        if ($this->esEgresadoOAlumno($user) && !$this->esPersonalGestion($user)) {
            return false;
        }

        if ($user->can('seguimiento-egresados.ver-analitica')) {
            return true;
        }

        $rolesPermitidos = ['admin', 'super-admin'];

        return $user->hasAnyRole($rolesPermitidos);
    }

    /**
     * Comprueba autorización para exportar datos del laboratorio analítico.
     */
    public function puedeExportarAnalitica(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        return $user->can('seguimiento-egresados.exportar-analitica') || $this->esPersonalGestion($user);
    }

    /**
     * Comprueba autorización para acceder a la entrada principal del módulo Puklla en Red.
     */
    public function puedeAccederDashboard(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        return $this->esPersonalGestion($user) || $user->hasRole('seguimiento-egresados')
            || $user->can('seguimiento-egresados.ver-dashboard');
    }
}
