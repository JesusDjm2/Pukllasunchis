<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('egresado_grupos_entrevistadores')) {
            Schema::create('egresado_grupos_entrevistadores', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->unsignedBigInteger('campana_id')->nullable()->index();
                $table->unsignedBigInteger('coordinador_id')->nullable()->index();
                $table->string('programa', 100)->nullable();
                $table->string('cohorte', 50)->nullable();
                $table->json('entrevistadores')->nullable(); // array de nombres o user_ids
                $table->json('egresados_ids')->nullable(); // array de IDs de egresados asignados
                $table->string('estado', 30)->default('activo'); // activo, completado, pausado
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('egresado_grupos_entrevistadores');
    }
};
