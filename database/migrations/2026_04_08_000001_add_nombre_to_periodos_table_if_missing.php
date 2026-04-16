<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('periodos') && ! Schema::hasColumn('periodos', 'nombre')) {
            Schema::table('periodos', function (Blueprint $table) {
                $table->string('nombre')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('periodos') && Schema::hasColumn('periodos', 'nombre')) {
            Schema::table('periodos', function (Blueprint $table) {
                $table->dropColumn('nombre');
            });
        }
    }
};
