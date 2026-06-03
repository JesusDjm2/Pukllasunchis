<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ce_inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('curso_especial_id')->constrained('cursos_especiales')->cascadeOnDelete();
            $table->foreignId('periodo_actual_id')->nullable()->constrained('periodo_actual')->nullOnDelete();
            $table->timestamp('inscrito_at')->useCurrent();

            $table->unique(['user_id', 'curso_especial_id'], 'unique_inscripcion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ce_inscripciones');
    }
};
