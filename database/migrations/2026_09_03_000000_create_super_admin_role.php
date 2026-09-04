<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * El rol 'super-admin' se usa en routes/admin.php (middleware
     * role:admin|super-admin y role:super-admin) pero solo existía como
     * script SQL suelto en database/sql/, nunca como migración — por eso
     * una BD nueva rompía ese middleware con RoleDoesNotExist aunque el
     * usuario ya tuviera el rol 'admin'.
     */
    public function up(): void
    {
        if (! Role::where('name', 'super-admin')->where('guard_name', 'web')->exists()) {
            Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        }
    }

    public function down(): void
    {
        Role::where('name', 'super-admin')->where('guard_name', 'web')->delete();
    }
};
