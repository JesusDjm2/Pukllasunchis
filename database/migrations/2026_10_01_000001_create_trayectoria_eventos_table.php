<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Línea de tiempo académica del estudiante: licencias, retiros, reincorporaciones,
     * cambios de ciclo/programa, egreso, becas… Un evento por fila y NUNCA se sobrescribe:
     * users.perfil / users.condicion / users.ciclo_id solo guardan el estado ACTUAL, así
     * que sin esta tabla se perdía toda la historia (quien pidió licencia y volvió figuraba
     * como si nada hubiera pasado).
     */
    public function up(): void
    {
        Schema::create('trayectoria_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // licencia | retiro | inhabilitacion | reincorporacion | cambio_ciclo | cambio_programa
            // | egreso | beca | sancion | observacion | estado_inicial
            $table->string('tipo', 30);
            $table->foreignId('periodo_actual_id')->nullable()->constrained('periodo_actual')->nullOnDelete();
            $table->foreignId('periodo_actual_ppd_id')->nullable()->constrained('periodo_actual_ppds')->nullOnDelete();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable(); // p. ej. cierre de una licencia al reincorporarse
            $table->string('valor_anterior')->nullable();
            $table->string('valor_nuevo')->nullable();
            $table->foreignId('ciclo_origen_id')->nullable()->constrained('ciclos')->nullOnDelete();
            $table->foreignId('ciclo_destino_id')->nullable()->constrained('ciclos')->nullOnDelete();
            $table->text('motivo')->nullable();
            $table->string('resolucion')->nullable();
            $table->string('documento')->nullable();
            // automatico (lo registró el sistema al detectar el cambio) | manual | migracion
            $table->string('origen', 20)->default('manual');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'tipo']);
            $table->index(['tipo', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trayectoria_eventos');
    }
};
