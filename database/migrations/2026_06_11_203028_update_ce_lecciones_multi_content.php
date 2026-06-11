<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ce_lecciones', function (Blueprint $table) {
            $table->text('video_url')->nullable()->after('archivo_url');
            $table->string('tipo')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ce_lecciones', function (Blueprint $table) {
            $table->dropColumn('video_url');
            $table->string('tipo')->nullable(false)->change();
        });
    }
};
