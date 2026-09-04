<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los cursos Extracurriculares no llevan horas ni créditos (ver
     * CursoController::store()/update(), validación `required_unless:cc,Extracurricular`).
     * La validación de Laravel ya lo permite, pero las columnas seguían siendo
     * NOT NULL a nivel de base de datos, causando un error de integridad al
     * guardar un curso Extracurricular sin esos datos.
     *
     * Se usa SQL crudo (en vez de Schema::change()) porque el proyecto no tiene
     * instalado doctrine/dbal, requisito de ->change() en Laravel 10.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE cursos MODIFY horas VARCHAR(255) NULL');
        DB::statement('ALTER TABLE cursos MODIFY creditos VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cursos MODIFY horas VARCHAR(255) NOT NULL DEFAULT ''");
        DB::statement("ALTER TABLE cursos MODIFY creditos VARCHAR(255) NOT NULL DEFAULT ''");
    }
};
