<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silabo_pdf', function (Blueprint $table) {
            $table->id();
            $table->string('pdf'); 
            // Relaciones
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('periodo_actual_id')->constrained('periodo_actual')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('silabo_pdf');
    }
};
