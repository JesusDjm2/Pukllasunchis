<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Secretaría en Titulación: gestiona las RD y el pago del trámite; el resto lo ve en modo lectura
     * (ver AreasTitulacion). Combinable con los roles que ya tenga la cuenta, como «admin».
     */
    public function up(): void
    {
        Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        Role::where('name', 'secretaria')->where('guard_name', 'web')->delete();
    }
};
