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
        Schema::create('periodo_ppds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_actual_ppd_id')->constrained('periodo_actual_ppds')->onDelete('cascade');
            $table->foreignId('alumno_id')->constrained()->onDelete('cascade');
            $table->foreignId('curso_id')->constrained()->onDelete('cascade');

            
            $table->decimal('calificacion_curso', 5, 2)->nullable();
            $table->decimal('calificacion_sistema', 5, 2)->nullable();
            $table->integer('nivel_desempeno')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodo_ppds');
    }
};
