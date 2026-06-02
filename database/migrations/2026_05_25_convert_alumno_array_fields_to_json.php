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
        Schema::table('alumnos', function (Blueprint $table) {
            // Convertir campos de string a JSON
            // Nota: Si la columna no existe, se crea como JSON
            // Si existe como string/text, se convierte
            
            // Primero, cambiar el tipo de dato a JSON
            // En MySQL, JSON es un tipo nativo desde 5.7
            $table->json('bienes_vivienda')->nullable()->change();
            $table->json('otros_servicios')->nullable()->change();
            $table->json('habilidades')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            // Volver a cambiar a string/text si es necesario
            $table->text('bienes_vivienda')->nullable()->change();
            $table->text('otros_servicios')->nullable()->change();
            $table->text('habilidades')->nullable()->change();
        });
    }
};
