<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los cursos asincrónicos son de un docente: el modelo (Docente::cursosEspeciales(),
     * CursoEspecialController) y el sidebar de layouts/docente.blade.php ya usan
     * `cursos_especiales.docente_id`, pero la tabla se creó sin esa columna y
     * ningún migration la agregaba, lo que rompía cualquier página del panel del
     * docente al consultar la relación. Nullable: un curso aún sin docente asignado
     * es válido y lo administra la plataforma.
     */
    public function up(): void
    {
        Schema::table('cursos_especiales', function (Blueprint $table) {
            $table->unsignedBigInteger('docente_id')->nullable()->after('orden');
            $table->foreign('docente_id')->references('id')->on('docentes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cursos_especiales', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
            $table->dropColumn('docente_id');
        });
    }
};
