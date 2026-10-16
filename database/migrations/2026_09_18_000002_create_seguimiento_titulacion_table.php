<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una fila por egresado. NO existe (ni se crea aquí) una tabla "egresados"
     * separada: el egresado sigue siendo el `User` de siempre (users.condicion
     * = 'Egresado', ver CicloController::marcarComoEgresados) — el "grupo/
     * promoción" ya lo modela su Ciclo real (p.ej. "Egresados 2024-I"), no
     * hace falta duplicarlo aquí.
     */
    public function up(): void
    {
        Schema::create('seguimiento_titulacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('estado_titulacion_id')->constrained('estados_titulacion');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_titulacion');
    }
};
