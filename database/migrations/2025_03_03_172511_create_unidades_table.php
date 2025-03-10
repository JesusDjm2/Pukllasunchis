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
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('silabo_id')->constrained('silabos')->onDelete('cascade');
            $table->string('titulo');
            $table->text('situacion');
            $table->text('duracion');
            $table->text('desempeno');
            $table->text('ejes');
            $table->text('evidencia');
            $table->text('final');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
