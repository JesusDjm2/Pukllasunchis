<?php

namespace App\Http\Controllers;

use App\Exports\PeriodoPpdExport;
use App\Models\Calificacionesppd;
use App\Models\PeriodoActualPpd;
use App\Models\PeriodoPpd;
use App\Models\ppd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PeriodoActualPpdController extends Controller
{
    /**
     * Calificaciones fuente para período PPD (opcionalmente filtradas por curso en config).
     */
    private function calificacionesPpdQueryParaPeriodo()
    {
        $q = Calificacionesppd::with(['ppd', 'curso'])->conDatosSincronizables();
        $cursos = config('ppd_periodo.cursos_sincronizacion_limitados', []);
        if (! empty($cursos)) {
            $q->whereIn('curso_id', $cursos);
        }

        return $q;
    }

    private function decimalONull(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if (is_numeric($valor)) {
            return round((float) $valor, 2);
        }

        return null;
    }

    private function enteroONull(mixed $valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if (is_numeric($valor)) {
            return (int) round((float) $valor);
        }

        return null;
    }

    private function notasNormalizadasParaPeriodoPpd(Calificacionesppd $c): array
    {
        return [
            'calificacion_curso' => $this->decimalONull($c->calificacion_curso),
            'calificacion_sistema' => $this->decimalONull($c->calificacion_sistema),
            'nivel_desempeno' => $this->enteroONull($c->nivel_desempeno),
        ];
    }

    /**
     * El período PPD "actual" sigue en calificación en calificacionesppds; no debe tener snapshot en periodo_ppds.
     */
    private function redirigirSiPeriodoPpdActual(PeriodoActualPpd $periodo): ?\Illuminate\Http\RedirectResponse
    {
        if (! (int) $periodo->actual) {
            return null;
        }

        return redirect()->route('periodoactual.index')
            ->with('warning', 'Este período PPD está marcado como actual (en curso). Las acciones de calificaciones por período (crear, sincronizar o ver registros) están deshabilitadas hasta que deje de ser el período actual.');
    }

    public function create()
    {
        return view('admin.periodos.ppd.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'calendario' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after_or_equal:fecha_inicio',
            'actual' => 'nullable|boolean',
        ]);

        if ($request->actual == 1) {
            PeriodoActualPpd::where('actual', 1)->update(['actual' => 0, 'formulario_habilitado' => 0]);
        }

        $rutaArchivo = null;

        if ($request->hasFile('calendario')) {

            $carpeta = public_path('img/calendarioppd');
            if (! file_exists($carpeta)) {
                mkdir($carpeta, 0777, true);
            }
            $archivo = $request->file('calendario');
            $nombreOriginal = time().'_'.$archivo->getClientOriginalName();
            $archivo->move($carpeta, $nombreOriginal);

            $rutaArchivo = 'img/calendarioppd/'.$nombreOriginal;
        }

        PeriodoActualPpd::create([
            'nombre' => $request->nombre,
            'calendario' => $rutaArchivo,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_cierre' => $request->fecha_cierre,
            'actual' => $request->actual == 1 ? 1 : 0,
        ]);

        return redirect()->route('periodoactual.index')
            ->with('success', 'Período PPD creado correctamente.');
    }

    public function show($id)
    {
        $periodo = PeriodoActualPpd::findOrFail($id);
        if ($redirect = $this->redirigirSiPeriodoPpdActual($periodo)) {
            return $redirect;
        }

        // Registros PeriodoPpd existentes con relaciones
        $registros = PeriodoPpd::where('periodo_actual_ppd_id', $periodo->id)
            ->with(['alumno.user', 'curso.ciclo.programa'])
            ->get();

        // Mapa alumno_id => [curso_id => registro]
        $registrosPorAlumnoCurso = [];
        $alumnosConRegistros     = [];
        foreach ($registros as $reg) {
            if (! $reg->alumno || ! $reg->curso) {
                continue;
            }
            $aid = $reg->alumno->id;
            $alumnosConRegistros[$aid]                     = $reg->alumno;
            $registrosPorAlumnoCurso[$aid][$reg->curso_id] = $reg;
        }

        // Cargar todos los cursos de cada programa (por programa_id del User del alumno)
        // Esto evita mezclar FID y PPD que puedan tener el mismo nombre de programa
        $cursosPorProgramaId = [];
        foreach ($alumnosConRegistros as $aid => $alumno) {
            $programaId = $alumno->user?->programa_id;
            if (! $programaId || isset($cursosPorProgramaId[$programaId])) {
                continue;
            }
            $cursosPorProgramaId[$programaId] = \App\Models\Curso::with(['ciclo.programa'])
                ->whereHas('ciclo', fn ($q) => $q->where('programa_id', $programaId))
                ->get()
                ->keyBy('id');
        }

        // Construir estructura por programa
        $registrosPorPrograma   = [];
        $sumaCalificaciones     = 0;
        $calificacionesConValor = 0;
        $cursosUnicos           = [];
        $totalFilas             = 0;

        foreach ($alumnosConRegistros as $aid => $alumno) {
            $programaId = $alumno->user?->programa_id;

            // Cursos del programa del alumno (todos, con o sin registro)
            $cursosDelPrograma = $programaId && isset($cursosPorProgramaId[$programaId])
                ? $cursosPorProgramaId[$programaId]
                : collect();

            // Si por alguna razón no hay programa cargado, al menos mostrar los que tienen registro
            if ($cursosDelPrograma->isEmpty()) {
                foreach ($registrosPorAlumnoCurso[$aid] ?? [] as $cursoId => $reg) {
                    if ($reg->curso) {
                        $cursosDelPrograma[$cursoId] = $reg->curso;
                    }
                }
            }

            if ($cursosDelPrograma->isEmpty()) {
                continue;
            }

            // Nombre del programa (desde el primer curso)
            $primerCurso    = $cursosDelPrograma->first();
            $programaNombre = $primerCurso?->ciclo?->programa?->nombre ?? 'Sin Programa';

            // Filas: todos los cursos del programa con o sin registro
            $filas = [];
            foreach ($cursosDelPrograma as $cursoId => $curso) {
                $reg                   = $registrosPorAlumnoCurso[$aid][$cursoId] ?? null;
                $filas[]               = ['curso' => $curso, 'registro' => $reg];
                $cursosUnicos[$cursoId] = true;
                $totalFilas++;

                if ($reg && $reg->calificacion_sistema !== null) {
                    $sumaCalificaciones    += $reg->calificacion_sistema;
                    $calificacionesConValor++;
                }
            }

            // Ordenar por nombre de curso
            usort($filas, fn ($a, $b) => strcmp($a['curso']->nombre ?? '', $b['curso']->nombre ?? ''));

            // Buscar o crear entrada del alumno bajo este programa
            $alumnoIndex = null;
            foreach ($registrosPorPrograma[$programaNombre] ?? [] as $idx => $ad) {
                if ($ad['alumno']->id === $aid) {
                    $alumnoIndex = $idx;
                    break;
                }
            }
            if ($alumnoIndex === null) {
                $registrosPorPrograma[$programaNombre][] = ['alumno' => $alumno, 'cursos' => []];
                $alumnoIndex = count($registrosPorPrograma[$programaNombre]) - 1;
            }

            foreach ($filas as $fila) {
                $registrosPorPrograma[$programaNombre][$alumnoIndex]['cursos'][] = $fila;
            }
        }

        $totalRegistros       = $registros->count();
        $totalAlumnos         = count($alumnosConRegistros);
        $totalCursos          = count($cursosUnicos);
        $promedioCalificacion = $calificacionesConValor > 0
            ? $sumaCalificaciones / $calificacionesConValor
            : 0;

        return view('admin.periodos.ppd.show', compact(
            'periodo',
            'registrosPorPrograma',
            'totalRegistros',
            'totalAlumnos',
            'totalCursos',
            'promedioCalificacion'
        ));
    }

    public function export($id)
    {
        $periodo = PeriodoActualPpd::findOrFail($id);
        if ($redirect = $this->redirigirSiPeriodoPpdActual($periodo)) {
            return $redirect;
        }
        $nombreArchivo = 'periodo_ppd_'.Str::slug($periodo->nombre).'_'.date('Y-m-d_His').'.xlsx';

        return Excel::download(new PeriodoPpdExport($id), $nombreArchivo);
    }

    /* public function edit(PeriodoActualPpd $periodos_admision_ppd)
    {
        return view('admin.periodos.ppd.edit', compact('periodos_admision_ppd'));
    } */
    public function edit(PeriodoActualPpd $periodos_de_ppd)
    {
        $periodoActualPpd = $periodos_de_ppd;

        return view('admin.periodos.ppd.edit', compact('periodoActualPpd'));
    }

    public function update(Request $request, $id)
    {
        $periodo = PeriodoActualPpd::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'calendario' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'fecha_inicio' => 'nullable|date',
            'fecha_cierre' => 'nullable|date|after_or_equal:fecha_inicio',
            'actual' => 'nullable|boolean',
        ]);

        // Si se marca actual, desmarcar los demás
        if ($request->actual == 1) {
            PeriodoActualPpd::where('actual', 1)
                ->where('id', '!=', $id)
                ->update(['actual' => 0, 'formulario_habilitado' => 0]);
        }

        // Manejo del archivo
        $rutaArchivo = $periodo->calendario; // conservar si no hay nuevo

        if ($request->hasFile('calendario')) {

            // borrar archivo anterior
            if ($periodo->calendario && file_exists(public_path($periodo->calendario))) {
                unlink(public_path($periodo->calendario));
            }

            $carpeta = public_path('img/calendarioppd');
            if (! file_exists($carpeta)) {
                mkdir($carpeta, 0777, true);
            }

            $archivo = $request->file('calendario');
            $nombreArchivo = time().'_'.$archivo->getClientOriginalName();
            $archivo->move($carpeta, $nombreArchivo);

            $rutaArchivo = 'img/calendarioppd/'.$nombreArchivo;
        }

        // Actualizar datos
        $periodo->update([
            'nombre' => $request->nombre,
            'calendario' => $rutaArchivo,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_cierre' => $request->fecha_cierre,
            'actual' => $request->actual == 1 ? 1 : 0,
        ]);

        return redirect()->route('periodoactual.index')
            ->with('success', 'Período PPD actualizado correctamente.');
    }

    public function updateRegistro(Request $request, PeriodoPpd $registro)
    {
        if (! auth()->check() || ! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'calificacion_curso'  => ['nullable', 'numeric', 'min:0', 'max:20'],
            'calificacion_sistema'=> ['nullable', 'numeric', 'min:0', 'max:20'],
            'nivel_desempeno'     => ['nullable', 'integer', 'min:0', 'max:4'],
        ]);

        $registro->update([
            'calificacion_curso'   => $this->decimalONull($validated['calificacion_curso'] ?? null),
            'calificacion_sistema' => $this->decimalONull($validated['calificacion_sistema'] ?? null),
            'nivel_desempeno'      => $this->enteroONull($validated['nivel_desempeno'] ?? null),
        ]);

        return response()->json([
            'ok'                   => true,
            'calificacion_curso'   => $registro->calificacion_curso,
            'calificacion_sistema' => $registro->calificacion_sistema,
            'nivel_desempeno'      => $registro->nivel_desempeno,
        ]);
    }

    public function storeRegistro(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'periodo_actual_ppd_id' => ['required', 'integer', 'exists:periodo_actual_ppds,id'],
            'alumno_id'             => ['required', 'integer'],
            'curso_id'              => ['required', 'integer', 'exists:cursos,id'],
            'calificacion_curso'    => ['nullable', 'numeric', 'min:0', 'max:20'],
            'calificacion_sistema'  => ['nullable', 'numeric', 'min:0', 'max:20'],
            'nivel_desempeno'       => ['nullable', 'integer', 'min:0', 'max:4'],
        ]);

        $registro = PeriodoPpd::create([
            'periodo_actual_ppd_id' => $validated['periodo_actual_ppd_id'],
            'alumno_id'             => $validated['alumno_id'],
            'curso_id'              => $validated['curso_id'],
            'calificacion_curso'    => $this->decimalONull($validated['calificacion_curso'] ?? null),
            'calificacion_sistema'  => $this->decimalONull($validated['calificacion_sistema'] ?? null),
            'nivel_desempeno'       => $this->enteroONull($validated['nivel_desempeno'] ?? null),
        ]);

        return response()->json([
            'ok'                    => true,
            'id'                    => $registro->id,
            'calificacion_curso'    => $registro->calificacion_curso,
            'calificacion_sistema'  => $registro->calificacion_sistema,
            'nivel_desempeno'       => $registro->nivel_desempeno,
        ]);
    }

    public function crearCalificaciones($id)
    {
        $periodo = PeriodoActualPpd::findOrFail($id);
        if ($redirect = $this->redirigirSiPeriodoPpdActual($periodo)) {
            return $redirect;
        }
        if ($periodo->periodosPpd()->exists()) {
            return redirect()->route('periodoactual.index')
                ->with('warning', 'Ya se han creado calificaciones para este período.');
        }

        $calificacionesPpd = $this->calificacionesPpdQueryParaPeriodo()->get();

        $contador = 0;
        $errores = [];
        $alumnosActualizados = []; // Para llevar control de alumnos ya actualizados

        foreach ($calificacionesPpd as $calificacion) {
            if (! $calificacion->ppd) {
                $errores[] = "Calificación ID {$calificacion->id} no tiene alumno PPD asociado";

                continue;
            }

            $existe = PeriodoPpd::where('periodo_actual_ppd_id', $periodo->id)
                ->where('alumno_id', $calificacion->ppd_id)
                ->where('curso_id', $calificacion->curso_id)
                ->exists();

            if ($existe) {
                continue;
            }

            $notas = $this->notasNormalizadasParaPeriodoPpd($calificacion);

            try {
                PeriodoPpd::create([
                    'periodo_actual_ppd_id' => $periodo->id,
                    'alumno_id' => $calificacion->ppd_id,
                    'curso_id' => $calificacion->curso_id,
                    'calificacion_curso' => $notas['calificacion_curso'],
                    'calificacion_sistema' => $notas['calificacion_sistema'],
                    'nivel_desempeno' => $notas['nivel_desempeno'],
                ]);

                $contador++;

                // Marcar al alumno como guardado si no lo hemos marcado ya en esta ejecución
                if (! in_array($calificacion->ppd_id, $alumnosActualizados)) {
                    $calificacion->ppd->update(['guardado' => true]);
                    $alumnosActualizados[] = $calificacion->ppd_id;
                }

            } catch (\Exception $e) {
                $errores[] = "Error al crear registro para alumno ID {$calificacion->ppd_id}, curso ID {$calificacion->curso_id}: ".$e->getMessage();
                Log::error('Error crear calificación PPD: '.$e->getMessage());
            }
        }

        // Mensaje de resultado
        $mensaje = "Se crearon {$contador} registros de calificaciones PPD para el período {$periodo->nombre}";
        $mensaje .= ' y se marcaron '.count($alumnosActualizados).' alumnos como guardados.';
        $cursosFiltro = config('ppd_periodo.cursos_sincronizacion_limitados', []);
        if (! empty($cursosFiltro)) {
            $mensaje .= ' (solo cursos ID: '.implode(', ', $cursosFiltro).').';
        }

        if (! empty($errores)) {
            $mensaje .= '. Se encontraron '.count($errores).' errores.';
            Log::error('Errores al crear calificaciones PPD:', $errores);
        }

        if ($contador > 0) {
            return redirect()->route('periodoactual.index')
                ->with('success', $mensaje);
        } else {
            return redirect()->route('periodoactual.index')
                ->with('warning', 'No se encontraron calificaciones PPD para crear. Verifica que haya calificaciones guardadas en el sistema.');
        }
    }

    public function sincronizarCalificaciones($id)
    {
        $periodo = PeriodoActualPpd::findOrFail($id);
        if ($redirect = $this->redirigirSiPeriodoPpdActual($periodo)) {
            return $redirect;
        }

        $calificacionesPpd = $this->calificacionesPpdQueryParaPeriodo()->get();

        $dataPorClave = [];
        $alumnosIds = [];

        foreach ($calificacionesPpd as $calificacion) {
            if ($calificacion->ppd) {
                $notas = $this->notasNormalizadasParaPeriodoPpd($calificacion);
                $clave = $periodo->id.'-'.$calificacion->ppd_id.'-'.$calificacion->curso_id;

                $dataPorClave[$clave] = [
                    'periodo_actual_ppd_id' => $periodo->id,
                    'alumno_id' => $calificacion->ppd_id,
                    'curso_id' => $calificacion->curso_id,
                    'calificacion_curso' => $notas['calificacion_curso'],
                    'calificacion_sistema' => $notas['calificacion_sistema'],
                    'nivel_desempeno' => $notas['nivel_desempeno'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $alumnosIds[] = $calificacion->ppd_id;
            }
        }

        $data = array_values($dataPorClave);

        $contadorNuevos = 0;
        $contadorActualizados = 0;
        foreach ($data as $fila) {
            $yaExistia = PeriodoPpd::where('periodo_actual_ppd_id', $fila['periodo_actual_ppd_id'])
                ->where('alumno_id', $fila['alumno_id'])
                ->where('curso_id', $fila['curso_id'])
                ->exists();
            if ($yaExistia) {
                $contadorActualizados++;
            } else {
                $contadorNuevos++;
            }
        }

        if (empty($data)) {
            $cursosFiltro = config('ppd_periodo.cursos_sincronizacion_limitados', []);
            $extra = ! empty($cursosFiltro)
                ? ' No hay filas en calificacionesppds para los cursos filtrados (IDs: '.implode(', ', $cursosFiltro).').'
                : '';

            return redirect()->route('periodoactual.index')
                ->with('warning', 'No hay calificaciones PPD para sincronizar.'.$extra);
        }

        // Usar transacción para asegurar consistencia
        DB::transaction(function () use ($data, $alumnosIds) {
            PeriodoPpd::upsert(
                $data,
                ['periodo_actual_ppd_id', 'alumno_id', 'curso_id'],
                ['calificacion_curso', 'calificacion_sistema', 'nivel_desempeno', 'updated_at']
            );

            $idsPpd = array_values(array_unique($alumnosIds));
            if (! empty($idsPpd)) {
                ppd::whereIn('id', $idsPpd)->update(['guardado' => true]);
            }
        });

        $total = count($data);
        $alumnosUnicos = count(array_unique($alumnosIds));
        $alumnosMarcados = ppd::whereIn('id', array_unique($alumnosIds))
            ->where('guardado', true)
            ->count();

        $mensaje = "Sincronización completada: 
        {$total} registros procesados 
        ({$contadorNuevos} nuevos, {$contadorActualizados} actualizados) 
        para {$alumnosUnicos} alumnos en el período {$periodo->nombre}.
        {$alumnosMarcados} alumnos marcados como guardados.";
        $cursosFiltro = config('ppd_periodo.cursos_sincronizacion_limitados', []);
        if (! empty($cursosFiltro)) {
            $mensaje .= ' Solo cursos ID: '.implode(', ', $cursosFiltro).'.';
        }

        return redirect()->route('periodoactual.index')
            ->with('success', $mensaje);
    }

    /* public function sincronizarCalificaciones($id)
    {
        $periodo = PeriodoActualPpd::findOrFail($id);

        $calificacionesPpd = Calificacionesppd::with(['ppd', 'curso'])
            ->whereNotNull('calificacion_curso')
            ->get();

        $data = [];
        $alumnosIds = [];

        foreach ($calificacionesPpd as $calificacion) {
            if ($calificacion->ppd) {
                $data[] = [
                    'periodo_actual_ppd_id' => $periodo->id,
                    'alumno_id' => $calificacion->ppd_id,
                    'curso_id' => $calificacion->curso_id,
                    'calificacion_curso' => $calificacion->calificacion_curso,
                    'calificacion_sistema' => $calificacion->calificacion_sistema,
                    'nivel_desempeno' => $calificacion->nivel_desempeno,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $alumnosIds[] = $calificacion->ppd_id;
            }
        }

        if (empty($data)) {
            return redirect()->route('periodoactual.index')
                ->with('warning', 'No hay calificaciones PPD para sincronizar.');
        }

        // Usar transacción para asegurar consistencia
        DB::transaction(function () use ($data, $alumnosIds) {
            // Sincronizar calificaciones
            PeriodoPpd::upsert(
                $data,
                ['periodo_actual_ppd_id', 'alumno_id', 'curso_id'],
                ['calificacion_curso', 'calificacion_sistema', 'nivel_desempeno', 'updated_at']
            );

            // Marcar alumnos como guardados (solo los que tienen registros)
            if (! empty($alumnosIds)) {
                $alumnosUnicos = array_unique($alumnosIds);
                ppd::whereIn('id', $alumnosUnicos)
                    ->update(['guardado' => true]);
            }
        });

        $total = count($data);
        $alumnosMarcados = count(array_unique($alumnosIds));

        return redirect()->route('periodoactual.index')
            ->with('success', "Sincronización completada: {$total} registros procesados para el período {$periodo->nombre} y {$alumnosMarcados} alumnos marcados como guardados.");
    } */
    public function toggleFormulario(PeriodoActualPpd $periodos_de_ppd)
    {
        $nuevoEstado = ! $periodos_de_ppd->formulario_habilitado;

        if ($nuevoEstado && ! $periodos_de_ppd->actual) {
            return redirect()->route('periodoactual.index')
                ->with('error', "Solo el periodo actual puede tener el formulario habilitado. \"{$periodos_de_ppd->nombre}\" no es el periodo actual.");
        }

        if ($nuevoEstado) {
            PeriodoActualPpd::where('id', '!=', $periodos_de_ppd->id)
                ->where('formulario_habilitado', true)
                ->update(['formulario_habilitado' => false]);
        }

        $periodos_de_ppd->update(['formulario_habilitado' => $nuevoEstado]);

        $estado = $periodos_de_ppd->formulario_habilitado ? 'habilitado' : 'deshabilitado';

        return redirect()->route('periodoactual.index')
            ->with('success', "Formulario de matrícula PPD {$estado} para el período: {$periodos_de_ppd->nombre}.");
    }

    public function destroy(PeriodoActualPpd $periodos_de_ppd)
    {
        $periodos_de_ppd->delete();

        return redirect()
            ->route('periodoactual.index') // Cambiado a la ruta correcta
            ->with('success', 'Periodo eliminado correctamente.');
    }
}
