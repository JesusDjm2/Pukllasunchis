<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Programa Primaria EIB tiene Ciclos "Egresados <año>" que no llevan
     * ningún curso (a diferencia del caso ya resuelto de PPD/FID, aquí no
     * hace falta reincorporarlos a un Ciclo real porque ya no cursan nada).
     * Solo se marca condicion='Egresado' en su fila de users para que
     * salgan de la vista de Matrícula y de los conteos/selectores por
     * Ciclo, igual que ya ocurre con los demás Egresados.
     *
     * Se respalda la condicion original antes de sobrescribirla.
     */
    private const TABLA_RESPALDO = 'respaldo_egresados_eib_20260904';
    private const PROGRAMA_EIB = 2;

    public function up(): void
    {
        Schema::create(self::TABLA_RESPALDO, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ciclo_id');
            $table->string('condicion_original')->nullable();
            $table->timestamp('respaldado_at');
        });

        $cicloIds = DB::table('ciclos')
            ->where('programa_id', self::PROGRAMA_EIB)
            ->where('nombre', 'like', 'Egresados%')
            ->pluck('id');

        if ($cicloIds->isEmpty()) {
            return;
        }

        $usuarios = DB::table('users')->whereIn('ciclo_id', $cicloIds)->get(['id', 'ciclo_id', 'condicion']);
        foreach ($usuarios as $u) {
            DB::table(self::TABLA_RESPALDO)->insert([
                'user_id' => $u->id,
                'ciclo_id' => $u->ciclo_id,
                'condicion_original' => $u->condicion,
                'respaldado_at' => now(),
            ]);
        }

        DB::table('users')->whereIn('ciclo_id', $cicloIds)->update(['condicion' => 'Egresado']);
    }

    public function down(): void
    {
        foreach (DB::table(self::TABLA_RESPALDO)->get() as $r) {
            DB::table('users')->where('id', $r->user_id)->update([
                'condicion' => $r->condicion_original,
            ]);
        }

        Schema::dropIfExists(self::TABLA_RESPALDO);
    }
};
