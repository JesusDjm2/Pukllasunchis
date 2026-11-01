<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Migración 100% aditiva y reversible para el MVP de Ayllu Puklla.
     * No modifica ni elimina ninguna tabla ni columna preexistente.
     */
    public function up(): void
    {
        // 1. Perfil intercultural y comunitario (extensión 1:1 de users)
        if (!Schema::hasTable('ayllu_perfiles')) {
            Schema::create('ayllu_perfiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->string('saludo_intercultural', 180)->nullable();
                $table->text('biografia')->nullable();
                $table->json('saberes_intereses')->nullable();
                $table->string('lengua_preferida', 40)->default('castellano_quechua');
                $table->boolean('mostrar_portafolio_comunidad')->default(true);
                $table->timestamps();
            });
        }

        // 2. Grupos / Ayllus temáticos e interculturales
        if (!Schema::hasTable('ayllu_grupos')) {
            Schema::create('ayllu_grupos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->string('nombre_quechua', 150)->nullable();
                $table->string('slug', 160)->unique();
                $table->text('descripcion')->nullable();
                $table->string('tipo', 40)->default('intercultural'); // intercultural, taller, circulo_estudio, investigacion, tutoria
                $table->string('icono', 60)->default('fas fa-users');
                $table->string('color', 30)->default('#cd9244');
                $table->foreignId('programa_id')->nullable()->constrained('programas')->nullOnDelete();
                $table->foreignId('ciclo_id')->nullable()->constrained('ciclos')->nullOnDelete();
                $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('es_oficial')->default(true);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        // 3. Miembros de grupos Ayllu
        if (!Schema::hasTable('ayllu_grupo_miembros')) {
            Schema::create('ayllu_grupo_miembros', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ayllu_grupo_id')->constrained('ayllu_grupos')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('rol', 30)->default('miembro'); // moderador, miembro
                $table->timestamps();

                $table->unique(['ayllu_grupo_id', 'user_id'], 'uq_ayllu_grupo_user');
            });
        }

        // 4. Publicaciones comunitarias y académicas (Plano 1: Social / Comunitario)
        if (!Schema::hasTable('ayllu_publicaciones')) {
            Schema::create('ayllu_publicaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('ambito', 30)->default('institucional'); // institucional, programa, ciclo, curso, grupo
                $table->foreignId('programa_id')->nullable()->constrained('programas')->nullOnDelete();
                $table->foreignId('ciclo_id')->nullable()->constrained('ciclos')->nullOnDelete();
                $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
                $table->foreignId('ayllu_grupo_id')->nullable()->constrained('ayllu_grupos')->nullOnDelete();
                $table->string('tipo', 40)->default('reflexion'); // reflexion, recurso, anuncio, pregunta, practica_eib, minka
                $table->string('titulo', 180)->nullable();
                $table->text('contenido');
                $table->text('resumen_quechua')->nullable();
                $table->json('etiquetas')->nullable();
                $table->string('enlace_externo', 500)->nullable();
                $table->string('enlace_titulo', 180)->nullable();
                $table->boolean('fijado')->default(false);
                $table->boolean('destacado_inicio')->default(false);
                $table->boolean('visible')->default(true);
                $table->timestamps();

                $table->index(['ambito', 'visible', 'created_at'], 'idx_ayllu_pub_ambito');
                $table->index(['curso_id', 'created_at'], 'idx_ayllu_pub_curso');
                $table->index(['ciclo_id', 'created_at'], 'idx_ayllu_pub_ciclo');
                $table->index(['ayllu_grupo_id', 'created_at'], 'idx_ayllu_pub_grupo');
            });
        }

        // 5. Comentarios de publicaciones
        if (!Schema::hasTable('ayllu_comentarios')) {
            Schema::create('ayllu_comentarios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ayllu_publicacion_id')->constrained('ayllu_publicaciones')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('parent_id')->nullable()->constrained('ayllu_comentarios')->onDelete('cascade');
                $table->text('contenido');
                $table->boolean('es_aporte_docente')->default(false);
                $table->timestamps();

                $table->index(['ayllu_publicacion_id', 'created_at'], 'idx_ayllu_com_pub');
            });
        }

        // 6. Reacciones comunitarias (Allinmi, Ayni, Yachay, Kallpa)
        if (!Schema::hasTable('ayllu_reacciones')) {
            Schema::create('ayllu_reacciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ayllu_publicacion_id')->constrained('ayllu_publicaciones')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('tipo', 30)->default('allinmi'); // allinmi, ayni, yachay, kallpa
                $table->timestamps();

                $table->unique(['ayllu_publicacion_id', 'user_id'], 'uq_ayllu_reaccion_user');
            });
        }

        // 7. Entregas y Evidencias Formativas (Plano 2: Evaluación Formativa — separada de notas oficiales)
        if (!Schema::hasTable('ayllu_entregas_formativas')) {
            Schema::create('ayllu_entregas_formativas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
                $table->foreignId('silabo_id')->nullable()->constrained('silabos')->nullOnDelete();
                $table->foreignId('unidad_id')->nullable()->constrained('unidades')->nullOnDelete();
                $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
                $table->foreignId('taqe_grupo_id')->nullable()->constrained('taqe_grupos')->nullOnDelete();
                $table->foreignId('taqe_trabajo_id')->nullable()->constrained('taqe_trabajos')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('titulo', 200);
                $table->text('descripcion_reflexion');
                $table->string('enlace_evidencia', 500)->nullable();
                $table->string('autoevaluacion_nivel', 40)->nullable(); // en_inicio, en_proceso, logrado, destacado
                $table->text('autoevaluacion_comentario')->nullable();
                $table->boolean('es_aporte_proyecto_integrador')->default(false);
                $table->boolean('en_portafolio')->default(false);
                $table->text('reflexion_portafolio')->nullable();
                $table->string('estado', 30)->default('enviado'); // borrador, enviado, retroalimentado, vinculado_taqe
                $table->timestamps();

                $table->index(['curso_id', 'user_id'], 'idx_ayllu_ent_curso_user');
                $table->index(['user_id', 'en_portafolio'], 'idx_ayllu_ent_portafolio');
            });
        }

        // 8. Retroalimentaciones Formativas (Diálogo pedagógico docente-estudiante / coevaluación)
        if (!Schema::hasTable('ayllu_retroalimentaciones')) {
            Schema::create('ayllu_retroalimentaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ayllu_entrega_formativa_id')->constrained('ayllu_entregas_formativas')->onDelete('cascade');
                $table->foreignId('autor_user_id')->constrained('users')->onDelete('cascade');
                $table->string('tipo', 30)->default('docente'); // docente, coevaluacion, respuesta_estudiante
                $table->string('valoracion_formativa', 40)->nullable(); // en_inicio, en_proceso, logrado, destacado
                $table->text('fortalezas')->nullable();
                $table->text('sugerencias_mejora')->nullable();
                $table->text('comentario_general');
                $table->json('criterios_rubrica')->nullable();
                $table->timestamps();

                $table->index(['ayllu_entrega_formativa_id', 'created_at'], 'idx_ayllu_retro_entrega');
            });
        }

        // 9. Eventos adicionales del Calendario Vivo Comunitario/Académico
        if (!Schema::hasTable('ayllu_eventos_calendario')) {
            Schema::create('ayllu_eventos_calendario', function (Blueprint $table) {
                $table->id();
                $table->string('titulo', 180);
                $table->text('descripcion')->nullable();
                $table->string('tipo', 40)->default('comunitario'); // academico, comunitario, entrega_formativa, taqe, tutoria
                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable();
                $table->string('hora', 20)->nullable();
                $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
                $table->foreignId('ciclo_id')->nullable()->constrained('ciclos')->nullOnDelete();
                $table->string('enlace', 500)->nullable();
                $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['fecha_inicio', 'tipo'], 'idx_ayllu_cal_fecha');
            });
        }

        // 10. Mensajes del Chat Académico y Comunitario
        if (!Schema::hasTable('ayllu_mensajes_chat')) {
            Schema::create('ayllu_mensajes_chat', function (Blueprint $table) {
                $table->id();
                $table->string('canal_tipo', 30)->default('curso'); // curso, grupo, directo
                $table->foreignId('curso_id')->nullable()->constrained('cursos')->onDelete('cascade');
                $table->foreignId('ayllu_grupo_id')->nullable()->constrained('ayllu_grupos')->onDelete('cascade');
                $table->foreignId('emisor_user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('receptor_user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->text('mensaje');
                $table->timestamp('leido_at')->nullable();
                $table->timestamps();

                $table->index(['canal_tipo', 'curso_id', 'created_at'], 'idx_ayllu_chat_curso');
                $table->index(['canal_tipo', 'ayllu_grupo_id', 'created_at'], 'idx_ayllu_chat_grupo');
                $table->index(['emisor_user_id', 'receptor_user_id', 'created_at'], 'idx_ayllu_chat_directo');
            });
        }

        // 11. Permisos Spatie granulares para Ayllu Puklla
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisosAyllu = [
            'ayllu.ver-inicio',
            'ayllu.publicar-muro-general',
            'ayllu.publicar-comunidad',
            'ayllu.moderar',
            'ayllu.retroalimentar-formativo',
        ];

        foreach ($permisosAyllu as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Roles institucionales que publican en el muro general y moderan
        $rolesMuroGeneral = [
            'super-admin',
            'admin',
            'adminB',
            'docente',
            'tutor',
            'investigador',
            'gestor-titulacion',
            'coordinacion-empleabilidad',
            'direccion',
        ];

        foreach ($rolesMuroGeneral as $rName) {
            if ($rol = Role::where(['name' => $rName, 'guard_name' => 'web'])->first()) {
                $rol->givePermissionTo($permisosAyllu);
            }
        }

        // Roles estudiantiles: acceden a Inicio y publican libremente en sus Comunidades de Curso, Ciclo y Grupos
        $rolesEstudiantes = ['alumno', 'alumnoB', 'seguimiento-egresados'];
        foreach ($rolesEstudiantes as $rName) {
            if ($rol = Role::where(['name' => $rName, 'guard_name' => 'web'])->first()) {
                $rol->givePermissionTo(['ayllu.ver-inicio', 'ayllu.publicar-comunidad']);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Rollback limpio y no destructivo sobre el núcleo existente.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayllu_mensajes_chat');
        Schema::dropIfExists('ayllu_eventos_calendario');
        Schema::dropIfExists('ayllu_retroalimentaciones');
        Schema::dropIfExists('ayllu_entregas_formativas');
        Schema::dropIfExists('ayllu_reacciones');
        Schema::dropIfExists('ayllu_comentarios');
        Schema::dropIfExists('ayllu_publicaciones');
        Schema::dropIfExists('ayllu_grupo_miembros');
        Schema::dropIfExists('ayllu_grupos');
        Schema::dropIfExists('ayllu_perfiles');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permisosAyllu = [
            'ayllu.ver-inicio',
            'ayllu.publicar-muro-general',
            'ayllu.publicar-comunidad',
            'ayllu.moderar',
            'ayllu.retroalimentar-formativo',
        ];
        foreach ($permisosAyllu as $permName) {
            Permission::where(['name' => $permName, 'guard_name' => 'web'])->delete();
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
