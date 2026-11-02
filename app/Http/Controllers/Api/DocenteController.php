<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocenteCursoResource;
use App\Models\Alumno;
use App\Models\Calificacionesppd;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\PeriodoDos;
use App\Models\PeriodoTres;
use App\Models\PeriodoUno;
use App\Models\ppd;
use App\Models\Sugerencia;
use App\Models\TaqeSolicitudAyuda;
use App\Models\Tutoria;
use App\Models\User;
use App\Services\AccesoCalificaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Lo que ve un docente desde la app: el mismo resumen y listado de cursos del
 * panel web (DocenteCOntroller@vistaDocente), funciones según roles (tutor,
 * investigador, titulación, etc.), y listado de alumnos de sus cursos.
 */
class DocenteController extends Controller
{
    public function resumen(Request $request)
    {
        $user = $request->user();
        $docente = $this->docente($request);
        $cursos = $this->cursosDelDocente($docente);

        $incidencias = \DB::table('incidencias')
            ->where('docente_id', $docente->id)
            ->selectRaw('count(*) as total, coalesce(sum(estado = ?), 0) as pendientes', ['pendiente'])
            ->first();

        // Roles del usuario
        $roles = $user->getRoleNames()->all();
        $esTutor = $user->hasRole('tutor');
        $esInvestigador = $user->hasRole('investigador');
        $esTitulacion = $user->hasAnyRole(['gestor-titulacion', 'secretaria']);
        $esBolsa = $user->hasRole('adminB');
        $esAdmin = $user->hasAnyRole(['admin', 'super-admin']);

        // Funciones de tutor
        $tutorData = null;
        if ($esTutor) {
            $ciclosTutor = $user->tutorCiclos()->with('programa')->get();
            $ciclosIds = $ciclosTutor->pluck('id');
            $tutoriasPendientes = Tutoria::whereIn('ciclo_id', $ciclosIds)->where('estado', 'pendiente')->count();
            $sugerenciasPendientes = Sugerencia::whereIn('ciclo_id', $ciclosIds)->where('estado', 'pendiente')->count();

            $tutorData = [
                'ciclos' => $ciclosTutor->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'programa' => $c->programa?->nombre,
                ])->values()->all(),
                'tutorias_pendientes' => $tutoriasPendientes,
                'sugerencias_pendientes' => $sugerenciasPendientes,
            ];
        }

        // Funciones de investigador (Taq'e)
        $investigadorData = null;
        if ($esInvestigador) {
            $solicitudesPendientes = TaqeSolicitudAyuda::where('estado', 'pendiente')->count();
            $investigadorData = [
                'solicitudes_pendientes' => $solicitudesPendientes,
            ];
        }

        // Proyectos integradores a cargo
        $proyectosACargo = [];
        if (method_exists($docente, 'proyectosIntegradores')) {
            $proyectosACargo = $docente->proyectosIntegradores()->get()->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'producto' => $p->producto,
                'rol' => $p->pivot->rol ?? 'encargado',
            ])->values()->all();
        }

        // Alumnos totales
        $totalAlumnos = 0;
        foreach ($cursos as $c) {
            $totalAlumnos += $this->esPpd($c)
                ? $this->alumnosPpdCount($c)
                : $c->alumnosValidosIds()->count();
        }

        return response()->json([
            'data' => [
                'periodo_actual' => optional(PeriodoActual::actual())->nombre,
                'cursos' => $cursos->count(),
                'cursos_fid' => $cursos->reject(fn (Curso $c) => $this->esPpd($c))->count(),
                'cursos_ppd' => $cursos->filter(fn (Curso $c) => $this->esPpd($c))->count(),
                'cursos_asincronicos' => $docente->cursosEspeciales()->count(),
                'incidencias_total' => (int) $incidencias->total,
                'incidencias_pendientes' => (int) $incidencias->pendientes,
                'notificaciones_sin_leer' => $request->user()->unreadNotifications()->count(),
                'total_alumnos' => $totalAlumnos,
                'roles' => $roles,
                'es_tutor' => $esTutor,
                'es_investigador' => $esInvestigador,
                'es_titulacion' => $esTitulacion,
                'es_bolsa' => $esBolsa,
                'es_admin' => $esAdmin,
                'tutor_data' => $tutorData,
                'investigador_data' => $investigadorData,
                'proyectos_a_cargo' => $proyectosACargo,
            ],
        ]);
    }

    public function cursos(Request $request)
    {
        $docente = $this->docente($request);
        $periodoActual = PeriodoActual::actual();

        $cursos = $this->cursosDelDocente($docente)
            ->sortBy(fn (Curso $curso) => $curso->nombre)
            ->values()
            ->map(fn (Curso $curso) => new DocenteCursoResource(
                $curso,
                $this->esPpd($curso) ? 'ppd' : 'fid',
                $this->tieneSilabo($curso, $periodoActual),
            ));

        return response()->json(['data' => $cursos]);
    }

    public function alumnos(Request $request)
    {
        $docente = $this->docente($request);
        $periodoActual = PeriodoActual::actual();

        $cursosConAlumnos = $docente->cursos()
            ->with(['ciclo.programa'])
            ->get()
            ->map(function (Curso $curso) {
                if ($this->esPpd($curso)) {
                    $alumnosPpd = $this->alumnosPpdDelCurso($curso);
                    $alumnos = $alumnosPpd->map(fn ($u) => [
                        'id' => $u->id,
                        'nombre_completo' => trim(($u->apellidos ?? '') . ', ' . ($u->name ?? ''), ', '),
                        'email' => $u->email,
                        'telefono' => $u->alumnoB?->numero ?: $u->telefono,
                        'telefono_referencia' => $u->alumnoB?->numero_referencia,
                        'dni' => $u->dni,
                    ])->values()->all();
                } else {
                    $alumnosIds = $curso->alumnosValidosIds();
                    $alumnos = Alumno::whereIn('id', $alumnosIds)
                        ->with('user')
                        ->orderBy('apellidos')
                        ->orderBy('nombres')
                        ->get()
                        ->map(fn (Alumno $a) => [
                            'id' => $a->id,
                            'nombre_completo' => $a->nombreCompleto(),
                            'email' => $a->email ?: $a->user?->email,
                            'telefono' => $a->numero ?: $a->user?->telefono,
                            'telefono_referencia' => $a->numero_referencia,
                            'dni' => $a->dni,
                        ])->values()->all();
                }

                return [
                    'curso_id' => $curso->id,
                    'curso_nombre' => $curso->nombre,
                    'ciclo' => $curso->ciclo?->nombre,
                    'programa' => $curso->ciclo?->programa?->nombre,
                    'area' => $this->esPpd($curso) ? 'ppd' : 'fid',
                    'total' => count($alumnos),
                    'alumnos' => $alumnos,
                ];
            })->values()->all();

        return response()->json(['data' => $cursosConAlumnos]);
    }

    public function tutorDashboard(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('tutor'), 403, 'No tienes el rol de tutor.');

        $ciclosTutor = $user->tutorCiclos()->with('programa')->get();
        $ciclosIds = $ciclosTutor->pluck('id');

        $tutorias = Tutoria::whereIn('ciclo_id', $ciclosIds)
            ->with(['alumno', 'ciclo.programa'])
            ->latest()
            ->take(30)
            ->get()
            ->map(fn (Tutoria $t) => [
                'id' => $t->id,
                'alumno_nombre' => $t->nombre_alumno ?: ($t->alumno ? $t->alumno->nombreCompleto() : 'Alumno'),
                'ciclo' => $t->ciclo?->nombre,
                'programa' => $t->ciclo?->programa?->nombre,
                'urgencia' => $t->urgencia_texto,
                'motivo' => $t->motivo,
                'estado' => $t->estado,
                'fecha' => $t->created_at?->format('d/m/Y H:i'),
            ])->values()->all();

        $sugerencias = Sugerencia::whereIn('ciclo_id', $ciclosIds)
            ->with(['alumno', 'ciclo.programa'])
            ->latest()
            ->take(30)
            ->get()
            ->map(fn (Sugerencia $s) => [
                'id' => $s->id,
                'alumno_nombre' => $s->alumno ? $s->alumno->nombreCompleto() : 'Alumno',
                'ciclo' => $s->ciclo?->nombre,
                'programa' => $s->ciclo?->programa?->nombre,
                'mensaje' => $s->mensaje,
                'estado' => $s->estado,
                'fecha' => $s->created_at?->format('d/m/Y H:i'),
            ])->values()->all();

        return response()->json([
            'data' => [
                'ciclos' => $ciclosTutor->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'programa' => $c->programa?->nombre,
                ])->values()->all(),
                'tutorias' => $tutorias,
                'sugerencias' => $sugerencias,
            ],
        ]);
    }

    /**
     * Devuelve los datos necesarios para calificar un curso:
     * - Información del curso y programa
     * - Estado de los períodos (si Parcial 1 o Parcial 2 / Desempeño están abiertos)
     * - Lista de competencias evaluadas en este curso (1 a 3)
     * - Lista de alumnos con sus calificaciones registradas previamente (FID o PPD)
     */
    public function calificaciones(Request $request, $cursoId)
    {
        AccesoCalificaciones::autorizarCurso($cursoId);

        $curso = Curso::with('ciclo.programa', 'competencias', 'competenciasSeleccionadas')->findOrFail($cursoId);
        $esPpd = $this->esPpd($curso);
        $periodoActual = PeriodoActual::where('actual', true)->first();

        $competencias = ($curso->competencias->count() <= 3 ? $curso->competencias : $curso->competenciasSeleccionadas)->values();
        if ($esPpd && $competencias->isEmpty()) {
            $competencias = $curso->competencias->sortBy(fn ($comp) => (int) preg_replace('/\D/', '', $comp->nombre))->values()->take(3);
        }

        $parcial1Habilitado = ! $periodoActual || $periodoActual->parcial1Habilitado();
        $parcial2Habilitado = ! $periodoActual || $periodoActual->parcial2DesempenoHabilitado();

        $alumnosRes = [];

        if ($esPpd) {
            $alumnos = $this->alumnosPpdDelCurso($curso);
            $notas = Calificacionesppd::where('curso_id', $curso->id)->get();
            $porPpd = $notas->whereNotNull('ppd_id')->keyBy('ppd_id');
            $porUser = $notas->whereNotNull('user_id')->keyBy('user_id');

            $alumnosRes = $alumnos->map(function ($u) use ($porPpd, $porUser) {
                $cal = ($u->alumnoB ? $porPpd->get($u->alumnoB->id) : null) ?? $porUser->get($u->id);
                return [
                    'id' => $u->id,
                    'ppd_id' => $u->alumnoB?->id,
                    'nombre_completo' => trim($u->apellidos . ', ' . $u->name, ', '),
                    'dni' => $u->dni,
                    'es_inhabilitado' => $u->roles->contains('name', 'inhabilitado'),
                    'no_matriculado' => false,
                    'calificacion' => $cal ? [
                        'pp_c1_4' => $cal->pp_c1_4,
                        'pf_c1_3' => $cal->pf_c1_3,
                        'pp_c2_4' => $cal->pp_c2_4,
                        'pf_c2_3' => $cal->pf_c2_3,
                        'pp_c3_4' => $cal->pp_c3_4,
                        'pf_c3_3' => $cal->pf_c3_3,
                        'calificacion_curso' => $cal->calificacion_curso,
                        'calificacion_sistema' => $cal->calificacion_sistema,
                        'nivel_desempeno' => $cal->nivel_desempeno,
                        'observaciones' => $cal->observaciones,
                    ] : null,
                ];
            })->values()->all();
        } else {
            [$alumnos] = $this->alumnosFidDelCurso($curso, $periodoActual);
            $p1Map = PeriodoUno::where('curso_id', $curso->id)->get()->keyBy('alumno_id');
            $p2Map = PeriodoDos::where('curso_id', $curso->id)->get()->keyBy('alumno_id');
            $p3Map = PeriodoTres::where('curso_id', $curso->id)->get()->keyBy('alumno_id');

            $alumnosRes = $alumnos->map(function ($a) use ($p1Map, $p2Map, $p3Map, $periodoActual) {
                $p1 = $p1Map->get($a->id);
                $p2 = $p2Map->get($a->id);
                $p3 = $p3Map->get($a->id);
                $inhabilitado = $a->user && $a->user->hasRole('inhabilitado');
                $noMatriculado = $periodoActual && ! $inhabilitado && ! $a->matriculaEnPeriodo($periodoActual->id);

                return [
                    'id' => $a->id,
                    'nombre_completo' => trim($a->apellidos . ', ' . $a->nombres, ', '),
                    'dni' => $a->dni,
                    'es_inhabilitado' => $inhabilitado,
                    'no_matriculado' => $noMatriculado,
                    'p1' => $p1 ? [
                        'valoracion_1' => $p1->valoracion_1 ? (int)$p1->valoracion_1 : null,
                        'valoracion_2' => $p1->valoracion_2 ? (int)$p1->valoracion_2 : null,
                        'valoracion_3' => $p1->valoracion_3 ? (int)$p1->valoracion_3 : null,
                        'valoracion_curso' => $p1->valoracion_curso ? (float)$p1->valoracion_curso : null,
                        'calificacion_curso' => $p1->calificacion_curso,
                        'calificacion_sistema' => $p1->calificacion_sistema ? (int)$p1->calificacion_sistema : null,
                        'observaciones' => $p1->observaciones,
                    ] : null,
                    'p2' => $p2 ? [
                        'valoracion_1' => $p2->valoracion_1 ? (int)$p2->valoracion_1 : null,
                        'valoracion_2' => $p2->valoracion_2 ? (int)$p2->valoracion_2 : null,
                        'valoracion_3' => $p2->valoracion_3 ? (int)$p2->valoracion_3 : null,
                        'valoracion_curso' => $p2->valoracion_curso ? (float)$p2->valoracion_curso : null,
                        'calificacion_curso' => $p2->calificacion_curso,
                        'calificacion_sistema' => $p2->calificacion_sistema ? (int)$p2->calificacion_sistema : null,
                        'observaciones' => $p2->observaciones,
                    ] : null,
                    'p3' => $p3 ? [
                        'valoracion_1' => $p3->valoracion_1 ? (int)$p3->valoracion_1 : null,
                        'valoracion_2' => $p3->valoracion_2 ? (int)$p3->valoracion_2 : null,
                        'valoracion_3' => $p3->valoracion_3 ? (int)$p3->valoracion_3 : null,
                        'valoracion_curso' => $p3->valoracion_curso ? (float)$p3->valoracion_curso : null,
                        'calificacion_curso' => $p3->calificacion_curso,
                        'calificacion_sistema' => $p3->calificacion_sistema ? (int)$p3->calificacion_sistema : null,
                        'observaciones' => $p3->observaciones,
                    ] : null,
                ];
            })->values()->all();
        }

        return response()->json([
            'data' => [
                'curso' => [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'area' => $esPpd ? 'ppd' : 'fid',
                    'es_ppd' => $esPpd,
                    'ciclo' => $curso->ciclo?->nombre,
                    'programa' => $curso->ciclo?->programa?->nombre,
                ],
                'periodo' => [
                    'nombre' => $periodoActual?->nombre,
                    'parcial1_habilitado' => $parcial1Habilitado,
                    'parcial2_habilitado' => $parcial2Habilitado,
                ],
                'competencias' => $competencias->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'descripcion' => $c->descripcion,
                ])->values()->all(),
                'alumnos' => $alumnosRes,
            ],
        ]);
    }

    /**
     * Guarda las calificaciones desde la app móvil.
     * En FID: soporta guardar por fase ('p1', 'p2', 'p3').
     * En PPD: guarda en calificacionesppds.
     */
    public function guardarCalificaciones(Request $request, $cursoId)
    {
        AccesoCalificaciones::autorizarCurso($cursoId);

        $curso = Curso::with('ciclo.programa', 'competencias', 'competenciasSeleccionadas')->findOrFail($cursoId);
        $esPpd = $this->esPpd($curso);
        $periodoActual = PeriodoActual::where('actual', true)->first();

        $competencias = ($curso->competencias->count() <= 3 ? $curso->competencias : $curso->competenciasSeleccionadas)->values();
        if ($esPpd && $competencias->isEmpty()) {
            $competencias = $curso->competencias->sortBy(fn ($comp) => (int) preg_replace('/\D/', '', $comp->nombre))->values()->take(3);
        }
        $numComps = max(1, $competencias->count());

        $request->validate([
            'periodo' => 'nullable|string|in:p1,p2,p3,ppd',
            'alumnos' => 'required|array',
        ]);

        $periodo = $request->input('periodo', $esPpd ? 'ppd' : 'p1');

        if ($esPpd) {
            foreach ($request->input('alumnos', []) as $data) {
                $ppdId = $data['ppd_id'] ?? null;
                $userId = $data['user_id'] ?? $data['id'] ?? null;
                if (empty($ppdId) && $userId) {
                    $ppdRecord = ppd::where('user_id', $userId)->first();
                    if (! $ppdRecord) {
                        $userRec = User::find($userId);
                        if ($userRec && $userRec->email) {
                            $ppdRecord = ppd::where('email', $userRec->email)->first();
                            if ($ppdRecord) {
                                $ppdRecord->user_id = $userId;
                                $ppdRecord->save();
                            }
                        }
                    }
                    $ppdId = $ppdRecord?->id;
                }

                $criterio = $ppdId ? ['curso_id' => $curso->id, 'ppd_id' => $ppdId] : ['curso_id' => $curso->id, 'user_id' => $userId];
                Calificacionesppd::updateOrCreate(
                    $criterio,
                    [
                        'user_id' => $userId,
                        'pp_c1_4' => $data['pp_c1_4'] ?? null,
                        'pf_c1_3' => $data['pf_c1_3'] ?? null,
                        'pp_c2_4' => $data['pp_c2_4'] ?? null,
                        'pf_c2_3' => $data['pf_c2_3'] ?? null,
                        'pp_c3_4' => $data['pp_c3_4'] ?? null,
                        'pf_c3_3' => $data['pf_c3_3'] ?? null,
                        'nivel_desempeno' => $data['nivel_desempeno'] ?? null,
                        'calificacion_curso' => isset($data['calificacion_curso']) ? (string) $data['calificacion_curso'] : null,
                        'calificacion_sistema' => isset($data['calificacion_sistema']) ? (string) $data['calificacion_sistema'] : null,
                        'observaciones' => $data['observaciones'] ?? null,
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Calificaciones PPD guardadas exitosamente.',
            ]);
        }

        // FID
        foreach ($request->input('alumnos', []) as $data) {
            $alumnoId = $data['alumno_id'] ?? $data['id'] ?? null;
            if (! $alumnoId) continue;

            $v1 = isset($data['valoracion_1']) && $data['valoracion_1'] !== '' ? (int) $data['valoracion_1'] : null;
            $v2 = isset($data['valoracion_2']) && $data['valoracion_2'] !== '' ? (int) $data['valoracion_2'] : null;
            $v3 = isset($data['valoracion_3']) && $data['valoracion_3'] !== '' ? (int) $data['valoracion_3'] : null;

            $validRatings = array_filter([$v1, $v2, $v3], fn ($v) => ! is_null($v));
            $promedio = count($validRatings) > 0 ? round(array_sum($validRatings) / $numComps, 2) : null;
            $vigesimal = self::calcularValoracionVigesimal($promedio);
            $nivel = self::calcularNivelLogro($vigesimal);

            $obs = $data['observaciones'] ?? null;

            if ($periodo === 'p1') {
                PeriodoUno::updateOrCreate(
                    ['alumno_id' => $alumnoId, 'curso_id' => $curso->id],
                    [
                        'valoracion_1' => $v1,
                        'valoracion_2' => $v2,
                        'valoracion_3' => $v3,
                        'valoracion_curso' => $promedio !== null ? number_format($promedio, 2, '.', '') : null,
                        'calificacion_curso' => $nivel,
                        'calificacion_sistema' => $vigesimal !== null ? (string) $vigesimal : null,
                        'observaciones' => $obs,
                    ]
                );
            } elseif ($periodo === 'p2') {
                PeriodoDos::updateOrCreate(
                    ['alumno_id' => $alumnoId, 'curso_id' => $curso->id],
                    [
                        'valoracion_1' => $v1,
                        'valoracion_2' => $v2,
                        'valoracion_3' => $v3,
                        'valoracion_curso' => $promedio !== null ? number_format($promedio, 2, '.', '') : null,
                        'calificacion_curso' => $nivel,
                        'calificacion_sistema' => $vigesimal !== null ? (string) $vigesimal : null,
                        'observaciones' => $obs,
                    ]
                );
            } elseif ($periodo === 'p3') {
                PeriodoTres::updateOrCreate(
                    ['alumno_id' => $alumnoId, 'curso_id' => $curso->id],
                    [
                        'valoracion_1' => $v1,
                        'valoracion_2' => $v2,
                        'valoracion_3' => $v3,
                        'valoracion_curso' => $promedio !== null ? number_format($promedio, 2, '.', '') : null,
                        'calificacion_curso' => $nivel,
                        'calificacion_sistema' => $vigesimal !== null ? (string) $vigesimal : null,
                        'observaciones' => $obs,
                    ]
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Calificaciones guardadas exitosamente.',
        ]);
    }

    public static function calcularValoracionVigesimal(?float $valor): ?int
    {
        if ($valor === null || $valor <= 0) return null;
        if ($valor <= 1.144) return 1;
        if ($valor <= 1.344) return 2;
        if ($valor <= 1.544) return 3;
        if ($valor <= 1.744) return 4;
        if ($valor <= 1.944) return 5;
        if ($valor <= 2.144) return 6;
        if ($valor <= 2.344) return 7;
        if ($valor <= 2.544) return 8;
        if ($valor <= 2.744) return 9;
        if ($valor <= 2.944) return 10;
        if ($valor <= 3.244) return 11;
        if ($valor <= 3.544) return 12;
        if ($valor <= 3.744) return 13;
        if ($valor <= 3.944) return 14;
        if ($valor <= 4.144) return 15;
        if ($valor <= 4.344) return 16;
        if ($valor <= 4.544) return 17;
        if ($valor <= 4.744) return 18;
        if ($valor <= 4.944) return 19;
        if ($valor <= 5.000) return 20;
        return null;
    }

    public static function calcularNivelLogro(?int $nota): ?string
    {
        if ($nota === null) return null;
        if ($nota <= 5) return 'Previo al inicio';
        if ($nota <= 10) return 'Inicio';
        if ($nota <= 14) return 'En proceso';
        if ($nota <= 19) return 'Logrado';
        if ($nota >= 20) return 'Destacado';
        return null;
    }

    private function alumnosFidDelCurso(Curso $curso, ?PeriodoActual $periodoActual): array
    {
        $filtroFidActivo = function ($q) {
            $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
              ->whereHas('roles', fn ($r) => $r->where('name', 'alumno'));
        };

        $filtroFidInhabilitado = function ($q) {
            $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
              ->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
              ->where('perfil', '!=', 'Retirado');
        };

        $alumnosRelacionadosActivos = $curso->alumnosDelPeriodo($periodoActual)
            ->with('user.roles')
            ->whereHas('user', $filtroFidActivo)
            ->get();

        $alumnosRelacionadosInhabilitados = collect();
        if ($periodoActual) {
            $alumnosRelacionadosInhabilitados = $curso->alumnos()
                ->with('user.roles')
                ->whereHas('user', $filtroFidInhabilitado)
                ->wherePivot('periodo_actual_id', $periodoActual->id)
                ->get();
        }

        $alumnosRelacionados = $alumnosRelacionadosActivos
            ->merge($alumnosRelacionadosInhabilitados)
            ->sortBy('apellidos')
            ->values();

        $alumnosCiclo = $curso->ciclo ? $curso->ciclo->alumnos()
            ->with('user.roles')
            ->whereHas('user', $filtroFidActivo)
            ->orderBy('apellidos')
            ->get() : collect();

        $alumnos = $alumnosCiclo
            ->merge($alumnosRelacionados)
            ->unique('id')
            ->sortBy('apellidos')
            ->values();

        return [$alumnos, $alumnosRelacionados];
    }

    private function docente(Request $request): Docente
    {
        $docente = $request->user()->docente;

        abort_unless(
            $docente,
            404,
            'No se encontró un perfil de docente para este usuario.'
        );

        return $docente;
    }

    /** @return Collection<int, Curso> */
    private function cursosDelDocente(Docente $docente): Collection
    {
        return $docente->cursos()
            ->with(['ciclo.programa', 'silabos', 'silabosPdf'])
            ->get();
    }

    /** Mismo criterio que el panel web: el programa del ciclo define el área. */
    private function esPpd(Curso $curso): bool
    {
        return str_contains($curso->ciclo->programa->nombre ?? '', 'PPD');
    }

    private function tieneSilabo(Curso $curso, ?PeriodoActual $periodoActual): bool
    {
        $silaboDelPeriodo = $curso->silabos->firstWhere('periodo_actual_id', $periodoActual?->id)
            ?? $curso->silabos->firstWhere('periodo', $periodoActual?->nombre);

        $silaboPdf = $curso->silabosPdf->firstWhere('periodo_actual_id', $periodoActual?->id);

        return (bool) ($silaboDelPeriodo || $silaboPdf || $curso->silabo);
    }

    private function alumnosPpdCount(Curso $curso): int
    {
        $programaId = $curso->ciclo?->programa_id;
        if (! $programaId) {
            return 0;
        }

        return \App\Models\User::where('programa_id', $programaId)
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
                  ->orWhere(function ($q2) {
                      $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                          ->where('perfil', '!=', 'Retirado');
                  });
            })
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            })
            ->count();
    }

    /** @return Collection<int, \App\Models\User> */
    private function alumnosPpdDelCurso(Curso $curso): Collection
    {
        $programaId = $curso->ciclo?->programa_id;
        if (! $programaId) {
            return collect();
        }

        return \App\Models\User::with(['alumnoB', 'roles'])
            ->where('programa_id', $programaId)
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
                  ->orWhere(function ($q2) {
                      $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                          ->where('perfil', '!=', 'Retirado');
                  });
            })
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            })
            ->orderBy('apellidos')
            ->get();
    }
}
