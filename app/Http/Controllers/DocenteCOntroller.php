<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\Silabo;
use App\Models\User;
use App\Support\ContactoAlumno;
use Hash;
use Illuminate\Http\Request;

class DocenteCOntroller extends Controller
{
    /**
     * routes/admin.php solo exige sesión para Route::resource('docente'): sin estas comprobaciones cualquier
     * usuario (incluso un alumno) podía listar, editar o ELIMINAR docentes (destroy borra también su cuenta).
     */
    private function soloAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'super-admin']), 403);
    }

    /** El propio docente o un administrador. */
    private function propioOAdmin(Docente $docente): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasAnyRole(['admin', 'super-admin']) || (int) $user->docente?->id === (int) $docente->id), 403);
    }

    public function index()
    {
        $this->soloAdmin();
        $docentes = Docente::with([
            'user.roles', 'user.tutorCiclos.incidencias', 'user.tutorCiclos.programa', 'incidencias',
            'cursos.ciclo.programa', 'cursos.competencias', 'cursos.competenciasSeleccionadas', 'cursos.relacionsilabo',
        ])->orderBy('nombre')->get();

        // Avance de calificación por curso, calculado UNA vez por curso (un curso puede tenerlo
        // más de un docente). FID: [1 => P1, 2 => P2, 3 => Desempeño]; PPD: % de casillas llenas.
        $avanceCursos = $docentes->pluck('cursos')->flatten()->unique('id')
            ->mapWithKeys(fn ($c) => [$c->id => $c->esPpd() ? $c->porcentajePPD() : $c->porcentajesFid()]);
        // Rendimiento por curso (promedio, desaprobados, carga sospechosa): una consulta por tabla.
        $rendimientoCursos = \App\Services\RendimientoCursos::de($avanceCursos->keys());
        // Para «Borrar calificaciones del curso»: notas por parte y docentes que comparten el curso.
        $notasPorCurso = collect([1 => 'periodouno', 2 => 'periodo_dos', 3 => 'periodo_tres', 'ppd' => 'calificacionesppds'])
            ->map(fn ($tabla) => \DB::table($tabla)->selectRaw('curso_id, count(*) as n')->groupBy('curso_id')->pluck('n', 'curso_id'));
        $docentesPorCurso = \DB::table('curso_docente')->selectRaw('curso_id, count(*) as n')->groupBy('curso_id')->pluck('n', 'curso_id');
        $totalDocentes = $docentes->count();
        $totalIncidencias = $docentes->sum(fn($d) => $d->incidencias->count());
        $totalSilabos = Silabo::count();

        $periodoActual = PeriodoActual::actual();
        $ciclos = Ciclo::all()->sortBy(fn ($c) => $c->ordenCiclo() ?? 999);

        $conteosZonaRiesgo = [
            'parcial1' => \DB::table('periodouno')->count(),
            'parcial2' => \DB::table('periodo_dos')->count(),
            'desempeno' => \DB::table('periodo_tres')->count(),
            'asignacionesFid' => \DB::table('curso_docente')
                ->whereIn('curso_id', Curso::whereHas('ciclo.programa', fn ($q) => $q->fid())->pluck('id'))
                ->count(),
            'asignacionesPpd' => \DB::table('curso_docente')
                ->whereIn('curso_id', Curso::whereHas('ciclo.programa', fn ($q) => $q->ppd())->pluck('id'))
                ->count(),
        ];

        return view('docentes.index', compact('docentes', 'totalDocentes', 'totalIncidencias', 'totalSilabos', 'periodoActual', 'ciclos', 'conteosZonaRiesgo', 'avanceCursos', 'rendimientoCursos', 'notasPorCurso', 'docentesPorCurso'));
    }

    public function vistaDocente($docenteId)
    {
        $periodoActual = PeriodoActual::actual();
        $nombrePeriodoActual = $periodoActual->nombre ?? null;
        $docente = Docente::findOrFail($docenteId);
        $user = $docente->user;

        return view('docentes.show', compact('user', 'docente', 'periodoActual'));
    }

    public function asignar($id)
    {
        $user = User::findOrFail($id);

        $docente = $user->docente;

        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::all();

        return view('docentes.asignar', compact('docente', 'programas', 'ciclos', 'cursos'));
    }

    /* public function asignarCurso(Request $request, $docenteId)
    {
        $request->validate([
            'programa_id' => 'required',
            'ciclo_id' => 'required',
            'curso_id' => 'required|exists:cursos,id',
        ]);

        $docente = Docente::findOrFail($docenteId);
        $curso = Curso::findOrFail($request->curso_id);
        $docente->cursos()->attach($curso->id);
        if ($docente->cursos()->where('curso_id', $curso->id)->exists()) {
            return redirect()
                ->route('docente.index')
                ->with('error', "El curso {$curso->nombre} ya está asignado a {$docente->nombre}.");
        }
        $docente->cursos()->attach($curso->id);

        return redirect()->route('docente.index')->with('success', 'Curso asignado a ' . $docente->nombre . ' correctamente.');
    } */
    public function asignarCurso(Request $request, $docenteId)
    {
        $request->validate([
            'programa_id' => 'required',
            'ciclo_id' => 'required',
            'curso_id' => 'required|exists:cursos,id',
        ]);

        $docente = Docente::findOrFail($docenteId);
        $curso = Curso::findOrFail($request->curso_id);

        // Usamos syncWithoutDetaching
        $result = $docente->cursos()->syncWithoutDetaching([$curso->id]);

        if (empty($result['attached'])) {
            return redirect()
                ->route('docente.index')
                ->with('error', "El curso {$curso->nombre} ya está asignado a {$docente->nombre}.");
        }

        return redirect()
            ->route('docente.index')
            ->with('success', "Curso {$curso->nombre} asignado a {$docente->nombre} correctamente.");
    }

    public function eliminarCurso(Docente $docente, Curso $curso)
    {
        $docente->cursos()->detach($curso->id);

        return redirect()->back()->with('success', 'Curso eliminado correctamente.');
    }

    public function edit($id)
    {
        $docente = Docente::findOrFail($id);
        $this->propioOAdmin($docente);

        return view('docentes.edit', compact('docente'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'dni' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'descripcion' => 'nullable|string',
            'password' => 'nullable|confirmed|min:8',
            'password_confirmation' => 'nullable|same:password',
        ]);

        // Buscar docente
        $docente = Docente::findOrFail($id);
        $this->propioOAdmin($docente);

        // Actualizar datos
        $docente->nombre = $request->input('nombre');
        $docente->dni = $request->input('dni');
        $docente->email = $request->input('email');
        $docente->descripcion = $request->input('descripcion');

        // Manejar foto
        if ($request->hasFile('foto')) {
            // Eliminar la foto anterior si existe
            if ($docente->foto) {
                $oldPath = public_path("docentes/fotos/{$docente->foto}");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Subir la nueva foto
            $foto = $request->file('foto');
            $nombreFoto = $foto->getClientOriginalName();
            $rutaFoto = public_path('docentes/fotos/');
            $foto->move($rutaFoto, $nombreFoto);

            // Guardar el nombre de la nueva foto en la base de datos
            $docente->foto = $nombreFoto;
        }

        // Manejar contraseña
        if ($request->filled('password')) {
            $docente->user->password = Hash::make($request->input('password'));
            $docente->user->save();
        }

        $docente->save();

        return redirect()->route('docente.show', $docente->id)
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function showBlog($docenteId)
    {
        $docente = Docente::findOrFail($docenteId);
        $blog = $docente->blog;

        return view('docentes.blog', compact('docente', 'blog'));
    }

    public function showAlumnos(Curso $curso, Docente $docente)
    {
        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;
        $periodoActual = \App\Models\PeriodoActual::where('actual', true)->first();

        $query = $programa->alumnos()
            ->where('ciclo_id', $ciclo->id)
            ->whereHas('user', fn ($q) => $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'alumnoB'))
                ->where(function ($q) {
                    $q->whereHas('roles', fn ($r) => $r->where('name', 'alumno'))
                      ->orWhere(function ($q2) {
                          $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                              ->where('perfil', '!=', 'Retirado');
                      });
                }));

        if ($periodoActual) {
            $query->where(function ($q) use ($periodoActual) {
                $q->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $periodoActual->id))
                  ->orWhereHas('user', fn ($u) => $u->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado')));
            });
        }

        $alumnos = $query->orderBy('apellidos')->get();
        $cantidadAlumnos = $alumnos->count();

        return view('docentes.alumnos', compact('curso', 'alumnos', 'cantidadAlumnos', 'ciclo', 'docente'));
    }

    public function show($id)
    {
        $docente = Docente::findOrFail($id);
        $alumno = auth()->user()->alumnoB;

        if (auth()->user()->hasRole('alumno')) {
            return view('alumnos.vistasAlumnos.docente', compact('docente', 'alumno'));
        } elseif (auth()->user()->hasRole('alumnoB')) {
            return view('alumnos.ppd.docente', compact('docente', 'alumno'));
        }

        $this->propioOAdmin($docente);
        $docente->load(['user.roles', 'cursos.ciclo.programa', 'cursosEspeciales']);
        $periodoActual = PeriodoActual::actual();

        return view('docentes.perfil', compact('docente', 'periodoActual'));
    }

    public function destroy($id)
    {
        $this->soloAdmin();
        $docente = Docente::findOrFail($id);
        // Eliminar el usuario relacionado
        if ($docente->user) {
            $docente->user->delete();
        }
        // Eliminar el docente
        $docente->delete();

        return redirect()->route('docente.index')->with('success', 'Docente eliminado correctamente.');
    }

    public function calificar($id)
    {
        $docente = Docente::findOrFail($id);
        $fechaLimite = \Carbon\Carbon::createFromTimeString('16:16')->toDateTimeString();

        return view('docentes.calificaciones.index', compact('docente', 'fechaLimite'));
    }

    public function calificarCurso(Request $request, $docenteId, $cursoId)
    {
        \App\Services\AccesoCalificaciones::autorizar($docenteId, $cursoId);

        $curso = Curso::findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);

        $competenciasIds = $request->input('competencias');
        if (empty($competenciasIds)) {
            $competenciasIds = $curso->competencias->count() <= 3
                ? $curso->competencias->pluck('id')
                : $curso->competenciasSeleccionadas->pluck('id');
        }
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasIds)->get();

        $periodoActual = \App\Models\PeriodoActual::where('actual', true)->first();

        [$alumnos, $alumnosRelacionados] = $this->alumnosFidDelCurso($curso, $periodoActual);

        $alumnos->each(function ($alumno) use ($periodoActual) {
            $alumno->es_inhabilitado = $alumno->user && $alumno->user->hasRole('inhabilitado');
            $alumno->no_matriculado = $periodoActual && ! $alumno->es_inhabilitado && ! $alumno->matriculaEnPeriodo($periodoActual->id);
        });

        $mostrarBotonDesempeno = true;

        // Ventanas de fecha para habilitar el registro de Parcial 1 y de Parcial
        // 2/Desempeño; sin periodo actual configurado, se deja todo habilitado.
        $parcial1Habilitado = ! $periodoActual || $periodoActual->parcial1Habilitado();
        $parcial2Habilitado = ! $periodoActual || $periodoActual->parcial2DesempenoHabilitado();

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno', 'alumnosRelacionados', 'periodoActual', 'parcial1Habilitado', 'parcial2Habilitado'));
        }

        return view('docentes.calificaciones.alumnos', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos', 'mostrarBotonDesempeno', 'alumnosRelacionados', 'periodoActual', 'parcial1Habilitado', 'parcial2Habilitado'));
    }

    public function calificarCursoPPD(Request $request, $docenteId, $cursoId)
    {
        \App\Services\AccesoCalificaciones::autorizar($docenteId, $cursoId);

        $curso = Curso::with('ciclo.programa', 'competencias', 'calificacionesppd')->findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);

        $competenciasIds = $request->input('competencias');
        if (empty($competenciasIds)) {
            $competenciasIds = $curso->competencias->count() <= 3
                ? $curso->competencias->pluck('id')
                : $curso->competenciasSeleccionadas->pluck('id');
        }
        $competenciasSeleccionadas = Competencia::whereIn('id', $competenciasIds)->get();

        // Alumnos que YA tienen TODOS los campos de Proceso/Final llenos para ESTE curso (no el
        // flag global ppd.guardado, ni el valor derivado calificacion_curso: la pantalla recalcula
        // ese campo cada 200ms para toda fila visible, incluso las que el docente nunca tocó, y
        // puede dejar una nota parcial que oculta de esta lista a alumnos que en realidad no
        // fueron calificados).
        $userIdsCalificados = $curso->alumnosPpdCalificadosCompletos();

        // Obtener alumnos que:
        // 1. Tienen rol 'alumnoB' o 'inhabilitado'
        // 2. Pertenecen al MISMO PROGRAMA que el curso. En PPD un alumno cursa Ciclo I y
        //    Ciclo II en paralelo dentro del mismo año (no es secuencial como en FID), así
        //    que a qué Ciclo esté asignado no debe limitar en qué cursos puede calificarse.
        // 3. No están marcados como "Egresado": cada promoción que termina el programa
        //    conserva su Ciclo II real (para no perder el vínculo con sus cursos y
        //    calificaciones), pero no forma parte de la cohorte que está cursando ahora
        //    y no debe aparecer mezclada en la pantalla de calificar del periodo actual.
        // Ya calificados o no, todos siguen en la lista con sus notas cargadas: en PPD las
        // notas quedan abiertas (corregir, completar observaciones) hasta que el alumno egresa.
        // Antes el docente dejaba de ver a quien tenía todas sus notas, y al terminar de
        // calificar un curso la lista quedaba vacía y ya no podía editar nada.
        $query = User::where(function ($q) {
            $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
              ->orWhere(function ($q2) {
                  $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                      ->where('perfil', '!=', 'Retirado');
              });
        })
            ->where('programa_id', $curso->ciclo?->programa_id)
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            })
            ->with(['roles', 'alumnoB'])
            ->orderBy('apellidos');


        $alumnos = $query->get();

        $alumnos = $alumnos->map(function ($alumno) use ($userIdsCalificados) {
            $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');

            // Si no hay alumnoB vinculado por user_id, intentar encontrar su registro PPD por email
            if ($alumno->alumnoB === null && $alumno->email) {
                $ppdPorEmail = ppd::where('email', $alumno->email)->first();
                if ($ppdPorEmail) {
                    // Vincular automáticamente para que futuras consultas funcionen
                    if (! $ppdPorEmail->user_id) {
                        $ppdPorEmail->user_id = $alumno->id;
                        $ppdPorEmail->save();
                    }
                    // Recargar la relación con el registro encontrado
                    $alumno->setRelation('alumnoB', $ppdPorEmail);
                }
            }

            $alumno->tiene_ppd = $alumno->alumnoB !== null;

            // Verificar si ya tiene calificacion_curso guardada para ESTE curso
            $alumno->tiene_calificacion_guardada = $userIdsCalificados->contains($alumno->id);

            return $alumno;
        });

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return view('docentes.calificaciones.alumnosppd', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    }

    /* public function calificarCursoPPD(Request $request, $docenteId, $cursoId)
    {
        $curso = Curso::with('ciclo.programa')->findOrFail($cursoId);
        $docente = Docente::findOrFail($docenteId);
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();
        $programaId = $curso->ciclo->programa->id;
        $alumnos = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['alumnoB', 'inhabilitado']);
        })
            ->whereHas('programa.ciclos.cursos', function ($query) use ($cursoId) {
                $query->where('id', $cursoId);
            })
            ->whereHas('alumnoB', function ($query) {
                $query->where('guardado', true);
            })

            ->with(['programa.ciclos.cursos', 'roles', 'alumnoB'])
            ->orderBy('apellidos')
            ->get();

        $alumnos = $alumnos->map(function ($alumno) {
            $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');
            $alumno->tiene_ppd = $alumno->alumnoB !== null;

            return $alumno;
        });

        if (auth()->user()->hasRole('admin')) {
            return view('admin.curso.calificaciones', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
        }

        return view('docentes.calificaciones.alumnosppd', compact('curso', 'docente', 'competenciasSeleccionadas', 'alumnos'));
    } */

    /**
     * Alumnos de los cursos del docente, un curso a la vez (?curso=ID, que es a donde lleva
     * el botón "Alumnos" de cada tarjeta del dashboard). Solo el propio docente o admin.
     */
    public function alumnosPanel(Request $request, $id)
    {
        $docente = Docente::with(['cursos.ciclo.programa'])->findOrFail($id);
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || (int) $user->docente?->id === (int) $docente->id, 403);

        $periodoActual = PeriodoActual::actual();

        // FID primero y luego PPD; dentro de cada uno, por número de ciclo (I, II… o 1, 2…).
        $cursos = $docente->cursos
            ->filter(fn ($curso) => $curso->ciclo && $curso->ciclo->programa)
            ->map(fn ($curso) => (object) [
                'curso' => $curso,
                'esPpd' => $curso->esPpd(),
                'alumnos' => $this->contactosDelCurso($curso, $periodoActual),
            ])
            ->sortBy([
                fn ($a, $b) => $a->esPpd <=> $b->esPpd,
                fn ($a, $b) => $this->ordenCiclo($a->curso->ciclo->nombre) <=> $this->ordenCiclo($b->curso->ciclo->nombre),
            ])
            ->values();

        $cursoSeleccionado = $cursos->firstWhere('curso.id', (int) $request->query('curso')) ?? $cursos->first();
        $puedeEscribir = (int) $user->docente?->id === (int) $docente->id;

        return view('docentes.alumnos.panel', compact('docente', 'cursos', 'cursoSeleccionado', 'puedeEscribir'));
    }

    /** El docente escribe por correo a uno, varios o todos los alumnos de UNO de sus cursos. */
    public function enviarMensajeCurso(Request $request, Docente $docente, Curso $curso)
    {
        // Solo el propio docente: ni admin puede escribir "en nombre de" otro docente.
        abort_unless((int) auth()->user()->docente?->id === (int) $docente->id, 403);
        abort_unless($docente->cursos()->whereKey($curso->id)->exists(), 403, 'Este curso no está asignado a tu cuenta.');

        $curso->loadMissing('ciclo.programa');
        [$tipo, $texto] = \App\Services\MensajeAlumnos::desdeFormulario(
            $request,
            $this->contactosDelCurso($curso, PeriodoActual::actual()),
            $docente->nombre,
            $docente->email ?: $docente->user?->email,
            'Docente de '.$curso->nombre.' · '.($curso->ciclo->programa->nombre ?? '').' — Ciclo '.($curso->ciclo->nombre ?? ''),
        );

        return redirect()->route('docente.alumnos', ['docente' => $docente->id, 'curso' => $curso->id])->with($tipo, $texto);
    }

    /**
     * Excel con las calificaciones de UN curso del docente en el periodo actual (botón "Excel"
     * de cada tarjeta del dashboard). Lleva Programa, Ciclo, Docente y Periodo en la cabecera
     * y en el nombre del archivo. Ver App\Exports\Docente.
     */
    public function exportarCalificacionesCurso(Docente $docente, Curso $curso)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || (int) $user->docente?->id === (int) $docente->id, 403);
        abort_unless($docente->cursos()->whereKey($curso->id)->exists(), 403, 'Este curso no está asignado a tu cuenta.');

        $curso->load(['ciclo.programa', 'competencias', 'competenciasSeleccionadas']);
        abort_unless($curso->ciclo && $curso->ciclo->programa, 404);

        $periodo = PeriodoActual::actual();
        $datos = $this->datosCalificacionesCurso($curso, $periodo);

        // Caracteres no válidos en nombres de archivo ("Curso: tema" → "Curso tema").
        $limpiar = fn ($t) => trim(preg_replace('/\s+/', ' ', preg_replace('/[\/\\\\:*?"<>|]+/', ' ', (string) $t)));
        $archivo = 'Calificaciones - '.$limpiar($curso->nombre)
            .' - '.$limpiar($curso->ciclo->programa->nombre)
            .' - Ciclo '.$limpiar(trim(str_ireplace('Ciclo', '', $curso->ciclo->nombre)))
            .' - '.$limpiar($docente->nombre)
            .' - '.$limpiar($periodo->nombre ?? 'periodo actual').'.xlsx';

        $export = $datos['esPpd']
            ? new \App\Exports\Docente\CursoPpdSheet($docente, $periodo, $datos)
            : new \App\Exports\Docente\CursoFidSheet($docente, $periodo, $datos);

        return \Maatwebsite\Excel\Facades\Excel::download($export, $archivo);
    }

    /**
     * Filas de calificaciones de un curso, con la misma lista de alumnos de la pantalla de
     * calificar. FID: Parcial 1 / Parcial 2 / Desempeño (periodouno, periodo_dos, periodo_tres).
     * PPD: calificacionesppds, enlazadas por ppd_id o, si no, por user_id.
     */
    private function datosCalificacionesCurso(Curso $curso, ?PeriodoActual $periodo): array
    {
        $valoraciones = [1 => 'Previo al Inicio', 2 => 'Inicio', 3 => 'En Proceso', 4 => 'Logrado', 5 => 'Destacado'];
        $numero = fn ($v) => is_numeric($v) ? (float) $v : null;

        if ($curso->esPpd()) {
            $competencias = $curso->competencias
                ->sortBy(fn ($comp) => (int) preg_replace('/\D/', '', $comp->nombre))->values()->take(3);
            $notas = \App\Models\Calificacionesppd::where('curso_id', $curso->id)->get();
            $porPpd = $notas->whereNotNull('ppd_id')->keyBy('ppd_id');
            $porUser = $notas->whereNotNull('user_id')->keyBy('user_id');

            $filas = $this->alumnosPpdDelCurso($curso)->map(function ($u) use ($porPpd, $porUser, $competencias, $numero) {
                // Igual que la pantalla de calificar PPD: primero por ppd_id, luego por user_id.
                $cal = ($u->alumnoB ? $porPpd->get($u->alumnoB->id) : null) ?? $porUser->get($u->id);
                $comps = [];
                foreach ($competencias->keys() as $k) {
                    $pp = $numero($cal?->{'pp_c'.($k + 1).'_4'});
                    $pf = $numero($cal?->{'pf_c'.($k + 1).'_3'});
                    $comps[] = ($pp !== null && $pf !== null) ? round($pp * 0.4 + $pf * 0.6) : null;
                }

                return [
                    'nombre' => trim($u->apellidos.', '.$u->name, ', '),
                    'estado' => $u->es_inhabilitado ? 'Inhabilitado' : null,
                    'comps' => $comps,
                    'nivel' => filled($cal?->nivel_desempeno) ? $cal->nivel_desempeno : null,
                    'calificacion' => $numero($cal?->calificacion_curso),
                    'nota' => $numero($cal?->calificacion_sistema),
                    'observaciones' => $cal?->observaciones,
                ];
            })->values()->all();

            return ['curso' => $curso, 'esPpd' => true, 'competencias' => $competencias, 'filas' => $filas];
        }

        $competencias = ($curso->competencias->count() <= 3 ? $curso->competencias : $curso->competenciasSeleccionadas)->values();
        [$alumnos] = $this->alumnosFidDelCurso($curso, $periodo);
        $tablas = [
            'p1' => \App\Models\PeriodoUno::where('curso_id', $curso->id)->get()->keyBy('alumno_id'),
            'p2' => \App\Models\PeriodoDos::where('curso_id', $curso->id)->get()->keyBy('alumno_id'),
            'p3' => \App\Models\PeriodoTres::where('curso_id', $curso->id)->get()->keyBy('alumno_id'),
        ];

        $filas = $alumnos->map(function ($a) use ($tablas, $competencias, $valoraciones, $numero, $periodo) {
            $inhabilitado = $a->user && $a->user->hasRole('inhabilitado');
            $fila = [
                'nombre' => trim($a->apellidos.', '.$a->nombres, ', '),
                'estado' => $inhabilitado ? 'Inhabilitado'
                    : (($periodo && ! $a->matriculaEnPeriodo($periodo->id)) ? 'No matriculado' : null),
            ];
            foreach ($tablas as $clave => $tabla) {
                $r = $tabla->get($a->id);
                $fila[$clave] = [
                    'comps' => $competencias->keys()->map(fn ($k) => $valoraciones[(int) ($r?->{'valoracion_'.($k + 1)})] ?? null)->all(),
                    'valoracion' => $numero($r?->valoracion_curso),
                    'nivel' => filled($r?->calificacion_curso) && $r->calificacion_curso !== '-' ? $r->calificacion_curso : null,
                    'nota' => $numero($r?->calificacion_sistema),
                ];
            }

            return $fila;
        })->values()->all();

        return ['curso' => $curso, 'esPpd' => false, 'competencias' => $competencias, 'filas' => $filas];
    }

    /** @return \Illuminate\Support\Collection<int, ContactoAlumno> */
    private function contactosDelCurso(Curso $curso, ?PeriodoActual $periodoActual)
    {
        if ($curso->esPpd()) {
            return $this->alumnosPpdDelCurso($curso)->map(fn ($u) => ContactoAlumno::desdeUsuarioPpd($u))->values();
        }

        // La misma lista que ve el docente al calificar el curso.
        [$alumnos] = $this->alumnosFidDelCurso($curso, $periodoActual);

        return $alumnos->map(fn ($a) => ContactoAlumno::desdeAlumno($a, $curso->ciclo_id))->values();
    }

    private function ordenCiclo(?string $nombre): int
    {
        $romanos = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10];
        $ciclo = strtoupper(trim(str_ireplace('Ciclo', '', (string) $nombre)));
        $base = trim(explode('-', $ciclo)[0]);

        return $romanos[$base] ?? (int) preg_replace('/\D/', '', $ciclo);
    }

    /**
     * Alumnos FID de un curso en el período: los activos de su ciclo + los
     * asignados explícitamente a ESTE curso en el período actual ("Asignar
     * cursos"). Un inhabilitado (salvo Retirado) solo aparece por asignación
     * explícita del período. Devuelve [todos, soloAsignadosExplícitos].
     * Usado por calificarCurso() y por el panel de Alumnos — una sola regla.
     */
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

        // Asignación directa de alumnos activos: solo la del período actual (Curso::alumnosDelPeriodo).
        // Antes incluía cualquier período y las filas antiguas sin período, y quien ya pasó a otro
        // ciclo seguía apareciendo en los cursos que llevó como extra.
        $alumnosRelacionadosActivos = $curso->alumnosDelPeriodo($periodoActual)
            ->with('user.roles')
            ->whereHas('user', $filtroFidActivo)
            ->get();

        // Asignación directa de inhabilitados: solo cuenta si es del período actual. Las filas de otros
        // períodos o sin período (datos heredados) no deben mantenerlo visible indefinidamente.
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

        $alumnosCiclo = $curso->ciclo->alumnos()
            ->with('user.roles')
            ->whereHas('user', $filtroFidActivo)
            ->orderBy('apellidos')
            ->get();

        $alumnos = $alumnosCiclo
            ->merge($alumnosRelacionados)
            ->unique('id')
            ->sortBy('apellidos')
            ->values();

        return [$alumnos, $alumnosRelacionados];
    }

    private function alumnosPpdDelCurso(Curso $curso)
    {
        $alumnos = User::with(['ciclo.programa', 'alumnoB', 'roles'])
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'alumnoB'))
                  ->orWhere(function ($q2) {
                      $q2->whereHas('roles', fn ($r) => $r->where('name', 'inhabilitado'))
                          ->where('perfil', '!=', 'Retirado');
                  });
            })
            // Mismo PROGRAMA que el curso, no mismo Ciclo exacto: en PPD un alumno cursa
            // Ciclo I y Ciclo II en paralelo dentro del mismo año.
            ->where('programa_id', $curso->ciclo?->programa_id)
            // Excluir egresados: conservan su Ciclo II real pero no son parte de la
            // cohorte del periodo actual (ver nota en calificarCursoPPD).
            ->where(function ($q) {
                $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado');
            })
            ->orderBy('apellidos')
            ->get();

        $alumnos->each(function ($alumno) {
            $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');
        });

        return $alumnos;
    }

    public function repositorio($docente)
    {
        $docente = Docente::findOrFail($docente);
        $periodoActual = PeriodoActual::actual();
        $nombrePeriodoActual = $periodoActual->nombre ?? null;

        $cursos = Curso::with(['silabos' => fn ($q) => $q->orderByDesc('periodo')])
            ->orderBy('nombre')
            ->get();

        // Un curso puede tener un sílabo estructurado por cada periodo académico.
        // Se genera una fila por (curso, periodo) para no ocultar periodos recientes
        // detrás de uno antiguo, como pasaba al usar la relación relacionsilabo() (hasOne sin orden).
        $filas = collect();
        foreach ($cursos as $curso) {
            $silabosCurso = $curso->silabos;

            if ($silabosCurso->isEmpty()) {
                if ($curso->silabo) {
                    $filas->push((object) [
                        'curso' => $curso,
                        'periodo' => 'Sílabo sin periodo',
                        'silabo' => null,
                        'mostrarPdfLegacy' => true,
                    ]);
                }

                continue;
            }

            foreach ($silabosCurso->values() as $index => $silabo) {
                $filas->push((object) [
                    'curso' => $curso,
                    'periodo' => $silabo->periodo,
                    'silabo' => $silabo,
                    // El PDF legacy (campo cursos.silabo) no tiene periodo propio;
                    // se muestra junto al sílabo estructurado más reciente del curso.
                    'mostrarPdfLegacy' => $index === 0 && (bool) $curso->silabo,
                ]);
            }
        }

        $conPeriodo = $filas->where('periodo', '!=', 'Sílabo sin periodo')
            ->groupBy('periodo')
            ->sortKeysDesc();
        $sinPeriodo = $filas->where('periodo', 'Sílabo sin periodo')->groupBy('periodo');

        $cursosAgrupados = $conPeriodo->merge($sinPeriodo);

        return view('docentes.silabos', compact('docente', 'cursosAgrupados', 'periodoActual', 'nombrePeriodoActual'));
    }

    /**
     * Taq'e (Ferias de Investigación): el docente solo observa qué grupos
     * existen en su curso y en qué estado están sus trabajos — no aprueba
     * integrantes ni cambia estados.
     */
    public function verTaqe($docenteId, $cursoId)
    {
        $docente = Docente::findOrFail($docenteId);
        $curso = Curso::with('ciclo.programa')->findOrFail($cursoId);
        // Solo el propio docente (o admin): antes bastaba cambiar el {docente} de la URL.
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || (int) $user->docente?->id === (int) $docente->id, 403);
        abort_unless($curso->docentes->contains('id', $docente->id), 403);

        $grupos = $curso->taqeGrupos()
            ->with(['alumnos', 'evidencias.subidoPor', 'finales.subidoPor', 'proyecto', 'periodoActual', 'programa', 'ciclo', 'curso'])
            ->orderByDesc('id')->get();

        return view('docentes.taqe', compact('docente', 'curso', 'grupos'));
    }
}
