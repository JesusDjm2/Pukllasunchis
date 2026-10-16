<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada matrícula guarda en qué programa/ciclo/condición estaba el alumno ese periodo
     * (users.ciclo_id se sobrescribe). Con esto la repitencia se detecta sola.
     *
     * Reconstrucción de lo existente, en este orden:
     *  1. ciclo = el más frecuente entre los cursos con nota archivada ese periodo
     *     (periodos / periodo_ppds); empate → el ciclo de id mayor.
     *  2. si el periodo es el vigente y no hay notas aún → ciclo y condición actuales del alumno.
     *  3. programa = el del ciclo.
     * SQL plano a propósito: producción solo se actualiza pegando SQL.
     */
    public function up(): void
    {
        foreach (['matriculas', 'matriculas_ppd'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('programa_id')->nullable()->after('id')->constrained('programas')->nullOnDelete();
                $table->foreignId('ciclo_id')->nullable()->after('programa_id')->constrained('ciclos')->nullOnDelete();
                $table->string('condicion')->nullable()->after('ciclo_id');
            });
        }

        // ── FID ──
        DB::statement("
            UPDATE matriculas m SET m.ciclo_id = (
                SELECT c.ciclo_id FROM periodos p JOIN cursos c ON c.id = p.curso_id
                WHERE p.alumno_id = m.alumno_id AND p.periodo_actual_id = m.periodo_actual_id
                GROUP BY c.ciclo_id ORDER BY COUNT(*) DESC, c.ciclo_id DESC LIMIT 1
            ) WHERE m.ciclo_id IS NULL
        ");
        DB::statement("
            UPDATE matriculas m
            JOIN periodo_actual pa ON pa.id = m.periodo_actual_id AND pa.actual = 1
            JOIN alumnos a ON a.id = m.alumno_id
            JOIN users u ON u.id = a.user_id
            SET m.ciclo_id = COALESCE(m.ciclo_id, u.ciclo_id, a.ciclo_id), m.condicion = COALESCE(m.condicion, u.condicion)
        ");
        DB::statement('UPDATE matriculas m JOIN ciclos c ON c.id = m.ciclo_id SET m.programa_id = c.programa_id WHERE m.programa_id IS NULL');

        // ── PPD (periodo_ppds.alumno_id es el id de la ficha ppds) ──
        DB::statement("
            UPDATE matriculas_ppd m SET m.ciclo_id = (
                SELECT c.ciclo_id FROM periodo_ppds p JOIN cursos c ON c.id = p.curso_id
                WHERE p.alumno_id = m.ppd_id AND p.periodo_actual_ppd_id = m.periodo_actual_ppd_id
                GROUP BY c.ciclo_id ORDER BY COUNT(*) DESC, c.ciclo_id DESC LIMIT 1
            ) WHERE m.ciclo_id IS NULL
        ");
        DB::statement("
            UPDATE matriculas_ppd m
            JOIN periodo_actual_ppds pa ON pa.id = m.periodo_actual_ppd_id AND pa.actual = 1
            JOIN ppds a ON a.id = m.ppd_id
            JOIN users u ON u.id = a.user_id
            SET m.ciclo_id = COALESCE(m.ciclo_id, u.ciclo_id, a.ciclo_id), m.condicion = COALESCE(m.condicion, u.condicion)
        ");
        DB::statement('UPDATE matriculas_ppd m JOIN ciclos c ON c.id = m.ciclo_id SET m.programa_id = c.programa_id WHERE m.programa_id IS NULL');
    }

    public function down(): void
    {
        foreach (['matriculas', 'matriculas_ppd'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropConstrainedForeignId('programa_id');
                $table->dropConstrainedForeignId('ciclo_id');
                $table->dropColumn('condicion');
            });
        }
    }
};
