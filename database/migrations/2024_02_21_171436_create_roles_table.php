<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $docente = Role::create(['name' => 'docente']);
        $alumno = Role::create(['name' => 'alumno']);
        $adminB = Role::create(['name' => 'adminB']);
        $alumnoB = Role::create(['name' => 'alumnoB']);
        $inhabilitado = Role::create(['name' => 'inhabilitado']);   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
