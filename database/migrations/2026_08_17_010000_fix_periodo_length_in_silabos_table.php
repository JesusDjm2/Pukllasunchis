<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('silabos', function (Blueprint $table) {
            $table->string('periodo', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('silabos', function (Blueprint $table) {
            $table->string('periodo', 10)->change();
        });
    }
};
