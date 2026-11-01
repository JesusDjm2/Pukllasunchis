<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\AsistenciaDetalle;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Services\AsistenciaService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DocenteAsistenciaController extends Controller
{
    /**
     * Pantalla para tomar o editar la lista de asistencia de una fecha en un curso.
     */
    public function tomar(Request $request, Docente $docente, Curso $curso)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || (int) $user->docente?->id === (int) $docente->id, 403);
        abort_unless($docente->cursos()->whereKey($curso->id)->exists() || $user->hasAnyRole(['admin', 'super-admin']), 403, 'Este curso no está asignado a tu cuenta.');

        $curso->loadMissing('ciclo.programa');
        $periodoActual = PeriodoActual::actual();

        // Fecha seleccionada (por defecto hoy)
        $fecha = $request->query('fecha', Carbon::today()->toDateString());
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = Carbon::today()->toDateString();
        }

        // Alumnos unificados del curso con sus banderas de Licencia y Beca (Amantani / Pronabec)
        $alumnos = AsistenciaService::alumnosDelCurso($curso, $periodoActual);

        // Buscar si ya existe una lista tomada para este curso en esta fecha
        $asistenciaExistente = Asistencia::with('detalles')
            ->where('curso_id', $curso->id)
            ->where('fecha', $fecha)
            ->first();

        // Mapear detalles previos por user_id para rellenar el formulario
        $detallesPrevios = $asistenciaExistente ? $asistenciaExistente->detalles->keyBy('user_id') : collect();

        // Contadores del curso para contexto
        $totalSesiones = Asistencia::where('curso_id', $curso->id)->count();

        return view('docentes.asistencia.tomar', compact(
            'docente',
            'curso',
            'periodoActual',
            'fecha',
            'alumnos',
            'asistenciaExistente',
            'detallesPrevios',
            'totalSesiones'
        ));
    }

    /**
     * Guarda o actualiza el registro de asistencia del curso.
     */
    public function guardar(Request $request, Docente $docente, Curso $curso)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || (int) $user->docente?->id === (int) $docente->id, 403);
        abort_unless($docente->cursos()->whereKey($curso->id)->exists() || $user->hasAnyRole(['admin', 'super-admin']), 403);

        $request->validate([
            'fecha' => 'required|date',
            'tema' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
            'alumnos' => 'required|array',
            'alumnos.*.estado' => 'required|in:presente,tardanza,falta,justificado,licencia',
            'alumnos.*.observacion' => 'nullable|string|max:255',
        ], [
            'alumnos.required' => 'No se enviaron datos de asistencia de los estudiantes.',
            'fecha.required' => 'La fecha de la clase es obligatoria.',
        ]);

        $asistencia = AsistenciaService::guardarAsistencia($curso, $docente, $request->all(), $user->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Asistencia guardada correctamente para el ' . Carbon::parse($asistencia->fecha)->isoFormat('dddd D [de] MMMM') . '!',
                'asistencia_id' => $asistencia->id,
                'presentes' => $asistencia->presentes_count,
                'faltas' => $asistencia->faltas_count,
                'tardanzas' => $asistencia->tardanzas_count,
            ]);
        }

        return redirect()->route('docente.asistencia.tomar', [
            'docente' => $docente->id,
            'curso' => $curso->id,
            'fecha' => $asistencia->fecha->format('Y-m-d'),
        ])->with('success', '¡Asistencia registrada exitosamente para la fecha ' . Carbon::parse($asistencia->fecha)->format('d/m/Y') . '!');
    }

    /**
     * Historial de sesiones de asistencia del curso.
     */
    public function historial(Request $request, Docente $docente, Curso $curso)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || (int) $user->docente?->id === (int) $docente->id, 403);
        abort_unless($docente->cursos()->whereKey($curso->id)->exists() || $user->hasAnyRole(['admin', 'super-admin']), 403);

        $curso->loadMissing('ciclo.programa');
        $periodoActual = PeriodoActual::actual();

        $sesiones = Asistencia::with(['detalles.user', 'detalles.alumno'])
            ->where('curso_id', $curso->id)
            ->orderByDesc('fecha')
            ->paginate(20);

        // Estadísticas acumuladas
        $todas = Asistencia::with('detalles')->where('curso_id', $curso->id)->get();
        $totalSesiones = $todas->count();
        $totalPresentes = $todas->sum(fn ($s) => $s->presentes_count);
        $totalFaltas = $todas->sum(fn ($s) => $s->faltas_count);
        $totalTardanzas = $todas->sum(fn ($s) => $s->tardanzas_count);
        $totalLicencias = $todas->sum(fn ($s) => $s->licencias_count);
        $totalRegistros = $todas->sum(fn ($s) => $s->detalles->count());

        $porcentajeGlobal = $totalRegistros > 0
            ? round((($totalPresentes + $totalTardanzas) / $totalRegistros) * 100, 1)
            : 0;

        return view('docentes.asistencia.historial', compact(
            'docente',
            'curso',
            'periodoActual',
            'sesiones',
            'totalSesiones',
            'totalPresentes',
            'totalFaltas',
            'totalTardanzas',
            'totalLicencias',
            'porcentajeGlobal'
        ));
    }

    /**
     * Elimina una sesión de asistencia registrada.
     */
    public function eliminar(Request $request, Docente $docente, Curso $curso, Asistencia $asistencia)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || (int) $user->docente?->id === (int) $docente->id, 403);
        abort_unless($asistencia->curso_id === $curso->id, 404);

        $fechaTexto = Carbon::parse($asistencia->fecha)->format('d/m/Y');
        $asistencia->delete();

        return redirect()->route('docente.asistencia.historial', ['docente' => $docente->id, 'curso' => $curso->id])
            ->with('success', 'Sesión de asistencia del ' . $fechaTexto . ' eliminada correctamente.');
    }
}
