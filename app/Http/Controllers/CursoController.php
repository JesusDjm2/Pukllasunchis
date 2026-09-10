<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\Programa;
use App\Models\SilaboPdf;
use App\Models\User;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    /** Devuelve true solo si el usuario autenticado tiene el rol super-admin. */
    private function esSuperAdmin(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function index()
    {
        $cursos       = Curso::all();
        $cursosInicial = Curso::whereHas('ciclo', fn($q) => $q->where('programa_id', 1))->get();
        $cursosEib     = Curso::whereHas('ciclo', fn($q) => $q->where('programa_id', 2))->get();
        $inicialPPD    = Curso::whereHas('ciclo', fn($q) => $q->where('programa_id', 3))->get();
        $primariaPPD   = Curso::whereHas('ciclo', fn($q) => $q->where('programa_id', 4))->get();

        // ── Ocultar extracurriculares para admin (no super-admin) ──────────
        if (! $this->esSuperAdmin()) {
            $sinExtra = fn($c) => strtolower(trim($c->cc ?? '')) !== 'extracurricular';
            $cursos        = $cursos->filter($sinExtra)->values();
            $cursosInicial = $cursosInicial->filter($sinExtra)->values();
            $cursosEib     = $cursosEib->filter($sinExtra)->values();
            $inicialPPD    = $inicialPPD->filter($sinExtra)->values();
            $primariaPPD   = $primariaPPD->filter($sinExtra)->values();
        }

        $cant   = $cursos->count();
        $inicial = $cursosInicial->count();
        $EIB    = $cursosEib->count();
        $iniPPD = $inicialPPD->count();
        $priPPD = $primariaPPD->count();

        $competencias = Competencia::all();
        // Solo ciclos con al menos un curso: un ciclo vacío (p. ej. "Egresados <año>")
        // en este selector siempre daría 0 resultados si se elige.
        $ciclosPorPrograma = Ciclo::with('programa')
            ->whereHas('cursos')
            ->orderBy('programa_id')->orderBy('id')->get()->groupBy('programa_id');

        return view('admin.curso.index', compact(
            'cursos', 'cant', 'inicial', 'cursosInicial',
            'EIB', 'cursosEib', 'competencias',
            'inicialPPD', 'primariaPPD', 'iniPPD', 'priPPD',
            'ciclosPorPrograma'
        ));
    }

    public function create()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::all();
        $competencias = Competencia::all();

        return view('admin.curso.create', compact('programas', 'ciclos', 'cursos', 'competencias'));
    }

    public function uploadSilabo(Request $request, Curso $curso)
    {
        $request->validate([
            'silabo' => 'required|mimes:pdf|max:2048',
        ], [
            'silabo.required' => 'Debes seleccionar un archivo PDF antes de subirlo.',
            'silabo.mimes' => 'El archivo debe ser un PDF válido.',
            'silabo.max' => 'El archivo no debe exceder los 2 MB.',
        ]);

        $periodoActual = PeriodoActual::actual();
        if (! $periodoActual) {
            return redirect()->back()->with('error', 'No hay un periodo activo en este momento.');
        }

        /* if ($request->hasFile('silabo')) {
            if ($curso->silabo) {
                $oldPath = public_path("docentes/silabo/{$curso->silabo}");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $silabo = $request->file('silabo');
            $nombreSilabo = $silabo->getClientOriginalName();
            $rutaSilabo = public_path('docentes/silabo/');
            $silabo->move($rutaSilabo, $nombreSilabo);

            $curso->silabo = $nombreSilabo;
            $curso->save();
        } */
        if ($request->hasFile('silabo')) {
            $silabo = $request->file('silabo');
            $nombreSilabo = $silabo->getClientOriginalName(); // mantiene el nombre original
            $rutaSilabo = public_path('docentes/silabo/');

            // Crear directorio si no existe
            if (! file_exists($rutaSilabo)) {
                mkdir($rutaSilabo, 0777, true);
            }

            // Si ya existe un archivo con el mismo nombre, se elimina para evitar conflicto
            $pathExistente = $rutaSilabo.$nombreSilabo;
            if (file_exists($pathExistente)) {
                unlink($pathExistente);
            }

            // Mover archivo
            $silabo->move($rutaSilabo, $nombreSilabo);

            // Buscar si ya existe un registro para este curso y periodo
            $silaboExistente = SilaboPdf::where('curso_id', $curso->id)
                ->where('periodo_actual_id', $periodoActual->id)
                ->first();

            if ($silaboExistente) {
                // Eliminar archivo anterior si existe
                $oldPath = public_path("docentes/silabo/{$silaboExistente->pdf}");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }

                $silaboExistente->update(['pdf' => $nombreSilabo]);
            } else {
                SilaboPdf::create([
                    'pdf' => $nombreSilabo,
                    'curso_id' => $curso->id,
                    'periodo_actual_id' => $periodoActual->id,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Sílabo actualizado exitosamente.');
    }

    public function destroySilabo(Curso $curso)
    {
        $silaboPdf = SilaboPdf::where('curso_id', $curso->id)->first();

        if ($silaboPdf) {
            $path = public_path("docentes/silabo/{$silaboPdf->pdf}");
            if (file_exists($path)) {
                unlink($path);
            }

            $silaboPdf->delete();

            return redirect()->back()->with('success', 'Sílabo en PDF eliminado exitosamente.');
        }

        if ($curso->silabo) {
            $path = public_path("docentes/silabo/{$curso->silabo}");
            if (file_exists($path)) {
                unlink($path);
            }

            $curso->silabo = null;
            $curso->save();

            return redirect()->back()->with('success', 'Sílabo eliminado exitosamente.');
        }

        return redirect()->back()->with('error', 'No se encontró ningún sílabo para eliminar.');
    }

    public function store(Request $request)
    {
        // Bloquear creación de extracurriculares para admin
        if (! $this->esSuperAdmin() && strtolower(trim($request->input('cc', ''))) === 'extracurricular') {
            return redirect()->back()
                ->with('error', 'No tienes permisos para crear cursos extracurriculares.')
                ->withInput();
        }

        $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'nombre' => 'required|string',
            // Los cursos Extracurriculares no llevan sumilla, horas ni créditos.
            'sumilla' => 'nullable|string|required_unless:cc,Extracurricular',
            'cc' => 'required|string',
            'horas' => 'nullable|string|required_unless:cc,Extracurricular',
            'creditos' => 'nullable|string|required_unless:cc,Extracurricular',
        ]);

        $curso = Curso::create([
            'programa_id' => $request->input('programa_id'),
            'ciclo_id' => $request->input('ciclo_id'),
            'nombre' => $request->input('nombre'),
            'cc' => $request->input('cc'),
            'horas' => $request->input('horas') !== '' ? $request->input('horas') : null,
            'creditos' => $request->input('creditos') !== '' ? $request->input('creditos') : null,
        ]);

        if ($request->has('competencias')) {
            $curso->competencias()->sync($request->input('competencias'));
        }

        return redirect()->route('curso.index')->with('success', 'Curso creado exitosamente');
    }

    public function classroomClaveCRUD(Request $request, Curso $curso)
    {
        if ($request->has('delete') && $request->input('delete') === 'true') {
            $curso->update(['classroom' => null, 'clave' => null]);

            return redirect()->back()->with('success', 'Classroom y Clave eliminados correctamente.');
        }
        $curso->update([
            'classroom' => $request->input('classroom'),
            'clave' => $request->input('clave'),
        ]);

        return redirect()->back()->with('success', 'Classroom y Clave guardados correctamente.');
    }

    public function edit(Curso $curso)
    {
        // Bloquear edición de extracurriculares para admin
        if (! $this->esSuperAdmin() && strtolower(trim($curso->cc ?? '')) === 'extracurricular') {
            return redirect()->route('curso.index')
                ->with('error', 'No tienes permisos para editar cursos extracurriculares.');
        }

        $programa = $curso->ciclo->programa;
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $competencias = Competencia::all();

        return view('admin.curso.edit', compact('programas', 'curso', 'ciclos', 'competencias', 'programa'));
    }

    public function update(Request $request, $id)
    {
        // Bloquear asignación de cc=Extracurricular para admin
        if (! $this->esSuperAdmin() && strtolower(trim($request->input('cc', ''))) === 'extracurricular') {
            return redirect()->back()
                ->with('error', 'No tienes permisos para asignar el tipo Extracurricular a un curso.')
                ->withInput();
        }

        $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'nombre' => 'required|string',
            // Los cursos Extracurriculares no llevan sumilla, horas ni créditos.
            'sumilla' => 'nullable|string|required_unless:cc,Extracurricular',
            'cc' => 'required|string',
            'horas' => 'nullable|string|required_unless:cc,Extracurricular',
            'creditos' => 'nullable|string|required_unless:cc,Extracurricular',
            'competencias' => 'array|exists:competencias,id', // Validar competencias
        ]);

        $curso = Curso::findOrFail($id);

        $curso->update([
            'programa_id' => $request->input('programa_id'),
            'ciclo_id' => $request->input('ciclo_id'),
            'nombre' => $request->input('nombre'),
            'sumilla' => $request->input('sumilla'),
            'cc' => $request->input('cc'),
            'horas' => $request->input('horas') !== '' ? $request->input('horas') : null,
            'creditos' => $request->input('creditos') !== '' ? $request->input('creditos') : null,
        ]);

        // Sincronizar las competencias con el curso
        $curso->competencias()->sync($request->input('competencias', []));

        return redirect()->route('curso.index')->with('success', 'Curso actualizado exitosamente');
    }

    /* public function show(Curso $curso)
    {
        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;
        $alumno = auth()->user()->alumnoB;

        // Alumnos del mismo ciclo
        $alumnosCiclo = $programa->alumnos()
            ->where('ciclo_id', $ciclo->id)
            ->orderBy('apellidos')
            ->get();

        $alumnosRelacionados = $curso->alumnos()->orderBy('apellidos')->get();

        $alumnos = $alumnosCiclo->merge($alumnosRelacionados)->unique('id')->values();

        $cantidadAlumnos = $alumnos->count() + $alumnosRelacionados->count();
        $docentes = $curso->docentes;

        if (auth()->check() && auth()->user()->hasRole('alumnoB')) {
            return view('alumnos.ppd.curso', compact('curso', 'alumnos', 'docentes', 'cantidadAlumnos', 'alumno'));
        }
        return view('admin.curso.show', compact('curso', 'alumnos', 'cantidadAlumnos', 'docentes'));
    } */
    public function show(Curso $curso)
    {
        // Bloquear vista de extracurriculares para admin
        if (! $this->esSuperAdmin() && strtolower(trim($curso->cc ?? '')) === 'extracurricular') {
            return redirect()->route('curso.index')
                ->with('error', 'No tienes permisos para ver cursos extracurriculares.');
        }

        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;

        $usersPrograma = User::where('programa_id', $programa->id)
            ->with('alumnoB')
            ->orderBy('apellidos')
            ->get();
        $alumnosPrograma = $usersPrograma->pluck('alumnoB')->filter();
        $alumnosRelacionados = $curso->alumnos()->orderBy('apellidos')->get();
        $alumnos = $alumnosPrograma->merge($alumnosRelacionados)->unique('id')->values();
        $cantidadAlumnos = $alumnos->count();
        $docentes = $curso->docentes;

        if (auth()->check() && auth()->user()->hasRole('alumnoB')) {
            $alumno = auth()->user()->alumnoB;
            return view('alumnos.ppd.curso', compact('curso', 'alumnos', 'docentes', 'cantidadAlumnos', 'alumno'));
        }

        if (auth()->check() && auth()->user()->hasRole('alumno')) {
            $alumno = auth()->user()->alumno;
            return view('alumnos.vistasAlumnos.curso', compact('curso', 'docentes', 'alumno'));
        }

        $alumno = null;
        return view('admin.curso.show', compact('curso', 'alumnos', 'cantidadAlumnos', 'docentes', 'alumno'));
    }

    public function asignarDocentesForm(Curso $curso)
    {
        $docentes = Docente::orderBy('nombre')->get();
        $asignados = $curso->docentes->pluck('id')->toArray();

        return view('admin.curso.docentes', compact('curso', 'docentes', 'asignados'));
    }

    public function asignarDocentesUpdate(Request $request, Curso $curso)
    {
        $curso->docentes()->sync($request->input('docentes', []));

        return redirect()->route('curso.index')->with('success', 'Docentes actualizados para el curso.');
    }

    public function destroy(Curso $curso)
    {
        // Bloquear eliminación de extracurriculares para admin
        if (! $this->esSuperAdmin() && strtolower(trim($curso->cc ?? '')) === 'extracurricular') {
            return redirect()->route('curso.index')
                ->with('error', 'No tienes permisos para eliminar cursos extracurriculares.');
        }

        $curso->delete();

        return redirect()->route('curso.index')->with('success', 'Curso eliminado exitosamente');
    }
}
