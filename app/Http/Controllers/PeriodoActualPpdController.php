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
            PeriodoActualPpd::where('actual', 1)->update(['actual' => 0]);
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

        // Obtener los registros existentes (con calificaciones)
        $registros = PeriodoPpd::where('periodo_actual_ppd_id', $periodo->id)
            ->with(['alumno', 'curso.ciclo.programa'])
            ->get();

        // Obtener TODOS los cursos disponibles
        $todosLosCursos = \App\Models\Curso::with(['ciclo.programa'])->get();

        // Agrupar cursos por programa
        $cursosPorPrograma = [];
        foreach ($todosLosCursos as $curso) {
            $programaNombre = 'Sin Programa';
            if ($curso->ciclo && $curso->ciclo->programa) {
                $programaNombre = $curso->ciclo->programa->nombre;
            }

            if (! isset($cursosPorPrograma[$programaNombre])) {
                $cursosPorPrograma[$programaNombre] = [];
            }
            $cursosPorPrograma[$programaNombre][] = $curso;
        }

        // Estructurar datos por programa y alumno
        $registrosPorPrograma = [];
        $totalRegistros = 0;
        $totalAlumnos = 0;
        $totalCursos = 0;
        $sumaCalificaciones = 0;
        $calificacionesConValor = 0;

        // Obtener todos los alumnos que aparecen en los registros existentes
        $alumnosConRegistros = [];
        foreach ($registros as $registro) {
            if (! $registro->alumno || ! $registro->curso) {
                continue;
            }

            $alumnoId = $registro->alumno->id;
            if (! isset($alumnosConRegistros[$alumnoId])) {
                $alumnosConRegistros[$alumnoId] = $registro->alumno;
            }
        }

        // Determinar a qué programas pertenece cada alumno (basado en sus cursos calificados)
        $programasPorAlumno = [];
        foreach ($registros as $registro) {
            if (! $registro->alumno || ! $registro->curso) {
                continue;
            }

            $alumnoId = $registro->alumno->id;
            $programaNombre = 'Sin Programa';
            if ($registro->curso->ciclo && $registro->curso->ciclo->programa) {
                $programaNombre = $registro->curso->ciclo->programa->nombre;
            }

            if (! isset($programasPorAlumno[$alumnoId])) {
                $programasPorAlumno[$alumnoId] = [];
            }

            if (! in_array($programaNombre, $programasPorAlumno[$alumnoId])) {
                $programasPorAlumno[$alumnoId][] = $programaNombre;
            }
        }

        // Para cada alumno y cada programa al que pertenece, obtener todos los cursos de ese programa
        foreach ($alumnosConRegistros as $alumnoId => $alumno) {
            if (! isset($programasPorAlumno[$alumnoId])) {
                continue;
            }

            foreach ($programasPorAlumno[$alumnoId] as $programaNombre) {
                if (! isset($registrosPorPrograma[$programaNombre])) {
                    $registrosPorPrograma[$programaNombre] = [];
                }

                // Verificar si el alumno ya está en este programa
                $alumnoIndex = null;
                foreach ($registrosPorPrograma[$programaNombre] as $index => $alumnoData) {
                    if ($alumnoData['alumno']->id === $alumnoId) {
                        $alumnoIndex = $index;
                        break;
                    }
                }

                // Si el alumno no existe en este programa, crearlo
                if ($alumnoIndex === null) {
                    $registrosPorPrograma[$programaNombre][] = [
                        'alumno' => $alumno,
                        'cursos' => [],
                    ];
                    $alumnoIndex = count($registrosPorPrograma[$programaNombre]) - 1;
                }

                // Obtener todos los cursos de este programa
                $cursosDelPrograma = isset($cursosPorPrograma[$programaNombre])
                    ? $cursosPorPrograma[$programaNombre]
                    : [];

                // Para cada curso del programa, buscar si existe registro de calificación
                foreach ($cursosDelPrograma as $curso) {
                    // Buscar si existe registro para este alumno y curso
                    $registroExistente = $registros->first(function ($reg) use ($alumnoId, $curso) {
                        return $reg->alumno_id == $alumnoId && $reg->curso_id == $curso->id;
                    });

                    // Verificar si este curso ya fue agregado para este alumno
                    $cursoYaAgregado = false;
                    foreach ($registrosPorPrograma[$programaNombre][$alumnoIndex]['cursos'] as $cursoExistente) {
                        if ($cursoExistente['curso']->id == $curso->id) {
                            $cursoYaAgregado = true;
                            break;
                        }
                    }

                    if (! $cursoYaAgregado) {
                        $registrosPorPrograma[$programaNombre][$alumnoIndex]['cursos'][] = [
                            'curso' => $curso,
                            'registro' => $registroExistente, // Puede ser null si no tiene calificación
                        ];

                        $totalRegistros++;

                        // Estadísticas solo para calificaciones existentes
                        if ($registroExistente && $registroExistente->calificacion_sistema) {
                            $sumaCalificaciones += $registroExistente->calificacion_sistema;
                            $calificacionesConValor++;
                        }
                    }
                }
            }
        }

        // Ordenar los cursos dentro de cada alumno (opcional, puedes ordenar por nombre de curso)
        foreach ($registrosPorPrograma as $programaNombre => &$alumnos) {
            foreach ($alumnos as &$alumnoData) {
                usort($alumnoData['cursos'], function ($a, $b) {
                    return strcmp($a['curso']->nombre, $b['curso']->nombre);
                });
            }
        }

        // Contar alumnos únicos
        $totalAlumnos = 0;
        foreach ($registrosPorPrograma as $programaNombre => $alumnos) {
            $totalAlumnos += count($alumnos);
        }

        // Contar cursos únicos
        $totalCursos = $todosLosCursos->count();

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
                ->update(['actual' => 0]);
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
    public function destroy(PeriodoActualPpd $periodos_de_ppd)
    {
        $periodos_de_ppd->delete();

        return redirect()
            ->route('periodoactual.index') // Cambiado a la ruta correcta
            ->with('success', 'Periodo eliminado correctamente.');
    }
}
