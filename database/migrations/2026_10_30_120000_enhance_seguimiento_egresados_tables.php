<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Mejoras aditivas para egresado_episodios_laborales
        if (Schema::hasTable('egresado_episodios_laborales')) {
            Schema::table('egresado_episodios_laborales', function (Blueprint $table) {
                if (!Schema::hasColumn('egresado_episodios_laborales', 'es_actual')) {
                    $table->boolean('es_actual')->default(true)->nullable()->after('contrato');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'sector')) {
                    $table->string('sector', 80)->nullable()->after('es_actual');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'trab_departamento')) {
                    $table->string('trab_departamento', 80)->nullable()->after('sector');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'trab_provincia')) {
                    $table->string('trab_provincia', 80)->nullable()->after('trab_departamento');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'trab_distrito')) {
                    $table->string('trab_distrito', 80)->nullable()->after('trab_provincia');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'registrado_por')) {
                    $table->string('registrado_por', 100)->nullable()->after('canal_registro');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'fecha_captura')) {
                    $table->timestamp('fecha_captura')->nullable()->after('registrado_por');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'dedicacion')) {
                    $table->string('dedicacion', 50)->nullable()->after('fecha_captura');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'motivo_cambio')) {
                    $table->string('motivo_cambio', 150)->nullable()->after('dedicacion');
                }
                if (!Schema::hasColumn('egresado_episodios_laborales', 'notas')) {
                    $table->text('notas')->nullable()->after('motivo_cambio');
                }
            });
        }

        // 2. Mejoras aditivas para egresado_actualizaciones
        if (Schema::hasTable('egresado_actualizaciones')) {
            Schema::table('egresado_actualizaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('egresado_actualizaciones', 'fecha_ultima_confirmacion')) {
                    $table->timestamp('fecha_ultima_confirmacion')->nullable()->after('registrado_por');
                }
                if (!Schema::hasColumn('egresado_actualizaciones', 'es_confirmacion_sin_cambios')) {
                    $table->boolean('es_confirmacion_sin_cambios')->default(false)->after('fecha_ultima_confirmacion');
                }
                if (!Schema::hasColumn('egresado_actualizaciones', 'periodo_campana')) {
                    $table->string('periodo_campana', 30)->nullable()->after('campana_id');
                }
            });
        }

        // 3. Registro formal de roles Spatie de Puklla en Red (si no existen)
        if (Schema::hasTable('roles')) {
            $nuevosRoles = [
                'egresado',
                'encuestador-seguimiento',
                'coordinacion-empleabilidad',
                'bienestar',
                'formacion-continua'
            ];

            foreach ($nuevosRoles as $rol) {
                Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('egresado_actualizaciones')) {
            Schema::table('egresado_actualizaciones', function (Blueprint $table) {
                $columns = ['fecha_ultima_confirmacion', 'es_confirmacion_sin_cambios', 'periodo_campana'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('egresado_actualizaciones', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('egresado_episodios_laborales')) {
            Schema::table('egresado_episodios_laborales', function (Blueprint $table) {
                $columns = [
                    'es_actual', 'sector', 'trab_departamento', 'trab_provincia',
                    'trab_distrito', 'registrado_por', 'fecha_captura',
                    'dedicacion', 'motivo_cambio', 'notas'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('egresado_episodios_laborales', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
