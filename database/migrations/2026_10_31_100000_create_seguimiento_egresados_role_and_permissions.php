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
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear tabla de trazabilidad y auditoría de cambios de rol institucional
        if (!Schema::hasTable('egresado_auditoria_roles')) {
            Schema::create('egresado_auditoria_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('Usuario afectado');
                $table->unsignedBigInteger('admin_id')->nullable()->comment('Super-admin o admin que autorizó el cambio');
                $table->string('rol', 50)->default('seguimiento-egresados');
                $table->string('accion', 20)->comment('asignado o revocado');
                $table->string('motivo', 255)->nullable();
                $table->string('ip', 45)->nullable();
                $table->json('roles_anteriores')->nullable();
                $table->json('roles_posteriores')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['user_id', 'rol']);
                $table->index(['admin_id', 'created_at']);
            });
        }

        // 2. Limpiar caché de Spatie Permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 3. Definición de los 10 permisos funcionales granulares del módulo
        $permisosModulo = [
            'seguimiento-egresados.ver-dashboard' => 'Ver entrada y dashboard de seguimiento de egresados',
            'seguimiento-egresados.ver-perfiles-autorizados' => 'Consultar perfiles de egresados asignados',
            'seguimiento-egresados.actualizar-perfiles-autorizados' => 'Actualizar datos y registrar historial laboral de egresados asignados',
            'seguimiento-egresados.registrar-entrevistas' => 'Registrar bitácora de llamadas y contactos de seguimiento',
            'seguimiento-egresados.gestionar-campanas' => 'Crear y configurar campañas institucionales de seguimiento',
            'seguimiento-egresados.gestionar-grupos' => 'Asignar egresados a entrevistadores y gestionar grupos',
            'seguimiento-egresados.ver-analitica' => 'Acceder al Laboratorio de Inteligencia y Observatorio Territorial',
            'seguimiento-egresados.exportar-analitica' => 'Exportar datos agregados y matrices cruzadas con secreto estadístico',
            'seguimiento-egresados.gestionar-casos' => 'Gestionar casos de atención prioritaria y derivaciones',
            'seguimiento-egresados.ver-contactos-autorizados' => 'Acceso auditado a datos de contacto de egresados asignados',
        ];

        foreach (array_keys($permisosModulo) as $permNombre) {
            Permission::firstOrCreate(['name' => $permNombre, 'guard_name' => 'web']);
        }

        // 4. Crear o recuperar el rol institucional `seguimiento-egresados`
        $rolSeguimiento = Role::firstOrCreate(['name' => 'seguimiento-egresados', 'guard_name' => 'web']);

        // 5. Asignar configuración de MÍNIMO PRIVILEGIO al rol operativo (entrevistadores/docentes)
        // Solo permisos operativos sobre perfiles expresamente autorizados
        $permisosOperativos = [
            'seguimiento-egresados.ver-dashboard',
            'seguimiento-egresados.ver-perfiles-autorizados',
            'seguimiento-egresados.actualizar-perfiles-autorizados',
            'seguimiento-egresados.registrar-entrevistas',
            'seguimiento-egresados.ver-contactos-autorizados',
        ];
        $rolSeguimiento->syncPermissions($permisosOperativos);

        // 6. Asignar todos los permisos a super-admin y coordinacion-empleabilidad
        if ($rolSuperAdmin = Role::where(['name' => 'super-admin', 'guard_name' => 'web'])->first()) {
            $rolSuperAdmin->givePermissionTo(array_keys($permisosModulo));
        }

        if ($rolAdmin = Role::where(['name' => 'admin', 'guard_name' => 'web'])->first()) {
            $rolAdmin->givePermissionTo(array_keys($permisosModulo));
        }

        if ($rolCoord = Role::where(['name' => 'coordinacion-empleabilidad', 'guard_name' => 'web'])->first()) {
            $rolCoord->givePermissionTo(array_keys($permisosModulo));
        }

        // 7. Volver a limpiar caché
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresado_auditoria_roles');

        $permisos = [
            'seguimiento-egresados.ver-dashboard',
            'seguimiento-egresados.ver-perfiles-autorizados',
            'seguimiento-egresados.actualizar-perfiles-autorizados',
            'seguimiento-egresados.registrar-entrevistas',
            'seguimiento-egresados.gestionar-campanas',
            'seguimiento-egresados.gestionar-grupos',
            'seguimiento-egresados.ver-analitica',
            'seguimiento-egresados.exportar-analitica',
            'seguimiento-egresados.gestionar-casos',
            'seguimiento-egresados.ver-contactos-autorizados',
        ];

        foreach ($permisos as $p) {
            Permission::where(['name' => $p, 'guard_name' => 'web'])->delete();
        }

        Role::where(['name' => 'seguimiento-egresados', 'guard_name' => 'web'])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
