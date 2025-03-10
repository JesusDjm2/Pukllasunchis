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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            Unidades::create([
                'silabo_id' => $silabo->id,
                'titulo' => $titulo,
                'situacion' => $request->situacion_aprendizaje[$key],
                'duracion' => $request->duracion[$key],
                'desempeno' => $request->desempeno_especifico[$key],
                'ejes' => $request->ejes_tematicos[$key],
                'evidencia' => $request->evidencia_proceso[$key],
                'final' => $request->evidencia_final[$key],
            ]);
        }
        //Rúbricas
        foreach ($request->criterio as $index => $criterio) {
            Rubricas::create([
                'silabo_id' => $silabo->id,
                'criterio' => $criterio,
                'destacado' => $request->destacado[$index],
                'logrado' => $request->logrado[$index],
                'proceso' => $request->proceso[$index],
                'inicio' => $request->inicio[$index]
            ]);
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
        return view('admin.curso.silabos.show', compact('silabo', 'curso', 'docentes', 'competencias'));
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
    /*  public function update(Request $request, $id)
     {
         $silabo = Silabo::findOrFail($id);
         $curso = Curso::findOrFail($request->curso_id);
         $docente = Docente::findOrFail($request->docente_id);
         $proyecto = Proyecto::findOrFail($request->proyecto_id);
         $request->validate([
             'curso_id' => 'required|exists:cursos,id',
             'sumilla' => 'nullable|string',
             'proyecto_id' => 'required|exists:proyectos,id',
             'descripcion_proyecto' => 'nullable|string',
             'vinculacion_pi' => 'nullable|string',
             'producto_curso' => 'nullable|string',

             'capacidad_id' => 'array',
             'capacidad_id.*' => 'exists:capacidades,id',
             'descripcion' => 'array',
             'descripcion.*' => 'nullable|string',

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
             'fecha1' => 'nullable|date',
             'fecha2' => 'nullable|date',
         ]);

         $curso = Curso::findOrFail($request->curso_id);
         $proyecto = Proyecto::findOrFail($request->proyecto_id);

         $silabo->update([
             'curso_id' => $curso->id,
             'fecha1' => $request->fecha1,
             'fecha2' => $request->fecha2,
             'nombre' => $curso->nombre,
             'sumilla' => $request->sumilla,
             'proyecto_integrador' => $proyecto->nombre,
             'descripcion_proyecto_integrador' => $proyecto->descripcion,
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

             'organizacion' => $request->organizacion,
             'modelos_metodologicos' => $request->modelos_metodologicos,
             'recursos' => $request->recursos,
             'referencias' => $request->referencias,
         ]);
         if ($request->has('titulo_unidad')) {
             foreach ($request->titulo_unidad as $key => $titulo) {
                 if (!empty($titulo)) {
                     $unidad = $silabo->unidades()->updateOrCreate(
                         ['id' => $request->unidad_id[$key] ?? null], // Si existe un ID, lo actualiza
                         [
                             'titulo' => $titulo,
                             'situacion' => $request->situacion_aprendizaje[$key] ?? '',
                             'duracion' => $request->duracion[$key] ?? '',
                             'desempeno' => $request->desempeno_especifico[$key] ?? '',
                             'ejes' => $request->ejes_tematicos[$key] ?? '',
                             'evidencia' => $request->evidencia_proceso[$key] ?? '',
                             'final' => $request->evidencia_final[$key] ?? '',
                         ]
                     );
                 }
             }
         }

         // ================================
         // ACTUALIZAR RÚBRICAS
         // ================================
         if ($request->has('criterio')) {
             foreach ($request->criterio as $index => $criterio) {
                 if (!empty($criterio)) {
                     $rubrica = $silabo->rubricas()->updateOrCreate(
                         ['id' => $request->rubrica_id[$index] ?? null], // Si existe un ID, lo actualiza
                         [
                             'criterio' => $criterio,
                             'destacado' => $request->destacado[$index] ?? '',
                             'logrado' => $request->logrado[$index] ?? '',
                             'proceso' => $request->proceso[$index] ?? '',
                             'inicio' => $request->inicio[$index] ?? ''
                         ]
                     );
                 }
             }
         }
         if (is_array($request->enfoques)) {
             
             $silabo->enfoques()->delete();

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

          if ($request->has('capacidad_id')) {
              foreach ($request->capacidad_id as $key => $capacidadId) {
                  if (!empty($request->descripcion[$key])) {
                      TextCapacidad::updateOrCreate(
                          ['capacidad_id' => $capacidadId], 
                          ['descripcion' => $request->descripcion[$key]]
                      );
                  }
              }
          }

         $capacidadSeleccionadas = $request->capacidad_seleccionada ?? [];

         foreach ($request->capacidad_id as $key => $capacidadId) {
             $descripcion = $request->descripcion[$key] ?? null;

             if (in_array($capacidadId, $capacidadSeleccionadas) && !empty($descripcion)) {
                 
                 TextCapacidad::updateOrCreate(
                     ['capacidad_id' => $capacidadId],
                     ['descripcion' => $descripcion]
                 );
             } else {
                 
                 TextCapacidad::where('capacidad_id', $capacidadId)->delete();
             }
         }


         if (auth()->user()->hasRole('docente')) {
             return redirect()->route('vistaDocente', ['docente' => $docente->id])
                 ->with('success', 'Sílabo creado correctamente.');
         }

         return redirect()->route('silabos.index')->with('success', 'Sílabo actualizado correctamente.');
     } */

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

        // Eliminar enfoques anteriores del sílabo
        EnfoqueSilabo::where('silabo_id', $silabo->id)->delete();

        // Volver a crear los enfoques
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
        }
        
        // Actualizar unidades (eliminamos las existentes y creamos nuevas)
        Unidades::where('silabo_id', $silabo->id)->delete();
        foreach ($request->titulo_unidad as $key => $titulo) {
            Unidades::create([
                'silabo_id' => $silabo->id,
                'titulo' => $titulo,
                'situacion' => $request->situacion_aprendizaje[$key],
                'duracion' => $request->duracion[$key],
                'desempeno' => $request->desempeno_especifico[$key],
                'ejes' => $request->ejes_tematicos[$key],
                'evidencia' => $request->evidencia_proceso[$key],
                'final' => $request->evidencia_final[$key],
            ]);
        }
        // Actualizar rúbricas (eliminamos las existentes y creamos nuevas)
        Rubricas::where('silabo_id', $silabo->id)->delete();
        foreach ($request->criterio as $index => $criterio) {
            Rubricas::create([
                'silabo_id' => $silabo->id,
                'criterio' => $criterio,
                'destacado' => $request->destacado[$index],
                'logrado' => $request->logrado[$index],
                'proceso' => $request->proceso[$index],
                'inicio' => $request->inicio[$index]
            ]);
        }

        if (auth()->user()->hasRole('docente')) {
            return redirect()->route('vistaDocente', ['docente' => $docente->id])
                ->with('success', 'Sílabo actualizado correctamente.');
        }

        return redirect()->route('silabos.index')->with('success', 'Sílabo actualizado correctamente.');
    }

}
