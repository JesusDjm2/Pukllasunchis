<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * alumno_cursos.periodo_actual_id existe en las BDs reales (agregado a mano)
     * y Alumno::cursosDelPeriodo() / AlumnoCursoController dependen de ella, pero
     * nunca hubo una migración que la creara — una BD nueva no la tiene.
     */
    public function up(): void
    {
        if (Schema::hasColumn('alumno_cursos', 'periodo_actual_id')) {
            return;
        }

        Schema::table('alumno_cursos', function (Blueprint $table) {
            $table->foreignId('periodo_actual_id')
                ->nullable()
                ->after('curso_id')
                ->constrained('periodo_actual')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('alumno_cursos', 'periodo_actual_id')) {
            return;
        }

        Schema::table('alumno_cursos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodo_actual_id');
        });
    }
};
