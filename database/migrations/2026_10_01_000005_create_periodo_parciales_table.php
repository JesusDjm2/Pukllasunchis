<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Archivo de Parcial 1 y Parcial 2 (FID). periodouno / periodo_dos no tienen periodo y se
     * vacían cada fin de periodo desde la "zona de riesgo" de /docente, así que hasta ahora solo
     * sobrevivía la nota final (tabla periodos). Tabla aparte a propósito: "Crear calificaciones"
     * borra de periodos los registros sin Desempeño, y los parciales no deben perderse con ellos.
     */
    public function up(): void
    {
        Schema::create('periodo_parciales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_actual_id')->constrained('periodo_actual')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->unsignedTinyInteger('parcial'); // 1 | 2
            $table->string('valoracion_curso')->nullable();
            $table->string('calificacion_curso')->nullable();
            $table->string('calificacion_sistema')->nullable();
            $table->timestamps();

            $table->unique(['periodo_actual_id', 'alumno_id', 'curso_id', 'parcial'], 'periodo_parciales_unico');
        });

        // Lo que hay HOY en periodouno / periodo_dos es del periodo vigente: se archiva ya.
        // Solo lo registrado desde que empezó ese periodo (por si quedaron restos de uno anterior).
        foreach ([1 => 'periodouno', 2 => 'periodo_dos'] as $parcial => $tabla) {
            DB::statement("
                INSERT INTO periodo_parciales (periodo_actual_id, alumno_id, curso_id, parcial, valoracion_curso, calificacion_curso, calificacion_sistema, created_at, updated_at)
                SELECT pa.id, t.alumno_id, t.curso_id, $parcial, t.valoracion_curso, t.calificacion_curso, t.calificacion_sistema, NOW(), NOW()
                FROM $tabla t
                JOIN periodo_actual pa ON pa.actual = 1 AND (pa.fecha_inicio IS NULL OR t.created_at >= pa.fecha_inicio)
                JOIN alumnos a ON a.id = t.alumno_id
                JOIN cursos c ON c.id = t.curso_id
                ON DUPLICATE KEY UPDATE valoracion_curso = VALUES(valoracion_curso), calificacion_curso = VALUES(calificacion_curso),
                    calificacion_sistema = VALUES(calificacion_sistema), updated_at = NOW()
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('periodo_parciales');
    }
};
