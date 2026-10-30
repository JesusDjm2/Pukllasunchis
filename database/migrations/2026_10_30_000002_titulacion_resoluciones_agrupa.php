<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * titulacion_resoluciones.agrupa: si la RD sirve para reconocer al grupo (GrupoTitulacion).
     * Quien se retira de un grupo conserva sus RD (no se eliminan), pero dejan de unirlo a él.
     */
    public function up(): void
    {
        Schema::table('titulacion_resoluciones', function (Blueprint $table) {
            $table->boolean('agrupa')->default(true)->after('detalle');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_resoluciones', function (Blueprint $table) {
            $table->dropColumn('agrupa');
        });
    }
};
