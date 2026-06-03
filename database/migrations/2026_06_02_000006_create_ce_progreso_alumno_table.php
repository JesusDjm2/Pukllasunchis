<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ce_progreso_alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ce_leccion_id')->nullable()->constrained('ce_lecciones')->cascadeOnDelete();
            $table->foreignId('ce_ejercicio_id')->nullable()->constrained('ce_ejercicios')->cascadeOnDelete();
            $table->boolean('completado')->default(false);
            $table->unsignedTinyInteger('puntaje')->nullable();
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['user_id', 'ce_leccion_id'], 'unique_progreso_leccion');
            $table->unique(['user_id', 'ce_ejercicio_id'], 'unique_progreso_ejercicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ce_progreso_alumno');
    }
};
