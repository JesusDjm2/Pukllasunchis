<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sugerencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('programa_id');
            $table->unsignedBigInteger('ciclo_id');
            $table->unsignedBigInteger('alumno_id')->nullable();
            $table->boolean('es_anonima')->default(false);
            $table->text('mensaje');
            $table->string('estado')->default('pendiente');
            $table->unsignedBigInteger('atendido_por')->nullable();
            $table->timestamp('atendido_at')->nullable();
            $table->text('notas_atencion')->nullable();
            $table->timestamps();

            $table->foreign('programa_id')->references('id')->on('programas')->onDelete('cascade');
            $table->foreign('ciclo_id')->references('id')->on('ciclos')->onDelete('cascade');
            $table->foreign('alumno_id')->references('id')->on('alumnos')->nullOnDelete();
            $table->foreign('atendido_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sugerencias');
    }
};
