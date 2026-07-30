<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reclamos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_reclamo')->nullable();

            // Datos del reclamante
            $table->string('nombre');
            $table->string('dni', 20);
            $table->string('domicilio');
            $table->string('telefono', 30);
            $table->string('correo');
            $table->enum('condicion_reclamante', [
                'Estudiante', 'Egresado', 'Postulante', 'Padre de familia', 'Público en general',
            ]);

            // Datos académicos (solo aplica si el reclamante está vinculado a un programa)
            $table->string('programa')->nullable();
            $table->string('ciclo')->nullable();
            $table->string('codigo_estudiante')->nullable();

            // Identificación del servicio
            $table->enum('tipo_servicio', ['Servicio educativo', 'Trámite administrativo', 'Otro servicio']);
            $table->text('descripcion_servicio')->nullable();
            $table->string('area_involucrada');
            $table->text('servicio_contratado')->nullable();

            // Reclamación
            $table->enum('tipo_reclamacion', ['Reclamo', 'Queja']);
            $table->text('descripcion_hechos');
            $table->text('pedido');

            // Adjunto
            $table->string('adjunto')->nullable();

            // Consentimientos
            $table->boolean('declara_informacion_verdadera')->default(false);
            $table->boolean('autoriza_tratamiento_datos')->default(false);
            $table->boolean('confirma_lectura_libro')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamos');
    }
};
