<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->dropIndex('bolsa_trabajo_ofertas_fecha_inicio_index');
            $table->dropColumn(['fecha_inicio', 'fecha_fin']);

            $table->boolean('vigente')->default(true)->after('imagen');
            $table->string('nombre_publicador')->after('vigente');
            $table->string('telefono_publicador')->nullable()->after('nombre_publicador');
            $table->string('relacion_publicador')->nullable()->after('telefono_publicador');
        });
    }

    public function down(): void
    {
        Schema::table('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->dropColumn(['vigente', 'nombre_publicador', 'telefono_publicador', 'relacion_publicador']);

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->index('fecha_inicio');
        });
    }
};
