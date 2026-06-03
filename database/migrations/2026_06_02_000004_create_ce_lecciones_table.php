<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ce_lecciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ce_unidad_id')->constrained('ce_unidades')->cascadeOnDelete();
            $table->string('nombre');
            $table->enum('tipo', ['texto', 'audio', 'video'])->default('texto');
            $table->longText('contenido_texto')->nullable();
            $table->string('archivo_url')->nullable();
            $table->unsignedSmallInteger('duracion_min')->nullable();
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ce_lecciones');
    }
};
