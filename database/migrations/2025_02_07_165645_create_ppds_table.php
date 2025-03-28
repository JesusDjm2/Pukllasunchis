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
        Schema::create('ppds', function (Blueprint $table) {
            $table->id();

            $table->string('email');
            $table->string('dni');
            $table->string('apellidos');
            $table->string('nombres');
            $table->string('numero');
            $table->string('numero_referencia');
            $table->string('procedencia_familiar');
            $table->string('direccion');
            $table->string('te_consideras');
            $table->string('lengua_1');
            $table->string('lengua_2');
            $table->string('estado_civil');
            $table->boolean('p_m_soltero');
            $table->bigInteger('num_hijos');
            $table->string('sector_socioeconomico');
            $table->string('num_comprobante');
            //Caracteristicas Familiares
            $table->string('convivientes');
            $table->string('quien_mantiene');
            $table->string('cant_dependientes_child');
            $table->string('cant_dependientes_old');
            $table->string('cant_dependientes_otros');
            //aspectos educativos
            //Educativos solo PPD
            $table->string('carrera_procedencia');
            $table->integer('ano_culminaste');
            $table->string('institucion_procedencia');
            $table->string('gestion_institucion');
            $table->string('direccion_institucion');
            $table->string('dep_dist_prov_institucion');

            $table->string('estudio_beca');
            $table->string('origen_beca')->nullable();
            $table->bigInteger('postulaciones_eesp')->nullable();
            $table->bigInteger('postulaciones_inst_uni')->nullable();
            $table->bigInteger('postulaciones_otros')->nullable();
            $table->string('tipo_preparacion');
            $table->string('motivo_estudio_eesp');
            $table->string('motivo_docencia');
            $table->string('motivo_especialidad');
            $table->boolean('internet');
            $table->string('internet_lugar')->nullable();
            $table->string('servicio_internet');
            $table->string('dispositivo_internet');
            $table->boolean('propio_compartido');
            $table->boolean('correo');
            $table->bigInteger('num_hrs_estudio');
            $table->string('forma_estudio');
            //Aspectos socioeconomicos
            $table->boolean('trabajas');
            $table->string('donde_trabajas')->nullable();
            $table->string('ingreso_mensual')->nullable();
            $table->string('egreso');
            $table->bigInteger('hrs_laboradas_sem');
            $table->boolean('ayuda_economica');
            $table->string('tiempo_ayuda');
            $table->string('tipo_apoyo_formacion');
            //Aspectos Vivienda
            $table->string('tipo_vivienda');
            $table->string('situacion_vivienda');
            $table->string('dormitorios_vivienda');
            $table->string('banos_vivienda');
            $table->string('material_vivienda');
            $table->string('bienes_vivienda')->nullable();
            $table->string('hrs_disponibles_agua');
            $table->string('hrs_disponibles_desague');
            $table->string('hrs_disponibles_luz');
            $table->string('otros_servicios')->nullable();
            //Aspectos de la Salud
            $table->boolean('problemas_salud');
            $table->boolean('ultima_consulta')->nullable();
            $table->string('motivo_consulta')->nullable();
            $table->string('tipo_seguro');
            $table->boolean('familiar_salud');
            // Aspectos culturales
            $table->string('frecuencia_lectura');
            $table->string('acceso_lectura');
            $table->string('visitas_museos');
            // Información adicional
            $table->string('actividades_internet');
            $table->string('habilidades')->nullable();
            $table->boolean('tiempo_libre');
            /*      
             */

            //Relaciones
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('programa_id');
            $table->foreign('programa_id')->references('id')->on('programas')->onDelete('cascade');
            $table->unsignedBigInteger('ciclo_id');
            $table->foreign('ciclo_id')->references('id')->on('ciclos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppds');
    }
};
