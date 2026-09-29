<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulacion_actas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguimiento_id')->constrained('seguimiento_titulacion')->cascadeOnDelete();
            $table->string('tipo', 20); // expo|tesis
            $table->string('numero_acta', 20)->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('promedio', 4, 2)->nullable();
            $table->text('titulo_trabajo')->nullable();
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_actas');
    }
};
