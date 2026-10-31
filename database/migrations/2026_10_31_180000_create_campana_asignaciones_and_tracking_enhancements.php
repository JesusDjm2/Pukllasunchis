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
        // 1. Crear tabla de asignaciones individuales equilibradas por campaña
        if (!Schema::hasTable('egresado_campana_asignaciones')) {
            Schema::create('egresado_campana_asignaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campana_id')->constrained('egresado_campanas')->onDelete('cascade');
                $table->foreignId('docente_user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('egresado_user_id')->constrained('users')->onDelete('cascade');
                $table->string('estado', 40)->default('asignado'); // asignado, contactado, completado, reasignado, revocado
                $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('fecha_asignacion')->useCurrent();
                $table->foreignId('docente_anterior_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('motivo_reasignacion')->nullable();
                $table->boolean('es_ensayo')->default(false);
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['campana_id', 'docente_user_id'], 'idx_camp_docente');
                $table->index(['campana_id', 'egresado_user_id'], 'idx_camp_egresado');
                $table->index(['estado'], 'idx_asig_estado');
            });
        }

        // 2. Campo es_ensayo en egresado_campanas
        if (Schema::hasTable('egresado_campanas')) {
            Schema::table('egresado_campanas', function (Blueprint $table) {
                if (!Schema::hasColumn('egresado_campanas', 'es_ensayo')) {
                    $table->boolean('es_ensayo')->default(false)->after('descripcion');
                }
            });
        }

        // 3. Mejoras aditivas para registro granular de llamadas de seguimiento
        if (Schema::hasTable('egresado_llamadas')) {
            Schema::table('egresado_llamadas', function (Blueprint $table) {
                if (!Schema::hasColumn('egresado_llamadas', 'campana_id')) {
                    $table->foreignId('campana_id')->nullable()->after('user_id')->constrained('egresado_campanas')->nullOnDelete();
                }
                if (!Schema::hasColumn('egresado_llamadas', 'docente_user_id')) {
                    $table->foreignId('docente_user_id')->nullable()->after('campana_id')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('egresado_llamadas', 'tipo_resultado')) {
                    $table->string('tipo_resultado', 60)->nullable()->after('resultado');
                }
                if (!Schema::hasColumn('egresado_llamadas', 'es_contacto_efectivo')) {
                    $table->boolean('es_contacto_efectivo')->default(false)->after('tipo_resultado');
                }
                if (!Schema::hasColumn('egresado_llamadas', 'fecha_reprogramada')) {
                    $table->timestamp('fecha_reprogramada')->nullable()->after('es_contacto_efectivo');
                }
                if (!Schema::hasColumn('egresado_llamadas', 'requiere_derivacion')) {
                    $table->boolean('requiere_derivacion')->default(false)->after('fecha_reprogramada');
                }
                if (!Schema::hasColumn('egresado_llamadas', 'motivo_derivacion')) {
                    $table->text('motivo_derivacion')->nullable()->after('requiere_derivacion');
                }
                if (!Schema::hasColumn('egresado_llamadas', 'es_ensayo')) {
                    $table->boolean('es_ensayo')->default(false)->after('motivo_derivacion');
                }
            });
        }

        // 4. Mejoras aditivas para egresado_actualizaciones (campo es_ensayo)
        if (Schema::hasTable('egresado_actualizaciones')) {
            Schema::table('egresado_actualizaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('egresado_actualizaciones', 'es_ensayo')) {
                    $table->boolean('es_ensayo')->default(false)->after('datos_extra');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('egresado_actualizaciones')) {
            Schema::table('egresado_actualizaciones', function (Blueprint $table) {
                if (Schema::hasColumn('egresado_actualizaciones', 'es_ensayo')) {
                    $table->dropColumn('es_ensayo');
                }
            });
        }

        if (Schema::hasTable('egresado_llamadas')) {
            Schema::table('egresado_llamadas', function (Blueprint $table) {
                $cols = ['es_ensayo', 'motivo_derivacion', 'requiere_derivacion', 'fecha_reprogramada', 'es_contacto_efectivo', 'tipo_resultado', 'docente_user_id', 'campana_id'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('egresado_llamadas', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('egresado_campanas')) {
            Schema::table('egresado_campanas', function (Blueprint $table) {
                if (Schema::hasColumn('egresado_campanas', 'es_ensayo')) {
                    $table->dropColumn('es_ensayo');
                }
            });
        }

        Schema::dropIfExists('egresado_campana_asignaciones');
    }
};
