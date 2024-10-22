<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calificacions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alumno_id');
            $table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('cascade');
            $table->unsignedBigInteger('curso_id'); 
            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade'); 

            $table->string('valoracion_1')->nullable();
            $table->string('valoracion_2')->nullable();
            $table->string('valoracion_3')->nullable();
            $table->string('valoracion_curso')->nullable();
            $table->string('calificacion_curso')->nullable();
            $table->string('calificacion_sistema')->nullable();
            $table->string('publicar')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacions');
    }
};
