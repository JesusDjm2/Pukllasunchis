<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datos de cada estudiante en un acta que no son notas: la condición (columna «Condición»
     * del acta física, p. ej. P / PB) y la observación (p. ej. «RETIRADA R.D. N° 86-03-ISPP-RP-C»).
     */
    public function up(): void
    {
        Schema::create('rp_acta_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('rp_actas')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('rp_estudiantes')->onDelete('cascade');
            $table->string('condicion', 20)->nullable();
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->unique(['acta_id', 'estudiante_id'], 'rp_acta_estudiante_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rp_acta_estudiante');
    }
};
