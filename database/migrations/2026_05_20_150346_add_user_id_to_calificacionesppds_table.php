<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite guardar calificaciones PPD para alumnos inhabilitados
     * que no cuentan con registro en la tabla `ppds`.
     * - ppd_id pasa a ser nullable
     * - Se agrega user_id nullable como llave alternativa
     */
    public function up(): void
    {
        Schema::table('calificacionesppds', function (Blueprint $table) {
            // Hacer ppd_id nullable (para inhabilitados sin registro PPD)
            $table->foreignId('ppd_id')->nullable()->change();

            // Columna alternativa para cuando no hay ppd_id
            $table->foreignId('user_id')
                ->nullable()
                ->after('ppd_id')
                ->constrained('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calificacionesppds', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('ppd_id')->nullable(false)->change();
        });
    }
};
