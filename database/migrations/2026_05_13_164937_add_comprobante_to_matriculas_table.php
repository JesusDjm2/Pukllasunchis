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
        if (Schema::hasColumn('matriculas', 'comprobante')) {
            return;
        }

        Schema::table('matriculas', function (Blueprint $table) {
            $table->string('comprobante')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('matriculas', 'comprobante')) {
            return;
        }

        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn('comprobante');
        });
    }
};
