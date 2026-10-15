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
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('periodo_actual_id')->constrained('periodo_actual')->onDelete('cascade');
            $table->timestamp('fecha_completado')->nullable();
            $table->string('estado')->default('matriculado');
            $table->string('comprobante')->nullable();
            $table->timestamps();

            $table->unique(['alumno_id', 'periodo_actual_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
