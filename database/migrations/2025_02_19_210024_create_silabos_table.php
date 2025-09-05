<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('silabos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->text('sumilla')->nullable();
            //Nueva tabla Periodo
            $table->string('periodo');

             /*Proyecto Integrador*/
            $table->string('proyecto_integrador')->nullable();
            $table->date('fecha_proyecto_integrador')->nullable();
            $table->text('producto_proyecto_integrador')->nullable();
            $table->text('descripcion_proyecto_integrador')->nullable();
            $table->text('vinculacion_pi')->nullable();
            $table->text('producto_curso')->nullable();

            /*Texto relacionado a competencias*/
            $table->text('capacidad1')->nullable();
            $table->text('desempeno1')->nullable();
            $table->text('criterio1')->nullable();
            $table->text('evidencia1')->nullable();
            $table->text('instrumento1')->nullable();

            $table->text('capacidad2')->nullable();
            $table->text('desempeno2')->nullable();
            $table->text('criterio2')->nullable();
            $table->text('evidencia2')->nullable();
            $table->text('instrumento2')->nullable();

            $table->text('capacidad3')->nullable();
            $table->text('desempeno3')->nullable();
            $table->text('criterio3')->nullable();
            $table->text('evidencia3')->nullable();
            $table->text('instrumento3')->nullable();

            $table->text('organizacion')->nullable();
            $table->text('modelos_metodologicos')->nullable();
            $table->text('recursos')->nullable();
            $table->text('referencias')->nullable();
            $table->text('fecha1')->nullable();
            $table->text('fecha2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('silabos');
    }
};
