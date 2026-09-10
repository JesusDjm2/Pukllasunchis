<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->after('imagen');
            $table->unsignedBigInteger('atendido_por')->nullable()->after('estado');
            $table->timestamp('atendido_at')->nullable()->after('atendido_por');
            $table->text('notas_atencion')->nullable()->after('atendido_at');

            $table->foreign('atendido_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropForeign(['atendido_por']);
            $table->dropColumn(['estado', 'atendido_por', 'atendido_at', 'notas_atencion']);
        });
    }
};
