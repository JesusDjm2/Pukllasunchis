<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->string('nombre_docente')->nullable()->after('docente_id');
            $table->unsignedBigInteger('docente_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn('nombre_docente');
            $table->unsignedBigInteger('docente_id')->nullable(false)->change();
        });
    }
};
