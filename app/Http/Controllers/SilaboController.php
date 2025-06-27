<?php

namespace App\Http\Controllers;

use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Enfoques;
use App\Models\EnfoqueSilabo;
use App\Models\Estandares;
use App\Models\Proyecto;
use App\Models\Rubricas;
use App\Models\Silabo;
use App\Models\TextCapacidad;
use App\Models\Unidades;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SilaboController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasRole('docente')) {
            return redirect()->route('docente.index');
        }
        return view('admin.curso.silabos.index');
    }

    public function create(Request $request)
    {
        $proyectos = Proyecto::all();
        $enfoques = Enfoques::all();
        $curso = Curso::findOrFail($request->curso_id);
        $docente = Docente::findOrFail($request->docente_id);
        $docente = Auth::user()->docente;
        $competencias = $curso->competenciasSeleccionadas()->with('capacidad')->get();
        $competencias = $curso->competenciasSeleccionadas()->with([
            'capacidad',
            'estandares' => function ($query) use ($curso) {
                $query->whereHas('ciclos', function ($q) use ($curso) {
                    $q->where('ciclo_id', $curso->ciclo_id);
                });
            }
        ])->get();

        if ($competencias->isEmpty()) {
            $competencias = $curso->competencias()->with([
                'capacidad',
                'estandares' => function ($query) use ($curso) {
                    $query->whereHas('ciclos', function ($q) use ($curso) {
                        $q->where('ciclo_id', $curso->ciclo_id);
                    });
                }
            ])->get();
        }


        return view('admin.curso.silabos.create', compact('curso', 'docente', 'proyectos', 'enfoques', 'competencias', 'docente'));
    }
    public function store(Request $request)
    {
        $existeSilabo = Silabo::where('curso_id', $request->curso_id)->exists();

        if ($existeSilabo) {
            return redirect()->back()->with('error', 'Ya existe un sílabo registrado para este curso, solo puede existir 1 sílabo para 1 curso.');
        }

        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'sumilla' => 'nullable|string',
            'fecha1' => 'nullable|date',
            'fecha2' => 'nullable|date',

            'proyecto_integrador' => 'nullable|string',
            'descripcion_proyecto_integrador' => 'nullable|string',
            'vinculacion_pi' => 'nullable|string',
            'producto_curso' => 'nullable|string',

            'capacidad1' => 'nullable|string',
            'desempeno1' => 'nullable|string',
            'criterio1' => 'nullable|string',
            'evidencia1' => 'nullable|string',
            'instrumento1' => 'nullable|string',

            'capacidad2' => 'nullable|string',
            'desempeno2' => 'nullable|string',
            'criterio2' => 'nullable|string',
            'evidencia2' => 'nullable|string',
            'instrumento2' => 'nullable|string',

            'capacidad3' => 'nullable|string',
            'desempeno3' => 'nullable|string',
            'criterio3' => 'nullable|string',
            'evidencia3' => 'nullable|string',
            'instrumento3' => 'nullable|string',

            'organizacion' => 'nullable|string',
            'modelos_metodologicos' => 'nullable|string',
            'recursos' => 'nullable|string',
            'referencias' => 'nullable|string',
        ]);

        $curso = Curso::findOrFail($request->curso_id);
        $docente = Docente::findOrFail($request->docente_id);

        $silabo = Silabo::create([
            'curso_id' => $curso->id,
            'sumilla' => $request->sumilla,
            'fecha1' => $request->fecha1,
            'fecha2' => $request->fecha2,
            'nombre' => $curso->nombre,

            'proyecto_integrador' => $request->proyecto_integrador,
            'descripcion_proyecto_integrador' => $request->descripcion_proyecto_integrador,
            'vinculacion_pi' => $request->vinculacion_pi,
            'producto_curso' => $request->producto_curso,

            'capacidad1' => $request->capacidad1,
            'desempeno1' => $request->desempeno1,
            'criterio1' => $request->criterio1,
            'evidencia1' => $request->evidencia1,
            'instrumento1' => $request->instrumento1,

            'capacidad2' => $request->capacidad2,
            'desempeno2' => $request->desempeno2,
            'criterio2' => $request->criterio2,
            'evidencia2' => $request->evidencia2,
            'instrumento2' => $request->instrumento2,

            'capacidad3' => $request->capacidad3,
            'desempeno3' => $request->desempeno3,
            'criterio3' => $request->criterio3,
            'evidencia3' => $request->evidencia3,
            'instrumento3' => $request->instrumento3,

            'modelos_metodologicos' => $request->modelos_metodologicos,
            'recursos' => $request->recursos,
            'referencias' => $request->referencias,
        ]);
        //Enfóques
        if (is_array($request->enfoques)) {
            foreach ($request->enfoques as $key => $enfoqueId) {
                $enfoque = Enfoques::find($enfoqueId);
                EnfoqueSilabo::create([
                    'silabo_id' => $silabo->id,
                    'nombre' => $enfoque ? $enfoque->nombre : 'Desconocido',
                    'descripcion' => $enfoque ? $enfoque->descripcion : 'Sin descripción',
                    'enfoque_observables' => $request->observables[$key] ?? '',
                    'silabo_concretas' => $request->concretas[$key] ?? '',
                ]);
            }
        }
        // Guardar unidades
        foreach ($request->titulo_unidad as $key => $titulo) {
            if (!empty($titulo)) {
                Unidades::create([
                    'silabo_id' => $silabo->id,
                    'titulo' => $titulo,
                    'situacion' => $request->situacion_aprendizaje[$key] ?? null,
                    'duracion' => $request->duracion[$key] ?? null,
                    'desempeno' => $request->desempeno_especifico[$key] ?? null,
                    'ejes' => $request->ejes_tematicos[$key] ?? null,
                    'evidencia' => $request->evidencia_proceso[$key] ?? null,
                    'final' => $request->evidencia_final[$key] ?? null,
                ]);
            }
        }
        //Rúbricas        
        foreach ($request->criterio as $index => $criterio) {
            if (!empty($criterio)) { // Verifica que el criterio no sea null o vacío
                Rubricas::create([
                    'silabo_id' => $silabo->id,
                    'criterio' => $criterio,
                    'destacado' => $request->destacado[$index] ?? null,
                    'logrado' => $request->logrado[$index] ?? null,
                    'proceso' => $request->proceso[$index] ?? null,
                    'inicio' => $request->inicio[$index] ?? null,
                ]);
            }
        }
        if (auth()->user()->hasRole('docente')) {
            return redirect()->route('vistaDocente', ['docente' => $docente->id])
                ->with('success', 'Sílabo creado correctamente.');
        }

        return redirect()->route('silabos.index')->with('success', 'Sílabo creado correctamente.');
    }
    public function show(Silabo $silabo)
    {
        $curso = $silabo->curso;
        $docentes = $curso->docentes;
        $competencias = $curso->competenciasSeleccionadas()->with([
            'capacidad',
            'estandares' => function ($query) use ($curso) {
                $query->whereHas('ciclos', function ($q) use ($curso) {
                    $q->where('ciclo_id', $curso->ciclo_id);
                });
            }
        ])->get();

        if ($competencias->isEmpty()) {
            $competencias = $curso->competencias()->with([
                'capacidad',
                'estandares' => function ($query) use ($curso) {
                    $query->whereHas('ciclos', function ($q) use ($curso) {
                        $q->where('ciclo_id', $curso->ciclo_id);
                    });
                }
            ])->get();
        }

        $silabo->load(['enfoques', 'unidades', 'rubricas']);
        if (Auth::user()->hasRole('alumno')) {
            $alumno = Auth::user()->alumno;
            return view('alumnos.vistasAlumnos.silabos', compact('silabo', 'curso', 'docentes', 'competencias', 'alumno'));

        } elseif (Auth::user()->hasRole('alumnoB')) {
            $alumno = Auth::user()->alumnoB;
            return view('alumnos.ppd.silabo', compact('silabo', 'curso', 'docentes', 'competencias', 'alumno'));

        } elseif (Auth::user()->hasRole('admin')) {
            return view('admin.silabo', compact('silabo', 'curso', 'docentes', 'competencias'));

        } else {
            return view('admin.curso.silabos.show', compact('silabo', 'curso', 'docentes', 'competencias'));
        }
    }

    public function edit($id, Request $request)
    {
        $silabo = Silabo::with(['unidades', 'rubricas', 'enfoques'])->findOrFail($id);
        $curso = Curso::findOrFail($request->curso_id);
        $docente = Docente::findOrFail($request->docente_id);

        $proyectos = Proyecto::all();
        $enfoques = Enfoques::all();
        $competencias = $curso->competenciasSeleccionadas()->with('capacidad')->get();
        $competencias = $curso->competenciasSeleccionadas()->with([
            'capacidad',
            'estandares' => function ($query) use ($curso) {
                $query->whereHas('ciclos', function ($q) use ($curso) {
                    $q->where('ciclo_id', $curso->ciclo_id);
                });
            }
        ])->get();

        if ($competencias->isEmpty()) {
            $competencias = $curso->competencias()->with([
                'capacidad',
                'estandares' => function ($query) use ($curso) {
                    $query->whereHas('ciclos', function ($q) use ($curso) {
                        $q->where('ciclo_id', $curso->ciclo_id);
                    });
                }
            ])->get();
        }

        return view('admin.curso.silabos.edit', compact('silabo', 'curso', 'docente', 'proyectos', 'enfoques', 'competencias'));
    }

    public function update(Request $request, Silabo $silabo)
    {
        $curso = Curso::findOrFail($request->curso_id);
        $docente = Docente::findOrFail($request->docente_id);
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'sumilla' => 'nullable|string',
            'fecha1' => 'nullable|date',
            'fecha2' => 'nullable|date',

            'proyecto_integrador' => 'nullable|string',
            'descripcion_proyecto_integrador' => 'nullable|string',
            'vinculacion_pi' => 'nullable|string',
            'producto_curso' => 'nullable|string',

            'capacidad1' => 'nullable|string',
            'desempeno1' => 'nullable|string',
            'criterio1' => 'nullable|string',
            'evidencia1' => 'nullable|string',
            'instrumento1' => 'nullable|string',

            'capacidad2' => 'nullable|string',
            'desempeno2' => 'nullable|string',
            'criterio2' => 'nullable|string',
            'evidencia2' => 'nullable|string',
            'instrumento2' => 'nullable|string',

            'capacidad3' => 'nullable|string',
            'desempeno3' => 'nullable|string',
            'criterio3' => 'nullable|string',
            'evidencia3' => 'nullable|string',
            'instrumento3' => 'nullable|string',

            'organizacion' => 'nullable|string',
            'modelos_metodologicos' => 'nullable|string',
            'recursos' => 'nullable|string',
            'referencias' => 'nullable|string',
        ]);

        // Actualizar datos del sílabo
        $silabo->update([
            'sumilla' => $request->sumilla,
            'fecha1' => $request->fecha1,
            'fecha2' => $request->fecha2,
            'proyecto_integrador' => $request->proyecto_integrador,
            'descripcion_proyecto_integrador' => $request->descripcion_proyecto_integrador,
            'vinculacion_pi' => $request->vinculacion_pi,
            'producto_curso' => $request->producto_curso,

            'capacidad1' => $request->capacidad1,
            'desempeno1' => $request->desempeno1,
            'criterio1' => $request->criterio1,
            'evidencia1' => $request->evidencia1,
            'instrumento1' => $request->instrumento1,

            'capacidad2' => $request->capacidad2,
            'desempeno2' => $request->desempeno2,
            'criterio2' => $request->criterio2,
            'evidencia2' => $request->evidencia2,
            'instrumento2' => $request->instrumento2,

            'capacidad3' => $request->capacidad3,
            'desempeno3' => $request->desempeno3,
            'criterio3' => $request->criterio3,
            'evidencia3' => $request->evidencia3,
            'instrumento3' => $request->instrumento3,

            'modelos_metodologicos' => $request->modelos_metodologicos,
            'recursos' => $request->recursos,
            'referencias' => $request->referencias,
        ]);


        // 🗑️ Eliminar todos los registros EnfoqueSilabo asociados al silabo
        // 🗑️ Eliminar los enfoques actuales del silabo para reemplazarlos con los nuevos
        EnfoqueSilabo::where('silabo_id', $silabo->id)->delete();

        // ✅ Recorrer los datos del formulario
        if (!empty($request->enfoques) && is_array($request->enfoques)) {
            foreach ($request->enfoques as $key => $enfoqueId) {
                EnfoqueSilabo::create([
                    'silabo_id' => $silabo->id,
                    'nombre' => $request->nombres[$key] ?? 'Desconocido', // ✅ Ahora toma nombre de la vista
                    'descripcion' => $request->descripciones[$key] ?? 'Sin descripción', // ✅ Ahora toma descripción de la vista
                    'enfoque_observables' => $request->observables[$key] ?? '',
                    'silabo_concretas' => $request->concretas[$key] ?? '',
                ]);
            }
        }


        /* EnfoqueSilabo::where('silabo_id', $silabo->id)->delete();
        if (!empty($request->enfoques) && is_array($request->enfoques)) {
            foreach ($request->enfoques as $key => $enfoqueId) {
                $enfoque = Enfoques::find($enfoqueId);

                EnfoqueSilabo::create([
                    'silabo_id' => $silabo->id,
                    'nombre' => $enfoque ? $enfoque->nombre : 'Desconocido',
                    'descripcion' => $enfoque ? $enfoque->descripcion : 'Sin descripción',
                    'enfoque_observables' => $request->observables[$key] ?? '',
                    'silabo_concretas' => $request->concretas[$key] ?? '',
                ]);
            }
        } */

        if (!empty($request->titulo_unidad) && is_array($request->titulo_unidad)) {
            // Eliminamos las unidades existentes antes de insertar las nuevas
            Unidades::where('silabo_id', $silabo->id)->delete();

            foreach ($request->titulo_unidad as $key => $titulo) {
                Unidades::create([
                    'silabo_id' => $silabo->id,
                    'titulo' => $titulo,
                    'situacion' => $request->situacion_aprendizaje[$key] ?? null,
                    'duracion' => $request->duracion[$key] ?? null,
                    'desempeno' => $request->desempeno_especifico[$key] ?? null,
                    'ejes' => $request->ejes_tematicos[$key] ?? null,
                    'evidencia' => $request->evidencia_proceso[$key] ?? null,
                    'final' => $request->evidencia_final[$key] ?? null,
                ]);
            }
        }


        if (!empty($request->criterio) && is_array($request->criterio)) {
            // Eliminamos las rúbricas existentes antes de insertar las nuevas
            Rubricas::where('silabo_id', $silabo->id)->delete();

            foreach ($request->criterio as $index => $criterio) {
                // Verificar que el criterio no sea nulo o vacío
                if (!empty($criterio)) {
                    Rubricas::create([
                        'silabo_id' => $silabo->id,
                        'criterio' => $criterio,
                        'destacado' => $request->destacado[$index] ?? null,
                        'logrado' => $request->logrado[$index] ?? null,
                        'proceso' => $request->proceso[$index] ?? null,
                        'inicio' => $request->inicio[$index] ?? null,
                    ]);
                }
            }
        }

        /* if (!empty($request->criterio) && is_array($request->criterio)) {
            // Eliminamos las rúbricas existentes antes de insertar las nuevas
            Rubricas::where('silabo_id', $silabo->id)->delete();

            foreach ($request->criterio as $index => $criterio) {
                Rubricas::create([
                    'silabo_id' => $silabo->id,
                    'criterio' => $criterio,
                    'destacado' => $request->destacado[$index] ?? null,
                    'logrado' => $request->logrado[$index] ?? null,
                    'proceso' => $request->proceso[$index] ?? null,
                    'inicio' => $request->inicio[$index] ?? null,
                ]);
            }
        } */
        if (auth()->user()->hasRole('docente')) {
            return redirect()->route('vistaDocente', ['docente' => $docente->id])
                ->with('success', 'Sílabo actualizado correctamente.');
        }

        return redirect()->route('silabos.index')->with('success', 'Sílabo actualizado correctamente.');
    }

    public function exportarPDF(Silabo $silabo)
    {
        $curso = $silabo->curso;
        $docentes = $curso->docentes;
        $competencias = $curso->competenciasSeleccionadas()->with([
            'capacidad',
            'estandares' => function ($query) use ($curso) {
                $query->whereHas('ciclos', function ($q) use ($curso) {
                    $q->where('ciclo_id', $curso->ciclo_id);
                });
            }
        ])->get();

        if ($competencias->isEmpty()) {
            $competencias = $curso->competencias()->with([
                'capacidad',
                'estandares' => function ($query) use ($curso) {
                    $query->whereHas('ciclos', function ($q) use ($curso) {
                        $q->where('ciclo_id', $curso->ciclo_id);
                    });
                }
            ])->get();
        }

        $silabo->load(['enfoques', 'unidades', 'rubricas']);
        $pdf = Pdf::loadView('admin.curso.silabos.pdf', compact('silabo', 'curso', 'docentes', 'competencias'));
        /* return $pdf->download('silabo_' . $silabo->id . '.pdf'); */
        return $pdf->download('silabo_' . Str::slug($silabo->nombre) . '.pdf');

    }

}
