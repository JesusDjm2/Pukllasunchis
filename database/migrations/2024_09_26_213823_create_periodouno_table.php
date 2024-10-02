<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('periodouno', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->date('fecha')->nullable();
            $table->string('comp1')->nullable();
            $table->string('comp2')->nullable();
            $table->string('comp3')->nullable();
            $table->string('valoracion_1')->nullable();
            $table->string('valoracion_2')->nullable();
            $table->string('valoracion_3')->nullable();
            $table->string('valoracion_curso')->nullable();
            $table->string('calificacion_curso')->nullable();
            $table->string('calificacion_sistema')->nullable();

            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('calificacion_id')->constrained('calificacions')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('periodouno');
    }
};
