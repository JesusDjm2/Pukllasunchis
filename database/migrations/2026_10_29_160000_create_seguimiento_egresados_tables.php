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
        // 1. Campañas semestrales de seguimiento
        if (!Schema::hasTable('egresado_campanas')) {
            Schema::create('egresado_campanas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100);
                $table->string('codigo', 30)->unique();
                $table->year('anio');
                $table->string('semestre', 10); // 'I' o 'II'
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->enum('estado', ['borrador', 'activa', 'cerrada'])->default('activa');
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }

        // 2. Fichas y actualizaciones de egresados
        if (!Schema::hasTable('egresado_actualizaciones')) {
            Schema::create('egresado_actualizaciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('identificador_temporal', 50)->nullable()->index(); // Para registros simulados / fichas ficticias
                $table->string('nombre_completo', 150);
                $table->string('programa', 100)->nullable();
                $table->string('cohorte_egreso', 30)->nullable();
                $table->unsignedBigInteger('campana_id')->nullable()->index();
                $table->string('canal', 40)->default('autorespuesta'); // 'autorespuesta', 'entrevista_telefonica', 'intranet'
                $table->string('registrado_por', 100)->nullable();

                // Contacto
                $table->string('telefono', 50)->nullable();
                $table->string('whatsapp', 50)->nullable();
                $table->string('telefono_alt', 50)->nullable();
                $table->string('correo', 150)->nullable();
                $table->string('direccion_contacto', 255)->nullable();
                $table->string('medio_preferido', 50)->nullable();

                // Ubicación Residencia
                $table->string('res_departamento', 80)->nullable();
                $table->string('res_provincia', 80)->nullable();
                $table->string('res_distrito', 80)->nullable();
                $table->string('res_centro', 100)->nullable();

                // Titulación
                $table->string('titulo_bach', 80)->nullable();
                $table->date('fecha_bach')->nullable();
                $table->string('titulo_lic', 80)->nullable();
                $table->date('fecha_lic')->nullable();
                $table->text('apoyo_titulacion')->nullable();

                // Empleo actual
                $table->string('trabaja', 50)->nullable(); // 'Trabaja', 'No trabaja', 'Prefiere no responder'
                $table->string('cambio_empleo', 80)->nullable();
                $table->string('campo', 80)->nullable(); // 'En educación', 'Fuera de educación'
                $table->string('tipo_practica', 80)->nullable(); // 'Docencia directa', 'Auxiliar...', etc.
                $table->string('institucion', 150)->nullable();
                $table->string('cargo', 150)->nullable();
                $table->string('nivel_modalidad', 80)->nullable();
                $table->string('sector', 80)->nullable();
                $table->string('contrato', 80)->nullable();
                $table->string('modalidad_tiempo', 50)->nullable();
                $table->date('inicio_empleo')->nullable();
                $table->date('fin_empleo')->nullable();
                $table->text('busqueda_empleo')->nullable();

                // Ubicación Trabajo
                $table->string('trab_departamento', 80)->nullable();
                $table->string('trab_provincia', 80)->nullable();
                $table->string('trab_distrito', 80)->nullable();
                $table->string('trab_centro', 100)->nullable();
                $table->text('lugar_trabajo_declarado')->nullable();

                // Práctica docente e interculturalidad
                $table->text('practicas_que_permanecen')->nullable();
                $table->text('eib')->nullable();
                $table->text('quechua')->nullable();
                $table->text('comunidad')->nullable();
                $table->text('dificultades_practica')->nullable();
                $table->string('satisfaccion', 80)->nullable();

                // Formación continua y red
                $table->text('temas_interes')->nullable();
                $table->text('necesidades_apoyo')->nullable();
                $table->text('puedo_compartir')->nullable();
                $table->string('formato_preferido', 50)->nullable();
                $table->text('disponibilidad')->nullable();
                $table->string('desea_red', 30)->nullable();
                $table->string('compartir_perfil', 30)->nullable();

                // Vínculos
                $table->text('companeros_contacto')->nullable();
                $table->string('frecuencia_vinculo', 50)->nullable();
                $table->text('tipo_vinculo')->nullable();
                $table->string('encuentros_red', 50)->nullable();

                $table->json('datos_extra')->nullable();
                $table->timestamps();
            });
        }

        // 3. Episodios laborales para trazabilidad longitudinal
        if (!Schema::hasTable('egresado_episodios_laborales')) {
            Schema::create('egresado_episodios_laborales', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('identificador_temporal', 50)->nullable()->index();
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->string('institucion', 150);
                $table->string('cargo', 150)->nullable();
                $table->string('contrato', 80)->nullable();
                $table->boolean('es_empleo_educativo')->default(true);
                $table->boolean('es_docencia_directa')->default(true);
                $table->string('canal_registro', 40)->nullable();
                $table->timestamps();
            });
        }

        // 4. Casos de acompañamiento institucional ("Del hallazgo a la acción")
        if (!Schema::hasTable('egresado_casos')) {
            Schema::create('egresado_casos', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 30)->unique();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('nombre_egresado', 150);
                $table->string('tema', 100);
                $table->string('responsable', 100);
                $table->date('fecha_gestion')->nullable();
                $table->enum('estado', ['Por revisar', 'En atención', 'Cerrado'])->default('Por revisar');
                $table->text('proposito')->nullable();
                $table->string('origen', 80)->default('Actualización autónoma');
                $table->timestamps();
            });
        }

        // 5. Historial de llamadas para recuperación telefónica
        if (!Schema::hasTable('egresado_llamadas')) {
            Schema::create('egresado_llamadas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('identificador_temporal', 50)->nullable()->index();
                $table->string('resultado', 80);
                $table->text('observaciones')->nullable();
                $table->string('encuestador', 100)->nullable();
                $table->timestamp('fecha_hora')->useCurrent();
                $table->timestamps();
            });
        }

        // 6. Auditoría de acceso a datos de contacto
        if (!Schema::hasTable('egresado_accesos_contacto')) {
            Schema::create('egresado_accesos_contacto', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id_consultante')->nullable()->index();
                $table->string('rol_consultante', 50);
                $table->unsignedBigInteger('user_id_consultado')->nullable()->index();
                $table->string('identificador_consultado', 50);
                $table->string('finalidad', 150);
                $table->timestamp('fecha_hora')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresado_accesos_contacto');
        Schema::dropIfExists('egresado_llamadas');
        Schema::dropIfExists('egresado_casos');
        Schema::dropIfExists('egresado_episodios_laborales');
        Schema::dropIfExists('egresado_actualizaciones');
        Schema::dropIfExists('egresado_campanas');
    }
};
