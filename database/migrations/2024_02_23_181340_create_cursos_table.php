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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cc');
            $table->string('horas');
            $table->string('creditos');
            $table->foreignId('ciclo_id')->constrained();
            $table->string('silabo')->nullable();
            //Classroom
            $table->string('classroom')->nullable();
            $table->string('clave')->nullable();
            
            $table->foreignId('docente_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
        });
        Schema::dropIfExists('cursos');
    }
};
