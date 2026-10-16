<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de correos de la Unidad de Investigación avisando que el PTI
     * está por vencer (antes solo se anotaba "enviado" en el Excel).
     * tipo: recordatorio | urgente | excel (marcado en la hoja, sin detalle).
     */
    public function up(): void
    {
        Schema::create('titulacion_avisos_pti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguimiento_id')->constrained('seguimiento_titulacion')->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->string('email')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('enviado')->default(true);
            $table->text('error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_avisos_pti');
    }
};
