<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración aditiva para la Mesa Docente de Proyectos Integradores Anuales en Ayllu Puklla.
     * Separa explícitamente:
     *  A. Definición y versión del proyecto curricular (inmutable en uso)
     *  B. Ejecución anual por año lectivo / período / cohorte
     *  C. Aporte de curso (114 posiciones documentales + correspondencia con catálogo cursos)
     *  D. Desarrollo y evidencia versionada con audiencia y protección de archivos
     *  E. Articulación y revisión entre cursos
     *  F. Síntesis comunitaria con revisión editorial institucional y auditoría de eventos
     */
    public function up(): void
    {
        if (!Schema::hasTable('ayllu_pi_lotes_importacion')) {
            Schema::create('ayllu_pi_lotes_importacion', function (Blueprint $table) {
                $table->id();
                $table->string('codigo_lote', 90)->unique();
                $table->string('version_manifiesto', 40)->default('2026.11.01.1');
                $table->string('estado', 30)->default('confirmado'); // vista_previa | confirmado | revertido
                $table->unsignedInteger('total_entradas')->default(0);
                $table->unsignedInteger('total_anomalias')->default(0);
                $table->json('archivos_fuente')->nullable();
                $table->json('resumen_conciliacion')->nullable();
                $table->foreignId('importado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('confirmado_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ayllu_pi_definiciones')) {
            Schema::create('ayllu_pi_definiciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('proyecto_institucional_id')->nullable()->index();
                $table->foreignId('lote_importacion_id')->nullable()->constrained('ayllu_pi_lotes_importacion')->nullOnDelete();
                $table->unsignedTinyInteger('anio_formativo')->index(); // 1..4
                $table->string('ciclos_texto', 20)->index(); // I-II, III-IV, V-VI, VII-VIII
                $table->unsignedSmallInteger('version_numero')->default(1);
                $table->string('codigo_fuente', 40)->index(); // matriz_excel | word_2020_2024 | word_copia_03 | revision_institucional
                $table->boolean('es_version_base_matriz')->default(false);
                $table->string('estado_aprobacion', 40)->default('documental_fuente'); // documental_fuente | en_revision | adoptada_ejecucion | archivada
                $table->text('denominacion_original');
                $table->string('denominacion_ciclo_par', 500)->nullable();
                $table->text('proposito_original');
                $table->string('ambito_exploracion', 500)->nullable();
                $table->text('fundamentacion_pedagogica')->nullable();
                $table->text('producto_integrador');
                $table->string('competencias_declaradas', 500)->nullable();
                $table->json('desempenos_especificos')->nullable();
                $table->json('hitos_documentados')->nullable(); // Solo poblado en Año 1
                $table->json('diferencias_documentales')->nullable();
                $table->string('archivo_origen', 255)->nullable();
                $table->string('referencia_ubicacion', 255)->nullable();
                $table->string('hash_contenido', 64)->index();
                $table->boolean('bloqueada_por_uso')->default(false);
                $table->foreignId('aprobado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('aprobado_at')->nullable();
                $table->timestamps();

                $table->unique(['anio_formativo', 'codigo_fuente', 'hash_contenido'], 'uq_ayllu_pi_def_anio_fuente_hash');
            });
        }

        if (!Schema::hasTable('ayllu_pi_ejecuciones')) {
            Schema::create('ayllu_pi_ejecuciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('definicion_id')->constrained('ayllu_pi_definiciones')->restrictOnDelete();
                $table->unsignedBigInteger('proyecto_institucional_id')->nullable()->index();
                $table->string('anio_lectivo', 20)->index(); // ej. 2026
                $table->unsignedBigInteger('periodo_actual_id')->nullable()->index();
                $table->string('cohorte', 120)->default('Cohorte 2026');
                $table->unsignedTinyInteger('anio_formativo')->index(); // 1..4
                $table->string('ciclo_impar_romano', 10); // I, III, V, VII
                $table->string('ciclo_par_romano', 10);   // II, IV, VI, VIII
                $table->string('estado', 30)->default('activa'); // planificacion | activa | cerrada
                $table->json('fechas_hitos_operativos')->nullable();
                $table->foreignId('coordinador_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('adoptado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('adoptado_at')->nullable();
                $table->timestamps();

                $table->unique(['anio_lectivo', 'anio_formativo', 'cohorte'], 'uq_ayllu_pi_ejec_lectivo_anio_cohorte');
            });
        }

        if (!Schema::hasTable('ayllu_pi_aportes_curso')) {
            Schema::create('ayllu_pi_aportes_curso', function (Blueprint $table) {
                $table->id();
                $table->foreignId('definicion_id')->constrained('ayllu_pi_definiciones')->cascadeOnDelete();
                $table->foreignId('ejecucion_id')->nullable()->constrained('ayllu_pi_ejecuciones')->nullOnDelete();
                $table->foreignId('lote_importacion_id')->nullable()->constrained('ayllu_pi_lotes_importacion')->nullOnDelete();
                $table->unsignedSmallInteger('posicion_global')->index(); // 1..114
                $table->unsignedSmallInteger('posicion_ciclo');
                $table->unsignedTinyInteger('anio_formativo')->index(); // 1..4
                $table->string('ciclo_romano', 10)->index(); // I..VIII
                $table->unsignedTinyInteger('ciclo_orden')->index(); // 1..8
                $table->string('agrupacion_programa_original', 80);
                $table->string('agrupacion_programa_codigo', 40)->index(); // FORMACION_GENERAL | INICIAL | PRIMARIA | PRIMARIA_EIB
                $table->boolean('es_aporte_comun')->default(false)->index();
                $table->text('curso_fuente_original');
                $table->string('curso_fuente_normalizado', 255)->index();
                $table->string('competencias_fuente_bruto', 255)->nullable();
                $table->string('competencias_fuente_mostrar', 255)->default('No consignado en la fuente');
                $table->string('competencias_formato_excel', 60)->nullable();
                $table->boolean('es_anomalia_competencia')->default(false)->index();
                $table->json('detalle_anomalia_competencia')->nullable();
                $table->text('estrategias_fuente_original')->nullable();
                $table->text('estrategias_mostrar');
                $table->text('producto_previsto_original')->nullable();
                $table->text('producto_previsto_mostrar');
                $table->text('producto_integrador_fuente')->nullable();
                $table->json('anotaciones_autor_historico')->nullable();
                $table->json('observaciones_calidad')->nullable();
                $table->string('archivo_fuente', 255);
                $table->string('hoja_fuente', 80)->default('Hoja 1');
                $table->string('celda_curso', 16);
                $table->string('celda_competencias', 16)->nullable();
                $table->string('celda_estrategias', 16)->nullable();
                $table->string('celda_producto', 16)->nullable();
                $table->string('celda_programa', 16)->nullable();
                $table->unsignedBigInteger('curso_institucional_id')->nullable()->index();
                $table->json('correspondencia_sugerida_json')->nullable();
                $table->string('estado_correspondencia', 40)->default('propuesta_pendiente'); // propuesta_pendiente | confirmada | sin_equivalente_catalogo
                $table->text('ajuste_operativo_mesa')->nullable();
                $table->string('estado_desarrollo', 45)->default('sin_registrar')->index(); // sin_registrar | en_desarrollo | para_revision_compartida | revisado | integrado_al_producto_anual
                $table->unsignedBigInteger('version_integrada_id')->nullable()->index();
                $table->foreignId('integrado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('integrado_at')->nullable();
                $table->text('nota_integracion_anual')->nullable();
                $table->timestamps();

                $table->unique(['definicion_id', 'ciclo_romano', 'celda_curso'], 'uq_ayllu_pi_aporte_def_ciclo_celda');
            });
        }

        if (!Schema::hasTable('ayllu_pi_asignaciones_mesa')) {
            Schema::create('ayllu_pi_asignaciones_mesa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ejecucion_id')->nullable()->constrained('ayllu_pi_ejecuciones')->cascadeOnDelete();
                $table->foreignId('aporte_curso_id')->nullable()->constrained('ayllu_pi_aportes_curso')->cascadeOnDelete();
                $table->unsignedBigInteger('curso_id')->nullable()->index();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('docente_id')->nullable()->index();
                $table->string('rol_mesa', 45)->index(); // docente_responsable_aporte | coordinador_ciclo | coordinador_proyecto | acompanante_equipo
                $table->string('programa_codigo', 40)->default('TODOS');
                $table->string('ciclo_romano', 10)->index();
                $table->boolean('activo')->default(true)->index();
                $table->foreignId('asignado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('revocado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('revocado_at')->nullable();
                $table->string('motivo_revocacion', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ayllu_pi_evidencia_versiones')) {
            Schema::create('ayllu_pi_evidencia_versiones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aporte_curso_id')->constrained('ayllu_pi_aportes_curso')->cascadeOnDelete();
                $table->foreignId('ejecucion_id')->nullable()->constrained('ayllu_pi_ejecuciones')->nullOnDelete();
                $table->unsignedBigInteger('curso_id')->nullable()->index();
                $table->unsignedBigInteger('taqe_grupo_id')->nullable()->index();
                $table->unsignedSmallInteger('numero_version')->default(1);
                $table->string('titulo_version', 220);
                $table->text('descripcion_avance');
                $table->string('autoria_tipo', 40)->default('docente'); // docente | equipo_estudiantes | estudiante_individual | mesa_ciclo
                $table->foreignId('autor_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('docente_acompanante_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('audiencia', 40)->default('mesa_docente_ciclo')->index(); // borrador_personal | mesa_docente_ciclo | equipo_estudiantes | ciclo_completo | comunidad_ayllu
                $table->json('colaboradores_autorizados_json')->nullable();
                $table->string('enlace_recurso', 500)->nullable();
                $table->string('archivo_privado_path', 500)->nullable();
                $table->string('archivo_nombre_original', 255)->nullable();
                $table->string('archivo_mime', 120)->nullable();
                $table->text('necesidades_articulacion')->nullable();
                $table->text('reconocimiento_autoria_comunitaria')->nullable();
                $table->string('restriccion_circulacion', 45)->default('ninguna'); // ninguna | uso_interno_ciclo | reserva_comunitaria | autorizacion_retirada
                $table->string('estado_version', 45)->default('en_desarrollo')->index(); // en_desarrollo | para_revision_compartida | revisado | devuelto_a_desarrollo | integrado_al_producto_anual
                $table->foreignId('revisado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('revisado_at')->nullable();
                $table->text('comentario_revision')->nullable();
                $table->json('aportes_vinculados_json')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ayllu_pi_articulaciones')) {
            Schema::create('ayllu_pi_articulaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ejecucion_id')->nullable()->constrained('ayllu_pi_ejecuciones')->cascadeOnDelete();
                $table->foreignId('aporte_origen_id')->constrained('ayllu_pi_aportes_curso')->cascadeOnDelete();
                $table->foreignId('aporte_destino_id')->constrained('ayllu_pi_aportes_curso')->cascadeOnDelete();
                $table->string('insumo_requerido', 255);
                $table->text('criterio_articulacion')->nullable();
                $table->date('fecha_acordada')->nullable();
                $table->string('estado_revision', 40)->default('pendiente'); // pendiente | entregado_en_revision | validado | observado
                $table->foreignId('acordado_por_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('validado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('validado_at')->nullable();
                $table->text('observaciones_revision')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ayllu_pi_bitacora_mesa')) {
            Schema::create('ayllu_pi_bitacora_mesa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ejecucion_id')->nullable()->constrained('ayllu_pi_ejecuciones')->cascadeOnDelete();
                $table->foreignId('aporte_curso_id')->nullable()->constrained('ayllu_pi_aportes_curso')->nullOnDelete();
                $table->foreignId('evidencia_version_id')->nullable()->constrained('ayllu_pi_evidencia_versiones')->nullOnDelete();
                $table->string('ciclo_romano', 10)->index();
                $table->string('programa_codigo', 40)->nullable()->index();
                $table->string('tipo_entrada', 40)->default('avance'); // avance | dificultad | acuerdo_mesa | devolucion_formativa | observacion_calidad
                $table->string('audiencia', 40)->default('mesa_docente_ciclo')->index(); // borrador_personal | mesa_docente_ciclo | equipo_estudiantes | ciclo_completo | comunidad_ayllu
                $table->string('titulo', 220);
                $table->text('contenido');
                $table->foreignId('autor_user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('taqe_grupo_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ayllu_pi_sintesis_comunitarias')) {
            Schema::create('ayllu_pi_sintesis_comunitarias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ejecucion_id')->nullable()->constrained('ayllu_pi_ejecuciones')->nullOnDelete();
                $table->foreignId('aporte_curso_id')->nullable()->constrained('ayllu_pi_aportes_curso')->nullOnDelete();
                $table->foreignId('evidencia_version_id')->nullable()->constrained('ayllu_pi_evidencia_versiones')->nullOnDelete();
                $table->string('ciclo_romano', 10)->index();
                $table->string('programa_codigo', 40)->default('TODOS');
                $table->string('titulo', 220);
                $table->text('resumen_sintesis');
                $table->string('autoria_reconocida', 255);
                $table->string('audiencia_objetivo', 40)->default('ciclo_completo'); // ciclo_completo | comunidad_ayllu
                $table->string('estado_editorial', 45)->default('pendiente_revision_editorial')->index(); // borrador | pendiente_revision_editorial | aprobada_publicada | rechazada | retirada
                $table->foreignId('solicitado_por_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('revisado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('revisado_at')->nullable();
                $table->text('observacion_editorial')->nullable();
                $table->unsignedBigInteger('ayllu_publicacion_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ayllu_pi_auditoria_eventos')) {
            Schema::create('ayllu_pi_auditoria_eventos', function (Blueprint $table) {
                $table->id();
                $table->string('accion', 80)->index();
                $table->string('entidad_tipo', 80)->index();
                $table->unsignedBigInteger('entidad_id')->nullable()->index();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ciclo_romano', 10)->nullable();
                $table->string('programa_codigo', 40)->nullable();
                $table->json('metadatos')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException(
                'Operación bloqueada: No se permite revertir las tablas de Mesa Docente de Proyectos Integradores en producción.'
            );
        }

        $tablas = [
            'ayllu_pi_auditoria_eventos',
            'ayllu_pi_sintesis_comunitarias',
            'ayllu_pi_bitacora_mesa',
            'ayllu_pi_articulaciones',
            'ayllu_pi_evidencia_versiones',
            'ayllu_pi_asignaciones_mesa',
            'ayllu_pi_aportes_curso',
            'ayllu_pi_ejecuciones',
            'ayllu_pi_definiciones',
            'ayllu_pi_lotes_importacion',
        ];

        $permitirDestructivo = filter_var(env('AYLLU_ALLOW_DESTRUCTIVE_ROLLBACK', false), FILTER_VALIDATE_BOOLEAN);
        if (!$permitirDestructivo) {
            foreach ($tablas as $tabla) {
                if (Schema::hasTable($tabla) && DB::table($tabla)->count() > 0) {
                    throw new \RuntimeException(
                        "Operación bloqueada por seguridad de datos: La tabla [{$tabla}] contiene registros. "
                        . "Defina AYLLU_ALLOW_DESTRUCTIVE_ROLLBACK=true si desea forzar el rollback en desarrollo."
                    );
                }
            }
        }

        foreach ($tablas as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
