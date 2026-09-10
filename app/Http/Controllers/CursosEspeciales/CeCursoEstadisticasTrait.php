<?php

namespace App\Http\Controllers\CursosEspeciales;

use App\Models\CursosEspeciales\CeProgresoAlumno;
use App\Models\CursosEspeciales\CursoEspecial;

trait CeCursoEstadisticasTrait
{
    protected function calcularPromedioAvance(CursoEspecial $curso): int
    {
        $curso->loadMissing('niveles.unidades.lecciones', 'niveles.unidades.ejercicios', 'inscripciones');

        $totalItems = $this->totalContenidos($curso);
        if ($totalItems === 0) {
            return 0;
        }
        $userIds = $curso->inscripciones->pluck('user_id')->unique()->filter();
        if ($userIds->isEmpty()) {
            return 0;
        }
        $completed = CeProgresoAlumno::whereIn('user_id', $userIds)
            ->where('completado', true)
            ->count();

        return (int) round($completed / ($userIds->count() * $totalItems) * 100);
    }

    protected function generarEstadisticasPorEstudiante(CursoEspecial $curso): array
    {
        $curso->loadMissing(
            'niveles.unidades.lecciones',
            'niveles.unidades.ejercicios',
            'inscripciones.user.programa',
            'inscripciones.user.ciclo'
        );

        $totalItems = $this->totalContenidos($curso);
        $userIds = $curso->inscripciones->pluck('user_id')->unique()->filter();

        $progresos = CeProgresoAlumno::whereIn('user_id', $userIds)
            ->where('completado', true)
            ->get()
            ->groupBy('user_id');

        $alumnos = $curso->inscripciones
            ->filter(fn ($inscripcion) => $inscripcion->user !== null)
            ->map(function ($inscripcion) use ($progresos, $totalItems) {
                $user = $inscripcion->user;
                $completedCount = $progresos->get($user->id)?->count() ?? 0;

                return [
                    'user'        => $user,
                    'programa'    => $user->programa?->nombre ?? 'Sin programa',
                    'ciclo'       => $user->ciclo?->nombre ?? 'Sin ciclo',
                    'porcentaje'  => $totalItems > 0 ? (int) round($completedCount / $totalItems * 100) : 0,
                    'inscrito_at' => $inscripcion->inscrito_at,
                ];
            });

        $alumnosPorGrupo = $alumnos
            ->groupBy(fn ($item) => $item['programa'] . ' / ' . $item['ciclo'])
            ->sortKeys();

        $promedio = $alumnos->count() ? (int) round($alumnos->avg('porcentaje')) : 0;

        return [
            'totalItems' => $totalItems,
            'alumnos' => $alumnos,
            'alumnosPorGrupo' => $alumnosPorGrupo,
            'promedioAvance' => $promedio,
        ];
    }

    protected function totalContenidos(CursoEspecial $curso): int
    {
        return $curso->niveles->sum(function ($nivel) {
            return $nivel->unidades->sum(fn ($unidad) => $unidad->lecciones->count() + $unidad->ejercicios->count());
        });
    }
}
