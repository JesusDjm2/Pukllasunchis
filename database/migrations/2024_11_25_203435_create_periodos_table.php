<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();            
            $table->string('valoracion_curso')->nullable();
            $table->string('calificacion_curso')->nullable();
            $table->string('calificacion_sistema')->nullable();

            // Relaciones
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('periodo_actual_id')->nullable()->constrained('periodo_actual')->onDelete('cascade');
            
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
