<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datos de los expedientes de secretaría que no tenían dónde guardarse:
     * el periodo de estudios de la promoción ("2016-I al 2021-II") y la
     * fecha hasta la que se extendió el plazo ("extension 19/12/24").
     */
    public function up(): void
    {
        Schema::table('seguimiento_titulacion', function (Blueprint $table) {
            $table->string('periodo_estudios', 40)->nullable()->after('numero_expediente');
        });

        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->date('fecha_extension')->nullable()->after('rd_extension');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->dropColumn('fecha_extension');
        });

        Schema::table('seguimiento_titulacion', function (Blueprint $table) {
            $table->dropColumn('periodo_estudios');
        });
    }
};
