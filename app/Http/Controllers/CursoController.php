<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Storage;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::all();
        $cant = Curso::count();
        $cursosInicial = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 1);
        })->get();
        $inicial = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 1);
        })->count();

        $cursosEib = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 2);
        })->get();
        $EIB = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 2);
        })->count();
        $inicialPPD = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 3);
        })->get();
        $iniPPD = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 3);
        })->count();
        $primariaPPD = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 4);
        })->get();
        $priPPD = Curso::whereHas('ciclo', function ($query) {
            $query->where('programa_id', 4);
        })->count();
        $competencias = Competencia::all();
        return view('admin.curso.index', compact('cursos', 'cant', 'inicial', 'cursosInicial', 'EIB', 'cursosEib', 'competencias', 'inicialPPD', 'primariaPPD', 'iniPPD', 'priPPD'));
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
        /* $request->validate([
            'silabo' => 'required|mimes:pdf|max:2048', // Validar el archivo PDF
        ]); */
        $request->validate([
            'silabo' => 'required|mimes:pdf|max:2048',
        ], [
            'silabo.required' => 'Debes seleccionar un archivo PDF antes de subirlo.',
            'silabo.mimes' => 'El archivo debe ser un PDF válido.',
            'silabo.max' => 'El archivo no debe exceder los 2MB.',
        ]);

        if ($request->hasFile('silabo')) {
            if ($curso->silabo) {
                $oldPath = public_path("docentes/silabo/{$curso->silabo}");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $silabo = $request->file('silabo');
            $nombreSilabo = $silabo->getClientOriginalName();
            $rutaSilabo = public_path("docentes/silabo/");
            $silabo->move($rutaSilabo, $nombreSilabo);

            $curso->silabo = $nombreSilabo;
            $curso->save();
        }

        return redirect()->back()->with('success', 'Sílabo actualizado exitosamente.');
    }
    public function destroySilabo(Curso $curso)
    {
        if ($curso->silabo) {
            $path = public_path("docentes/silabo/{$curso->silabo}");
            if (file_exists($path)) {
                unlink($path);
            }

            $curso->silabo = null;
            $curso->save();
        }

        return redirect()->back()->with('success', 'Sílabo eliminado exitosamente.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'nombre' => 'required|string',
            'sumilla' => 'required|string',
            'cc' => 'required|string',
            'horas' => 'required|string',
            'creditos' => 'required|string',
        ]);

        $curso = Curso::create([
            'programa_id' => $request->input('programa_id'),
            'ciclo_id' => $request->input('ciclo_id'),
            'nombre' => $request->input('nombre'),
            'cc' => $request->input('cc'),
            'horas' => $request->input('horas'),
            'creditos' => $request->input('creditos'),
        ]);

        if ($request->has('competencias')) {
            $curso->competencias()->sync($request->input('competencias'));
        }

        return redirect()->route('curso.index')->with('success', 'Curso creado exitosamente');
    }
    public function classroomClaveCRUD(Request $request, Curso $curso)
    {
        // Verificar si se debe eliminar
        if ($request->has('delete') && $request->input('delete') === 'true') {
            // Eliminar valores de classroom y clave
            $curso->update(['classroom' => null, 'clave' => null]);
            return redirect()->back()->with('success', 'Classroom y Clave eliminados correctamente.');
        }

        // Crear o actualizar los valores
        $curso->update([
            'classroom' => $request->input('classroom'),
            'clave' => $request->input('clave')
        ]);

        return redirect()->back()->with('success', 'Classroom y Clave guardados correctamente.');
    }
    public function edit(Curso $curso)
    {
        $programa = $curso->ciclo->programa;
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $competencias = Competencia::all();
        return view('admin.curso.edit', compact('programas', 'curso', 'ciclos', 'competencias', 'programa'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'nombre' => 'required|string',
            'sumilla' => 'required|string',
            'cc' => 'required|string',
            'horas' => 'required|string',
            'creditos' => 'required|string',
            'competencias' => 'array|exists:competencias,id' // Validar competencias
        ]);

        $curso = Curso::findOrFail($id);

        $curso->update([
            'programa_id' => $request->input('programa_id'),
            'ciclo_id' => $request->input('ciclo_id'),
            'nombre' => $request->input('nombre'),
            'sumilla' => $request->input('sumilla'),
            'cc' => $request->input('cc'),
            'horas' => $request->input('horas'),
            'creditos' => $request->input('creditos'),
        ]);

        // Sincronizar las competencias con el curso
        $curso->competencias()->sync($request->input('competencias', []));

        return redirect()->route('curso.index')->with('success', 'Curso actualizado exitosamente');
    }
    public function show(Curso $curso)
    {
        $programa = $curso->ciclo->programa;
        $ciclo = $curso->ciclo;
        /* $alumno = auth()->user()->ppd; */
        $alumno = auth()->user()->alumnoB;
        $alumnos = $programa->alumnos()
            ->where('ciclo_id', $ciclo->id)
            ->orderBy('apellidos')
            ->get();
        $cantidadAlumnos = $alumnos->count();
        $docentes = $curso->docentes;

        if (auth()->check() && auth()->user()->hasRole('alumnoB')) {
            return view('alumnos.ppd.curso', compact('curso', 'alumnos', 'docentes', 'cantidadAlumnos', 'alumno'));
        }
        return view('admin.curso.show', compact('curso', 'alumnos', 'cantidadAlumnos', 'docentes'));
    }
    public function destroy(Curso $curso)
    {
        $curso->delete();

        return redirect()->route('curso.index')->with('success', 'Curso eliminado exitosamente');
    }
}
