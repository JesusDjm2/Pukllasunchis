<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inicio del proceso por grupo y pasos nuevos del TI:
 *  - seguimiento_titulacion.grupo_id: integrantes que iniciaron juntos el PTI (id del seguimiento
 *    de quien lo inició). Antes el grupo solo se deducía de RD/actas compartidas.
 *  - titulacion_tesis.url_repositorio_ti: el TI aprobado se sube al Repositorio Institucional
 *    antes del Bachiller en SUNEDU (url_repositorio queda para la Tesis).
 *  - titulacion_participantes: asesores (1 o más) y jurado del TI/Tesis, con cargo opcional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seguimiento_titulacion', function (Blueprint $table) {
            $table->unsignedBigInteger('grupo_id')->nullable()->after('numero_expediente')->index();
        });

        Schema::table('titulacion_tesis', function (Blueprint $table) {
            $table->string('url_repositorio_ti', 500)->nullable()->after('rd_ti');
        });

        Schema::create('titulacion_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguimiento_id')->constrained('seguimiento_titulacion')->cascadeOnDelete();
            $table->string('etapa', 10);    // ti | tesis
            $table->string('funcion', 10);  // asesor | jurado
            $table->foreignId('docente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('asesor_externo_id')->nullable()->constrained('titulacion_asesores')->nullOnDelete();
            $table->string('cargo', 40)->nullable(); // opcional: Presidente, Secretario, Vocal, Coasesor…
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['seguimiento_id', 'etapa', 'funcion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_participantes');
        Schema::table('titulacion_tesis', fn (Blueprint $table) => $table->dropColumn('url_repositorio_ti'));
        Schema::table('seguimiento_titulacion', function (Blueprint $table) {
            $table->dropIndex(['grupo_id']);
            $table->dropColumn('grupo_id');
        });
    }
};
