<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rendimiento de cada curso en el periodo vigente (listado de docentes del admin): cuántos
 * alumnos tienen nota, promedio, desaprobados y si hay una nota sospechosa de estar mal cargada.
 *
 * Una consulta por tabla para TODOS los cursos (no una por curso). Notas en vivo:
 *  - FID: periodouno / periodo_dos / periodo_tres; por alumno vale la más avanzada (como SeguimientoCiclo).
 *  - PPD: calificacionesppds (nota final del curso).
 * Mismas reglas que HistorialAcademico: desaprobado < 11; un 0 o vacío es SIN calificar.
 */
class RendimientoCursos
{
    /**
     * Notas de 1 a 5 en al menos la mitad del curso (y no menos de 5): suele ser la valoración 1–4
     * cargada como si fuera la nota vigesimal, o un valor por defecto. Se marca para revisar.
     */
    private const SOSPECHA_NOTA_MAX = 5;
    private const SOSPECHA_PROPORCION = 0.5;
    private const SOSPECHA_MINIMO = 5;

    /** @return Collection<int, object{con_nota:int, promedio:?float, desaprobados:int, porcentaje:int, sospechosa:?array{0:int,1:int}}> por curso_id */
    public static function de(Collection $cursoIds): Collection
    {
        $ids = $cursoIds->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $notas = []; // [curso_id][clave_alumno] = nota vigente
        $nivel = [];
        foreach ([1 => 'periodouno', 2 => 'periodo_dos', 3 => 'periodo_tres'] as $n => $tabla) {
            foreach (DB::table($tabla)->whereIn('curso_id', $ids)->get(['alumno_id', 'curso_id', 'calificacion_sistema']) as $f) {
                $nota = self::numero($f->calificacion_sistema);
                $clave = 'f'.$f->alumno_id;
                if ($nota !== null && $n >= ($nivel[$f->curso_id][$clave] ?? 0)) {
                    $notas[$f->curso_id][$clave] = $nota;
                    $nivel[$f->curso_id][$clave] = $n;
                }
            }
        }
        foreach (DB::table('calificacionesppds')->whereIn('curso_id', $ids)->get(['ppd_id', 'curso_id', 'calificacion_sistema']) as $f) {
            $nota = self::numero($f->calificacion_sistema);
            if ($nota !== null) {
                $notas[$f->curso_id]['p'.$f->ppd_id] = $nota;
            }
        }

        return collect($notas)->map(function ($porAlumno) {
            $valores = collect($porAlumno)->values();
            $desaprobados = $valores->filter(fn ($v) => $v < HistorialAcademico::NOTA_MINIMA)->count();
            $bajas = $valores->filter(fn ($v) => $v <= self::SOSPECHA_NOTA_MAX)->count();

            return (object) [
                'con_nota' => $valores->count(),
                'promedio' => round($valores->avg(), 1),
                'desaprobados' => $desaprobados,
                'porcentaje' => (int) round($desaprobados * 100 / $valores->count()),
                // [notas de 1 a 5, total con nota]
                'sospechosa' => $bajas >= self::SOSPECHA_MINIMO && $bajas >= $valores->count() * self::SOSPECHA_PROPORCION
                    ? [$bajas, $valores->count()] : null,
            ];
        });
    }

    private static function numero($valor): ?float
    {
        return is_numeric($valor) && (float) $valor > 0 ? (float) $valor : null;
    }
}
