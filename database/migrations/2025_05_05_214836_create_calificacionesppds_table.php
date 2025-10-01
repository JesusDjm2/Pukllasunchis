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
        Schema::create('calificacionesppds', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->date('fecha')->nullable();
            //Competencias
            $table->string('comp1')->nullable();
            $table->string('comp2')->nullable();
            $table->string('comp3')->nullable();

            // Productos de Proceso - Competencia 1
            $table->string('pp_c1_1')->nullable();
            $table->string('pp_c1_2')->nullable();
            $table->string('pp_c1_3')->nullable();
            $table->string('pp_c1_4')->nullable();

            // Productos de Proceso - Competencia 2
            $table->string('pp_c2_1')->nullable();
            $table->string('pp_c2_2')->nullable();
            $table->string('pp_c2_3')->nullable();
            $table->string('pp_c2_4')->nullable();

            // Productos de Proceso - Competencia 3
            $table->string('pp_c3_1')->nullable();
            $table->string('pp_c3_2')->nullable();
            $table->string('pp_c3_3')->nullable();
            $table->string('pp_c3_4')->nullable();

            // Producto Final - Competencia 1
            $table->string('pf_c1_1')->nullable();
            $table->string('pf_c1_2')->nullable();
            $table->string('pf_c1_3')->nullable();

            // Producto Final - Competencia 2
            $table->string('pf_c2_1')->nullable();
            $table->string('pf_c2_2')->nullable();
            $table->string('pf_c2_3')->nullable();

            // Producto Final - Competencia 3
            $table->string('pf_c3_1')->nullable();
            $table->string('pf_c3_2')->nullable();
            $table->string('pf_c3_3')->nullable();

            // Promedios Generales - Competencia 1
            $table->string('pg_c1_1')->nullable();
            $table->string('pg_c1_2')->nullable();
            $table->string('pg_c1_3')->nullable();

            // Promedios Generales - Competencia 2
            $table->string('pg_c2_1')->nullable();
            $table->string('pg_c2_2')->nullable();
            $table->string('pg_c2_3')->nullable();

            // Promedios Generales - Competencia 3
            $table->string('pg_c3_1')->nullable();
            $table->string('pg_c3_2')->nullable();
            $table->string('pg_c3_3')->nullable();

            // Evaluaciones finales
            $table->string('nivel_desempeno')->nullable();
            $table->string('calificacion_curso')->nullable();
            $table->string('calificacion_sistema')->nullable();

            // Relaciones
            $table->foreignId('ppd_id')->constrained('ppds')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacionesppds');
    }
};
