<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * "Egresados 2025" (ciclos id 33 y 34) se creó como un Ciclo aparte sin
     * cursos para representar a la generación que terminó el programa PPD.
     * Como los cursos viven en Ciclo I/Ciclo II, mover a un alumno a ese
     * ciclo lo dejaba sin ningún curso al que calificar (porcentajePPD() y
     * el roster del docente exigen que el ciclo_id coincida exactamente).
     * Se reincorporan a su Ciclo II real —donde ya tienen sus calificaciones
     * guardadas— y se marca su condición como "Egresado" para no perder ese
     * dato sin depender más del ciclo para representarlo.
     */
    private const MAPA_EGRESADOS_A_CICLO_II = [
        33 => 31, // Educación Inicial PPD: Egresados 2025 -> Ciclo II
        34 => 32, // Educación Primaria PPD: Egresados 2025 -> Ciclo II
    ];

    public function up(): void
    {
        foreach (self::MAPA_EGRESADOS_A_CICLO_II as $cicloEgresadosId => $cicloIiId) {
            DB::table('users')
                ->where('ciclo_id', $cicloEgresadosId)
                ->update(['ciclo_id' => $cicloIiId, 'condicion' => 'Egresado']);

            DB::table('ppds')
                ->where('ciclo_id', $cicloEgresadosId)
                ->update(['ciclo_id' => $cicloIiId]);
        }
    }

    public function down(): void
    {
        foreach (self::MAPA_EGRESADOS_A_CICLO_II as $cicloEgresadosId => $cicloIiId) {
            $userIds = DB::table('users')
                ->where('ciclo_id', $cicloIiId)
                ->where('condicion', 'Egresado')
                ->pluck('id');

            DB::table('users')->whereIn('id', $userIds)->update(['ciclo_id' => $cicloEgresadosId]);
            DB::table('ppds')->whereIn('user_id', $userIds)->update(['ciclo_id' => $cicloEgresadosId]);
        }
    }
};
