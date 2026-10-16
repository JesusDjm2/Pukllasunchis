<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4 del expediente:
     *  - seguimiento_acciones: el acompañamiento institucional (entrevistas, llamadas,
     *    derivaciones, compromisos…) con responsable, acuerdos y fecha de revisión.
     *  - expediente_documentos: archivos del alumno (resoluciones, solicitudes de licencia,
     *    constancias…), opcionalmente ligados a un evento de trayectoria o a una acción.
     *    Se guardan en storage/app/expedientes (privado, se descargan solo con sesión).
     */
    public function up(): void
    {
        Schema::create('seguimiento_acciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // entrevista | llamada | derivacion | compromiso | reunion_familia | visita | otro
            $table->string('tipo', 30);
            $table->date('fecha');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resumen');
            $table->text('acuerdos')->nullable();
            $table->date('proxima_revision')->nullable();
            $table->string('estado', 15)->default('abierto'); // abierto | cerrado
            $table->boolean('confidencial')->default(false);
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'fecha']);
            $table->index(['estado', 'proxima_revision']);
        });

        Schema::create('expediente_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trayectoria_evento_id')->nullable()->constrained('trayectoria_eventos')->nullOnDelete();
            $table->foreignId('seguimiento_accion_id')->nullable()->constrained('seguimiento_acciones')->nullOnDelete();
            $table->string('titulo');
            $table->string('archivo');          // ruta relativa dentro del disco local
            $table->string('nombre_original');
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('tamano')->nullable(); // bytes
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_documentos');
        Schema::dropIfExists('seguimiento_acciones');
    }
};
