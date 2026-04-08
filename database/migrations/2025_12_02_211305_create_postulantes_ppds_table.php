<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('postulantes_ppds', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('programa');
            $table->string('apellidos');
            $table->string('nombres');
            $table->string('dni', 15)->unique();
            $table->string('genero')->nullable();

            $table->string('estadoCivil');
            $table->string('vecesPostulo');
            $table->string('fecha_nacimiento');
            $table->string('lugar_nacimiento');
            $table->string('departamento_nacimiento');
            $table->string('provincia_nacimiento');
            $table->string('distrito_nacimiento');
            $table->string('edad');
            $table->string('hijos');
            $table->string('lengua_1');
            $table->string('lengua_2')->nullable();
            $table->string('trabaja')->nullable();
            $table->string('lugar_trabajo')->nullable();
            $table->string('cargo')->nullable();
            $table->string('opinionEespp')->nullable();
            $table->string('carrera');

            $table->string('domicilio')->nullable();
            $table->string('telefono')->nullable();
            $table->string('tipo_institucion')->nullable();
            $table->string('nombre_institucion')->nullable();
            $table->string('gestion_institucion')->nullable();
            $table->string('direccion_institucion')->nullable();
            $table->string('departamento_institucion')->nullable();
            $table->string('provincia_institucion')->nullable();
            $table->string('distrito_institucion')->nullable();

            $table->year('anio_conclusion')->nullable();
            $table->string('medio_conocimiento')->nullable();

            //Adjuntos
            $table->string('dni_adjunto')->nullable();
            $table->string('certificado')->nullable();
            $table->string('foto')->nullable();
            $table->string('titulo')->nullable();
            $table->string('voucher');

            $table->boolean('enviado')->default(false)->nullable();
            $table->string('constancia')->nullable();
            $table->boolean('apto')->nullable();
            $table->boolean('apto2')->nullable();

            $table->boolean('convertido')->default(false);

            /* $table->foreignId('admin_ppds_id')->constrained('admin_ppds')->onDelete('cascade')->after('id'); */
            $table->foreignId('admin_ppds_id')
                ->nullable()->change();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    /* public function down(): void
    {
        Schema::dropIfExists('postulantes_ppds');
    } */
    public function down()
    {
        Schema::table('postulantes_ppds', function (Blueprint $table) {
            $table->foreignId('admin_ppds_id')
                ->nullable(false)
                ->change();
        });
    }
};
