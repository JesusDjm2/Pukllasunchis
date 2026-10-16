<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo Taq'e (Ferias de Investigación): grupos de alumnos, su
     * membresía (con estado de invitación) y los trabajos que suben.
     * curso_id/ciclo_id/programa_id/periodo_actual_id se guardan
     * denormalizados en taqe_grupos (mismo criterio que ya usa Incidencia
     * con programa_id/ciclo_id) para que el Investigador filtre sin joins.
     */
    public function up(): void
    {
        Schema::create('taqe_grupos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('curso_id')->constrained('cursos');
            $table->foreignId('ciclo_id')->constrained('ciclos');
            $table->foreignId('programa_id')->constrained('programas');
            $table->foreignId('periodo_actual_id')->constrained('periodo_actual');
            $table->foreignId('creador_alumno_id')->constrained('alumnos');
            $table->timestamps();
        });

        Schema::create('taqe_grupo_alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taqe_grupo_id')->constrained('taqe_grupos')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->string('estado')->default('pendiente'); // pendiente | aceptado | rechazado
            $table->timestamp('respondido_at')->nullable();
            $table->timestamps();

            $table->unique(['taqe_grupo_id', 'alumno_id']);
        });

        Schema::create('taqe_trabajos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taqe_grupo_id')->constrained('taqe_grupos')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('archivo'); // id del archivo en Google Drive
            $table->string('tipo_archivo'); // imagen | pdf | word | video
            $table->string('estado')->default('en_proceso'); // en_proceso | finalizado
            $table->foreignId('subido_por_alumno_id')->constrained('alumnos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taqe_trabajos');
        Schema::dropIfExists('taqe_grupo_alumno');
        Schema::dropIfExists('taqe_grupos');
    }
};
