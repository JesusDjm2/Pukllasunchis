<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La "prioridad" de la tutoría pasa a ser cómo quiere conversar el alumno
 * (Tutoria::OPCIONES, se guarda en la misma columna `urgencia`). Si elige hablar
 * con otro docente, `docente_id` guarda a quién (users.id). El motivo es opcional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutorias', function (Blueprint $table) {
            $table->unsignedBigInteger('docente_id')->nullable()->after('alumno_id');
            $table->foreign('docente_id')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('ALTER TABLE tutorias MODIFY motivo TEXT NULL');
    }

    public function down(): void
    {
        Schema::table('tutorias', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
            $table->dropColumn('docente_id');
        });

        DB::statement("UPDATE tutorias SET motivo = '' WHERE motivo IS NULL");
        DB::statement('ALTER TABLE tutorias MODIFY motivo TEXT NOT NULL');
    }
};
