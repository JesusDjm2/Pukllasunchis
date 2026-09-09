<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Enfoques;
use App\Models\EnfoqueSilabo;
use App\Models\PeriodoActual;
use App\Models\Programa;
use App\Models\Proyecto;
use App\Models\Rubricas;
use App\Models\Silabo;
use App\Models\Unidades;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SilaboController extends Controller
{
    /** Solo docentes (gestionan sus propios sílabos) y staff admin pueden crear/editar/eliminar sílabos. */
    private function puedeGestionarSilabos(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('docente') || $user->hasRole('admin') || $user->hasRole('super-admin'));
    }

    public function index(Request $request)
    {
        abort_unless($this->puedeGestionarSilabos(), 403);

        if (auth()->user()->hasRole('docente')) {
            return redirect()->route('docente.index');
        }

        $periodoFiltroId = $request->filled('periodo_id') ? (int) $request->input('periodo_id') : null;

        $silabos = Silabo::with(['curso.ciclo.programa', 'curso.docentes', 'periodoActual'])
            ->when($periodoFiltroId, fn ($q) => $q->where('periodo_actual_id', $periodoFiltroId))
            ->orderByDesc('id')
            ->get();

        $todosLosPeriodos = PeriodoActual::orderBy('nombre', 'asc')->get();

        return view('admin.curso.silabos.index', compact('silabos', 'todosLosPeriodos', 'periodoFiltroId'));
    }

    public function create(Request $request)
    {
        abort_unless($this->puedeGestionarSilabos(), 403);

        // Sin curso preseleccionado (p. ej. entrando desde el listado admin de Sílabos):
        // primero se elige el curso antes de mostrar el formulario completo.
        if (! $request->filled('curso_id')) {
            $programas = Programa::all();
            $ciclos = Ciclo::all();

            return view('admin.curso.silabos.seleccionar-curso', compact('programas', 'ciclos'));
        }

        $proyectos = Proyecto::all();
        $periodoActual = PeriodoActual::actual();
        $enfoques = Enfoques::all();
        $curso = Curso::findOrFail($request->curso_id);
        $docente = Auth::user()->docente
            ?? ($request->filled('docente_id') ? Docente::find($request->docente_id) : $curso->docentes->first());
        $competencias = $curso->competenciasSeleccionadas()->with('capacidad')->get();
        $competencias = $curso->competenciasSeleccionadas()->with([
            'capacidad',
            'estandares' => function ($query) use ($curso) {
                $query->whereHas('ciclos', function ($q) use ($curso) {
                    $q->where('ciclo_id', $curso->ciclo_id);
                });
            },
        ])->get();

        if ($competencias->isEmpty()) {
            $competencias = $curso->competencias()->with([
                'capacidad',
                'estandares' => function ($query) use ($curso) {
                    $query->whereHas('ciclos', function ($q) use ($curso) {
                        $q->where('ciclo_id', $curso->ciclo_id);
                    });
                },
            ])->get();
        }

        return view('admin.curso.silabos.create', compact('curso', 'docente', 'proyectos', 'enfoques', 'competencias', 'docente', 'periodoActual'));
    }

    public function store(Request $request)
    {
        abort_unless($this->puedeGestionarSilabos(), 403);

        $periodoActual = PeriodoActual::actual();

        if (! $periodoActual) {
            return redirect()->back()->with('error', 'No hay un periodo activo configurado.');
        }
        // Verificar si ya existe un sílabo para este curso en el mismo periodo actual
        $existeSilabo = Silabo::where('curso_id', $request->curso_id)
            ->where('periodo', $periodoActual->nombre)
            ->exists();
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

        $silabo = Silabo::create([
            'curso_id' => $curso->id,
            'sumilla' => $request->sumilla,
            // El nombre del periodo se toma del periodo activo resuelto en el servidor,
            // nunca del formulario: evita que quede desincronizado de periodo_actual_id
            // si el campo oculto que lo replicaba en el cliente no llega a poblarse.
            'periodo' => $periodoActual->nombre,
            'periodo_actual_id' => $periodoActual->id,
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
            if (! empty($titulo)) {
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
            if (! empty($criterio)) { // Verifica que el criterio no sea null o vacío
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
            return redirect()->route('vistaDocente', ['docente' => auth()->user()->docente->id])
                ->with('success', 'Sílabo creado correctamente.');
        }

        return redirect()->route('silabos.index')->with('success', 'Sílabo creado correctamente.');
    }

    public function show(Silabo $silabo)
    {
        // Un alumno no puede ver el sílabo antes de la 3ª semana del periodo, ni
        // aunque conozca la URL directa — el link ya se oculta en el dashboard,
        // esto es la misma regla aplicada del lado del servidor.
        if (Auth::user()->hasAnyRole(['alumno', 'alumnoB']) && ! ($silabo->periodoActual?->silabosVisibles() ?? true)) {
            return redirect()->back()->with('error', 'Este sílabo estará disponible a partir del '
                .$silabo->periodoActual->fechaSilabosVisibles()->format('d/m/Y').'.');
        }

        $curso = $silabo->curso;
        // El periodo a mostrar es el del propio sílabo, no el que esté activo "hoy":
        // un sílabo de un periodo ya cerrado debe seguir mostrando su nombre y fechas
        // originales, no las del periodo académico actual.
        $periodoActual = $silabo->periodoActual;
        $docentes = $curso->docentes;
        $competencias = $curso->competenciasSeleccionadas()->with([
            'capacidad',
            'estandares' => function ($query) use ($curso) {
                $query->whereHas('ciclos', function ($q) use ($curso) {
                    $q->where('ciclo_id', $curso->ciclo_id);
                });
            },
        ])->get();

        if ($competencias->isEmpty()) {
            $competencias = $curso->competencias()->with([
                'capacidad',
                'estandares' => function ($query) use ($curso) {
                    $query->whereHas('ciclos', function ($q) use ($curso) {
                        $q->where('ciclo_id', $curso->ciclo_id);
                    });
                },
            ])->get();
        }

        $silabo->load(['enfoques', 'unidades', 'rubricas']);
        if (Auth::user()->hasRole('alumno')) {
            $alumno = Auth::user()->alumno;
            return view('alumnos.vistasAlumnos.silabos', compact('silabo', 'curso', 'docentes', 'competencias', 'alumno', 'periodoActual'));

        } elseif (Auth::user()->hasRole('alumnoB')) {
            $alumno = Auth::user()->alumnoB;
            return view('alumnos.ppd.silabo', compact('silabo', 'curso', 'docentes', 'competencias', 'alumno', 'periodoActual'));

        } elseif (Auth::user()->hasRole('admin')) {
            return view('admin.silabo', compact('silabo', 'curso', 'docentes', 'competencias', 'periodoActual'));

        } else {
            return view('admin.curso.silabos.show', compact('silabo', 'curso', 'docentes', 'competencias', 'periodoActual'));
        }
    }

    public function edit($id, Request $request)
    {
        abort_unless($this->puedeGestionarSilabos(), 403);

        $silabo = Silabo::with(['unidades', 'rubricas', 'enfoques'])->findOrFail($id);
        $curso = $request->filled('curso_id') ? Curso::findOrFail($request->curso_id) : $silabo->curso;
        $docente = $request->filled('docente_id') ? Docente::findOrFail($request->docente_id) : $curso->docentes->first();
        $proyectos = Proyecto::all();
        $enfoques = Enfoques::all();
        $competencias = $curso->competenciasSeleccionadas()->with('capacidad')->get();
        $competencias = $curso->competenciasSeleccionadas()->with([
            'capacidad',
            'estandares' => function ($query) use ($curso) {
                $query->whereHas('ciclos', function ($q) use ($curso) {
                    $q->where('ciclo_id', $curso->ciclo_id);
                });
            },
        ])->get();

        if ($competencias->isEmpty()) {
            $competencias = $curso->competencias()->with([
                'capacidad',
                'estandares' => function ($query) use ($curso) {
                    $query->whereHas('ciclos', function ($q) use ($curso) {
                        $q->where('ciclo_id', $curso->ciclo_id);
                    });
                },
            ])->get();
        }

        return view('admin.curso.silabos.edit', compact('silabo', 'curso', 'docente', 'proyectos', 'enfoques', 'competencias'));
    }

    public function update(Request $request, Silabo $silabo)
    {
        abort_unless($this->puedeGestionarSilabos(), 403);

        $curso = $request->filled('curso_id') ? Curso::findOrFail($request->curso_id) : $silabo->curso;
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

        // Editar el contenido de un sílabo no debe poder cambiar a qué periodo
        // académico pertenece: "periodo" y "periodo_actual_id" quedan fijos desde
        // que se creó (o se reutilizó vía duplicarParaPeriodoActual).
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
        if (! empty($request->enfoques) && is_array($request->enfoques)) {
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

        if (! empty($request->titulo_unidad) && is_array($request->titulo_unidad)) {
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

        if (! empty($request->criterio) && is_array($request->criterio)) {
            // Eliminamos las rúbricas existentes antes de insertar las nuevas
            Rubricas::where('silabo_id', $silabo->id)->delete();

            foreach ($request->criterio as $index => $criterio) {
                // Verificar que el criterio no sea nulo o vacío
                if (! empty($criterio)) {
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

        if (auth()->user()->hasRole('docente')) {
            return redirect()->route('vistaDocente', ['docente' => auth()->user()->docente->id])
                ->with('success', 'Sílabo actualizado correctamente.');
        }

        return redirect()->route('silabos.index')->with('success', 'Sílabo actualizado correctamente.');
    }

    public function destroy(Silabo $silabo)
    {
        abort_unless($this->puedeGestionarSilabos(), 403);

        $silabo->delete();

        // Un docente debe volver a su propio dashboard (con el curso ya listo para
        // elegir de nuevo entre crear/subir/reusar), no a la lista general de admin.
        if (auth()->user()->hasRole('docente')) {
            return redirect()->route('vistaDocente', ['docente' => auth()->user()->docente->id])
                ->with('success', 'Sílabo eliminado correctamente.');
        }

        return redirect()->route('silabos.index')->with('success', 'Sílabo eliminado correctamente.');
    }

    /**
     * Reutiliza un sílabo de un periodo anterior como base para el periodo actual:
     * crea una copia (con sus enfoques, unidades y rúbricas) para el mismo curso
     * — es decir, mismo Programa y Ciclo, ya que el curso no cambia entre periodos.
     */
    public function reuse(Silabo $silabo)
    {
        abort_unless($this->puedeGestionarSilabos(), 403);

        $curso = $silabo->curso;
        $user = auth()->user();

        if ($user->hasRole('docente')) {
            abort_unless($user->docente && $curso->docentes->contains('id', $user->docente->id), 403);
        }

        $periodoActual = PeriodoActual::actual();
        if (! $periodoActual) {
            return redirect()->back()->with('error', 'No hay un periodo activo configurado.');
        }

        $yaExiste = Silabo::where('curso_id', $curso->id)
            ->where('periodo_actual_id', $periodoActual->id)
            ->exists();
        if ($yaExiste) {
            return redirect()->back()->with('error', 'Ya existe un sílabo registrado para este curso en el periodo actual.');
        }

        $nuevo = $silabo->replicate(['periodo', 'periodo_actual_id', 'created_at', 'updated_at']);
        $nuevo->periodo = $periodoActual->nombre;
        $nuevo->periodo_actual_id = $periodoActual->id;
        $nuevo->save();

        foreach ($silabo->enfoques as $enfoque) {
            $nuevo->enfoques()->create($enfoque->only(['nombre', 'descripcion', 'enfoque_observables', 'silabo_concretas']));
        }
        foreach ($silabo->unidades as $unidad) {
            $nuevo->unidades()->create($unidad->only(['titulo', 'situacion', 'duracion', 'desempeno', 'ejes', 'evidencia', 'final']));
        }
        foreach ($silabo->rubricas as $rubrica) {
            $nuevo->rubricas()->create($rubrica->only(['criterio', 'destacado', 'logrado', 'proceso', 'inicio']));
        }

        $mensaje = 'Sílabo reutilizado. Revisa y ajusta lo que necesites antes de darlo por terminado.';

        if ($user->hasRole('docente')) {
            return redirect()->route('silabos.edit', [
                'silabo' => $nuevo->id,
                'curso_id' => $curso->id,
                'docente_id' => $user->docente->id,
            ])->with('success', $mensaje);
        }

        return redirect()->route('silabos.edit', $nuevo->id)->with('success', $mensaje);
    }

    public function exportarPDF(Silabo $silabo)
    {
        $curso = $silabo->curso;
        // Mismo criterio que show(): el PDF debe reflejar el periodo del sílabo,
        // no el periodo activo del día en que se genera el PDF.
        $periodoActual = $silabo->periodoActual;
        $docentes = $curso->docentes;
        $competencias = $curso->competenciasSeleccionadas()->with([
            'capacidad',
            'estandares' => function ($query) use ($curso) {
                $query->whereHas('ciclos', function ($q) use ($curso) {
                    $q->where('ciclo_id', $curso->ciclo_id);
                });
            },
        ])->get();

        if ($competencias->isEmpty()) {
            $competencias = $curso->competencias()->with([
                'capacidad',
                'estandares' => function ($query) use ($curso) {
                    $query->whereHas('ciclos', function ($q) use ($curso) {
                        $q->where('ciclo_id', $curso->ciclo_id);
                    });
                },
            ])->get();
        }
        $silabo->load(['enfoques', 'unidades', 'rubricas']);
        $pdf = Pdf::loadView('admin.curso.silabos.pdf', compact('silabo', 'curso', 'docentes', 'competencias', 'periodoActual'));

        return $pdf->download('silabo_'.Str::slug($silabo->nombre).'.pdf');
    }
}
