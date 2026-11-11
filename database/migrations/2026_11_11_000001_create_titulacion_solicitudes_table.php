<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitudes de los trámites de titulación (formularios que hoy son de Google). Una fila por solicitud;
 * 'tipo' = clave de App\Services\Titulacion\TramitesExtraordinarios (empieza con 'aprobacion_pti').
 * Lo propio de cada tipo (área y línea de investigación, ciclo…) va en 'datos'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulacion_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 30)->index();
            $table->foreignId('solicitante_id')->nullable()->constrained('users')->nullOnDelete();
            // Se llena cuando la solicitud ya tiene proceso (al iniciarlo desde aquí o si ya existía).
            $table->foreignId('seguimiento_id')->nullable()->constrained('seguimiento_titulacion')->nullOnDelete();
            $table->string('correo', 150)->default('');
            $table->json('integrantes')->nullable();   // [{nombre, user_id}]
            $table->string('celulares', 200)->default('');
            $table->string('programa_estudios', 100)->default('');
            $table->string('ciclo', 30)->default('');
            $table->text('titulo')->nullable();
            $table->json('datos')->nullable();
            $table->text('archivo')->nullable();       // documento principal (PTI): enlace de Drive o archivo
            $table->text('comprobante')->nullable();   // uno o varios enlaces / archivo
            $table->boolean('declaracion')->default(false);
            $table->string('estado', 20)->default('recibida');
            $table->text('observacion')->nullable();
            $table->string('origen', 20)->default('sistema');
            $table->string('clave_importacion', 40)->nullable()->unique();
            $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atendido_en')->nullable();
            $table->timestamps();
            $table->index(['tipo', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_solicitudes');
    }
};
