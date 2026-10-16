<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Nuevo rol para el módulo TAKE (Ferias de Investigación): se asigna
     * sobre una cuenta que ya tiene el rol "docente", igual que hoy conviven
     * "docente" y "tutor" en una misma cuenta.
     */
    public function up(): void
    {
        Role::firstOrCreate(['name' => 'investigador', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        Role::where('name', 'investigador')->where('guard_name', 'web')->delete();
    }
};
