<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Docentes encargados/organizadores de cada Proyecto Integrador (además de los docentes de los cursos).
 * Un docente puede estar en varios proyectos, con un solo rol en cada uno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyecto_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('docente_id')->constrained('docentes')->cascadeOnDelete();
            $table->enum('rol', ['encargado', 'organizador'])->default('organizador');
            $table->timestamps();
            $table->unique(['proyecto_id', 'docente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_docente');
    }
};
