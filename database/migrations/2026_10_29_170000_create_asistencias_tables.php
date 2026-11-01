<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->foreignId('periodo_actual_id')->nullable()->constrained('periodo_actual')->nullOnDelete();
            $table->date('fecha');
            $table->string('tema')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['curso_id', 'fecha'], 'asistencias_curso_fecha_unique');
        });

        Schema::create('asistencia_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')->constrained('asistencias')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('alumno_id')->nullable()->constrained('alumnos')->nullOnDelete();
            $table->string('estado', 20)->default('presente'); // presente, tardanza, falta, justificado, licencia
            $table->boolean('en_licencia')->default(false);
            $table->boolean('es_beca')->default(false);
            $table->string('tipo_beca', 100)->nullable();
            $table->string('origen_beca', 255)->nullable();
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->unique(['asistencia_id', 'user_id'], 'asistencia_detalles_asistencia_user_unique');
            $table->index(['asistencia_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_detalles');
        Schema::dropIfExists('asistencias');
    }
};
