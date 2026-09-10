<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->date('fecha_publicacion')->nullable()->after('imagen');
        });

        // Las ofertas existentes usan la fecha de creación como fecha de publicación inicial.
        DB::table('bolsa_trabajo_ofertas')
            ->whereNull('fecha_publicacion')
            ->update(['fecha_publicacion' => DB::raw('DATE(created_at)')]);

        Schema::table('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->dropColumn('vigente');
        });
    }

    public function down(): void
    {
        Schema::table('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->boolean('vigente')->default(true)->after('imagen');
        });

        Schema::table('bolsa_trabajo_ofertas', function (Blueprint $table) {
            $table->dropColumn('fecha_publicacion');
        });
    }
};
