<?php

namespace App\Exports;

use App\Models\Curso;
use App\Models\PeriodoActualPpd;
use App\Models\PeriodoPpd;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PeriodoPpdExport implements FromView
{
    protected $periodoId;

    public function __construct($periodoId)
    {
        $this->periodoId = $periodoId;
    }

    public function view(): View
    {
        $periodo = PeriodoActualPpd::findOrFail($this->periodoId);

        // Obtener los registros existentes (con calificaciones)
        $registros = PeriodoPpd::where('periodo_actual_ppd_id', $periodo->id)
            ->with(['alumno', 'curso.ciclo.programa'])
            ->get();

        // Obtener TODOS los cursos disponibles (para poder mostrar los que no tienen calificación)
        $todosLosCursos = Curso::with(['ciclo.programa'])->get();

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
        $sumaCalificaciones = 0;
        $calificacionesConValor = 0;

        // Primero, obtener todos los alumnos que aparecen en los registros existentes
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

        // Para cada alumno, obtener todos los cursos de los programas a los que pertenece
        // Primero, necesitamos saber qué programas tiene cada alumno basado en sus cursos calificados
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

        $totalAlumnos = 0;
        foreach ($registrosPorPrograma as $programaNombre => $alumnos) {
            $totalAlumnos += count($alumnos);
        }

        $totalCursos = $todosLosCursos->count();

        $promedioCalificacion = $calificacionesConValor > 0
            ? $sumaCalificaciones / $calificacionesConValor
            : 0;

        return view('alumnos.ppd.export', compact(
            'periodo',
            'registrosPorPrograma',
            'totalRegistros',
            'totalAlumnos',
            'totalCursos',
            'promedioCalificacion'
        ));
    }
}
