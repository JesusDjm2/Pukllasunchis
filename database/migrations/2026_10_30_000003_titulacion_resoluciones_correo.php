<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Cuándo Secretaría envió esta RD por correo al estudiante. */
    public function up(): void
    {
        Schema::table('titulacion_resoluciones', function (Blueprint $table) {
            $table->dateTime('correo_enviado_en')->nullable()->after('agrupa');
        });
    }

    public function down(): void
    {
        Schema::table('titulacion_resoluciones', function (Blueprint $table) {
            $table->dropColumn('correo_enviado_en');
        });
    }
};
