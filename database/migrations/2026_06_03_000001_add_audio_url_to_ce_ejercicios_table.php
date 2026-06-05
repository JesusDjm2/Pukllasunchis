<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ce_ejercicios', function (Blueprint $table) {
            $table->string('audio_url', 500)->nullable()->after('respuesta_correcta');
        });
    }

    public function down(): void
    {
        Schema::table('ce_ejercicios', function (Blueprint $table) {
            $table->dropColumn('audio_url');
        });
    }
};
