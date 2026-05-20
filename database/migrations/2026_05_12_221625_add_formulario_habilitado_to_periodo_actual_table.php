<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('periodo_actual', function (Blueprint $table) {
            $table->boolean('formulario_habilitado')->default(false)->after('actual');
        });
    }

    public function down(): void
    {
        Schema::table('periodo_actual', function (Blueprint $table) {
            $table->dropColumn('formulario_habilitado');
        });
    }
};
