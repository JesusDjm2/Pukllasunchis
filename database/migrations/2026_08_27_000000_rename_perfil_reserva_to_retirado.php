<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('perfil', 'Reserva')->update(['perfil' => 'Retirado']);
    }

    public function down(): void
    {
        DB::table('users')->where('perfil', 'Retirado')->update(['perfil' => 'Reserva']);
    }
};
