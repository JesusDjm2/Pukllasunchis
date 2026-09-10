<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matriculas_ppd', function (Blueprint $table) {
            $table->string('voucher_imagen')->nullable()->after('comprobante');
        });
    }

    public function down(): void
    {
        Schema::table('matriculas_ppd', function (Blueprint $table) {
            $table->dropColumn('voucher_imagen');
        });
    }
};
