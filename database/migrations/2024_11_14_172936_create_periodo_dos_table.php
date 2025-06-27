<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('periodo_dos', function (Blueprint $table) {
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

            //Observaciones
            $table->text('observaciones')->nullable();

            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('calificacion_id')->nullable()->constrained('calificacions')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodo_dos');
    }
};
