<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los archivos del grupo dejan de tener un estado propio (en_proceso /
 * finalizado era solo una etiqueta): ahora cada uno es una EVIDENCIA del
 * proceso o el TRABAJO FINAL (un único PDF por grupo). El grupo pasa a
 * "finalizado" cuando sube su trabajo final, ya no a mano.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taqe_trabajos', function (Blueprint $table) {
            $table->string('categoria')->default('evidencia')->after('tipo_archivo'); // evidencia | final
        });
    }

    public function down(): void
    {
        Schema::table('taqe_trabajos', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }
};
