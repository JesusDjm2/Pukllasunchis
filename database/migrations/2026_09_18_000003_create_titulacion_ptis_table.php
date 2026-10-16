<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulacion_ptis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguimiento_id')->constrained('seguimiento_titulacion')->cascadeOnDelete();
            $table->text('titulo')->nullable();
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_pti')->nullable();
            $table->string('rd_pti')->nullable();
            $table->string('link_iti', 500)->nullable();
            $table->string('estado', 50)->default('pendiente'); // pendiente|aprobado|vencido
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulacion_ptis');
    }
};
