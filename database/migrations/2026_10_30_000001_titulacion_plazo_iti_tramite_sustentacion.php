<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - titulacion_ptis.fecha_iti: día en que el asesor compartió el informe ITI (plazo de 8 días
     *   hábiles desde que aprueba el enlace del TI).
     * - titulacion_tesis.tramite_sust_*: pago del trámite de aprobación de sustentación (va antes
     *   de la RD) y fecha en que se envió al estudiante el correo con la RD adjunta.
     */
    public function up(): void
    {
        Schema::table('titulacion_ptis', function (Blueprint $table) {
            $table->date('fecha_iti')->nullable()->after('link_iti');
        });
        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->date('tramite_sust_pagado_en')->nullable()->after('rd_tesis');
            $table->string('tramite_sust_recibo', 100)->nullable()->after('tramite_sust_pagado_en');
            $table->dateTime('correo_rd_tesis_enviado_en')->nullable()->after('tramite_sust_recibo');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_ptis', function (Blueprint $table) {
            $table->dropColumn('fecha_iti');
        });
        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->dropColumn(['tramite_sust_pagado_en', 'tramite_sust_recibo', 'correo_rd_tesis_enviado_en']);
        });
    }
};
