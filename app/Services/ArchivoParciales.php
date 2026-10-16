<?php

namespace App\Services;

use App\Models\PeriodoActual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Copia Parcial 1 (periodouno) y Parcial 2 (periodo_dos) a periodo_parciales, ligados a un
 * periodo. Se llama al "Crear calificaciones" del periodo y JUSTO ANTES de vaciar esas tablas
 * desde la zona de riesgo de /docente (antes eso borraba los parciales para siempre).
 * Es idempotente: re-archivar el mismo periodo actualiza, no duplica.
 */
class ArchivoParciales
{
    public static function archivar(?PeriodoActual $periodo, ?array $soloParciales = null): int
    {
        if (! $periodo) {
            return 0;
        }

        $total = 0;
        foreach ([1 => 'periodouno', 2 => 'periodo_dos'] as $parcial => $tabla) {
            if ($soloParciales && ! in_array($parcial, $soloParciales, true)) {
                continue;
            }
            try {
                $total += DB::affectingStatement("
                    INSERT INTO periodo_parciales (periodo_actual_id, alumno_id, curso_id, parcial, valoracion_curso, calificacion_curso, calificacion_sistema, created_at, updated_at)
                    SELECT ?, t.alumno_id, t.curso_id, ?, t.valoracion_curso, t.calificacion_curso, t.calificacion_sistema, NOW(), NOW()
                    FROM $tabla t
                    JOIN alumnos a ON a.id = t.alumno_id
                    JOIN cursos c ON c.id = t.curso_id
                    ON DUPLICATE KEY UPDATE valoracion_curso = VALUES(valoracion_curso), calificacion_curso = VALUES(calificacion_curso),
                        calificacion_sistema = VALUES(calificacion_sistema), updated_at = NOW()
                ", [$periodo->id, $parcial]);
            } catch (\Throwable $e) {
                Log::error('ArchivoParciales: no se pudo archivar', ['parcial' => $parcial, 'periodo' => $periodo->id, 'error' => $e->getMessage()]);
                throw $e; // quien llama decide: antes de vaciar una tabla, un fallo aquí debe frenar el borrado
            }
        }

        return $total;
    }
}
