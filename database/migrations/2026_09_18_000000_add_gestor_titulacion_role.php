<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Nuevo rol para el módulo de Titulación (seguimiento de PTI, tesis,
     * actas, jurados y resoluciones de egresados). Se asigna igual que
     * "docente"/"tutor"/"investigador": combinable con los roles que ya
     * tenga la cuenta, no los reemplaza (ver 2026_09_10_000000_add_investigador_role.php).
     */
    public function up(): void
    {
        Role::firstOrCreate(['name' => 'gestor-titulacion', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        Role::where('name', 'gestor-titulacion')->where('guard_name', 'web')->delete();
    }
};
