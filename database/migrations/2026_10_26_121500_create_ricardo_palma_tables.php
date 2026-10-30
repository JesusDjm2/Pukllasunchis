<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo aislado: IESP Ricardo Palma (Actas Consolidadas Históricas 1995-2015).
     * No modifica ni vincula tablas operativas de FID ni PPD.
     */
    public function up(): void
    {
        // 1. Programas / Especialidades de Ricardo Palma
        Schema::create('rp_programas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 150);
            $table->string('nivel', 60)->default('Superior Pedagógico');
            $table->unsignedTinyInteger('total_semestres')->default(10);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 2. Periodos Académicos Históricos (1995 - 2015)
        Schema::create('rp_periodos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique(); // Ej: '1996-II', '2006-I'
            $table->unsignedSmallInteger('anio');   // Ej: 1996
            $table->string('semestre', 10);        // Ej: 'I', 'II'
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['anio', 'semestre']);
        });

        // 3. Malla de Cursos / Asignaturas por Programa y Semestre
        Schema::create('rp_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained('rp_programas')->onDelete('cascade');
            $table->unsignedTinyInteger('numero_semestre'); // 1 al 10
            $table->string('codigo', 30)->nullable();
            $table->string('nombre', 200);
            $table->unsignedTinyInteger('creditos')->default(2);
            $table->unsignedTinyInteger('horas')->nullable();
            $table->timestamps();

            $table->index(['programa_id', 'numero_semestre']);
        });

        // 4. Estudiantes Históricos de Ricardo Palma
        Schema::create('rp_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->nullable()->constrained('rp_programas')->onDelete('set null');
            $table->string('numero_matricula', 50)->nullable()->index(); // Clave principal de acta física
            $table->string('dni', 15)->nullable()->index();
            $table->string('apellidos', 150);
            $table->string('nombres', 150);
            $table->char('sexo', 1)->nullable(); // 'M' o 'F'
            $table->string('telefono', 50)->nullable();
            $table->unsignedSmallInteger('anio_ingreso')->nullable();
            $table->string('estado', 50)->default('REGULAR'); // REGULAR, RETIRADO, EGRESADO, TRASLADADO
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // 5. Actas Consolidadas (Cabecera del Documento Físico)
        Schema::create('rp_actas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('rp_periodos')->onDelete('restrict');
            $table->foreignId('programa_id')->constrained('rp_programas')->onDelete('restrict');
            $table->unsignedTinyInteger('numero_semestre'); // 1 al 10
            $table->string('numero_acta', 50)->nullable(); // Ej: '002906'
            $table->string('seccion', 30)->default('UNICA');
            $table->string('turno', 30)->default('TARDE');
            $table->date('fecha_emision')->nullable();
            $table->unsignedTinyInteger('total_creditos')->nullable();
            $table->string('archivo_escaneo_path', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(
                ['periodo_id', 'programa_id', 'numero_semestre', 'seccion', 'turno'],
                'rp_actas_identificador_unico'
            );
        });

        // 6. Calificaciones Detalladas por Acta, Estudiante y Curso
        Schema::create('rp_calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('rp_actas')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('rp_estudiantes')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('rp_cursos')->onDelete('cascade');
            $table->decimal('calificacion', 4, 2)->nullable(); // CLF: 0.00 a 20.00
            $table->decimal('puntaje', 6, 2)->nullable();      // PJT: CLF * Créditos
            $table->string('observacion', 50)->nullable();     // 'APROBADO', 'DESAPROBADO', 'RETIRADO', 'LICENCIA'
            $table->timestamps();

            $table->unique(['acta_id', 'estudiante_id', 'curso_id'], 'rp_calif_acta_est_curso_unico');
            $table->index(['estudiante_id', 'curso_id']);
        });

        // 7. Registro de Auditoría de Cargas Masivas
        Schema::create('rp_importaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('tipo', 50); // 'ACTA_CONSOLIDADA', 'PADRON_ESTUDIANTES'
            $table->string('archivo_nombre', 255);
            $table->string('archivo_path', 255)->nullable();
            $table->foreignId('periodo_id')->nullable()->constrained('rp_periodos')->onDelete('set null');
            $table->foreignId('programa_id')->nullable()->constrained('rp_programas')->onDelete('set null');
            $table->unsignedTinyInteger('numero_semestre')->nullable();
            $table->unsignedInteger('total_filas')->default(0);
            $table->unsignedInteger('filas_exitosas')->default(0);
            $table->unsignedInteger('filas_con_error')->default(0);
            $table->json('detalles_errores')->nullable();
            $table->string('estado', 30)->default('COMPLETADO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rp_importaciones');
        Schema::dropIfExists('rp_calificaciones');
        Schema::dropIfExists('rp_actas');
        Schema::dropIfExists('rp_estudiantes');
        Schema::dropIfExists('rp_cursos');
        Schema::dropIfExists('rp_periodos');
        Schema::dropIfExists('rp_programas');
    }
};
