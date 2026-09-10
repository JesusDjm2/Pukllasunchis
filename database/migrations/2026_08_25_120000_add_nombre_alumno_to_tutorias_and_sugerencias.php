<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutorias', function (Blueprint $table) {
            $table->string('nombre_alumno')->nullable()->after('alumno_id');
        });

        // doctrine/dbal no está instalado en el proyecto, así que la columna
        // se vuelve nullable con SQL directo en vez de Blueprint::change().
        DB::statement('ALTER TABLE `tutorias` MODIFY `alumno_id` BIGINT UNSIGNED NULL');

        Schema::table('sugerencias', function (Blueprint $table) {
            $table->string('nombre_alumno')->nullable()->after('alumno_id');
        });
    }

    public function down(): void
    {
        Schema::table('tutorias', function (Blueprint $table) {
            $table->dropColumn('nombre_alumno');
        });

        Schema::table('sugerencias', function (Blueprint $table) {
            $table->dropColumn('nombre_alumno');
        });
    }
};
