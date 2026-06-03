<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ce_niveles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_especial_id')->constrained('cursos_especiales')->cascadeOnDelete();
            $table->string('nombre');
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ce_niveles');
    }
};
