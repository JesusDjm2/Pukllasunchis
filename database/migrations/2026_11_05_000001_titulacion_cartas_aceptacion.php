<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carta de aceptación de la Institución Educativa (paso después de la RD del PTI): el estudiante
 * la solicita desde su intranet y el Área de Investigación sube la carta o la observa.
 * Una fila por integrante del grupo (mismo «lote»): así el paso se calcula desde la ficha de
 * cada uno sin buscar en el grupo, igual que las RD.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulacion_cartas_aceptacion', function (Blueprint $table) {
            $table->id();
            $table->char('lote', 36)->index();
            $table->foreignId('seguimiento_id')->constrained('seguimiento_titulacion')->cascadeOnDelete();
            $table->foreignId('solicitante_id')->constrained('users')->cascadeOnDelete();
            $table->string('correo', 150);
            $table->string('grupo', 1000);
            $table->string('celular', 20);
            $table->string('programa_estudios', 100);
            $table->string('programa_formacion', 100);
            $table->string('institucion', 300);
            $table->string('director', 200);
            $table->string('titulo', 1000);
            $table->string('comprobante', 120);
            $table->boolean('declaracion')->default(false);
            $table->string('estado', 20)->default('enviada');
            $table->text('observacion')->nullable();
            $table->string('carta', 500)->nullable();
            $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atendido_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_cartas_aceptacion');
    }
};
