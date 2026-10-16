<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bitácora de cambios de todo el módulo de Titulación: quién creó,
     * actualizó o eliminó cada registro y con qué datos (antes/después),
     * poblada automáticamente por el trait App\Models\Titulacion\Concerns\Auditable
     * en los eventos de Eloquent — no requiere que cada controlador la llame
     * a mano. No sustituye a created_by/updated_by/deleted_by de cada tabla
     * (esas son para mostrar "quién" rápido sin JOIN); esta tabla es el
     * historial completo para cuando se necesite ver qué cambió exactamente.
     */
    public function up(): void
    {
        Schema::create('titulacion_auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 50); // creado|actualizado|eliminado
            $table->string('tabla_afectada', 100);
            $table->unsignedBigInteger('registro_id');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['tabla_afectada', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_auditorias');
    }
};
