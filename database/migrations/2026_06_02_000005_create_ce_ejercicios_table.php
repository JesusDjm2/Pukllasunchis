<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ce_ejercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ce_unidad_id')->constrained('ce_unidades')->cascadeOnDelete();
            $table->enum('tipo', ['multiple', 'completar', 'emparejar'])->default('multiple');
            $table->text('pregunta');
            $table->json('opciones')->nullable();
            $table->string('respuesta_correcta');
            $table->unsignedTinyInteger('puntaje_max')->default(1);
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ce_ejercicios');
    }
};
