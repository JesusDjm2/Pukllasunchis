<?php

namespace App\Services;

use App\Models\Ayllu\AylluEntregaFormativa;
use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluPiAporteCurso;
use App\Models\Ayllu\AylluPiAsignacionMesa;
use App\Models\Ayllu\AylluPiBitacoraMesa;
use App\Models\Ayllu\AylluPiEvidenciaVersion;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\TaqeGrupo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
     * Verifica si el usuario es tutor de algún ciclo con el numeral romano indicado (I..VIII).
     */
    public function esTutorDeCicloRomano(User $user, string $cicloRomano): bool
    {
        if (!$user->hasRole('tutor')) {
            return false;
        }

        return $user->tutorCiclos()
            ->whereRaw('UPPER(TRIM(ciclos.nombre)) = ?', [strtoupper(trim($cicloRomano))])
            ->exists();
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

    /* =========================================================================
     * MESA DOCENTE DE PROYECTOS INTEGRADORES ANUALES (Permisos granulares)
     * Separa explícitamente: ver, editar, comentar, descargar, revisar, aprobar y publicar.
     * No concede acceso global por el mero rol genérico «docente».
     * ========================================================================= */

    /**
     * Verifica si el usuario es coordinador autorizado de proyectos integradores en el ciclo/ejecución.
     */
    public function esCoordinadorProyectos(User $user, ?string $cicloRomano, ?int $ejecucionId, array $ctx): bool
    {
        if ($this->esAutoridadInstitucional($user, $ctx)) {
            return true;
        }

        // Verificar asignación explícita y vigente en ayllu_pi_asignaciones_mesa
        if (Schema::hasTable('ayllu_pi_asignaciones_mesa')) {
            $q = AylluPiAsignacionMesa::where('user_id', $user->id)
                ->where('activo', true)
                ->whereIn('rol_mesa', ['coordinador_proyecto', 'coordinador_ciclo']);

            if ($cicloRomano) {
                $q->where(function ($sub) use ($cicloRomano) {
                    $sub->where('ciclo_romano', strtoupper(trim($cicloRomano)))
                        ->orWhere('ciclo_romano', 'TODOS');
                });
            }

            if ($ejecucionId) {
                $q->where(function ($sub) use ($ejecucionId) {
                    $sub->whereNull('ejecucion_id')
                        ->orWhere('ejecucion_id', $ejecucionId);
                });
            }

            if ($q->exists()) {
                return true;
            }
        }

        // Verificar si el docente está registrado como organizador/encargado en proyecto_docente para ese ciclo
        if (!empty($ctx['docente']) && $cicloRomano && Schema::hasTable('proyecto_docente')) {
            $proyectoIdsCiclo = Ciclo::whereRaw('UPPER(TRIM(nombre)) = ?', [strtoupper(trim($cicloRomano))])
                ->whereNotNull('proyecto_id')
                ->pluck('proyecto_id');

            if ($proyectoIdsCiclo->isNotEmpty()) {
                $esEncargado = DB::table('proyecto_docente')
                    ->where('docente_id', $ctx['docente']->id)
                    ->whereIn('proyecto_id', $proyectoIdsCiclo)
                    ->exists();
                if ($esEncargado) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifica si un docente o coordinador pertenece activamente a la Mesa Docente de un ciclo (I..VIII) o de su par anual.
     * Un docente ajeno sin cursos ni asignaciones vigentes en ese ciclo/año NO pertenece a esa mesa.
     */
    public function perteneceAMesaDocenteDelCiclo(User $user, string $cicloRomano, ?int $ejecucionId, array $ctx): bool
    {
        $cicloNorm = strtoupper(trim($cicloRomano));

        if ($this->esCoordinadorProyectos($user, $cicloNorm, $ejecucionId, $ctx)) {
            return true;
        }

        if ($this->esTutorDeCicloRomano($user, $cicloNorm)) {
            return true;
        }

        // Asignación activa en ayllu_pi_asignaciones_mesa para este ciclo
        if (Schema::hasTable('ayllu_pi_asignaciones_mesa')) {
            $tieneAsignacionMesa = AylluPiAsignacionMesa::where('user_id', $user->id)
                ->where('activo', true)
                ->where(function ($q) use ($cicloNorm) {
                    $q->where('ciclo_romano', $cicloNorm)
                        ->orWhere('ciclo_romano', 'TODOS');
                })
                ->when($ejecucionId, function ($q) use ($ejecucionId) {
                    $q->where(function ($sub) use ($ejecucionId) {
                        $sub->whereNull('ejecucion_id')->orWhere('ejecucion_id', $ejecucionId);
                    });
                })
                ->exists();

            if ($tieneAsignacionMesa) {
                return true;
            }
        }

        // Curso asignado actualmente en curso_docente dentro de ese ciclo (o su par anual de continuidad)
        if (!empty($ctx['docente'])) {
            $paresAnuales = [
                'I' => ['I', 'II'], 'II' => ['I', 'II'],
                'III' => ['III', 'IV'], 'IV' => ['III', 'IV'],
                'V' => ['V', 'VI'], 'VI' => ['V', 'VI'],
                'VII' => ['VII', 'VIII'], 'VIII' => ['VII', 'VIII'],
            ];
            $ciclosPar = $paresAnuales[$cicloNorm] ?? [$cicloNorm];

            return $ctx['docente']->cursos()
                ->whereHas('ciclo', function ($q) use ($ciclosPar) {
                    $q->whereIn(DB::raw('UPPER(TRIM(nombre))'), $ciclosPar);
                })
                ->exists();
        }

        return false;
    }

    /**
     * Verifica si un estudiante pertenece al ciclo romano indicado (I..VIII) o tiene curso matriculado en él.
     */
    public function estudiantePerteneceACicloRomano(User $user, string $cicloRomano, array $ctx): bool
    {
        $cicloNorm = strtoupper(trim($cicloRomano));
        $paresAnuales = [
            'I' => ['I', 'II'], 'II' => ['I', 'II'],
            'III' => ['III', 'IV'], 'IV' => ['III', 'IV'],
            'V' => ['V', 'VI'], 'VI' => ['V', 'VI'],
            'VII' => ['VII', 'VIII'], 'VIII' => ['VII', 'VIII'],
        ];
        $ciclosPar = $paresAnuales[$cicloNorm] ?? [$cicloNorm];

        if (!empty($ctx['ciclo']) && in_array(strtoupper(trim((string) $ctx['ciclo']->nombre)), $ciclosPar, true)) {
            return true;
        }

        if (isset($ctx['cursos']) && $ctx['cursos']->isNotEmpty()) {
            foreach ($ctx['cursos'] as $c) {
                if (in_array(strtoupper(trim((string) $c->ciclo?->nombre)), $ciclosPar, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 1. PERMISO VER: ¿Puede el usuario consultar la ficha de un aporte de curso?
     * - Autoridades / Coordinadores: sí.
     * - Docentes de la mesa del ciclo/año: sí pueden consultar los aportes compartidos de su ciclo/año.
     * - Docentes ajenos (de otros años sin asignación en este ciclo): NO obtienen acceso a deliberaciones ni fichas operativas ajenas.
     * - Estudiantes del ciclo/año: ven la vista estudiantil autorizada de su ciclo/programa.
     */
    public function puedeVerAporteMesa(User $user, AylluPiAporteCurso $aporte, array $ctx): bool
    {
        if ($this->perteneceAMesaDocenteDelCiclo($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx)) {
            return true;
        }

        if ((!empty($ctx['esAlumnoFid']) || !empty($ctx['esAlumnoPpd']))
            && $this->estudiantePerteneceACicloRomano($user, $aporte->ciclo_romano, $ctx)
            && $this->estudianteCorrespondeAProgramaDeAporte($user, $aporte, $ctx)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Verifica que el programa del estudiante o sus cursos matriculados correspondan a la agrupación
     * documental del aporte (evitando que un estudiante de Primaria acceda a fichas operativas de Primaria EIB o Inicial).
     */
    public function estudianteCorrespondeAProgramaDeAporte(User $user, AylluPiAporteCurso $aporte, array $ctx): bool
    {
        if ($aporte->agrupacion_programa_codigo === 'FORMACION_GENERAL' || $aporte->es_aporte_comun) {
            return true;
        }

        if ($aporte->curso_institucional_id && isset($ctx['cursos']) && $ctx['cursos']->contains('id', $aporte->curso_institucional_id)) {
            return true;
        }

        $sugeridos = $aporte->correspondencia_sugerida_json['cursos_sugeridos'] ?? [];
        $cursoIdsCandidatos = collect($sugeridos)->pluck('curso_id')->filter()->values();
        if ($cursoIdsCandidatos->isNotEmpty() && isset($ctx['cursos']) && $ctx['cursos']->pluck('id')->intersect($cursoIdsCandidatos)->isNotEmpty()) {
            return true;
        }

        $prog = $ctx['programa'] ?? null;
        if ($prog) {
            $codigoEstudiante = null;
            if ($prog->esInicial()) {
                $codigoEstudiante = 'INICIAL';
            } elseif ($prog->esPrimaria() && $prog->eib) {
                $codigoEstudiante = 'PRIMARIA_EIB';
            } elseif ($prog->esPrimaria() && !$prog->eib) {
                $codigoEstudiante = 'PRIMARIA';
            }

            if ($codigoEstudiante && $codigoEstudiante === $aporte->agrupacion_programa_codigo) {
                return true;
            }
        }

        return false;
    }

    /**
     * 2. PERMISO EDITAR: ¿Puede el docente o coordinador editar el aporte operativo y registrar versiones docentes en él?
     * El docente edita únicamente su propio aporte según asignación vigente (en ayllu_pi_asignaciones_mesa o en curso_docente).
     * Revocar la asignación revoca inmediatamente este permiso sin borrar la autoría histórica.
     */
    public function puedeEditarAporteCurso(User $user, AylluPiAporteCurso $aporte, array $ctx): bool
    {
        if ($this->esCoordinadorProyectos($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx)) {
            return true;
        }

        // Asignación directa activa al aporte en ayllu_pi_asignaciones_mesa
        if (Schema::hasTable('ayllu_pi_asignaciones_mesa')) {
            $asignadoAlAporte = AylluPiAsignacionMesa::where('user_id', $user->id)
                ->where('aporte_curso_id', $aporte->id)
                ->where('activo', true)
                ->exists();

            if ($asignadoAlAporte) {
                return true;
            }

            // Si existió una asignación explícita para este usuario sobre este aporte o ciclo y fue revocada,
            // no debe caer en fallback genérico
            $fueRevocadoExplicito = AylluPiAsignacionMesa::where('user_id', $user->id)
                ->where(function ($q) use ($aporte) {
                    $q->where('aporte_curso_id', $aporte->id)
                        ->orWhere(function ($sub) use ($aporte) {
                            $sub->whereNull('aporte_curso_id')
                                ->where('ciclo_romano', $aporte->ciclo_romano);
                        });
                })
                ->where('activo', false)
                ->whereNotNull('revocado_at')
                ->exists();

            if ($fueRevocadoExplicito) {
                return false;
            }
        }

        // Asignación vigente en curso_docente sobre el curso institucional enlazado o candidato exacto del mismo ciclo
        if (!empty($ctx['docente'])) {
            if ($aporte->curso_institucional_id) {
                $esDocenteCursoEnlazado = DB::table('curso_docente')
                    ->where('curso_id', $aporte->curso_institucional_id)
                    ->where('docente_id', $ctx['docente']->id)
                    ->exists();
                if ($esDocenteCursoEnlazado) {
                    return true;
                }
            }

            $sugeridos = $aporte->correspondencia_sugerida_json['cursos_sugeridos'] ?? [];
            $cursoIdsCandidatos = collect($sugeridos)->pluck('curso_id')->filter()->values()->all();
            if (!empty($cursoIdsCandidatos)) {
                return DB::table('curso_docente')
                    ->whereIn('curso_id', $cursoIdsCandidatos)
                    ->where('docente_id', $ctx['docente']->id)
                    ->exists();
            }
        }

        return false;
    }

    /**
     * ¿Puede un estudiante o docente registrar una evidencia en un aporte?
     * - Docente responsable o coordinador: sí.
     * - Estudiante matriculado en el curso correspondiente (con equipo Taq'e autorizado o individual): sí, con audiencia equipo_estudiantes.
     */
    public function puedeRegistrarEvidenciaEnAporte(User $user, AylluPiAporteCurso $aporte, array $ctx): bool
    {
        if ($this->puedeEditarAporteCurso($user, $aporte, $ctx)) {
            return true;
        }

        if (!empty($ctx['esAlumnoFid']) || !empty($ctx['esAlumnoPpd'])) {
            if ($aporte->curso_institucional_id && isset($ctx['cursos']) && $ctx['cursos']->contains('id', $aporte->curso_institucional_id)) {
                return true;
            }
            $sugeridos = $aporte->correspondencia_sugerida_json['cursos_sugeridos'] ?? [];
            $cursoIdsCandidatos = collect($sugeridos)->pluck('curso_id')->filter()->values();
            if ($cursoIdsCandidatos->isNotEmpty() && isset($ctx['cursos'])) {
                return $ctx['cursos']->pluck('id')->intersect($cursoIdsCandidatos)->isNotEmpty();
            }
        }

        return false;
    }

    /**
     * 3. PERMISO COMENTAR / BITÁCORA EN MESA DEL CICLO:
     * Docentes y coordinadores pertenecientes a la mesa del ciclo pueden comentar y registrar acuerdos/avances.
     */
    public function puedeComentarEnMesaCiclo(User $user, string $cicloRomano, ?int $ejecucionId, array $ctx): bool
    {
        return $this->perteneceAMesaDocenteDelCiclo($user, $cicloRomano, $ejecucionId, $ctx);
    }

    /**
     * 4. PERMISO REVISAR E INTEGRAR AL PRODUCTO ANUAL:
     * Coordinadores del proyecto/ciclo o docentes miembros de la mesa del ciclo (para revisión cruzada),
     * siendo la integración final al producto anual atribución de la coordinación o responsable designado.
     */
    public function puedeRevisarAporte(User $user, AylluPiAporteCurso $aporte, array $ctx): bool
    {
        return $this->perteneceAMesaDocenteDelCiclo($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx);
    }

    public function puedeIntegrarAlProductoAnual(User $user, AylluPiAporteCurso $aporte, array $ctx): bool
    {
        if ($this->esCoordinadorProyectos($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx)) {
            return true;
        }

        return $this->esTutorDeCicloRomano($user, $aporte->ciclo_romano);
    }

    /**
     * 5. PERMISO APROBAR DEFINICIÓN CURRICULAR / ADOPTAR VERSIÓN / GESTIONAR ASIGNACIONES:
     */
    public function puedeAprobarDefinicionCurricular(User $user, array $ctx): bool
    {
        return $this->esCoordinadorProyectos($user, null, null, $ctx);
    }

    /**
     * 6. PERMISO PUBLICAR SÍNTESIS COMUNITARIA (Revisión editorial institucional):
     * Solicitar una síntesis puede hacerlo un docente responsable del ciclo;
     * aprobar y publicar la síntesis en el muro comunitario requiere coordinación/autoridad editorial.
     */
    public function puedeAprobarPublicacionSintesis(User $user, string $cicloRomano, ?int $ejecucionId, array $ctx): bool
    {
        return $this->esCoordinadorProyectos($user, $cicloRomano, $ejecucionId, $ctx)
            || $this->esTutorDeCicloRomano($user, $cicloRomano);
    }

    /**
     * 7. PERMISO VER EVIDENCIA VERSIONADA SEGÚN AUDIENCIA:
     * - borrador_personal: solo autor y colaboradores expresamente elegidos.
     * - mesa_docente_ciclo: solo docentes de la mesa del ciclo y coordinación (nunca estudiantes ni docentes ajenos).
     * - equipo_estudiantes: autor, integrantes aceptados del equipo Taq'e y docentes acompañantes del curso/ciclo.
     * - ciclo_completo: integrantes (estudiantes y docentes) del ciclo.
     * - comunidad_ayllu: miembros autenticados de Ayllu Puklla (siempre que no tenga autorización retirada).
     */
    public function puedeVerEvidenciaVersion(User $user, AylluPiEvidenciaVersion $version, array $ctx): bool
    {
        if ($version->restriccion_circulacion === 'autorizacion_retirada') {
            return (int) $version->autor_user_id === (int) $user->id
                || $this->esAutoridadInstitucional($user, $ctx);
        }

        if ((int) $version->autor_user_id === (int) $user->id) {
            return true;
        }

        $aporte = $version->aporte ?? AylluPiAporteCurso::find($version->aporte_curso_id);
        if (!$aporte) {
            return false;
        }

        $colaboradores = collect($version->colaboradores_autorizados_json ?? [])->map(fn ($id) => (int) $id);
        if ($colaboradores->contains((int) $user->id)) {
            return true;
        }

        return match ($version->audiencia) {
            'borrador_personal' => false,
            'mesa_docente_ciclo' => $this->perteneceAMesaDocenteDelCiclo($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx),
            'equipo_estudiantes' => $this->puedeAccederEvidenciaEquipo($user, $version, $aporte, $ctx),
            'ciclo_completo' => $this->perteneceAMesaDocenteDelCiclo($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx)
                || ($this->estudiantePerteneceACicloRomano($user, $aporte->ciclo_romano, $ctx)
                    && $this->estudianteCorrespondeAProgramaDeAporte($user, $aporte, $ctx)),
            'comunidad_ayllu' => true,
            default => false,
        };
    }

    protected function puedeAccederEvidenciaEquipo(
        User $user,
        AylluPiEvidenciaVersion $version,
        AylluPiAporteCurso $aporte,
        array $ctx
    ): bool {
        if ($this->perteneceAMesaDocenteDelCiclo($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx)) {
            return true;
        }

        if ($version->taqe_grupo_id && !empty($ctx['alumno'])) {
            $grupo = $version->taqeGrupo ?? TaqeGrupo::find($version->taqe_grupo_id);
            if ($grupo) {
                if ((int) $grupo->creador_alumno_id === (int) $ctx['alumno']->id) {
                    return true;
                }
                return $grupo->alumnosAceptados()->where('alumnos.id', $ctx['alumno']->id)->exists();
            }
        }

        return false;
    }

    /**
     * 8. PERMISO DESCARGAR ARCHIVO PRIVADO DE EVIDENCIA:
     * Compartir una síntesis comunitaria NO amplía los permisos de los archivos originales de la evidencia.
     * Si la evidencia tiene restricción `reserva_comunitaria` o `autorizacion_retirada`, se bloquea la descarga externa.
     */
    public function puedeDescargarArchivoEvidencia(User $user, AylluPiEvidenciaVersion $version, array $ctx): bool
    {
        if (empty($version->archivo_privado_path)) {
            return false;
        }

        if ($version->restriccion_circulacion === 'autorizacion_retirada') {
            return (int) $version->autor_user_id === (int) $user->id;
        }

        if ($version->restriccion_circulacion === 'reserva_comunitaria') {
            $aporte = $version->aporte ?? AylluPiAporteCurso::find($version->aporte_curso_id);
            return (int) $version->autor_user_id === (int) $user->id
                || ($aporte && $this->perteneceAMesaDocenteDelCiclo($user, $aporte->ciclo_romano, $aporte->ejecucion_id, $ctx));
        }

        return $this->puedeVerEvidenciaVersion($user, $version, $ctx);
    }

    /**
     * Verifica si el usuario puede ver una entrada de la bitácora según su audiencia.
     * Los estudiantes nunca ven entradas con audiencia `mesa_docente_ciclo` ni `borrador_personal`.
     */
    public function puedeVerEntradaBitacora(User $user, AylluPiBitacoraMesa $entrada, array $ctx): bool
    {
        if ((int) $entrada->autor_user_id === (int) $user->id) {
            return true;
        }

        return match ($entrada->audiencia) {
            'borrador_personal' => false,
            'mesa_docente_ciclo' => $this->perteneceAMesaDocenteDelCiclo($user, $entrada->ciclo_romano, $entrada->ejecucion_id, $ctx),
            'equipo_estudiantes' => $this->perteneceAMesaDocenteDelCiclo($user, $entrada->ciclo_romano, $entrada->ejecucion_id, $ctx)
                || ($entrada->taqe_grupo_id && !empty($ctx['alumno']) && DB::table('taqe_grupo_alumno')
                    ->where('taqe_grupo_id', $entrada->taqe_grupo_id)
                    ->where('alumno_id', $ctx['alumno']->id)
                    ->where('estado', 'aceptado')
                    ->exists()),
            'ciclo_completo' => $this->perteneceAMesaDocenteDelCiclo($user, $entrada->ciclo_romano, $entrada->ejecucion_id, $ctx)
                || $this->estudiantePerteneceACicloRomano($user, $entrada->ciclo_romano, $ctx),
            'comunidad_ayllu' => true,
            default => false,
        };
    }
}
