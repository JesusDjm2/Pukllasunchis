<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('egresado_perfil_versiones')) {
            Schema::create('egresado_perfil_versiones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('registrado_por')->nullable();
                $table->json('datos');
                $table->timestamp('confirmado_at')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('egresado_perfil_borradores')) {
            Schema::create('egresado_perfil_borradores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->json('datos');
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('seguimiento_lugares')) {
            Schema::create('seguimiento_lugares', function (Blueprint $table) {
                $table->id();
                $table->string('fuente', 80);
                $table->string('codigo', 100);
                $table->string('tipo', 30); // localidad, comunidad, escuela, sede
                $table->string('nombre');
                $table->string('departamento');
                $table->string('provincia');
                $table->string('distrito');
                $table->string('ubigeo', 6)->nullable()->index();
                $table->decimal('latitud', 10, 7)->nullable();
                $table->decimal('longitud', 11, 7)->nullable();
                $table->date('fecha_fuente');
                $table->timestamps();
                $table->unique(['fuente', 'codigo', 'tipo']);
            });
        }
    }

    public function down(): void
    {
        // Reversión conservadora: nunca destruir historial ni catálogos cargados.
        // Restaurar la versión de aplicación anterior es seguro al ser tablas aditivas.
    }
};
