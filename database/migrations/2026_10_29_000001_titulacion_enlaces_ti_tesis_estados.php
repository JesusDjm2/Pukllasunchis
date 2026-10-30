<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enlace del TI (nuevo) y del de la Tesis con dos estados: «enviado» por el estudiante y
     * «aprobado» por el asesor, cada uno con su fecha. La RD de aprobación de la etapa
     * (exposición / sustentación) solo se registra con el enlace aprobado.
     */
    public function up(): void
    {
        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->string('link_ti', 500)->nullable()->after('rd_ti');
            $table->string('link_ti_estado', 20)->nullable()->after('link_ti');
            $table->date('link_ti_enviado_en')->nullable()->after('link_ti_estado');
            $table->date('link_ti_aprobado_en')->nullable()->after('link_ti_enviado_en');
            $table->string('link_tesis_estado', 20)->nullable()->after('link_tesis');
            $table->date('link_tesis_enviado_en')->nullable()->after('link_tesis_estado');
            $table->date('link_tesis_aprobado_en')->nullable()->after('link_tesis_enviado_en');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->dropColumn(['link_ti', 'link_ti_estado', 'link_ti_enviado_en', 'link_ti_aprobado_en',
                'link_tesis_estado', 'link_tesis_enviado_en', 'link_tesis_aprobado_en']);
        });
    }
};
