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
        Schema::create('rubricas', function (Blueprint $table) {
            $table->id();$table->foreignId('silabo_id')->constrained('silabos')->onDelete('cascade');
            $table->text('criterio')->nullable();
            $table->text('destacado')->nullable();
            $table->text('logrado')->nullable();
            $table->text('proceso')->nullable();
            $table->text('inicio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubricas');
    }
};
