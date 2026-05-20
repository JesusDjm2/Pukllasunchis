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
        $periodoActual = PeriodoActual::where('actual', true)->first();

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

        // Si no hay asignaciones en este período, los cursos del ciclo se muestran todos seleccionados por defecto
        $cicloIdsDefecto = $asignadosIds === [] ? $cursosCiclo->pluck('id')->toArray() : [];

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
        $periodoActual = PeriodoActual::where('actual', true)->first();

        $cursoIds = $request->input('cursos', []);

        $validos = Curso::whereIn('id', $cursoIds)
            ->whereHas('ciclo', fn ($q) => $q->where('programa_id', $alumno->programa_id))
            ->pluck('id')
            ->toArray();

        // Eliminar asignaciones del período actual para este alumno
        DB::table('alumno_cursos')
            ->where('alumno_id', $alumno->id)
            ->where('periodo_actual_id', $periodoActual?->id)
            ->delete();

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
