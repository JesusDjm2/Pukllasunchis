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
        Schema::table('matriculas', function (Blueprint $table) {
            $table->boolean('voucher_verificado')->default(false)->after('comprobante');
            $table->timestamp('voucher_verificado_at')->nullable()->after('voucher_verificado');
            $table->timestamp('ficha_enviada_at')->nullable()->after('voucher_verificado_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn(['voucher_verificado', 'voucher_verificado_at', 'ficha_enviada_at']);
        });
    }
};
