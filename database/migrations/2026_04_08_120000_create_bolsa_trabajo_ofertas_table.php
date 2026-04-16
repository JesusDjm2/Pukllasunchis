<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->longText('detalles');
            $table->string('imagen')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->timestamps();

            $table->index(['anio', 'mes']);
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolsa_trabajo_ofertas');
    }
};
