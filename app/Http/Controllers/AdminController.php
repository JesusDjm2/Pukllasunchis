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
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function index()
    {
        $alumno = auth()->user()?->alumno;
        $admins = User::with(['tutorCiclos', 'docente.cursos'])->get();
        $totalAlumnos = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumno');
        })->count();

        // Conteos
        $totalRecords        = User::count();
        $conteoDocentes      = User::role('docente')->count();
        $conteoAlumnos       = User::role('alumno')->count();
        $conteoPpd           = User::role('alumnoB')->count();
        $conteoAdmin         = User::role('admin')->count();
        $conteoSuperAdmin    = User::role('super-admin')->count();
        $conteoInhabilitados = User::role('inhabilitado')->count();
        $conteoTutores       = User::role('tutor')->count();
        $alumnosConBeca = User::where('beca', 1)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'alumno');
            })
            ->count();

        $data = compact(
            'alumno', 'admins', 'totalAlumnos', 'totalRecords', 'alumnosConBeca',
            'conteoDocentes', 'conteoAdmin', 'conteoSuperAdmin', 'conteoInhabilitados',
            'conteoAlumnos', 'conteoPpd', 'conteoTutores'
        );

        // Super-admin usa layout y vista propios (sin conflicto con admin)
        if (auth()->user()?->hasRole('super-admin')) {
            return view('superadmin.dashboard', $data);
        }

        return view('admin.index', $data);
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
                'ciclo',
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
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $currentProgramId = $admin->programa_id;
        $currentRoles = $admin->getRoleNames()->toArray();
        $currentCicloId = $admin->ciclo_id;
        $departamentosData = Departamento::all();
        $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin';

        return view('admin.edit', compact('admin', 'programas', 'ciclos', 'currentRoles', 'currentCicloId', 'currentProgramId', 'departamentosData', 'layout'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:255',
            'condicion' => 'nullable|string|max:40',
            'pendiente' => 'nullable|string',
            'perfil' => 'nullable|string',
            'beca' => 'nullable|boolean',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:7|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'string|in:super-admin,admin,docente,alumno,adminB,alumnoB,inhabilitado,tutor',
            'programa_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:programas,id'],
            'ciclo_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:ciclos,id'],
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'apellidos' => $request->input('apellidos'),
            'dni' => $request->input('dni'),
            'condicion' => $request->input('condicion'),
            'pendiente' => $request->input('pendiente'),
            'perfil' => $request->input('perfil'),
            'beca' => $request->input('beca'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        $roles = $request->input('roles', []);
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
            'beca' => 'nullable|boolean',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:7|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'string|in:super-admin,admin,docente,alumno,adminB,alumnoB,inhabilitado,tutor',
            'programa_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:programas,id'],
            'ciclo_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:ciclos,id'],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'telefono' => 'nullable|string|max:20',
            'whatsapp_key' => 'nullable|string|max:20',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->input('name');
        $user->apellidos = $request->input('apellidos');
        $user->dni = $request->input('dni');
        $user->condicion = $request->input('condicion');
        $user->perfil = $request->input('perfil');
        $user->pendiente = $request->input('pendiente');
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

        if (in_array('docente', $roles) || in_array('tutor', $roles)) {
            $user->telefono = $request->input('telefono');
        }
        if (in_array('tutor', $roles)) {
            $user->whatsapp_key = $request->input('whatsapp_key');
        }

        $user->syncRoles($roles);

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

        return redirect()->route('admin')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
        $admin = User::findOrFail($id);
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

        try {
            Mail::to($email)->send(new NotificacionRegistro($alumno, $matricula->periodoActual, true));
            $matricula->update(['ficha_enviada_at' => now()]);

            $mensaje = $yaEnviada
                ? 'Ficha de matrícula reenviada correctamente al alumno.'
                : 'Ficha de matrícula enviada correctamente al alumno.';

            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'ficha_enviada_at' => $matricula->ficha_enviada_at->format('d/m/Y H:i'),
                ]);
            }

            return redirect()->back()->with('success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error enviando ficha de matrícula (verificación manual): '.$e->getMessage());
            $mensaje = 'Ocurrió un error al enviar el correo. Revisa el log.';

            return $wantsJson
                ? response()->json(['success' => false, 'message' => $mensaje], 500)
                : redirect()->back()->with('error', $mensaje);
        }
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

        try {
            Mail::to($email)->send(new NotificacionRegistro($alumno, $matricula->periodoActualPpd, true));
            $matricula->update(['ficha_enviada_at' => now()]);

            $mensaje = $yaEnviada
                ? 'Notificación de matrícula PPD reenviada correctamente al alumno.'
                : 'Notificación de matrícula PPD enviada correctamente al alumno.';

            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'ficha_enviada_at' => $matricula->ficha_enviada_at->format('d/m/Y H:i'),
                ]);
            }

            return redirect()->back()->with('success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error enviando ficha de matrícula PPD (verificación manual): '.$e->getMessage());
            $mensaje = 'Ocurrió un error al enviar el correo. Revisa el log.';

            return $wantsJson
                ? response()->json(['success' => false, 'message' => $mensaje], 500)
                : redirect()->back()->with('error', $mensaje);
        }
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
            });
    }
}
