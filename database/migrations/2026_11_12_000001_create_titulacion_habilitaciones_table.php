<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Excepciones de Secretaría: alumnos habilitados a solicitar la aprobación de su PTI aunque todavía no cumplan
 * la regla del ciclo (FID desde el Ciclo IX). Una fila por alumno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulacion_habilitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('motivo', 500)->nullable();
            $table->foreignId('habilitado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_habilitaciones');
    }
};
