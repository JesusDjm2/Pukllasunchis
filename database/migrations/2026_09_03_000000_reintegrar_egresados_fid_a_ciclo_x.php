<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mismo problema que ya se resolvió para PPD (ver
     * 2026_09_02_000000_reintegrar_egresados_ppd_a_ciclo_ii.php): cada
     * promoción que termina FID se movía a un Ciclo "Egresados <año>" nuevo,
     * sin ningún curso asociado. Eso deja a esos alumnos sin curso al que
     * calificar y, además, ensucia los selectores/conteos por Ciclo de la
     * pantalla de Matrícula con ciclos que ya no están vigentes.
     *
     * Se reincorporan a su Ciclo X real (el último de la malla, donde ya
     * tienen sus calificaciones guardadas) y se marca su condición como
     * "Egresado" — igual que en PPD — para poder excluirlos de los
     * listados/conteos del periodo actual sin depender del Ciclo para eso.
     *
     * A diferencia de la migración de PPD, aquí SÍ se guarda una copia de
     * seguridad exacta (tabla respaldo_egresados_fid_20260903) antes de
     * tocar nada, porque algunos de estos alumnos tenían un valor real en
     * "condicion" (Beca 18 / Beca Continua) que se sobreescribe a
     * "Egresado". El down() restaura cada registro exactamente como estaba.
     */
    private const CICLOS_EGRESADOS_YA_MIGRADOS_PPD = [33, 34];

    private const PROGRAMA_A_CICLO_X = [
        1 => 13, // Programa Inicial -> Ciclo X
        2 => 20, // Programa Primaria EIB -> Ciclo X
    ];

    private const TABLA_RESPALDO = 'respaldo_egresados_fid_20260903';

    public function up(): void
    {
        Schema::create(self::TABLA_RESPALDO, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ciclo_id_original');
            $table->string('condicion_original')->nullable();
            $table->unsignedBigInteger('alumno_id')->nullable();
            $table->unsignedBigInteger('alumno_ciclo_id_original')->nullable();
            $table->timestamp('respaldado_at');
        });

        $ciclosEgresadosIds = DB::table('ciclos')
            ->where('nombre', 'like', 'Egresados%')
            ->whereNotIn('id', self::CICLOS_EGRESADOS_YA_MIGRADOS_PPD)
            ->pluck('id', 'id');

        if ($ciclosEgresadosIds->isEmpty()) {
            return;
        }

        // 1) Respaldo exacto de cada usuario/alumno antes de tocar nada.
        $usuarios = DB::table('users')->whereIn('ciclo_id', $ciclosEgresadosIds)->get(['id', 'ciclo_id', 'condicion']);
        foreach ($usuarios as $u) {
            $alumno = DB::table('alumnos')->where('user_id', $u->id)->first(['id', 'ciclo_id']);

            DB::table(self::TABLA_RESPALDO)->insert([
                'user_id' => $u->id,
                'ciclo_id_original' => $u->ciclo_id,
                'condicion_original' => $u->condicion,
                'alumno_id' => $alumno->id ?? null,
                'alumno_ciclo_id_original' => $alumno->ciclo_id ?? null,
                'respaldado_at' => now(),
            ]);
        }

        // 2) Mover cada ciclo "Egresados <año>" a su Ciclo X real, según programa.
        $ciclos = DB::table('ciclos')->whereIn('id', $ciclosEgresadosIds)->get(['id', 'programa_id']);
        foreach ($ciclos as $ciclo) {
            $cicloDestino = self::PROGRAMA_A_CICLO_X[$ciclo->programa_id] ?? null;
            if (! $cicloDestino) {
                continue;
            }

            DB::table('users')->where('ciclo_id', $ciclo->id)
                ->update(['ciclo_id' => $cicloDestino, 'condicion' => 'Egresado']);

            DB::table('alumnos')->where('ciclo_id', $ciclo->id)
                ->update(['ciclo_id' => $cicloDestino]);
        }
    }

    public function down(): void
    {
        foreach (DB::table(self::TABLA_RESPALDO)->get() as $r) {
            DB::table('users')->where('id', $r->user_id)->update([
                'ciclo_id' => $r->ciclo_id_original,
                'condicion' => $r->condicion_original,
            ]);

            if ($r->alumno_id) {
                DB::table('alumnos')->where('id', $r->alumno_id)->update([
                    'ciclo_id' => $r->alumno_ciclo_id_original,
                ]);
            }
        }

        Schema::dropIfExists(self::TABLA_RESPALDO);
    }
};
