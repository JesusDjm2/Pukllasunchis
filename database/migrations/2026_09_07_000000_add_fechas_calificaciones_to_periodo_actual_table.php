<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ventanas de fecha que administración usa para habilitar el registro de
     * calificaciones: una para Parcial 1, y otra compartida para Parcial 2 y
     * Desempeño (se guardan en tablas distintas pero se habilitan juntos).
     * Todas nullable: sin fecha configurada, esa ventana no restringe nada.
     */
    public function up(): void
    {
        Schema::table('periodo_actual', function (Blueprint $table) {
            $table->date('calificaciones_parcial1_inicio')->nullable()->after('formulario_habilitado');
            $table->date('calificaciones_parcial1_cierre')->nullable()->after('calificaciones_parcial1_inicio');
            $table->date('calificaciones_parcial2_inicio')->nullable()->after('calificaciones_parcial1_cierre');
            $table->date('calificaciones_parcial2_cierre')->nullable()->after('calificaciones_parcial2_inicio');
        });
    }

    public function down(): void
    {
        Schema::table('periodo_actual', function (Blueprint $table) {
            $table->dropColumn([
                'calificaciones_parcial1_inicio',
                'calificaciones_parcial1_cierre',
                'calificaciones_parcial2_inicio',
                'calificaciones_parcial2_cierre',
            ]);
        });
    }
};
