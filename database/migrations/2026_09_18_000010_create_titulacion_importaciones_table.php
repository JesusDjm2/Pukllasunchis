<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Zona de aterrizaje (staging) de los CSV de Carlos/Marilyn — NUNCA se
     * escribe directo en seguimiento_titulacion/titulacion_ptis/etc. desde
     * el importador. Cada fila de cada archivo fuente queda aquí tal cual
     * (datos_originales) + una vista normalizada (datos_normalizados) +
     * el intento de vincular con un user_id real. La fusión/objeción entre
     * lo que dice Carlos vs lo que dice Marilyn para un mismo egresado es
     * una decisión humana (panel de sincronización, Fase 6) — este staging
     * es lo que ese panel compara, no un reemplazo de esa revisión.
     */
    public function up(): void
    {
        Schema::create('titulacion_importaciones', function (Blueprint $table) {
            $table->id();
            $table->string('lote_importacion', 40); // agrupa todas las filas de una misma corrida del comando
            $table->string('origen', 40); // ppd-carlos|fid-carlos|exp-ppd-marilyn|exp-fid-marilyn|expo-sust-marilyn
            $table->string('archivo_nombre');
            $table->unsignedInteger('fila_num');
            $table->string('dni', 20)->nullable();
            $table->string('nombre_estudiante')->nullable();
            $table->string('nombre_normalizado')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado_match', 20)->default('sin_match'); // vinculado|ambiguo|sin_match
            $table->json('datos_originales'); // fila cruda, keyed por el encabezado real del CSV
            $table->json('datos_normalizados')->nullable(); // vista mapeada a campos comunes
            $table->foreignId('importado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['origen', 'estado_match']);
            $table->index('dni');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_importaciones');
    }
};
