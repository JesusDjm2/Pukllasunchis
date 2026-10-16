<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado del PROYECTO del grupo (distinto del estado de cada trabajo
     * individual en taqe_trabajos): mientras esté "en_proceso" se pueden
     * seguir subiendo avances; al pasar a "finalizado" (solo el organizador,
     * o Super Admin) se bloquean nuevas subidas — ver TaqeController::storeTrabajo().
     */
    public function up(): void
    {
        Schema::table('taqe_grupos', function (Blueprint $table) {
            $table->string('estado')->default('en_proceso')->after('creador_alumno_id');
        });
    }

    public function down(): void
    {
        Schema::table('taqe_grupos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
