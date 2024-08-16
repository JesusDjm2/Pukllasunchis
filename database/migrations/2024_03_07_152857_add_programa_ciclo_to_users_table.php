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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('programa_id')->nullable();
            $table->unsignedBigInteger('ciclo_id')->nullable();

            // Definir las claves foráneas
            $table->foreign('programa_id')->references('id')->on('programas')->onDelete('set null');
            $table->foreign('ciclo_id')->references('id')->on('ciclos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['programa_id']);
            $table->dropForeign(['ciclo_id']);
            $table->dropColumn(['programa_id', 'ciclo_id']);
        });
    }
};
