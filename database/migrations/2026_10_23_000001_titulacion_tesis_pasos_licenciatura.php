<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pasos de la Tesis (Licenciatura) que el gestor registra desde la ficha: enlaces de los
 * dictámenes (uno o más), enlace de la tesis y la aceptación del grado por la Escuela (casilla
 * con fecha). La situación de la tesis usa la columna `estado` que ya existía (nuevos valores:
 * no_solicito, no_se_presenta). SQL para producción: database/sql_produccion/2026_10_23_tesis_pasos.sql
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->json('enlaces_dictamen')->nullable()->after('fecha_extension');
            $table->string('link_tesis', 500)->nullable()->after('rd_tesis');
            $table->boolean('licenciatura_escuela')->default(false)->after('estado');
            $table->date('fecha_licenciatura_escuela')->nullable()->after('licenciatura_escuela');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->dropColumn(['enlaces_dictamen', 'link_tesis', 'licenciatura_escuela', 'fecha_licenciatura_escuela']);
        });
    }
};
