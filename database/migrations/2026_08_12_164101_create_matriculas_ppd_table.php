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
        Schema::create('matriculas_ppd', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppd_id')->constrained('ppds')->onDelete('cascade');
            $table->foreignId('periodo_actual_ppd_id')->constrained('periodo_actual_ppds')->onDelete('cascade');
            $table->timestamp('fecha_completado')->nullable();
            $table->string('estado')->default('matriculado');
            $table->timestamps();

            $table->unique(['ppd_id', 'periodo_actual_ppd_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas_ppd');
    }
};
