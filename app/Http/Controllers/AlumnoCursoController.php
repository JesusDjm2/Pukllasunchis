<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\PeriodoActual;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoCursoController extends Controller
{
    public function asignar($id)
    {
        $user = User::with(['alumno.ciclo.cursos', 'alumno.programa'])->findOrFail($id);
        $alumno = $user->alumno;
        $periodoActual = PeriodoActual::actual();

        $cursosCiclo = Curso::where('ciclo_id', $alumno->ciclo_id)
            ->whereHas('ciclo', fn ($q) => $q->where('programa_id', $alumno->programa_id))
            ->orderBy('nombre')
            ->get();

        $ciclosExtras = Ciclo::where('programa_id', $alumno->programa_id)
            ->where('id', '!=', $alumno->ciclo_id)
            ->with(['cursos' => fn ($q) => $q->orderBy('nombre')])
            ->orderBy('id')
            ->get();

        $asignadosIds = $periodoActual
            ? $alumno->cursosDelPeriodo($periodoActual->id)->pluck('cursos.id')->toArray()
            : [];

        // Si no hay asignaciones en este período, los cursos del ciclo se muestran todos seleccionados por
        // defecto (conveniencia para alumnos nuevos). Un inhabilitado NUNCA recibe este default: si un admin
        // lo vació a propósito, debe quedarse vacío en vez de "revivir" con todos los cursos del ciclo marcados.
        $cicloIdsDefecto = ($asignadosIds === [] && ! $user->hasRole('inhabilitado'))
            ? $cursosCiclo->pluck('id')->toArray()
            : [];

        return view('admin.curso.asignarcursos.asignacion', compact(
            'alumno',
            'periodoActual',
            'cursosCiclo',
            'ciclosExtras',
            'asignadosIds',
            'cicloIdsDefecto'
        ));
    }

    public function guardarCursos(Request $request, $alumnoId)
    {
        $alumno = Alumno::with('user')->findOrFail($alumnoId);
        $periodoActual = PeriodoActual::actual();

        $cursoIds = $request->input('cursos', []);

        $validos = Curso::whereIn('id', $cursoIds)
            ->whereHas('ciclo', fn ($q) => $q->where('programa_id', $alumno->programa_id))
            ->pluck('id')
            ->toArray();

        // Eliminar asignaciones del período actual para este alumno.
        $borrado = DB::table('alumno_cursos')->where('alumno_id', $alumno->id);

        // Para inhabilitados, también se limpian filas heredadas sin período (datos antiguos de antes de que
        // "alumno_cursos" tuviera periodo_actual_id), que de otro modo nunca se borran y lo mantienen visible
        // indefinidamente en las pantallas de calificar. NO se hace esto para alumnos activos: hay ~300
        // asignaciones legítimas de "cursos adicionales" que aún dependen de esas filas sin período.
        if ($alumno->user && $alumno->user->hasRole('inhabilitado')) {
            $borrado->where(function ($q) use ($periodoActual) {
                $q->where('periodo_actual_id', $periodoActual?->id)
                    ->orWhereNull('periodo_actual_id');
            });
        } else {
            $borrado->where('periodo_actual_id', $periodoActual?->id);
        }

        $borrado->delete();

        // Insertar las nuevas asignaciones con el período
        $now = now();
        foreach ($validos as $cursoId) {
            DB::table('alumno_cursos')->insert([
                'alumno_id'        => $alumno->id,
                'curso_id'         => $cursoId,
                'periodo_actual_id' => $periodoActual?->id,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        $periodoNombre = $periodoActual?->nombre ?? 'sin período';

        return redirect()
            ->route('admin')
            ->with('success', "Cursos de {$alumno->user->name} actualizados para el período {$periodoNombre}.");
    }
}
