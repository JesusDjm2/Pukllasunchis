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
        Schema::create('postulantes_regulars', function (Blueprint $table) {
            $table->id();
            $table->string('email'); 
            $table->string('programa');
            $table->string('estudio_beca');            
            $table->string('apellidos');
            $table->string('nombres');
            $table->string('dni');              
            $table->boolean('genero');
            $table->string('direccion')->nullable();
            $table->string('numero');
            $table->date('fecha_nacimiento');
            $table->string('lugar_nacimiento')->nullable();
            $table->string('distrito_nacimiento')->nullable();
            $table->string('provincia_nacimiento')->nullable();
            $table->string('departamento_nacimiento')->nullable();
            $table->string('contacto')->nullable();

            //Colegio y trabajo
            $table->string('colegio');
            $table->string('codigo_colegio');
            $table->string('gestion_colegio');
            $table->string('direccion_colegio')->nullable();
            $table->string('distrito_colegio')->nullable();
            $table->string('provincia_colegio')->nullable();
            $table->string('departamento_colegio')->nullable();
            $table->string('ano_termino_colegio')->nullable();
            $table->string('promedio_colegio')->nullable();
            $table->string('lengua_1');
            $table->string('lengua_2')->nullable();
            $table->string('estado_civil')->nullable();
            $table->bigInteger('num_hijos')->nullable();
            $table->boolean('trabajas')->nullable();
            $table->string('donde_trabajas')->nullable();
            $table->string('cargo_trabajas')->nullable();            
            $table->string('describe_eespp');

            //Documentos adjuntos
            $table->string('dni_pdf')->nullable();
            $table->string('partida_nacimiento_pdf')->nullable(); 
            $table->string('certificado_secundaria_pdf')->nullable(); 
            $table->string('foto')->nullable(); 
            $table->string('declaracion_jurada_salud_pdf')->nullable(); 
            $table->string('declaracion_jurada_documentos_pdf')->nullable(); 
            $table->string('declaracion_jurada_conectividad_pdf')->nullable(); 
            $table->string('voucher_pago')->nullable(); 

            $table->boolean('observaciones')->default(false)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulantes_regulars');
    }
};
