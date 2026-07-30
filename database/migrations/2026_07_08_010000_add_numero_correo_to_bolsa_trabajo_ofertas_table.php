<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bolsa_trabajo_ofertas', 'numero_correo')) {
            return;
        }

        Schema::table('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->string('numero_correo', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->dropColumn('numero_correo');
        });
    }
};
