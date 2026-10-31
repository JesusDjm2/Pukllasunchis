<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocenteCursoResource;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\Sugerencia;
use App\Models\TaqeSolicitudAyuda;
use App\Models\Tutoria;
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
            $totalAlumnos += $c->alumnosValidosIds()->count();
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
                $alumnosIds = $curso->alumnosValidosIds();
                $alumnos = Alumno::whereIn('id', $alumnosIds)
                    ->with('user')
                    ->orderBy('apellidos')
                    ->orderBy('nombres')
                    ->get()
                    ->map(fn (Alumno $a) => [
                        'id' => $a->id,
                        'nombre_completo' => $a->nombreCompleto(),
                        'email' => $a->user?->email,
                        'telefono' => $a->telefono,
                        'dni' => $a->dni,
                    ])->values()->all();

                return [
                    'curso_id' => $curso->id,
                    'curso_nombre' => $curso->nombre,
                    'ciclo' => $curso->ciclo?->nombre,
                    'programa' => $curso->ciclo?->programa?->nombre,
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
}
