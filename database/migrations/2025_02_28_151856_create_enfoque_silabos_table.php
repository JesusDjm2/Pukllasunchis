<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enfoque_silabos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('silabo_id')->constrained()->onDelete('cascade'); 
            $table->text('descripcion');
            $table->text('enfoque_observables');
            $table->text('silabo_concretas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enfoque_silabos');
    }
};
