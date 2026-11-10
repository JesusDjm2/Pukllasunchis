<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** N° de la Carta de Presentación que Secretaría envía al aprobar (correlativo por año). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titulacion_cartas_aceptacion', function (Blueprint $table) {
            $table->string('numero_carta', 10)->nullable()->after('carta');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_cartas_aceptacion', function (Blueprint $table) {
            $table->dropColumn('numero_carta');
        });
    }
};
