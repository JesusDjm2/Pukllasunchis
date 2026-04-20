<?php

namespace App\Http\Controllers;

use App\Exports\AlumnosFidExport;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $alumno = auth()->user()->alumno;
        $admins = User::all();
        $totalAlumnos = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumno');
        })->count();

        //Conteos
        $totalRecords = User::count();
        $conteoDocentes = User::role('docente')->count();
        $conteoAlumnos = User::role('alumno')->count();
        $conteoPpd = User::role('alumnoB')->count();
        $conteoAdmin = User::role('admin')->count();
        $conteoInhabilitados = User::role('inhabilitado')->count();
        $conteoTutores = User::role('tutor')->count();
        $alumnosConBeca = User::where('beca', 1)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'alumno');
            })
            ->count();

        return view('admin.index', compact('alumno', 'admins', 'totalAlumnos', 'totalRecords', 'alumnosConBeca', 'conteoDocentes', 'conteoAdmin', 'conteoInhabilitados', 'conteoAlumnos', 'conteoPpd', 'conteoTutores'));
    }

    public function alumnos(Request $request)
    {
        $query = $this->alumnosFidFilteredQuery($request);
        $busquedaActiva = $request->filled('search') && trim((string) $request->input('search')) !== '';

        $alumnos = $query
            ->with(['programa', 'ciclo', 'user.roles'])
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
            ->whereNotNull('ciclo_id')
            ->selectRaw('ciclo_id, COUNT(*) as total')
            ->groupBy('ciclo_id')
            ->get()
            ->keyBy('ciclo_id');

        if ($alumnos->isEmpty() && ! $request->has('search_page')) {
            session()->flash('error', 'No se han encontrado resultados. Se ha buscado un total de '.$totalRecords.' registros.');
        }

        $programasFiltro = Programa::query()
            ->whereHas('alumnos', function ($q) {
                $q->whereHas('user', fn ($sub) => $this->applyAlumnoFidUserConstraints($sub));
            })
            ->orderBy('nombre')
            ->get();
        $ciclosFiltro = collect();
        if ($request->filled('programa_id')) {
            $ciclosFiltro = Ciclo::query()
                ->where('programa_id', (int) $request->input('programa_id'))
                ->whereHas('alumnos', function ($q) {
                    $q->whereHas('user', fn ($sub) => $this->applyAlumnoFidUserConstraints($sub));
                })
                ->orderBy('id')
                ->get();
        }

        $ciclosParaExportacion = Ciclo::query()
            ->with('programa')
            ->whereHas('alumnos', function ($q) {
                $q->whereHas('user', fn ($sub) => $this->applyAlumnoFidUserConstraints($sub))
                    ->whereNotNull('ciclo_id');
            })
            ->orderBy('programa_id')
            ->orderBy('id')
            ->get();

        return view('alumnos.index', compact(
            'alumnos',
            'totalRecords',
            'programasFiltro',
            'ciclosFiltro',
            'conteoGrupoListado',
            'totalesPorCicloId',
            'busquedaActiva',
            'ciclosParaExportacion',
        ));
    }

    public function exportAlumnosExcel(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'ciclo_ids' => ['required', 'array', 'min:1'],
            'ciclo_ids.*' => ['integer', 'exists:ciclos,id'],
        ]);

        $cicloIds = array_values(array_unique(array_map('intval', $validated['ciclo_ids'])));

        $query = $this->alumnosFidFilteredQuery($request, false)
            ->whereIn('ciclo_id', $cicloIds);
        $alumnos = $query
            ->with(['programa', 'ciclo', 'user.roles'])
            ->orderByRaw('programa_id IS NULL, programa_id')
            ->orderByRaw('ciclo_id IS NULL, ciclo_id')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $nombreArchivo = 'alumnos_fid_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new AlumnosFidExport($alumnos), $nombreArchivo);
    }

    public function alumnoCarnet(Alumno $alumno)
    {
        $this->assertAlumnoCarnetAutorizado($alumno);

        return view('alumnos.carnet', array_merge(
            $this->buildCarnetViewData($alumno),
            ['alumno' => $alumno]
        ));
    }

    public function alumnosppd()
    {
        $alumnos = User::role('alumnoB')->get();
        $ppd = ppd::all();
        $totalRecords = $ppd->count();
        $counts = [
            'Inicial' => $alumnos->filter(fn ($a) => Str::contains($a->ciclo->programa->nombre, 'Inicial'))->count(),
            'Primaria' => $alumnos->filter(fn ($a) => Str::contains($a->ciclo->programa->nombre, 'Primaria') && ! Str::contains($a->ciclo->programa->nombre, 'EIB'))->count(),
            'Primaria EIB' => $alumnos->filter(fn ($a) => Str::contains($a->ciclo->programa->nombre, 'Primaria EIB'))->count(),
        ];

        return view('alumnos.ppd.lista', compact('alumnos', 'ppd', 'totalRecords', 'counts'));
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

        return view('admin.create', compact('programas', 'ciclos', 'cursos'));
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

        return view('admin.edit', compact('admin', 'programas', 'ciclos', 'currentRoles', 'currentCicloId', 'currentProgramId', 'departamentosData'));
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
            'roles.*' => 'string|in:admin,docente,alumno,adminB,alumnoB,inhabilitado,tutor',
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
            'roles.*' => 'string|in:admin,docente,alumno,adminB,alumnoB,inhabilitado,tutor',
            'programa_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:programas,id'],
            'ciclo_id' => [\Illuminate\Validation\Rule::requiredIf(fn () => !empty(array_intersect($request->input('roles', []), ['alumno', 'alumnoB']))), 'nullable', 'exists:ciclos,id'],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
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
        $user->syncRoles($roles);

        if (in_array('docente', $roles)) {
            $docente = $user->docente;
            if ($docente) {
                $docente->nombre = $request->input('name').' '.$request->input('apellidos');
                $docente->dni = $request->input('dni');
                $docente->email = $request->input('email');
                $docente->save();
            }
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
        $ref = $alumno->user?->created_at ?? $alumno->created_at;
        $anioIngreso = $ref ? $ref->format('Y') : '';

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

        return $query;
    }

    private function applyAlumnoFidUserConstraints($userQuery): void
    {
        $userQuery
            ->whereDoesntHave('roles', function ($roleQuery) {
                $roleQuery->where('name', 'alumnoB');
            })
            ->where(function ($outer) {
                $outer
                    ->where(function ($inhabilitadoOk) {
                        $inhabilitadoOk
                            ->whereDoesntHave('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'inhabilitado');
                            })
                            ->orWhere('perfil', 'Deuda');
                    })
                    ->where(function ($esContextoFid) {
                        $esContextoFid
                            ->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'alumno');
                            })
                            ->orWhere(function ($soloInhabilitadoDeuda) {
                                $soloInhabilitadoDeuda
                                    ->whereDoesntHave('roles', function ($roleQuery) {
                                        $roleQuery->where('name', 'alumno');
                                    })
                                    ->whereHas('roles', function ($roleQuery) {
                                        $roleQuery->where('name', 'inhabilitado');
                                    })
                                    ->where('perfil', 'Deuda');
                            });
                    });
            });
    }
}
