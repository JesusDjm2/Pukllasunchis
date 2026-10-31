<?php

namespace App\Http\Controllers;

use App\Exports\AlumnosFidExport;
use App\Exports\AlumnosPpdExport;
use App\Exports\BecasCalificacionesExport;
use App\Mail\NotificacionRegistro;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\Matricula;
use App\Models\MatriculaPpd;
use App\Models\PeriodoActual;
use App\Models\PeriodoActualPpd;
use App\Models\PeriodoDos;
use App\Models\PeriodoTres;
use App\Models\PeriodoUno;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function login()
    {
        if (auth()->check()) {
            return $this->redirectToDashboard(auth()->user());
        }

        return view('admin.login');
    }

    /**
     * Destino de RouteServiceProvider::HOME ('/home'): a dónde manda el
     * middleware "guest" a un usuario ya autenticado (p. ej. si intenta
     * enviar el formulario de /login con una sesión ya iniciada).
     */
    public function homeRedirect()
    {
        return $this->redirectToDashboard(auth()->user());
    }

    public function index(Request $request)
    {
        $alumno = auth()->user()?->alumno;
        $esSuperAdmin = (bool) auth()->user()?->hasRole('super-admin');

        // Periodo de matrícula que se muestra (FID y PPD tienen periodos distintos).
        // Por defecto el vigente; ?periodo= / ?periodo_ppd= permiten revisar uno anterior.
        $periodosFid = PeriodoActual::orderByDesc('id')->get(['id', 'nombre', 'actual']);
        $periodosPpd = PeriodoActualPpd::orderByDesc('id')->get(['id', 'nombre', 'actual', 'semestre_matricula']);
        $periodoFid = $periodosFid->firstWhere('id', (int) $request->input('periodo')) ?? $periodosFid->firstWhere('actual', true) ?? $periodosFid->first();
        $periodoPpd = $periodosPpd->firstWhere('id', (int) $request->input('periodo_ppd')) ?? $periodosPpd->firstWhere('actual', true) ?? $periodosPpd->first();
        // PPD es anual pero se matricula por semestre: ?semestre_ppd= o el abierto en el periodo.
        $semestrePpd = in_array((int) $request->input('semestre_ppd'), [1, 2], true) ? (int) $request->input('semestre_ppd') : ($periodoPpd?->semestre_matricula ?? 1);

        // Una sola consulta con todo lo que pintan las pestañas (antes: ~2300 consultas por
        // hasRole()/programa/ciclo/alumno resueltos fila por fila en la vista). La matrícula
        // del periodo elegido viene en la misma carga: el directorio reemplaza a "Matrículas".
        $usuarios = User::with([
            'roles', 'programa', 'ciclo.programa',
            'alumno.matriculas' => fn ($q) => $q->where('periodo_actual_id', $periodoFid?->id ?? 0),
            'alumnoB.matriculas' => fn ($q) => $q->where('periodo_actual_ppd_id', $periodoPpd?->id ?? 0)->where('semestre', $semestrePpd),
            'docente.cursos.ciclo.programa', 'tutorCiclos.programa',
        ])->orderBy('apellidos')->orderBy('name')->get();

        $conRol = fn (string $rol) => $usuarios->filter(fn ($u) => $u->roles->contains('name', $rol))->values();
        $listas = [
            'fid' => $conRol('alumno'),
            'ppd' => $conRol('alumnoB'),
            'inhabilitados' => $conRol('inhabilitado'),
            'docentes' => $conRol('docente'),
            'tutores' => $conRol('tutor'),
            'admins' => $conRol('admin'),
            'titulacion' => $conRol('gestor-titulacion'),
            'superadmins' => $esSuperAdmin ? $conRol('super-admin') : collect(),
            'bolsa' => $conRol('adminB'),
        ];

        // Estudiantes agrupados por Programa → Ciclo, en el orden real (FID antes que PPD,
        // Ciclo I antes que X, luego los "Egresados <año>-<I|II>" del más reciente al más
        // antiguo); los que no tienen programa/ciclo van en un grupo al final.
        $ordenCiclo = function ($ciclo) {
            if (! $ciclo) {
                return 99999;
            }
            if (($n = $ciclo->ordenCiclo()) < 999) { // 999 = "no es un ciclo I–X"
                return $n;
            }
            if (preg_match('/(\d{4})\s*-\s*(I{1,2})\b/', $ciclo->nombre, $m)) {
                return 1000 + (3000 - (int) $m[1]) * 10 + (3 - strlen($m[2]));
            }

            return 900;
        };
        // PPD dura un año: sus egresados se agrupan por PROMOCIÓN ("Promoción 2025"), no por su
        // ciclo (todos terminan en Ciclo II y se confundirían con quienes lo cursan ahora).
        $promocionesPpd = \App\Services\PromocionPpd::anios($usuarios->filter(fn ($u) => $u->programa?->esPpd()));
        $grupoDe = fn ($u) => $u->condicion === 'Egresado' && isset($promocionesPpd[$u->id])
            ? ($u->programa_id ?? 0) . '-p' . $promocionesPpd[$u->id]
            : ($u->programa_id ?? 0) . '-' . ($u->ciclo_id ?? 0);
        $agrupar = fn ($lista) => $lista
            ->groupBy($grupoDe)
            ->sortBy(fn ($g, $clave) => [
                $g->first()->programa ? 0 : 1,
                $g->first()->programa?->modalidad?->value ?? '',
                $g->first()->programa_id ?? 0,
                str_contains($clave, '-p') ? 2000 + (3000 - (int) substr($clave, strpos($clave, '-p') + 2)) : $ordenCiclo($g->first()->ciclo),
            ]);
        $gruposEstudiantes = [
            'fid' => $agrupar($listas['fid']),
            'ppd' => $agrupar($listas['ppd']),
            'inhabilitados' => $agrupar($listas['inhabilitados']),
        ];

        // Conteos (mismos nombres de antes: los usan las tarjetas de ambas vistas)
        $totalRecords        = $usuarios->count();
        $totalAlumnos        = $listas['fid']->count();
        $conteoAlumnos       = $totalAlumnos;
        $conteoPpd           = $listas['ppd']->count();
        $conteoDocentes      = $listas['docentes']->count();
        $conteoAdmin         = $listas['admins']->count();
        $conteoSuperAdmin    = $conRol('super-admin')->count();
        $conteoInhabilitados = $listas['inhabilitados']->count();
        $conteoTutores       = $listas['tutores']->count();
        // Becas: solo estudiantes vigentes (FID y PPD, sin egresados ni retirados). Antes se
        // mostraba junto al total de registros, que incluye docentes y personal.
        $vigentes = $listas['fid']->concat($listas['ppd'])->unique('id')
            ->reject(fn ($u) => $u->condicion === 'Egresado' || in_array($u->perfil, ['Egresado', 'Retirado'], true));
        $estudiantesVigentes = $vigentes->count();
        $becas = [
            'total' => $vigentes->where('beca', 1)->count(),
            'fid' => $vigentes->where('beca', 1)->filter(fn ($u) => $listas['fid']->contains('id', $u->id))->count(),
        ];
        $becas['ppd'] = $becas['total'] - $becas['fid'];
        $alumnosConBeca = $becas['total'];

        // Estado de matrícula de cada estudiante (misma regla que la exportación) y conteos por
        // pestaña para los filtros rápidos.
        $matriculas = [];
        $conteoMatricula = [];
        foreach (['fid', 'ppd', 'inhabilitados'] as $tab) {
            $conteoMatricula[$tab] = ['matriculado' => 0, 'sin_matricula' => 0, 'sin_ficha' => 0, 'por_verificar' => 0, 'sin_enviar' => 0];
            foreach ($listas[$tab] as $u) {
                $m = $matriculas[$u->id] ??= \App\Services\EstadoMatricula::de($u);
                if (isset($conteoMatricula[$tab][$m->estado])) {
                    $conteoMatricula[$tab][$m->estado]++;
                }
                if ($m->estado === 'matriculado' && ! $m->voucher_verificado) {
                    $conteoMatricula[$tab]['por_verificar']++;
                }
                if ($m->estado === 'matriculado' && $m->voucher_verificado && ! $m->ficha_enviada_at) {
                    $conteoMatricula[$tab]['sin_enviar']++;
                }
            }
        }

        $data = compact(
            'alumno', 'esSuperAdmin', 'listas', 'gruposEstudiantes', 'totalAlumnos', 'totalRecords', 'alumnosConBeca', 'becas', 'estudiantesVigentes',
            'conteoDocentes', 'conteoAdmin', 'conteoSuperAdmin', 'conteoInhabilitados',
            'conteoAlumnos', 'conteoPpd', 'conteoTutores',
            'matriculas', 'conteoMatricula', 'periodosFid', 'periodosPpd', 'periodoFid', 'periodoPpd', 'semestrePpd', 'promocionesPpd'
        );

        // Super-admin usa layout y vista propios (sin conflicto con admin)
        if (auth()->user()?->hasRole('super-admin')) {
            return view('superadmin.dashboard', $data);
        }

        return view('admin.index', $data);
    }

    /**
     * Las antiguas pantallas "Matrículas FID / PPD" ahora son pestañas del directorio
     * (/Administrador). Se redirige para no romper marcadores ni enlaces viejos.
     */
    public function directorioRedirect(Request $request, string $tab)
    {
        $query = array_filter(['periodo' => $tab !== 'ppd' ? $request->input('periodo_id') : null,
            'periodo_ppd' => $tab === 'ppd' ? $request->input('periodo_id') : null]);

        return redirect()->to(route('admin', $query) . '#tab=' . $tab);
    }

    /**
     * Exportador único del directorio: exporta EXACTAMENTE lo que el usuario ve (o lo que
     * seleccionó). La lista de ids la arma la vista con los filtros aplicados.
     */
    public function exportarDirectorio(Request $request)
    {
        $datos = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:5000'],
            'ids.*' => ['integer'],
            'tab' => ['required', 'in:fid,ppd,inhabilitados'],
            'preset' => ['required', Rule::in(array_keys(\App\Exports\DirectorioEstudiantesExport::PRESETS))],
            'periodo' => ['nullable', 'integer'],
            'periodo_ppd' => ['nullable', 'integer'],
            'semestre_ppd' => ['nullable', 'in:1,2'],
        ], ['ids.required' => 'No hay estudiantes para exportar con los filtros actuales.']);

        $periodoFid = PeriodoActual::find($datos['periodo'] ?? null) ?? PeriodoActual::actual();
        $periodoPpd = PeriodoActualPpd::find($datos['periodo_ppd'] ?? null) ?? PeriodoActualPpd::actual();
        $esPpd = $datos['tab'] === 'ppd';
        $semestrePpd = (int) ($datos['semestre_ppd'] ?? ($periodoPpd?->semestre_matricula ?? 1));
        $sufijo = $datos['tab'] . '_' . now()->format('Y-m-d_His') . '.xlsx';

        // Ficha completa (socioeconómica): las exportaciones de siempre, con los mismos alumnos.
        if ($datos['preset'] === 'ficha') {
            if ($esPpd) {
                $usuarios = User::with(['programa', 'ciclo.programa', 'alumnoB', 'roles'])->whereIn('id', $datos['ids'])
                    ->has('alumnoB')->orderBy('apellidos')->orderBy('name')->get();

                return Excel::download(new AlumnosPpdExport($usuarios), 'estudiantes_ficha_' . $sufijo);
            }
            $alumnos = Alumno::with(['programa', 'ciclo', 'user.roles',
                'matriculas' => fn ($q) => $q->where('periodo_actual_id', $periodoFid?->id ?? 0)])
                ->whereIn('user_id', $datos['ids'])->orderBy('apellidos')->orderBy('nombres')->get();

            return Excel::download(new AlumnosFidExport($alumnos, $periodoFid?->id), 'estudiantes_ficha_' . $sufijo);
        }

        $usuarios = User::with([
            'programa', 'ciclo', 'roles',
            'alumno.matriculas' => fn ($q) => $q->where('periodo_actual_id', $periodoFid?->id ?? 0),
            'alumnoB.matriculas' => fn ($q) => $q->where('periodo_actual_ppd_id', $periodoPpd?->id ?? 0)->where('semestre', $semestrePpd),
        ])->whereIn('id', $datos['ids'])->orderBy('apellidos')->orderBy('name')->get();

        return Excel::download(
            new \App\Exports\DirectorioEstudiantesExport($usuarios, $datos['preset'], $esPpd ? $periodoPpd?->nombre . ' · ' . ($semestrePpd === 2 ? '2.º' : '1.er') . ' semestre' : $periodoFid?->nombre),
            'estudiantes_' . $datos['preset'] . '_' . $sufijo
        );
    }

    public function alumnos(Request $request)
    {
        $periodoActual = PeriodoActual::where('actual', true)->first();
        $periodoFiltroId = $request->filled('periodo_id')
            ? (int) $request->input('periodo_id')
            : ($periodoActual?->id);

        $soloBecas = $request->get('solo_becas') === '1';

        $estadoMatricula = in_array($request->input('estado_matricula'), ['matriculados', 'no_matriculados'], true)
            ? $request->input('estado_matricula')
            : null;

        $query = $this->alumnosFidFilteredQuery($request);

        // Conteos para la tarjeta resumen: se calculan ANTES de aplicar el filtro de
        // estado de matrícula, para que el resumen siempre refleje el universo completo
        // (programa/ciclo/búsqueda) independientemente de qué estado esté filtrado.
        $totalListadoBase = $periodoFiltroId ? (clone $query)->count() : null;
        $totalMatriculadosBase = $periodoFiltroId
            ? (clone $query)->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoFiltroId))->count()
            : null;

        if ($periodoFiltroId && $estadoMatricula === 'matriculados') {
            $query->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoFiltroId));
        } elseif ($periodoFiltroId && $estadoMatricula === 'no_matriculados') {
            $query->whereDoesntHave('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoFiltroId));
        }

        $busquedaActiva = $request->filled('search') && trim((string) $request->input('search')) !== '';

        $alumnos = $query
            ->with([
                'programa',
                'ciclo.tutores',
                'user.roles',
                'matriculas' => fn ($q) => $q->where('periodo_actual_id', $periodoFiltroId),
            ])
            ->orderByRaw('programa_id IS NULL, programa_id')
            ->orderByRaw('ciclo_id IS NULL, ciclo_id')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
        $totalRecords = $alumnos->count();

        $conteoGrupoListado = $alumnos->groupBy(function (Alumno $a) {
            return (string) ($a->programa_id ?? '0').'|'.(string) ($a->ciclo_id ?? '0');
        })->map->count();

        $totalesPorCicloId = Alumno::query()
            ->whereHas('user', fn ($sub) => $this->applyAlumnoFidUserConstraints($sub))
            ->when($soloBecas, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('beca', 1)))
            ->whereNotNull('ciclo_id')
            ->selectRaw('ciclo_id, COUNT(*) as total')
            ->groupBy('ciclo_id')
            ->get()
            ->keyBy('ciclo_id');

        // Mismos totales por ciclo pero solo de alumnos matriculados en el periodo
        // filtrado — alimenta la vista previa del modal de exportación cuando se
        // elige "Solo matriculados" / "Faltan matricularse".
        $totalesMatriculadosPorCicloId = $periodoFiltroId
            ? Alumno::query()
                ->whereHas('user', fn ($sub) => $this->applyAlumnoFidUserConstraints($sub))
                ->when($soloBecas, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('beca', 1)))
                ->whereNotNull('ciclo_id')
                ->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoFiltroId))
                ->selectRaw('ciclo_id, COUNT(*) as total')
                ->groupBy('ciclo_id')
                ->get()
                ->keyBy('ciclo_id')
            : collect();

        if ($alumnos->isEmpty() && ! $request->has('search_page')) {
            session()->flash('error', 'No se han encontrado resultados. Se ha buscado un total de '.$totalRecords.' registros.');
        }

        $programasFiltro = Programa::query()
            ->whereHas('alumnos', function ($q) use ($soloBecas) {
                $q->whereHas('user', fn ($sub) => $this->applyAlumnoFidUserConstraints($sub));
                if ($soloBecas) {
                    $q->whereHas('user', fn ($u) => $u->where('beca', 1));
                }
            })
            ->orderBy('nombre')
            ->get();

        $ciclosFiltro = collect();
        if ($request->filled('programa_id')) {
            $ciclosFiltro = Ciclo::query()
                ->where('programa_id', (int) $request->input('programa_id'))
                ->whereHas('alumnos', function ($q) use ($soloBecas) {
                    $q->whereHas('user', fn ($sub) => $this->applyAlumnoFidUserConstraints($sub));
                    if ($soloBecas) {
                        $q->whereHas('user', fn ($u) => $u->where('beca', 1));
                    }
                })
                ->orderBy('id')
                ->get();
        }

        $ciclosParaExportacion = Ciclo::query()
            ->with('programa')
            ->whereHas('alumnos', function ($q) use ($periodoFiltroId, $soloBecas) {
                $q->whereHas('user', fn ($sub) => $this->applyAlumnoFidUserConstraints($sub))
                    ->whereNotNull('ciclo_id');
                if ($periodoFiltroId) {
                    $q->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoFiltroId));
                }
                if ($soloBecas) {
                    $q->whereHas('user', fn ($u) => $u->where('beca', 1));
                }
            })
            ->orderBy('programa_id')
            ->orderBy('id')
            ->get();

        $todosLosPeriodos = PeriodoActual::orderBy('nombre', 'asc')->get();

        if ($request->boolean('partial')) {
            return view('alumnos._tabla_fid', compact('alumnos', 'conteoGrupoListado', 'totalesPorCicloId', 'periodoFiltroId'));
        }

        return view('alumnos.index', compact(
            'alumnos',
            'totalRecords',
            'programasFiltro',
            'ciclosFiltro',
            'conteoGrupoListado',
            'totalesPorCicloId',
            'totalesMatriculadosPorCicloId',
            'busquedaActiva',
            'ciclosParaExportacion',
            'periodoActual',
            'periodoFiltroId',
            'todosLosPeriodos',
            'soloBecas',
            'estadoMatricula',
            'totalListadoBase',
            'totalMatriculadosBase',
        ));
    }

    public function exportAlumnosExcel(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'ciclo_ids' => ['required', 'array', 'min:1'],
            'ciclo_ids.*' => ['integer', 'exists:ciclos,id'],
            'estado_matricula' => ['nullable', 'in:matriculados,no_matriculados'],
            'solo_importantes' => ['nullable', 'boolean'],
        ]);

        $cicloIds = array_values(array_unique(array_map('intval', $validated['ciclo_ids'])));
        $estadoMatricula = $validated['estado_matricula'] ?? null;
        $soloImportantes = $request->boolean('solo_importantes');

        $periodoFiltroId = $request->filled('periodo_id')
            ? (int) $request->input('periodo_id')
            : (PeriodoActual::where('actual', true)->first()?->id);

        $query = $this->alumnosFidFilteredQuery($request, false)
            ->whereIn('ciclo_id', $cicloIds);

        if ($periodoFiltroId && $estadoMatricula === 'matriculados') {
            $query->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoFiltroId));
        } elseif ($periodoFiltroId && $estadoMatricula === 'no_matriculados') {
            $query->whereDoesntHave('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoFiltroId));
        }

        $alumnos = $query
            ->with([
                'programa',
                'ciclo',
                'user.roles',
                'matriculas' => fn ($q) => $periodoFiltroId ? $q->where('periodo_actual_id', $periodoFiltroId) : $q,
            ])
            ->orderByRaw('programa_id IS NULL, programa_id')
            ->orderByRaw('ciclo_id IS NULL, ciclo_id')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $prefijo = $request->get('solo_becas') === '1' ? 'alumnos_becas_fid' : 'alumnos_fid';
        $nombreArchivo = $prefijo.'_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new AlumnosFidExport($alumnos, $periodoFiltroId, $soloImportantes), $nombreArchivo);
    }

    /**
     * Calificaciones (Parcial 1, Parcial 2, Desempeño) de alumnos becarios.
     * Lee directo de periodo_uno/periodo_dos/periodo_tres (datos en vivo del ciclo
     * en curso), no de la tabla `periodos` que solo se llena al archivar un periodo cerrado.
     */
    public function exportBecasCalificaciones()
    {
        if (! auth()->check() || ! auth()->user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $alumnoIds = Alumno::whereHas('user', fn ($q) => $q->where('beca', 1))->pluck('id');

        $parcial1 = PeriodoUno::whereIn('alumno_id', $alumnoIds)->get()->groupBy('alumno_id');
        $parcial2 = PeriodoDos::whereIn('alumno_id', $alumnoIds)->get()->groupBy('alumno_id');
        $desempeno = PeriodoTres::whereIn('alumno_id', $alumnoIds)->get()->groupBy('alumno_id');

        $cursoIds = $parcial1->flatten()->pluck('curso_id')
            ->merge($parcial2->flatten()->pluck('curso_id'))
            ->merge($desempeno->flatten()->pluck('curso_id'))
            ->unique();

        $cursos = Curso::with('ciclo')->whereIn('id', $cursoIds)->get()->keyBy('id');
        $alumnos = Alumno::with(['user', 'programa'])->whereIn('id', $alumnoIds)->get()->keyBy('id');

        $filas = collect();

        foreach ($alumnoIds as $alumnoId) {
            $p1ByCurso = ($parcial1->get($alumnoId) ?? collect())->keyBy('curso_id');
            $p2ByCurso = ($parcial2->get($alumnoId) ?? collect())->keyBy('curso_id');
            $p3ByCurso = ($desempeno->get($alumnoId) ?? collect())->keyBy('curso_id');

            $alumnoCursoIds = $p1ByCurso->keys()
                ->merge($p2ByCurso->keys())
                ->merge($p3ByCurso->keys())
                ->unique();

            foreach ($alumnoCursoIds as $cursoId) {
                $curso = $cursos->get($cursoId);
                if (! $curso) {
                    continue;
                }

                $filas->push([
                    'alumno' => $alumnos->get($alumnoId),
                    'curso' => $curso,
                    'parcial1' => $p1ByCurso->get($cursoId),
                    'parcial2' => $p2ByCurso->get($cursoId),
                    'desempeno' => $p3ByCurso->get($cursoId),
                ]);
            }
        }

        $nombreArchivo = 'Calificaciones_Becas_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new BecasCalificacionesExport($filas), $nombreArchivo);
    }

    public function alumnoCarnet(Alumno $alumno)
    {
        $this->assertAlumnoCarnetAutorizado($alumno);

        return view('alumnos.carnet', array_merge(
            $this->buildCarnetViewData($alumno),
            ['alumno' => $alumno]
        ));
    }

    public function alumnosppd(Request $request)
    {
        $periodoActualPpd = PeriodoActualPpd::where('actual', true)->first();
        $periodoFiltroId = $request->filled('periodo_id')
            ? (int) $request->input('periodo_id')
            : ($periodoActualPpd?->id);

        $estadoMatricula = in_array($request->input('estado_matricula'), ['matriculados', 'no_matriculados'], true)
            ? $request->input('estado_matricula')
            : null;

        $query = $this->alumnosPpdFilteredQuery($request);
        $busquedaActiva = $request->filled('search') && trim((string) $request->input('search')) !== '';

        $totalListadoBase = $periodoFiltroId ? (clone $query)->count() : null;
        $totalMatriculadosBase = $periodoFiltroId
            ? (clone $query)->whereHas('alumnoB.matriculas', fn ($m) => $m->where('periodo_actual_ppd_id', $periodoFiltroId))->count()
            : null;

        if ($periodoFiltroId && $estadoMatricula === 'matriculados') {
            $query->whereHas('alumnoB.matriculas', fn ($m) => $m->where('periodo_actual_ppd_id', $periodoFiltroId));
        } elseif ($periodoFiltroId && $estadoMatricula === 'no_matriculados') {
            $query->where(function ($q) use ($periodoFiltroId) {
                $q->whereDoesntHave('alumnoB')
                    ->orWhereDoesntHave('alumnoB.matriculas', fn ($m) => $m->where('periodo_actual_ppd_id', $periodoFiltroId));
            });
        }

        $alumnos = $query
            ->with([
                'programa',
                'ciclo.programa',
                'alumnoB.matriculas' => fn ($q) => $q->where('periodo_actual_ppd_id', $periodoFiltroId),
                'roles',
            ])
            ->orderByRaw('programa_id IS NULL, programa_id')
            ->orderByRaw('ciclo_id IS NULL, ciclo_id')
            ->orderBy('apellidos')
            ->orderBy('name')
            ->get();

        $totalRecords = $alumnos->count();

        $conteoGrupoListado = $alumnos->groupBy(function (User $u) {
            return (string) ($u->programa_id ?? '0').'|'.(string) ($u->ciclo_id ?? '0');
        })->map->count();

        $totalesPorCicloId = User::role('alumnoB')
            ->whereNotNull('ciclo_id')
            ->selectRaw('ciclo_id, COUNT(*) as total')
            ->groupBy('ciclo_id')
            ->get()
            ->keyBy('ciclo_id');

        $programaIdsConPpd = User::role('alumnoB')
            ->whereNotNull('programa_id')
            ->distinct()
            ->pluck('programa_id');

        $programasFiltro = Programa::query()
            ->whereIn('id', $programaIdsConPpd)
            ->orderBy('nombre')
            ->get();

        $ciclosFiltro = collect();
        if ($request->filled('programa_id')) {
            $cicloIdsConPpd = User::role('alumnoB')
                ->where('programa_id', (int) $request->input('programa_id'))
                ->whereNotNull('ciclo_id')
                ->distinct()
                ->pluck('ciclo_id');

            $ciclosFiltro = Ciclo::query()
                ->whereIn('id', $cicloIdsConPpd)
                ->where('programa_id', (int) $request->input('programa_id'))
                ->orderBy('id')
                ->get();
        }

        $cicloIdsConPpdTotal = User::role('alumnoB')
            ->whereNotNull('ciclo_id')
            ->distinct()
            ->pluck('ciclo_id');

        $ciclosParaExportacion = Ciclo::query()
            ->with('programa')
            ->whereIn('id', $cicloIdsConPpdTotal)
            ->orderBy('programa_id')
            ->orderBy('id')
            ->get();

        $todosLosPeriodosPpd = PeriodoActualPpd::orderBy('nombre', 'asc')->get();

        if ($request->boolean('partial')) {
            return view('alumnos.ppd._tabla_ppd', compact('alumnos', 'conteoGrupoListado', 'totalesPorCicloId', 'periodoFiltroId'));
        }

        return view('alumnos.ppd.lista', compact(
            'alumnos',
            'totalRecords',
            'programasFiltro',
            'ciclosFiltro',
            'conteoGrupoListado',
            'totalesPorCicloId',
            'busquedaActiva',
            'ciclosParaExportacion',
            'periodoActualPpd',
            'periodoFiltroId',
            'todosLosPeriodosPpd',
            'estadoMatricula',
            'totalListadoBase',
            'totalMatriculadosBase',
        ));
    }

    public function exportAlumnosPpdExcel(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'ciclo_ids' => ['required', 'array', 'min:1'],
            'ciclo_ids.*' => ['integer', 'exists:ciclos,id'],
        ]);

        $cicloIds = array_values(array_unique(array_map('intval', $validated['ciclo_ids'])));

        $alumnos = $this->alumnosPpdFilteredQuery($request, false)
            ->whereIn('ciclo_id', $cicloIds)
            ->with(['programa', 'ciclo.programa', 'alumnoB', 'roles'])
            ->orderByRaw('programa_id IS NULL, programa_id')
            ->orderByRaw('ciclo_id IS NULL, ciclo_id')
            ->orderBy('apellidos')
            ->orderBy('name')
            ->get();

        $nombreArchivo = 'alumnos_ppd_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new AlumnosPpdExport($alumnos), $nombreArchivo);
    }

    public function relacionarUsuario($alumnoId)
    {
        $alumno = Alumno::find($alumnoId);
        $user = new User([
            'name' => $alumno->nombres,
            'apellidos' => $alumno->apellidos,
            'dni' => $alumno->dni,
            'email' => $alumno->email,
            'password' => Hash::make($alumno->dni),
        ]);

        $user->save();

        $alumno->user()->associate($user);
        $alumno->save();

        return redirect()->route('login')->with('success', 'Relación con usuario establecida correctamente.');
    }

    public function asignarRolAlumno($alumnoId)
    {
        $user = User::find($alumnoId);
        if (! $user) {
            return response()->json(['error' => 'Usuario no encontrado.'], 404);
        }
        $user->assignRole('alumno');

        return response()->json(['success' => 'Rol asignado correctamente.']);
    }

    public function create()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::all();
        $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin';

        return view('admin.create', compact('programas', 'ciclos', 'cursos', 'layout'));
    }

    public function edit($id)
    {
        $admin = User::findOrFail($id);

        if ($admin->hasRole('super-admin') && !auth()->user()?->hasRole('super-admin')) {
            abort(403, 'No tienes autorización para editar una cuenta con privilegios de super-admin.');
        }

        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $currentProgramId = $admin->programa_id;
        $currentRoles = $admin->getRoleNames()->toArray();
        $currentCicloId = $admin->ciclo_id;
        $departamentosData = Departamento::all();
        $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin';

        return view('admin.edit', compact('admin', 'programas', 'ciclos', 'currentRoles', 'currentCicloId', 'currentProgramId', 'departamentosData', 'layout'));
    }

    /**
     * users.pendiente a partir del formulario: los cursos elegidos ("Nombre - Ciclo X") unidos
     * con " | ", o nada si se marcó "No" o no se eligió ninguno. Si llega el campo de texto
     * antiguo (otra pantalla), se respeta tal cual.
     */
    private function pendienteDesdeRequest(Request $request): ?string
    {
        if (! $request->has('pendientes') && ! $request->has('tiene_cursos_pendientes')) {
            return $request->input('pendiente');
        }
        if ($request->input('tiene_cursos_pendientes') === '0') {
            return null;
        }
        $cursos = collect($request->input('pendientes', []))->map(fn ($c) => trim((string) $c))->filter()->unique()->values();

        $texto = $cursos->implode(User::SEPARADOR_PENDIENTES);
        // La columna admite 500 caracteres (unos 8 cursos con su ciclo).
        if (mb_strlen($texto) > 500) {
            throw \Illuminate\Validation\ValidationException::withMessages(['pendientes' => 'Demasiados cursos pendientes para guardarlos juntos: quite alguno.']);
        }

        return $cursos->isEmpty() ? null : $texto;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:255',
            'condicion' => 'nullable|string|max:40',
            'pendiente' => 'nullable|string',
            'pendientes' => 'nullable|array',
            'pendientes.*' => 'string|max:255',
            'perfil' => 'nullable|string',
            'beca' => 'nullable|boolean',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:7|confirmed',
            'roles' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
                if (in_array('investigador', $value, true) && ! in_array('docente', $value, true)) {
                    $fail('El rol Investigador requiere que también se asigne el rol Docente.');
                }
            }],
            'roles.*' => 'string|in:super-admin,admin,docente,alumno,adminB,alumnoB,inhabilitado,tutor,investigador,gestor-titulacion,seguimiento-egresados',
            'programa_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:programas,id'],
            'ciclo_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:ciclos,id'],
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'apellidos' => $request->input('apellidos'),
            'dni' => $request->input('dni'),
            'condicion' => $request->input('condicion'),
            'pendiente' => $this->pendienteDesdeRequest($request),
            'perfil' => $request->input('perfil'),
            'beca' => $request->input('beca'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        $roles = $request->input('roles', []);

        // Protección contra elevación de privilegios: solo un super-admin puede otorgar super-admin
        if (in_array('super-admin', $roles, true) && !auth()->user()?->hasRole('super-admin')) {
            abort(403, 'Solo un super-admin puede asignar el rol super-admin.');
        }

        $user->syncRoles($roles);

        if (in_array('docente', $roles)) {
            $docente = new Docente;
            $docente->nombre = $request->input('name').' '.$request->input('apellidos');
            $docente->dni = $request->input('dni');
            $docente->email = $request->input('email');
            $docente->user_id = $user->id;
            $docente->save();
        }

        if (in_array('alumno', $roles) || in_array('alumnoB', $roles)) {
            $user->programa()->associate($request->input('programa_id'));
            $user->ciclo()->associate($request->input('ciclo_id'));
            $user->save();

            if ($request->has('cursos')) {
                $user->cursos()->attach($request->input('cursos'));
            }
        }

        return redirect()->route('admin')->with('success', 'Nuevo usuario creado exitosamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:255',
            'condicion' => 'nullable|string',
            'perfil' => 'nullable|string',
            'pendiente' => 'nullable|string',
            'pendientes' => 'nullable|array',
            'pendientes.*' => 'string|max:255',
            'beca' => 'nullable|boolean',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:7|confirmed',
            'roles' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
                if (in_array('investigador', $value, true) && ! in_array('docente', $value, true)) {
                    $fail('El rol Investigador requiere que también se asigne el rol Docente.');
                }
            }],
            'roles.*' => 'string|in:super-admin,admin,docente,alumno,adminB,alumnoB,inhabilitado,tutor,investigador,gestor-titulacion,seguimiento-egresados',
            'programa_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:programas,id'],
            'ciclo_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:ciclos,id'],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'telefono' => 'nullable|string|max:20',
            'whatsapp_key' => 'nullable|string|max:20',
        ]);

        $user = User::findOrFail($id);

        // Licencia: máximo 2 años ACUMULADOS (aunque estén partidas) — ver Trayectoria.
        if ($request->input('perfil') === 'Licencia' && $user->perfil !== 'Licencia'
            && ! \App\Services\Trayectoria::puedeIniciarLicencia($user)) {
            return back()->withInput()->withErrors(['perfil' => \App\Services\Trayectoria::mensajeLicenciaAgotada($user)]);
        }

        $user->name = $request->input('name');
        $user->apellidos = $request->input('apellidos');
        $user->dni = $request->input('dni');
        $user->condicion = $request->input('condicion');
        $user->perfil = $request->input('perfil');
        $user->pendiente = $this->pendienteDesdeRequest($request);
        $user->beca = $request->input('beca');
        $user->email = $request->input('email');

        /* if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        } */
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['string', 'min:7', 'confirmed'],
            ]);
            $user->password = Hash::make($request->input('password'));
        }

        // Manejo de la imagen
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = time().'_'.$foto->getClientOriginalName();
            $foto->move(public_path('img/estudiantes'), $nombreFoto);
            $user->foto = $nombreFoto;
        }

        $roles = $request->input('roles', []);
        $authUser = auth()->user();

        // 1. Protección de cuentas privilegiadas existentes
        if ($user->hasRole('super-admin') && !$authUser?->hasRole('super-admin')) {
            abort(403, 'No tienes autorización para modificar una cuenta con privilegios de super-admin.');
        }

        // 2. Prevención de elevación de privilegios hacia super-admin
        if (in_array('super-admin', $roles, true) && !$authUser?->hasRole('super-admin')) {
            abort(403, 'Solo un super-admin puede asignar el rol super-admin.');
        }

        // 3. Prevención de auto-elevación indebida de privilegios
        if ((int)$user->id === (int)$authUser?->id && in_array('super-admin', $roles, true) && !$authUser->hasRole('super-admin')) {
            abort(403, 'No puedes elevar tus propios privilegios.');
        }

        if (in_array('docente', $roles) || in_array('tutor', $roles)) {
            $user->telefono = $request->input('telefono');
        }
        if (in_array('tutor', $roles)) {
            $user->whatsapp_key = $request->input('whatsapp_key');
        }

        // Trazabilidad y auditoría institucional de asignación/revocación de roles
        $teniaSeguimiento = $user->hasRole('seguimiento-egresados');
        $tendraSeguimiento = in_array('seguimiento-egresados', $roles, true);

        if ($teniaSeguimiento !== $tendraSeguimiento) {
            if (!auth()->user()?->hasAnyRole(['super-admin', 'admin'])) {
                abort(403, 'No tienes autorización para asignar o revocar el rol de seguimiento de egresados.');
            }
        }

        $rolesAnteriores = $user->getRoleNames()->toArray();
        $user->syncRoles($roles);

        if (!$teniaSeguimiento && $tendraSeguimiento) {
            \App\Models\SeguimientoEgresado\AuditoriaRolSeguimiento::registrarEvento(
                $user,
                'asignado',
                'seguimiento-egresados',
                'Asignación del rol Seguimiento de Egresados desde panel administrativo',
                $rolesAnteriores,
                $user->getRoleNames()->toArray()
            );
            \Illuminate\Support\Facades\Log::info("Rol 'seguimiento-egresados' asignado al usuario ID {$user->id} por admin ID " . auth()->id());
        } elseif ($teniaSeguimiento && !$tendraSeguimiento) {
            \App\Models\SeguimientoEgresado\AuditoriaRolSeguimiento::registrarEvento(
                $user,
                'revocado',
                'seguimiento-egresados',
                'Revocación del rol Seguimiento de Egresados desde panel administrativo',
                $rolesAnteriores,
                $user->getRoleNames()->toArray()
            );
            \Illuminate\Support\Facades\Log::info("Rol 'seguimiento-egresados' revocado del usuario ID {$user->id} por admin ID " . auth()->id());
        }

        if (in_array('docente', $roles)) {
            $docente = $user->docente;
            if (! $docente) {
                $docente = new Docente;
                $docente->user_id = $user->id;
            }
            $docente->nombre = $request->input('name').' '.$request->input('apellidos');
            $docente->dni = $request->input('dni');
            $docente->email = $request->input('email');
            $docente->save();
        }

        if (in_array('alumno', $roles) || in_array('alumnoB', $roles)) {
            $user->programa()->associate($request->input('programa_id'));
            $user->ciclo()->associate($request->input('ciclo_id'));
        }
        $user->save();

        if (in_array('inhabilitado', $roles) && $user->perfil === 'Licencia') {
            $this->matricularAutomaticamentePorLicencia($user);
        }

        return redirect()->route('admin')->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Un alumno FID inhabilitado por Licencia sigue siendo, formalmente,
     * alumno de la institución (pausó su carrera por un tiempo, no la dejó
     * como uno Retirado) — a diferencia de otros motivos de inhabilitación,
     * este debe: (1) conservar el rol "alumno" además de "inhabilitado", y
     * (2) quedar matriculado automáticamente en el periodo actual, sin pasar
     * por el flujo normal de voucher/comprobante, para que no desaparezca de
     * los listados y reportes de matrícula.
     */
    private function matricularAutomaticamentePorLicencia(User $user): void
    {
        if (! $user->hasRole('alumno')) {
            $user->assignRole('alumno');
        }

        $alumno = Alumno::where('user_id', $user->id)->first();
        $periodoActual = PeriodoActual::where('actual', true)->first();

        if (! $alumno || ! $periodoActual) {
            // Sin ficha de alumno o sin periodo activo configurado no hay a
            // qué matricular — el rol ya quedó asignado arriba de todos modos.
            return;
        }

        Matricula::firstOrCreate(
            ['alumno_id' => $alumno->id, 'periodo_actual_id' => $periodoActual->id],
            [
                'fecha_completado' => now(),
                'estado' => 'matriculado',
                'comprobante' => 'Matrícula automática por Licencia (sin voucher)',
                'voucher_verificado' => false,
            ]
        );
    }

    /**
     * Cambia solo el motivo de inhabilitación (perfil) de un usuario ya
     * inhabilitado, desde el popup de la pestaña "Inhabilitados" — sin pasar
     * por el formulario completo de edición (que exige nombre/DNI/email/roles
     * de nuevo). El rol "inhabilitado" no se toca aquí: esto solo reclasifica
     * el motivo (Deuda ⇄ Licencia ⇄ Retirado ⇄ Sin matrícula), nunca rehabilita
     * a alguien por completo (eso sigue siendo una decisión del edit completo).
     */
    public function actualizarPerfilInhabilitado(Request $request, User $user)
    {
        abort_unless($user->hasRole('inhabilitado'), 404);

        $validated = $request->validate([
            'perfil' => ['required', \Illuminate\Validation\Rule::in(['Deuda', 'Sin matrícula', 'Licencia', 'Retirado'])],
        ]);

        // También se cambia desde las pestañas FID/PPD (chip "Inhabilitado · …"): volver a la misma.
        $volver = in_array($request->input('volver'), ['fid', 'ppd', 'inhabilitados'], true) ? $request->input('volver') : 'inhabilitados';

        if ($validated['perfil'] === 'Licencia' && $user->perfil !== 'Licencia'
            && ! \App\Services\Trayectoria::puedeIniciarLicencia($user)) {
            return redirect(route('admin').'#'.$volver)->with('error', \App\Services\Trayectoria::mensajeLicenciaAgotada($user));
        }

        $user->perfil = $validated['perfil'];
        $user->save();

        if ($validated['perfil'] === 'Licencia') {
            $this->matricularAutomaticamentePorLicencia($user);
        }

        return redirect(route('admin').'#'.$volver)
            ->with('success', 'Motivo de inhabilitación de '.$user->apellidos.', '.$user->name.' actualizado a "'.$validated['perfil'].'".');
    }

    public function destroy($id)
    {
        $admin = User::findOrFail($id);

        if ($admin->hasRole('super-admin') && !auth()->user()?->hasRole('super-admin')) {
            abort(403, 'Solo un super-admin puede eliminar una cuenta de super-admin.');
        }

        $hasAlumnoRole = $admin->hasRole('alumno');
        $userCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->count();

        if ($userCount > 1) {
            // Si el usuario tiene el rol de 'alumno', eliminar el registro en 'alumnos'
            if ($hasAlumnoRole) {
                // Elimina el registro relacionado en la tabla 'alumnos'
                $alumno = Alumno::where('user_id', $admin->id)->first();
                if ($alumno) {
                    $alumno->delete();
                }
            }
            // Eliminar el usuario
            $admin->delete();

            return redirect()->route('admin')->with('success', 'Usuario eliminado exitosamente');
        } else {
            return redirect()->route('admin')->with('error', 'No puedes eliminar al último usuario registrado con el rol de admin');
        }
    }

    // ── Gestión Tutor → Ciclo ────────────────────────────────────────────────

    public function tutorCiclosForm($userId)
    {
        $tutor  = User::role('tutor')->findOrFail($userId);
        $ciclos = Ciclo::with('programa')->get()->sortBy(fn ($c) => $c->ordenCiclo());
        $asignados = $tutor->tutorCiclos->pluck('id')->toArray();

        return view('admin.tutor-ciclos', compact('tutor', 'ciclos', 'asignados'));
    }

    public function tutorCiclosUpdate(Request $request, $userId)
    {
        $tutor = User::role('tutor')->findOrFail($userId);
        $tutor->tutorCiclos()->sync($request->input('ciclos', []));

        return redirect()->route('admin')->with('success', 'Ciclos del tutor actualizados correctamente.');
    }

    /**
     * Listado FID (tabla alumnos): excluye PPD (rol alumnoB). Incluye usuario con rol alumno, o bien solo inhabilitado
     * si users.perfil es Deuda. Quien tenga rol inhabilitado sin perfil Deuda queda fuera.
     */
    private function assertAlumnoCarnetAutorizado(Alumno $alumno): void
    {
        if (! auth()->check()) {
            abort(403);
        }

        $ok = Alumno::query()
            ->whereKey($alumno->getKey())
            ->whereHas('user', fn ($subQuery) => $this->applyAlumnoFidUserConstraints($subQuery))
            ->exists();

        if (! $ok) {
            abort(404);
        }
    }

    /**
     * @return array{nombres: string, apellidoPaterno: string, apellidoMaterno: string, programaNombre: string, anioIngreso: string, dni: string, fotoSrc: ?string, logoSrc: ?string}
     */
    private function buildCarnetViewData(Alumno $alumno): array
    {
        $alumno->loadMissing(['programa', 'ciclo', 'user']);
        [$apellidoPaterno, $apellidoMaterno] = $this->splitApellidosParaCarnet((string) $alumno->apellidos);
        $email = $alumno->user?->email ?? $alumno->email ?? '';
        preg_match('/20\d{2}/', $email, $matches);
        $anioIngreso = $matches[0] ?? (($alumno->user?->created_at ?? $alumno->created_at)?->format('Y') ?? '');

        return [
            'programaNombre' => (string) ($alumno->programa->nombre ?? ''),
            'anioIngreso' => $anioIngreso,
            'dni' => (string) $alumno->dni,
            'nombres' => mb_strtoupper((string) $alumno->nombres, 'UTF-8'),
            'apellidoPaterno' => mb_strtoupper($apellidoPaterno, 'UTF-8'),
            'apellidoMaterno' => mb_strtoupper($apellidoMaterno !== '' ? $apellidoMaterno : '—', 'UTF-8'),
            'fotoSrc' => $this->carnetFotoDataUri($alumno),
            'logoSrc' => $this->carnetLogoDataUri(),
        ];
    }

    private function splitApellidosParaCarnet(string $apellidos): array
    {
        $apellidos = trim(preg_replace('/\s+/u', ' ', $apellidos));
        if ($apellidos === '') {
            return ['', ''];
        }
        $parts = preg_split('/\s+/u', $apellidos, 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    /** Foto del carnet: solo desde el archivo vinculado en `users.foto` (relación user). */
    private function carnetFotoDataUri(Alumno $alumno): ?string
    {
        $foto = $alumno->user?->foto;
        if (! $foto) {
            return null;
        }
        $path = public_path('img/estudiantes/'.$foto);
        if (! is_file($path)) {
            return null;
        }
        $mime = @mime_content_type($path) ?: 'image/jpeg';
        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }

    /** Logo del encabezado del carnet: PNG blanco sobre fondo transparente. */
    private function carnetLogoDataUri(): ?string
    {
        $candidates = [
            public_path('img/Logo-Pukllasunchis-blanco.png'),
            public_path('img/logo-iesp-pukllasunchis.png'),
            public_path('img/logo-iesp-pukllasunchis-svg.svg'),
        ];

        foreach ($candidates as $path) {
            if (! is_file($path)) {
                continue;
            }
            $mime = @mime_content_type($path) ?: 'image/png';
            if (str_ends_with(strtolower($path), '.svg')) {
                $mime = 'image/svg+xml';
            }

            return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
        }

        return null;
    }

    private function alumnosFidFilteredQuery(Request $request, bool $aplicarFiltrosProgramaCiclo = true): Builder
    {
        $query = Alumno::query();
        $query->whereHas('user', fn ($subQuery) => $this->applyAlumnoFidUserConstraints($subQuery));
        $withUser = $request->get('with_user');
        if ($withUser === '1') {
            $query->has('user');
        } elseif ($withUser === '0') {
            $query->doesntHave('user');
        }

        $busquedaActiva = $request->filled('search') && trim((string) $request->input('search')) !== '';

        if ($aplicarFiltrosProgramaCiclo && ! $busquedaActiva) {
            if ($request->filled('programa_id')) {
                $query->where('programa_id', (int) $request->input('programa_id'));
            }
            if ($request->filled('ciclo_id')) {
                $query->where('ciclo_id', (int) $request->input('ciclo_id'));
            }
        }

        if ($busquedaActiva) {
            $searchTerms = array_filter(array_map('trim', explode(' ', (string) $request->input('search'))));
            $query->where(function ($subquery) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if ($term === '') {
                        continue;
                    }
                    $subquery->where(function ($nameOrApellidoQuery) use ($term) {
                        $nameOrApellidoQuery->where('nombres', 'like', '%'.$term.'%')
                            ->orWhere('apellidos', 'like', '%'.$term.'%');
                    })
                        ->orWhere('dni', 'like', '%'.$term.'%')
                        ->orWhereHas('programa', function ($programaQuery) use ($term) {
                            $programaQuery->where('nombre', 'like', '%'.$term.'%');
                        });
                }
            });
        }

        if ($request->get('solo_becas') === '1') {
            $query->whereHas('user', fn ($q) => $q->where('beca', 1));
        }

        return $query;
    }

    private function alumnosPpdFilteredQuery(Request $request, bool $aplicarFiltrosProgramaCiclo = true): Builder
    {
        $query = User::role('alumnoB');

        $busquedaActiva = $request->filled('search') && trim((string) $request->input('search')) !== '';

        if ($aplicarFiltrosProgramaCiclo && ! $busquedaActiva) {
            if ($request->filled('programa_id')) {
                $query->where('programa_id', (int) $request->input('programa_id'));
            }
            if ($request->filled('ciclo_id')) {
                $query->where('ciclo_id', (int) $request->input('ciclo_id'));
            }
        }

        if ($busquedaActiva) {
            $searchTerms = array_filter(array_map('trim', explode(' ', (string) $request->input('search'))));
            $query->where(function ($sub) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if ($term === '') {
                        continue;
                    }
                    $sub->where(function ($q) use ($term) {
                        $q->where('name', 'like', '%'.$term.'%')
                            ->orWhere('apellidos', 'like', '%'.$term.'%')
                            ->orWhereHas('alumnoB', function ($qb) use ($term) {
                                $qb->where('numero', 'like', '%'.$term.'%')
                                    ->orWhere('numero_referencia', 'like', '%'.$term.'%');
                            });
                    })->orWhere('dni', 'like', '%'.$term.'%');
                }
            });
        }

        return $query;
    }

    public function quitarMatricula(Matricula $matricula)
    {
        $matricula->delete();

        return redirect()->back()->with('success', 'Matrícula eliminada correctamente.');
    }

    public function quitarMatriculaPpd(MatriculaPpd $matricula)
    {
        $matricula->delete();

        return redirect()->back()->with('success', 'Matrícula PPD eliminada correctamente.');
    }

    public function verificarVoucherMatricula(Request $request, Matricula $matricula)
    {
        $nuevoEstado = ! $matricula->voucher_verificado;
        $matricula->update([
            'voucher_verificado' => $nuevoEstado,
            'voucher_verificado_at' => $nuevoEstado ? now() : null,
        ]);

        $mensaje = $nuevoEstado
            ? 'Voucher marcado como verificado.'
            : 'Verificación de voucher removida.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'voucher_verificado' => $nuevoEstado,
                'ficha_enviada' => (bool) $matricula->ficha_enviada_at,
                'message' => $mensaje,
            ]);
        }

        return redirect()->back()->with('success', $mensaje);
    }

    public function enviarFichaMatricula(Request $request, Matricula $matricula)
    {
        $wantsJson = $request->wantsJson() || $request->ajax();

        if (! $matricula->voucher_verificado) {
            $mensaje = 'Debes verificar el voucher antes de enviar la ficha.';

            return $wantsJson
                ? response()->json(['success' => false, 'message' => $mensaje], 422)
                : redirect()->back()->with('error', $mensaje);
        }

        $yaEnviada = (bool) $matricula->ficha_enviada_at;

        $alumno = $matricula->alumno()->with('programa', 'ciclo')->first();
        $email = $alumno?->user()->first()?->email ?? $alumno?->email;

        if (! $alumno || ! $email) {
            $mensaje = 'El alumno no tiene un correo registrado.';

            return $wantsJson
                ? response()->json(['success' => false, 'message' => $mensaje], 422)
                : redirect()->back()->with('error', $mensaje);
        }

        return $this->enviarFichaDespuesDeResponder($request, $matricula, $alumno, $matricula->periodoActual, $email, $yaEnviada, 'FID');
    }

    /**
     * Envía la ficha de matrícula DESPUÉS de responder al navegador.
     *
     * Antes el correo se mandaba en el momento (SMTP de Gmail: conexión, TLS, login y subida del
     * PDF adjunto) y el clic quedaba esperando varios segundos. Ahora se responde al instante y el
     * envío corre al terminar la petición (PHP-FPM cierra la respuesta con fastcgi_finish_request;
     * si el servidor no lo soporta, se comporta como antes: no empeora nada).
     * ficha_enviada_at se marca SOLO si el envío sale bien; si falla queda en el log.
     */
    private function enviarFichaDespuesDeResponder(Request $request, $matricula, $alumno, $periodo, string $email, bool $yaEnviada, string $tipo)
    {
        app()->terminating(function () use ($matricula, $alumno, $periodo, $email, $tipo) {
            try {
                Mail::to($email)->send(new NotificacionRegistro($alumno, $periodo, true, $matricula));
                $matricula->update(['ficha_enviada_at' => now()]);
            } catch (\Throwable $e) {
                Log::error("Error enviando ficha de matrícula {$tipo} (envío diferido)", [
                    'matricula_id' => $matricula->id, 'email' => $email, 'error' => $e->getMessage(),
                ]);
            }
        });

        $mensaje = ($yaEnviada ? 'Reenviando' : 'Enviando') . " la ficha de matrícula a {$email}. Llegará en unos segundos.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'ficha_enviada_at' => now()->format('d/m/Y'),
            ]);
        }

        return redirect()->back()->with('success', $mensaje);
    }

    public function verificarVoucherMatriculaPpd(Request $request, MatriculaPpd $matricula)
    {
        $nuevoEstado = ! $matricula->voucher_verificado;
        $matricula->update([
            'voucher_verificado' => $nuevoEstado,
            'voucher_verificado_at' => $nuevoEstado ? now() : null,
        ]);

        $mensaje = $nuevoEstado
            ? 'Voucher marcado como verificado.'
            : 'Verificación de voucher removida.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'voucher_verificado' => $nuevoEstado,
                'ficha_enviada' => (bool) $matricula->ficha_enviada_at,
                'message' => $mensaje,
            ]);
        }

        return redirect()->back()->with('success', $mensaje);
    }

    public function enviarFichaMatriculaPpd(Request $request, MatriculaPpd $matricula)
    {
        $wantsJson = $request->wantsJson() || $request->ajax();

        if (! $matricula->voucher_verificado) {
            $mensaje = 'Debes verificar el voucher antes de enviar la ficha.';

            return $wantsJson
                ? response()->json(['success' => false, 'message' => $mensaje], 422)
                : redirect()->back()->with('error', $mensaje);
        }

        $yaEnviada = (bool) $matricula->ficha_enviada_at;

        $alumno = $matricula->ppd()->with('programa', 'ciclo')->first();
        $email = $alumno?->user()->first()?->email ?? $alumno?->email;

        if (! $alumno || ! $email) {
            $mensaje = 'El alumno no tiene un correo registrado.';

            return $wantsJson
                ? response()->json(['success' => false, 'message' => $mensaje], 422)
                : redirect()->back()->with('error', $mensaje);
        }

        return $this->enviarFichaDespuesDeResponder($request, $matricula, $alumno, $matricula->periodoActualPpd, $email, $yaEnviada, 'PPD');
    }

    private function applyAlumnoFidUserConstraints($userQuery): void
    {
        $userQuery
            ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'alumno'))
                  ->orWhere(function ($q2) {
                      $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                          ->where('perfil', '!=', 'Retirado');
                  });
            })
            // Cada promoción que termina el programa conserva su Ciclo X real (para no
            // perder el vínculo con sus cursos/calificaciones), pero no es parte del
            // periodo actual: no debe contarse en la Matrícula ni en los selectores/
            // conteos por Ciclo (mismo criterio que ya se aplica en PPD).
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            });
    }
}
